<?php

namespace ShinyDeploy\DeploymentTasks\SshCommand;

use ShinyDeploy\Core\DeploymentTasks\Task;
use ShinyDeploy\Domain\Deployment;

class SshCommand extends Task
{
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
     * @throws \ZMQException
     * @return void
     */
    public function runRemoteAfterDeployCommands(Deployment $deployment): void
    {
        // Skip task execution in list mode:
        if ($deployment->inListMode() === true) {
            return;
        }

        $tasksConfigs = $this->getTaskConfigs($deployment);
        if (empty($tasksConfigs)) {
            return;
        }

        /** @var \ShinyDeploy\Responder\WsLogResponder|\ShinyDeploy\Responder\NullResponder $responder */
        $responder = $deployment->getLogResponder();
        /** @var \ShinyDeploy\Domain\Server\SshServer $server */
        $server = $deployment->getServer();

        // skip if server does not allow ssh commands:
        if ($server->getType() !== 'ssh') {
            $responder->danger('Server is not of type ssh. Skipping task execution.');
            return;
        }

        // Execute SSH commands on target server:
        foreach ($tasksConfigs as $tasksConfig) {
            $command = trim($tasksConfig['arguments']);
            $responder->info('Executing task: ' . $tasksConfig['name']);
            $response = $server->executeCommand($command);
            if ($response === false) {
                $responder->danger('Task failed.');
            } else {
                $responder->log($response);
            }
        }
    }
}
