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
     */
    public function listAvailableTasks(): array
    {
        if (empty($this->config['deployment_tasks'])) {
            return  [];
        }

        $taskList = [];
        foreach ($this->config['deployment_tasks'] as $taskConfig) {
            array_push($taskList, [
                'type' => $taskConfig['type'],
                'name' => $taskConfig['name'],
            ]);
        }

        return $taskList;
    }

    /**
     * Initializes and returns all available tasks.
     *
     * @return array
     * @throws \ShinyDeploy\Exceptions\ShinyDeployException
     */
    public function getTasks(): array
    {
        $taskList = $this->listAvailableTasks();
        if (empty($taskList)) {
            return [];
        }

        $tasks = [];
        foreach ($taskList as $taskData) {
            $task = $this->taskFactory->make($taskData['type']);
            $task->subscribeToEvents();
            array_push($tasks, $task);
        }

        return $tasks;
    }
}
