<?php

namespace App\Repository;

use App\DTO\TaskDTO;
use App\DTO\TaskStatusDTO;
use App\Models\Task;
use App\DTO\TaskFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function getFilterTask(TaskFilterDTO $filters): array | LengthAwarePaginator;

    public function show(int $id): ?Task;
    public function store(TaskDTO $taskDTO): Task;
    public function update(int $id, TaskDTO $updateTaskDTO): ?Task;
    public function updateStatus(Task $task, TaskStatusDTO $updateTaskStatusDTO): ?Task;

    public function destroy(int $id): void;
}
