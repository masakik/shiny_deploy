<?php

namespace ShinyDeploy\Domain\Deployment\Tasks;

abstract class Task implements TaskInterface
{
    protected $identifier = '';

    protected $name = '';

    public function __construct()
    {
        $this->setIdentifier();
        $this->setName();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
