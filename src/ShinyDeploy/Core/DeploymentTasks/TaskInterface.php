<?php

namespace ShinyDeploy\Core\DeploymentTasks;

interface TaskInterface
{
    /**
     * Retrieves identifier.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Sets a descriptive name for the task.
     *
     * @return void
     */
    public function provideName(): void;

    /**
     * Retrieves name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Subscribes the task to all events it needs to know about.
     */
    public function subscribeToEvents(): void;
}
