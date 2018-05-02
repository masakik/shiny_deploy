<?php

namespace ShinyDeploy\Domain\Deployment\Tasks;

use ShinyDeploy\Core\Domain;

class Tasks extends Domain
{
    /**
     * Fetches a list of currently registered tasks.
     *
     * @return array List of task identifiers.
     */
    public function getAvailableTasks(): array
    {
        $taskConfig = $this->config['tasks'] ?? [];
        if (empty($taskConfig)) {
            return  [];
        }

        $tasks = [];
        foreach ($taskConfig as $taskData) {
            /** @var TaskInterface $task */
            $task = new $taskData['class'];
            array_push($tasks, [
                'id' => $task->getIdentifier(),
                'name' => $task->getName(),
            ]);
        }

        return $tasks;
    }
}
