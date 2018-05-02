<?php

namespace ShinyDeploy\Action\WsDataAction;

use ShinyDeploy\Domain\Deployment\Tasks\Tasks;

class GetTasks extends WsDataAction
{
    public function __invoke(array $actionPayload): bool
    {
        $this->authorize($this->clientId);

        $tasksDomain = new Tasks($this->config, $this->logger);
        $tasks = $tasksDomain->getAvailableTasks();
        $this->responder->setPayload($tasks);

        return true;
    }
}
