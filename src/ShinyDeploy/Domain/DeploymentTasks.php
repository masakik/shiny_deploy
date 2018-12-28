<?php

namespace ShinyDeploy\Domain;

use ShinyDeploy\Core\Domain;

class DeploymentTasks extends Domain
{
    /**
     * Fetches a list of currently registered tasks.
     *
     * @return array List of task identifiers and names.
     */
    public function getAvailableTasks(): array
    {
        $taskConfig = $this->config['deployment_tasks'] ?? [];
        if (empty($taskConfig)) {
            return  [];
        }

        $taskList = [];
        foreach ($taskConfig as $taskClassName) {
            /** @var \ShinyDeploy\Core\DeploymentTasks\TaskInterface $task */
            $task = new $taskClassName;
            array_push($taskList, [
                'id' => $task->getIdentifier(),
                'name' => $task->getName(),
            ]);
        }

        return $taskList;
    }
}
