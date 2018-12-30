<?php

namespace ShinyDeploy\Core;

use Apix\Log\Logger;
use Noodlehaus\Config;

class EventManagerFactory
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
     * @var null $eventManager
     */
    private $eventManager = null;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Creates and returns instance of the event manager.
     *
     * @return EventManager
     */
    public function make(): EventManager
    {
        if ($this->eventManager !== null) {
            return $this->eventManager;
        }

        $this->eventManager = new EventManager;
        return $this->eventManager;
    }
}
