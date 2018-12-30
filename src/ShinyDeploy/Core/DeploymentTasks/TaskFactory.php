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
        if (!isset($this->config['deployment_tasks'][$type])) {
            throw new \InvalidArgumentException('Invalid task identifier. Task not found in config.');
        }

        $className = $this->config['deployment_tasks'][$type];
        /** @var \ShinyDeploy\Core\DeploymentTasks\TaskInterface $task */
        $task = new $className($type, $this->config, $this->logger, $this->eventManager);

        return $task;
    }
}
