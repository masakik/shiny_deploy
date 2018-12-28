<?php

namespace ShinyDeploy\Action\WsDataAction;

use ShinyDeploy\Domain\DeploymentTasks;

class GetDeploymentTasks extends WsDataAction
{
    /**
     * Retrieves a list of available deployment tasks.
     *
     * @param array $actionPayload
     * @return bool
     * @throws \ShinyDeploy\Exceptions\InvalidTokenException
     */
    public function __invoke(array $actionPayload): bool
    {
        $this->authorize($this->clientId);

        $tasksDomain = new DeploymentTasks($this->config, $this->logger);
        $tasks = $tasksDomain->getAvailableTasks();
        $this->responder->setPayload($tasks);

        return true;
    }
}
