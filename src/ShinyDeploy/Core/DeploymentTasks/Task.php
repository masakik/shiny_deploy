<?php

namespace ShinyDeploy\Core\DeploymentTasks;

abstract class Task implements TaskInterface
{
    /**
     * @var string $identifier Unique identifier of the task.
     */
    protected $identifier = '';

    /**
     * @var string $name Descriptive name of the task.
     */
    protected $name = '';

    public function __construct()
    {
        $this->provideIdentifier();
        $this->provideName();
    }

    /**
     * Retrieves identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
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
