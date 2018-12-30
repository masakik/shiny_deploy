<?php

namespace ShinyDeploy\Domain;

use Apix\Log\Logger;
use Noodlehaus\Config;
use ShinyDeploy\Core\DeploymentTasks\TaskFactory;
use ShinyDeploy\Core\Domain;

class DeploymentTasks extends Domain
{
    /**
     * @var TaskFactory $taskFactory
     */
    protected $taskFactory;

    public function __construct(Config $config, Logger $logger, TaskFactory $taskFactory)
    {
        parent::__construct($config, $logger);
        $this->taskFactory = $taskFactory;
    }

    /**
     * Provides a list of currently registered tasks.
     *
     * @return array List of task identifiers and names.
     * @throws \ShinyDeploy\Exceptions\ShinyDeployException
     */
    public function listAvailableTasks(): array
    {
        $taskConfig = $this->config['deployment_tasks'] ?? [];
        if (empty($taskConfig)) {
            return  [];
        }

        $taskList = [];
        foreach ($taskConfig as $type => $taskClassName) {
            $task = $this->taskFactory->make($type);
            array_push($taskList, [
                'type' => $task->getType(),
                'name' => $task->getName(),
            ]);
            unset($task);
        }

        return $taskList;
    }
}
