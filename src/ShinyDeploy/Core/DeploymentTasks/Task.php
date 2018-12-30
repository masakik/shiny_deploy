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

    /**
     * @var string $name Descriptive name of the task.
     */
    protected $name = '';

    public function __construct(string $type, Config $config, Logger $logger, EventManager $eventManager)
    {
        $this->type = $type;
        $this->config = $config;
        $this->logger = $logger;
        $this->eventManager = $eventManager;

        $this->provideName();
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

    /**
     * Retrieves name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
