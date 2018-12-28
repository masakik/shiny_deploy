<?php

namespace ShinyDeploy\DeploymentTasks\SshCommand;

use ShinyDeploy\Core\DeploymentTasks\Task;

class SshCommand extends Task
{
    /**
     * @inheritdoc
     */
    public function provideIdentifier(): void
    {
        $this->identifier = 'ssh_command';
    }

    /**
     * @inheritdoc
     */
    public function provideName(): void
    {
        $this->name = 'Executes a command on the target server.';
    }
}
