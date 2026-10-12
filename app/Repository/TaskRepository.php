<?php

namespace App\Repository;

use App\DTO\TaskDTO;
use App\DTO\TaskStatusDTO;
use App\Models\Task;
use App\DTO\TaskFilterDTO;

class TaskRepository implements TaskRepositoryInterface
{

    public function getFilterTask(TaskFilterDTO $filters): array
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

        return $query->get()->all();
    }


    public function show(int $id): ?Task
    {
        return task::find($id);

    }

    public function store(TaskDTO $taskDTO): Task
    {
        return task::create($taskDTO->toArray());
    }

    public function update(int $id, TaskDTO $updateTaskDTO): ?Task
    {
        $task = task::find($id);
        if($task){
            $task->update($updateTaskDTO->toArray());
            return $task->fresh();
        }
        return null;
    }

    public function updateStatus(Task $task, TaskStatusDTO $updateTaskStatusDTO): Task
    {
        $task->update($updateTaskStatusDTO->toArray());
        return $task->fresh();
    }

    public function destroy(int $id): void
    {
        $task = $this->show($id);
        $task->delete();
    }
}
