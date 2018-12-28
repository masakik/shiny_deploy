<?php
namespace ShinyDeploy\Core;

use Apix\Log\Logger;
use Noodlehaus\Config;

class Action
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
}
