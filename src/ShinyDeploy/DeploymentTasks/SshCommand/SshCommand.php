<?php

namespace ShinyDeploy\DeploymentTasks\SshCommand;

use ShinyDeploy\Core\DeploymentTasks\Task;
use ShinyDeploy\Domain\Deployment;

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
        $this->name = 'SSH Command';
    }

    /**
     * @inheritdoc
     */
    public function subscribeToEvents(): void
    {
        $this->eventManager->on('deploymentStarted', function (Deployment $deployment) {
            // ...
        });
    }
}
