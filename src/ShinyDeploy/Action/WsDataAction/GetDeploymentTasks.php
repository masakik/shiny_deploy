<?php

namespace ShinyDeploy\Action\WsDataAction;

use ShinyDeploy\Core\DeploymentTasks\TaskFactory;
use ShinyDeploy\Domain\DeploymentTasks;

class GetDeploymentTasks extends WsDataAction
{
    /**
     * Retrieves a list of available deployment tasks.
     *
     * @param array $actionPayload
     * @return bool
     * @throws \ShinyDeploy\Exceptions\InvalidTokenException
     * @throws \ShinyDeploy\Exceptions\ShinyDeployException
     */
    public function __invoke(array $actionPayload): bool
    {
        $this->authorize($this->clientId);

        $taskFactory = new TaskFactory($this->config, $this->logger, $this->eventManager);
        $tasksDomain = new DeploymentTasks($this->config, $this->logger, $taskFactory);
        $tasks = $tasksDomain->listAvailableTasks();
        $this->responder->setPayload($tasks);

        return true;
    }
}
