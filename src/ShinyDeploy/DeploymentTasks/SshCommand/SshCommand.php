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
        $this->eventManager->on('deploymentCompleted', [$this, 'runRemoteAfterDeployCommands']);
    }

    /**
     * Executes SSH commands on remote server as configured in deployment.
     *
     * @param Deployment $deployment
     */
    public function runRemoteAfterDeployCommands(Deployment $deployment): void
    {
        // @todo Skip execution if deployment is in list mode.

        $tasksConfigs = $this->getTaskConfigs($deployment);
        if (empty($tasksConfigs)) {
            return;
        }

        // @todo Run through task configurations and execute commands
    }
}
