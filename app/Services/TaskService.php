<?php

use App\Models\Task;
use App\Repository\TaskRepository;
use App\DTO\TaskDTO;
use App\Interfaces\TaskInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use \App\DTO\TaskFilterDTO;


class TaskService
{
    public function __construct(
        private readonly TaskInterface $taskInterface
    ){}

    public function getTaskFilter(TaskFilterDTO $filters): array
    {
        return $this->taskInterface->getFilterTask($filters);
    }

    public function createTask(TaskDTO $taskDTO)
    {
        return $this->taskInterface->store($taskDTO);
    }

    public function getById(int $id): Task
    {
        return $this->taskInterface->show($id);
    }

    public function updateTaskFromTaskDTO(TaskDTO $taskDTO, int $id)
    {
        $task = $this->taskInterface->show($id);
        if($task){
            $this->taskInterface->update($task, $taskDTO);
            return $task->refresh();
        }
        return null;
    }

    public function updateTaskStatus(TaskDTO $taskDTO, int $id)
    {
        $task = $this->taskInterface->show($id);
        if($task){
            $this->taskInterface->updateStatus($task, $taskDTO);
            return $task->refresh();
        }
        return null;
    }

    public function deleteTask(int $id)
    {
        $task = $this->taskInterface->show($id);

        if($task){
            $this->taskInterface->destroy($task);
        }
    }
}
