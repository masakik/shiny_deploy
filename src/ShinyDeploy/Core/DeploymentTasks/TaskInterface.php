<?php

namespace ShinyDeploy\Core\DeploymentTasks;

interface TaskInterface
{
    /**
     * Sets a unique identifier for the task.
     *
     * @return void
     */
    public function provideIdentifier(): void;

    /**
     * Retrieves identifier.
     *
     * @return string
     */
    public function getIdentifier(): string;

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
}
