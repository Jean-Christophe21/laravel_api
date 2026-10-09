<?php

namespace App\Repository;

use App\DTO\TaskDTO;
use App\Http\Requests\UpdatetaskRequest;
use App\Interfaces\TaskInterface;
use App\Models\Task;
use Illuminate\Http\Request;
use App\DTO\TaskFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskInterface
{

    public function getFilterTask(TaskFilterDTO $filters): array|LengthAwarePaginator
    {
        $per_page = (int)$filters->per_page;

        $query = task::query()
            ->when($filters->status !== null,
            fn($query) => $query->where('status', $filters->status
            ))
            ->when(
                    $filters->priority !== null,
                    fn ($query) => $query->where('priority', $filters->priority)
            )
            ->when(
                    $filters->title !== null,
                    fn ($query) => $query->where('title',  $filters->title)
            );

        if($filters->page !==null){
            return $query->paginate($per_page)->items();
        }

        return $query->get()->toArray();
    }


    public function show(int $id): Task
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
