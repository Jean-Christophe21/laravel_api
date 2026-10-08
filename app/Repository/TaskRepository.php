<?php

namespace App\Repository;

use App\DTO\TaskDTO;
use App\Http\Requests\UpdatetaskRequest;
use App\Interfaces\TaskInterface;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskRepository implements TaskInterface
{

    public function get(Request $request): array
    {
        $per_page = (int)$request->query('per_page', 10);

        $query = task::query();
        if($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if($request->filled('title')){
            $query->where('title', $request->query('title'));
        }

        if($request->filled('page')){
            return $query->paginate($per_page);
        }

        return $query->get();
    }


    public function show($id): Task
    {
        return task::find($id);
    }

    public function store(TaskDTO $taskDTO): Task
    {
        $task = task::create($taskDTO);
        return  $task;
    }

    public function update(Task $task, TaskDTO $taskDTO): Task
    {
        $task->update($taskDTO->toArray());
        return $task->fresh();
    }

    public function updateStatus(Task $task, TaskDTO $taskDTO): Task
    {
        $task->update($taskDTO->toArray());
        return $task->fresh();
    }

    public function destroy(Task $task): void
    {
        $task->delete();
    }
}
