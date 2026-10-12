<?php

namespace App\Services;

use App\DTO\TaskDTO;
use App\DTO\TaskFilterDTO;
use App\DTO\TaskStatusDTO;
use App\Models\Task;

interface TaskServiceInterface
{
    public function getTaskFilter(TaskFilterDTO $filters): array;

    public function createTask(TaskDTO $taskDTO): Task;

    public function getById(int $id): ?Task;

    public function updateTaskFromTaskDTO(TaskDTO $updateTaskFields, int $id):?Task;

    public function updateTaskStatus(TaskStatusDTO $updateTaskStatus, int $id): ?Task;

    public function deleteTask(int $id): void;

}
