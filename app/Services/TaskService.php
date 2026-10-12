<?php

namespace App\Services;

use App\DTO\TaskDTO;
use App\DTO\TaskStatusDTO;
use App\Models\Task;
use App\DTO\TaskFilterDTO;
use App\Repository\TaskRepositoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskServiceInterface
    ){}

    public function getTaskFilter(TaskFilterDTO $filters): array
    {
        return $this->taskServiceInterface->getFilterTask($filters);
    }

    public function createTask(TaskDTO $taskDTO): Task
    {
        return $this->taskServiceInterface->store($taskDTO);
    }

    public function getById(int $id): ?Task
    {
        return $this->taskServiceInterface->show($id);
    }

    public function updateTaskFromTaskDTO(TaskDTO $updateTaskFields, int $id):?Task
    {
        $task = $this->taskServiceInterface->update($id, $updateTaskFields);
        return $task;
    }

    public function updateTaskStatus(TaskStatusDTO $updateTaskStatus, int $id): ?Task
    {
        $task = $this->taskServiceInterface->show($id);
        if($task){
            $this->taskServiceInterface->updateStatus($task, $updateTaskStatus);
            return $task->refresh();
        }
        return null;
    }

    public function deleteTask(int $id): void
    {
        $task = $this->taskServiceInterface->show($id);

        if($task){
            $this->taskServiceInterface->destroy($task->id);
        }
        else{
            throw new ResourceNotFoundException("Task Not found");
        }
    }
}
