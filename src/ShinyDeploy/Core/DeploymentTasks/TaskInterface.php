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
     * Subscribes the task to all events it needs to know about.
     */
    public function subscribeToEvents(): void;
}
