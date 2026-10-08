<?php

namespace App\Interfaces;

use App\DTO\TaskDTO;
use App\Http\Requests\UpdatetaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

interface TaskInterface
{
    public function get(Request $request): array;
    public function show($id): Task;
    public function store(TaskDTO $taskDTO): Task;
    public function update(Task $task, TaskDTO $taskDTO): Task;

    public function updateStatus(Task $task, TaskDTO $taskDTO): Task;

    public function destroy(Task $task): void;
}
