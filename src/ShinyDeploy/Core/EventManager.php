<?php

namespace ShinyDeploy\Core;

class EventManager
{
    /**
     * @var array $listeners
     */
    protected $listeners = [];

    /**
     * Adds new event listener.
     *
     * @param string $eventName
     * @param callable $listener
     * @return void
     */
    public function on(string $eventName, callable $listener): void
    {
        if (empty($eventName)) {
            throw new \InvalidArgumentException('EventName can not be empty.');
        }

        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        array_push($this->listeners[$eventName], $listener);
    }

    /**
     * Removes single event listener.
     *
     * @param string $eventName
     * @param callable $listener
     * @return void
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        if (empty($eventName)) {
            throw new \InvalidArgumentException('EventName can not be empty.');
        }
        if (!isset($this->listeners[$eventName])) {
            return;
        }
        $index = array_search($listener, $this->listeners[$eventName], true);
        if ($index === false) {
            return;
        }
        unset($this->listeners[$eventName][$index]);
    }

    /**
     * Removes all event listeners.
     *
     * @param string $eventName Will only remove listeners for this event if provided.
     */
    public function removeAllListeners(string $eventName = ''): void
    {
        if (!empty($eventName)) {
            unset($this->listeners[$eventName]);
            return;
        }
        $this->listeners = [];
    }

    /**
     * Emit an event. (Calls all listeners listening to this specific event)
     *
     * @param string $eventName
     * @param array $arguments
     * @return void
     */
    public function emit(string $eventName, array $arguments = []): void
    {
        if (empty($eventName)) {
            throw new \InvalidArgumentException('EventName can not be empty.');
        }

        if (empty($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            call_user_func_array($listener, $arguments);
        }
    }
}
