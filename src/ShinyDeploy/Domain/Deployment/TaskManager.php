<?php

namespace ShinyDeploy\Domain\Deployment;

use Apix\Log\Logger;
use League\Event\EmitterInterface;
use League\Event\EmitterTrait;
use League\Event\Event;
use Noodlehaus\Config;
use ShinyDeploy\Core\Domain;
use ShinyDeploy\Domain\Server\SshServer;

class TaskManager extends Domain
{
    // Task manager supports events:
    use EmitterTrait;

    protected $selectedTasks = [];

    protected $tasks = [];

    public function __construct(Config $config, Logger $logger, EmitterInterface $emitter)
    {
        parent::__construct($config, $logger);
        $this->setEmitter($emitter);
        $this->registerListener();
    }

    /**
     * Adds all required listeners.
     *
     * @return void
     */
    public function registerListener(): void
    {
        $this->addListener('deployment.onStart', [$this, 'onDeploymentStart']);
        $this->addListener('deployment.onAfterLocalRepoPrepared', [$this, 'onAfterLocalRepoPrepared']);
        $this->addListener('deployment.onAfterDeploymentCompleted', [$this, 'onAfterDeploymentCompleted']);
    }

    /**
     * Prepares all tasks when deployment is started.
     *
     * @param Event $event
     * @param Deployment $deployment
     * @return void
     */
    public function onDeploymentStart(Event $event, Deployment $deployment): void
    {
        // if deployment is in list mode, there is nothing to do
        if ($deployment->inListMode() === true) {
            return;
        }

        // Prepare/Filter tasks that need to be executed during deployment
        $this->setTasks($deployment);
    }

    /**
     * Executes tasks that should run before deployment.
     *
     * @param Event $event
     * @param Deployment $deployment
     * @throws \ZMQException
     * @return void
     */
    public function onAfterLocalRepoPrepared(Event $event, Deployment $deployment): void
    {
        // if deployment is in list mode, there is nothing to do
        if ($deployment->inListMode() === true) {
            return;
        }

        $deployment->logResponder->log('Running tasks...');
        $this->runTasks($deployment, 'before');
    }

    /**
     * Executes tasks that should run after deployment.
     *
     * @param Event $event
     * @param Deployment $deployment
     * @throws \ZMQException
     * @return void
     */
    public function onAfterDeploymentCompleted(Event $event, Deployment $deployment): void
    {
        // if deployment is in list mode, there is nothing to do
        if ($deployment->inListMode() === true) {
            return;
        }

        $deployment->logResponder->log('Running tasks...');
        $this->runTasks($deployment, 'after');
    }

    /**
     * Sets a set of tasks-filters that were selected manually.
     *
     * @param array $selectedTasks
     * @return void
     */
    public function setSelectedTasks(array $selectedTasks): void
    {
        $this->selectedTasks = $selectedTasks;
    }

    /**
     * Estimates which tasks need to be executed during deployment.
     *
     * @param Deployment $deployment
     * @return bool
     */
    private function setTasks(Deployment $deployment): bool
    {
        if (!isset($deployment->data['tasks'])) {
            return false;
        }

        $tasks = $deployment->data['tasks'];
        if (empty($tasks)) {
            return false;
        }

        $this->tasks = $tasks;

        // check if we need to filter out some tasks:
        if (empty($this->selectedTasks)) {
            $this->filterNonDefaultTasks();
        } else {
            $this->filterNonSelectedTasks();
        }

        return true;
    }

    /**
     * Removes tasks from task-list not enabled by default.
     *
     * @return bool
     */
    private function filterNonDefaultTasks(): bool
    {
        foreach ($this->tasks as $i => $task) {
            if ((int) $task['run_by_default'] !== 1) {
                unset($this->tasks[$i]);
            }
        }
        array_merge($this->tasks, []);
        return true;
    }

    /**
     * Removes tasks from task-list not enabled/selected in GUI.
     *
     * @return bool
     */
    private function filterNonSelectedTasks(): bool
    {
        // noting to do if task-filter is empty
        if (empty($this->selectedTasks)) {
            return true;
        }

        // collect task-ids to remove
        $tasksToRemove = [];
        foreach ($this->selectedTasks as $taskId => $taskEnabled) {
            if ((int)$taskEnabled === 1) {
                continue;
            }
            array_push($tasksToRemove, $taskId);
        }

        // remove tasks
        foreach ($this->tasks as $i => $task) {
            if (in_array($task['id'], $tasksToRemove)) {
                unset($this->tasks[$i]);
            }
        }
        array_merge($this->tasks, []);
        return true;
    }

    /**
     * Runs user defined tasks on target server.
     *
     * @param Deployment $deployment
     * @param string $type
     * @return boolean
     * @throws \ZMQException
     */
    private function runTasks(Deployment $deployment, string $type) : bool
    {
        // Skip if no tasks defined
        if (empty($this->tasks)) {
            return true;
        }

        // Skip if no tasks of given type defined:
        $typeTasks = [];
        foreach ($this->tasks as $task) {
            if ($task['type'] === $type) {
                array_push($typeTasks, $task);
            }
        }
        if (empty($typeTasks)) {
            return true;
        }

        /** @var SshServer $server */
        $server = $deployment->getServer();

        // Skip if server is not ssh capable:
        if ($server->getType() !== 'ssh') {
            $deployment->logResponder->danger('Server not of type SSH. Skipping tasks.');
            return false;
        }

        // Execute tasks on server:
        $remotePath = $deployment->getRemotePath();
        foreach ($typeTasks as $task) {
            $command = 'cd ' . $remotePath . ' && ' . $task['command'];
            $deployment->logResponder->info('Executing task: ' . $task['name']);
            $response = $server->executeCommand($command);
            if ($response === false) {
                $deployment->logResponder->danger('Task failed.');
            } else {
                $deployment->logResponder->log($response);
            }
        }

        return true;
    }
}
