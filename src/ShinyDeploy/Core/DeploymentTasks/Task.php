<?php

namespace ShinyDeploy\Core\DeploymentTasks;

use Apix\Log\Logger;
use Noodlehaus\Config;
use ShinyDeploy\Core\EventManager;

abstract class Task implements TaskInterface
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

    /**
     * @var string $type Unique type/identifier of the task.
     */
    protected $type = '';

    public function __construct(Config $config, Logger $logger, EventManager $eventManager, string $type)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->type = $type;
    }

    /**
     * Retrieves type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
