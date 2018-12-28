<?php

namespace ShinyDeploy\Core;

class EventManagerFactory
{
    /**
     * @var null $eventManager
     */
    private $eventManager = null;

    /**
     * Creates and returns instance of the event manager.
     *
     * @return EventManager
     */
    public function make(): EventManager
    {
        if ($this->eventManager === null) {
            $this->eventManager = new EventManager;
            // @todo Register event listeners
        }
        return $this->eventManager;
    }
}
