<?php

namespace ShinyDeploy\Core\DeploymentTasks;

use Apix\Log\Logger;
use Noodlehaus\Config;
use ShinyDeploy\Core\EventManager;
use ShinyDeploy\Domain\Deployment;

abstract class Task implements TaskInterface
{
    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var EventManager $eventManager
     */
    protected $eventManager;

    /**
     * @var string $type Unique type/identifier of the task.
     */
    protected $type = '';

    public function __construct(Config $config, Logger $logger, EventManager $eventManager, string $type)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->type = $type;
    }

    /**
     * Retrieves type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Filters task configuration data from deployment which is relevant for the current task and deployment.
     *
     * @param Deployment $deployment
     * @return array
     */
    public function getTaskConfigs(Deployment $deployment): array
    {
        $taskData = $deployment->getTaskData();
        if (empty($taskData)) {
            return [];
        }

        // filter task configurations by type:
        $taskData = $this->filterTasksOfMatchingType($taskData);
        if (empty($taskData)) {
            return [];
        }

        if ($deployment->getInitiator() === 'gui') {
            $taskData = $this->filterSelectedTasks($taskData, $deployment->getSelectedTasks());
        } else {
            $taskData = $this->filterDefaultTasks($taskData);
        }

        return $taskData;
    }

    /**
     * Returns only those task-configurations from deployment that match type of current task-object.
     *
     * @param array $taskData
     * @return array
     */
    protected function filterTasksOfMatchingType(array $taskData): array
    {
        $matchingTasks = [];
        foreach ($taskData as $item) {
            if ($item['type'] !== $this->type) {
                continue;
            }
            array_push($matchingTasks, $item);
        }

        return $matchingTasks;
    }

    /**
     * Returns only task-configurations that are selected by user via GUI.
     *
     * @param array $taskData
     * @param array $selectedTasks
     * @return array
     */
    protected function filterSelectedTasks(array $taskData, array $selectedTasks): array
    {
        if (empty($selectedTasks)) {
            return [];
        }

        // build task index-id map:
        $taskMap = [];
        foreach ($taskData as $i => $item) {
            $taskMap[$item['id']] = $i;
        }

        // filter selected tasks:
        $filteredTasks = [];
        foreach ($selectedTasks as $taskId => $taskEnabled) {
            if (empty($taskEnabled)) {
                continue;
            }
            if (!isset($taskMap[$taskId])) {
                continue;
            }
            $taskIndex = $taskMap[$taskId];
            array_push($filteredTasks, $taskData[$taskIndex]);
        }

        return $filteredTasks;
    }

    /**
     * Returns only task-configurations of tasks which are enabled by default.
     *
     * @param array $taskData
     * @return array
     */
    protected function filterDefaultTasks(array $taskData): array
    {
        $filteredTasks = [];
        foreach ($taskData as $item) {
            if (empty($item['run_by_default'])) {
                continue;
            }
            array_push($filteredTasks, $item);
        }
        return $filteredTasks;
    }
}
