<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\TaskController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function(){
    Route::get('tasks',[TaskController::class, 'index']);
    Route::get('tasks/{id}', [TaskController::class, 'show']);

    Route::post('tasks', [TaskController::class, 'store']);
    Route::match(['put', 'patch'], 'tasks/{id}', [TaskController::class, 'update']);
    Route::patch('tasks/{id}/status',[TaskController::class, 'updateStatus']);
    Route::delete('tasks/{id}', [TaskController::class, 'destroy']);
});
