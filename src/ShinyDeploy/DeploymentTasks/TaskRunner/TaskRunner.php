<?php

namespace ShinyDeploy\DeploymentTasks\TaskRunner;

use ShinyDeploy\Core\DeploymentTasks\Task;
use ShinyDeploy\Domain\Deployment;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class TaskRunner extends Task
{
    /**
     * @var Deployment $deployment
     */
    protected $deployment;

    /**
     * @var \ShinyDeploy\Domain\Repository $repository
     */
    protected $repository;

    /**
     * @var \ShinyDeploy\Responder\WsLogResponder|\ShinyDeploy\Responder\NullResponder $responder
     */
    protected $responder;

    /**
     * @inheritdoc
     */
    public function subscribeToEvents(): void
    {
        $this->eventManager->on('deploymentChangedFilesSorted', [$this, 'runTasks']);
    }

    /**
     * Runs all "Tasks" configured for current deployment.
     *
     * @param Deployment $deployment
     * @throws \ZMQException
     */
    public function runTasks(Deployment $deployment): void
    {
        // Skip execution if deployment is in list mode
        if ($deployment->inListMode()) {
            return;
        }

        $tasksConfigs = $this->getTaskConfigs($deployment);
        if (empty($tasksConfigs)) {
            return;
        }

        $this->deployment = $deployment;
        $this->responder = $deployment->getLogResponder();
        $this->repository = $deployment->getRepository();

        // Loop through task and execute them
        foreach ($tasksConfigs as $taskConfigData) {
            $this->responder->info('Executing task: ' . $taskConfigData['name']);
            try {
                $tasksConfig = Yaml::parse($taskConfigData['arguments']);
            } catch (ParseException $e) {
                $this->responder->danger('Skipping task. Invalid configuration.');
                continue;
            }
            if ($this->taskConfigIsValid($tasksConfig) === false) {
                $this->responder->danger('Skipping task. Invalid configuration.');
                continue;
            }

            $this->handleTask($tasksConfig);
        }
    }

    /**
     * Executes a single task as configured in deployment.
     *
     * @param array $taskConfig
     * @return void
     */
    protected function handleTask(array $taskConfig): void
    {
        if ($this->conditionsMatch($taskConfig['if'])) {
            $this->handleTaskConsequents($taskConfig['then']);
        }
    }

    /**
     * Checks if all the conditions configured for a task are true.
     *
     * @param array $conditions
     * @return bool
     */
    protected function conditionsMatch(array $conditions): bool
    {
        // if there are no conditions we can exit here
        if (empty($conditions)) {
            return true;
        }

        // Loop through conditions and check of they match
        foreach ($conditions as $conditionType => $conditionArguments) {
            if ($this->checkCondition($conditionType, $conditionArguments) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if a single task-condition matches.
     *
     * @param string $type
     * @param $arguments
     * @return bool
     */
    protected function checkCondition(string $type, $arguments): bool
    {
        switch ($type) {
            case 'changelist_match':
                return $this->checkChangelistMatchCondition($arguments);
            default:
                $this->logger->warning('Unknown condition type in task.');
                return false;
        }
    }

    /**
     * Checks if a file in the changelist matches the given pattern.
     *
     * @param string $pattern
     * @return bool
     */
    protected function checkChangelistMatchCondition(string $pattern): bool
    {
        $changedFiles = $this->deployment->getChangedFiles();

        // if there are no changed files condition can never match:
        if (empty($changedFiles)) {
            return false;
        }

        // merge all changed files into one array:
        if (isset($changedFiles['upload']) || isset($changedFiles['delete'])) {
            $changedFilesCombined = [];
            if (!empty($changedFiles['upload'])) {
                $changedFilesCombined = array_merge($changedFilesCombined, $changedFiles['upload']);
            }
            if (!empty($changedFiles['delete'])) {
                $changedFilesCombined = array_merge($changedFilesCombined, $changedFiles['delete']);
            }
            $changedFiles = $changedFilesCombined;
        }

        // check if pattern matches one of the changed files:
        foreach ($changedFiles as $file) {
            if (preg_match($pattern, $file) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Loops through all task-actions and executes them.
     *
     * @param array $consequents
     * @return void
     */
    protected function handleTaskConsequents(array $consequents): void
    {
        // if there are no consequents there is nothing to do here
        if (empty($consequents)) {
            return;
        }

        // loop through consequents and execute them
        foreach ($consequents as $action => $arguments) {
            $this->runTaskAction($action, $arguments);
        }
    }

    /**
     * Runs task action (if known).
     *
     * @param string $action
     * @param $arguments
     * @return void
     */
    protected function runTaskAction(string $action, $arguments): void
    {
        switch ($action) {
            case 'exec':
                $this->runActionExec($arguments);
                break;
            case 'upload':
                $this->runActionUpload($arguments);
                break;
            default:
                $this->logger->warning('Invalid task action.');
        }
    }

    /**
     * Runs task-action of type "exec" which executes a command on the deployment server.
     *
     * @param array $arguments
     * @return void
     */
    protected function runActionExec(array $arguments): void
    {
        if (empty($arguments)) {
            return;
        }

        $repoDir = $this->repository->getLocalPath();
        $repoDir = rtrim($repoDir, '/');
        foreach ($arguments as $command) {
            $command = str_replace('{$repo_dir}', $repoDir, $command);
            exec($command, $output, $exitCode);
            if ($exitCode !== 0) {
                $this->logger->warning('Deployment task action exited with non 0 code.');
                $this->logger->debug('Exec output: ' . print_r($output, true));
            }
        }
    }

    /**
     * Runs task-actions of type "upload". This action adds files to the list of files to upload for the
     * current deployment.
     *
     * @param array $arguments
     * @return void
     */
    protected function runActionUpload(array $arguments): void
    {
        if (empty($arguments)) {
            return;
        }

        $changedFiles = $this->deployment->getChangedFiles();
        if (!isset($changedFiles['upload'])) {
            $changedFiles['upload'] = [];
        }

        $repoDir = $this->repository->getLocalPath();
        $repoDir = rtrim($repoDir, '/');
        $filesToUpload = [];
        foreach ($arguments as $pathRelative) {
            $pathAbsolute = $repoDir . '/' . $pathRelative;
            // if path is neither file or folder we can skip it
            if (!file_exists($pathAbsolute)) {
                $this->logger->warning('File to upload does not exists in repository path.');
                continue;
            }

            // if path is a file we add it to the list of files to upload
            if (is_file($pathAbsolute)) {
                array_push($filesToUpload, $pathRelative);
                continue;
            }

            // if path is a folder we collect files from that folder and add them to list of files to upload:
            $files = $this->listFilesInDirectory($pathAbsolute);
            $filesToUpload = array_merge($filesToUpload, $files);
        }

        // add new files to list of changed files in current deployment:
        $changedFiles['upload'] = array_merge($changedFiles['upload'], $filesToUpload);
        $this->deployment->setChangedFiles($changedFiles);
    }

    /**
     * Recursively collect list of all files within given folder.
     *
     * @param string $pathToDirectory
     * @return array
     */
    private function listFilesInDirectory(string $pathToDirectory): array
    {
        $dirIterator = new \RecursiveDirectoryIterator($pathToDirectory);
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        $repoDir = $this->repository->getLocalPath();
        $repoDir = rtrim($repoDir, '/');
        $files = [];
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            $pathAbsolute = $file->getPathname();
            $pathRelative = str_replace($repoDir, '', $pathAbsolute);
            $pathRelative = trim($pathRelative, '/');
            array_push($files, $pathRelative);
        }

        return $files;
    }

    /**
     * Verifies that task configuration includes all required blocks.
     *
     * @param array $taskConfig
     * @return bool
     */
    protected function taskConfigIsValid(array $taskConfig): bool
    {
        if (!isset($taskConfig['if'])) {
            return false;
        }
        if (!isset($taskConfig['then'])) {
            return false;
        }
        return true;
    }
}
