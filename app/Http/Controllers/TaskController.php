<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStatusRequest;
use App\Models\Task;
use App\Http\Requests\StoretaskRequest;
use App\Http\Requests\UpdatetaskRequest;
use http\Env\Response;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
            return response()->json($query->paginate($per_page),200);
        }



        return response()->json([
                'data' => $query->get()
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
        //validation de la requête
        $validated = $request->validated();
        $task = task::create($validated);
        return response()->json([
            'message' => 'create with success',
            'data' => $task,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $task = task::find($id);
        if(!$task){
            return response()->json([
                'message'=> 'Object not found'
            ], 404);
        }
        return response()->json([
            'data' => $task
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
    public function update(UpdatetaskRequest $request, Task $task)
    {
        $validated = $request->validated();
        $task->update($validated);

        return response()->json([
                'message' => 'Task update with success',
                'data' => $task,
            ], 200);
    }

    public function updateStatus(UpdateStatusRequest $request, Task $task)
    {
        $validated = $request->validated();
        $task->update([
            'status' => $validated['status'],
        ]);

        $task->refresh();

        return response()->json([
            'message' => 'Task status update witch success',
            'data' => [
                'id' => $task->id,
                'status' => $task->status,
                'updated at' => $task->updated_at,
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json([
                'message' => 'delete successfully'
            ], 200);
        }

}
