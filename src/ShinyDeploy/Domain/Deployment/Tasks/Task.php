<?php

namespace ShinyDeploy\Domain\Deployment\Tasks;

abstract class Task implements TaskInterface
{
    protected $identifier = '';

    public function __construct()
    {
        $this->setIdentifier();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
