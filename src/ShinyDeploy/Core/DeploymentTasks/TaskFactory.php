<?php

namespace ShinyDeploy\Core\DeploymentTasks;

use Apix\Log\Logger;
use Noodlehaus\Config;
use ShinyDeploy\Core\EventManager;
use ShinyDeploy\Exceptions\ShinyDeployException;

class TaskFactory
{
    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var EventManager $eventManager
     */
    protected $eventManager;

    public function __construct(Config $config, Logger $logger, EventManager $eventManager)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
    }

    /**
     * Creates deployment task of given "type".
     *
     * @param string $type
     * @return TaskInterface
     * @throws ShinyDeployException
     */
    public function make(string $type): TaskInterface
    {
        if (!isset($this->config['deployment_tasks'])) {
            throw new ShinyDeployException('Section deployment_tasks is missing in config file.');
        }

        $taskConfig = [];
        foreach ($this->config['deployment_tasks'] as $dtConfig) {
            if ($dtConfig['type'] === $type) {
                $taskConfig = $dtConfig;
                break;
            }
        }
        unset($dtConfig);

        if (empty($taskConfig)) {
            throw new \InvalidArgumentException('Invalid task identifier. Task not found in config.');
        }

        $className = $taskConfig['class'];
        /** @var \ShinyDeploy\Core\DeploymentTasks\TaskInterface $task */
        $task = new $className($this->config, $this->logger, $this->eventManager, $type);

        return $task;
    }
}
