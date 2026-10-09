<?php

namespace App\Http\Controllers;

use App\DTO\TaskDTO;
use App\DTO\TaskFilterDTO;
use App\Http\Requests\TaskFilterRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Interfaces\TaskInterface;
use App\Models\Task;
use App\Http\Requests\StoretaskRequest;
use App\Http\Requests\UpdatetaskRequest;
use TaskService;
use App\Http\Resources\TaskResource;
use http\Env\Response;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(
//        private readonly TaskInterface $taskInterface,
        private readonly TaskService $taskService
    )
    {

    }
    public function index(TaskFilterRequest $request)
    {
        $filters = TaskFilterDTO::fromRequest($request);

        $tasks = $this->taskService->getTaskFilter($filters);

        return response()->json([
                'data' => TaskResource::collection($tasks)
                ],200);
    }

    /**
     * Show the form for creating a new resource.
     */
//    public function create()
//    {
//        //request()->request
//    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoretaskRequest $request)
    {
        $taskDTO = TaskDTO::fromRequest($request->validated());

        $task = $this->taskService->createTask($taskDTO);

        return response()->json([
            'message' => 'create with success',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $task = $this->taskInterface->show($id);

        if(!$task){
            return response()->json([
                'message'=> 'Object not found'
            ], 404);
        }
        return response()->json([
            'data' => new TaskResource($task)
        ],200);

    }

    /**
     * Show the form for editing the specified resource.
     */
//    public function edit(task $task)
//    {
//        //
//    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatetaskRequest $request,int $id)
    {

        $validated = $request->validated();

        $taskDTO = TaskDTO::fromRequest($request->validated());

        $task = $this->taskService->update($taskDTO, $id);

        if($task){
            return response()->json([
                'message' => 'Task update with success',
                'data' => new TaskResource($task),
            ], 200);
        }

        return response()->json([
            'message' => 'Task not found'
        ], 404);
    }

    public function updateStatus(UpdateStatusRequest $request, int $id)
    {
        $taskDTO = TaskDTO::fromRequest($request->validated());

        $task = $this->taskService->updateTaskStatus($taskDTO, $id);

        if($task) {
            return response()->json([
                'message' => 'Task status update witch success',
                'data' => new TaskResource($task),
            ]);
        }
        return response()->json([
            'message' => 'Task not found'
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->taskService->deleteTask($id);
        return response()->json([
                'message' => 'delete successfully'
            ], 200);
        }

}
