<?php

namespace App\Interfaces;

use App\DTO\TaskDTO;
use App\Http\Requests\UpdatetaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use App\DTO\TaskFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskInterface
{
    public function getFilterTask(TaskFilterDTO $filters): array | LengthAwarePaginator;

    public function show(int $id): Task;
    public function store(TaskDTO $taskDTO): Task;
    public function update(Task $task, TaskDTO $taskDTO): Task;

    public function updateStatus(Task $task, TaskDTO $taskDTO): Task;

    public function destroy(Task $task): void;
}
