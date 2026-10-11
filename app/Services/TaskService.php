<?php

namespace App\Services;

use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\UpdatetaskRequest;
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

    public function updateTaskFromTaskDTO(UpdatetaskRequest $request, int $id)
    {
        $task = $this->taskInterface->show($id);
        if($task){
            $this->taskInterface->update($task, $request);
            return $task->refresh();
        }
        return null;
    }

    public function updateTaskStatus(UpdateStatusRequest $request, int $id)
    {
        $task = $this->taskInterface->show($id);
        if($task){
            $this->taskInterface->updateStatus($task, $request);
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
