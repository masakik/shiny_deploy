<?php

namespace ShinyDeploy\Domain\Deployment\Tasks;

class TaskSshCommand extends Task
{
    public function setIdentifier(): void
    {
        $this->identifier = 'ssh_command';
    }

    public function setName(): void
    {
        $this->name = "SSH Command";
    }
}
