<?php

use App\Modules\ToDoList\Tasks\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/get-tasks-all', [TaskController::class, 'indexAll']);
Route::get('/tasks/sync-updated', [TaskController::class, 'syncUpdated']);
Route::get('/tasks/sync-deleted', [TaskController::class, 'syncDeleted']);
Route::resource('tasks', TaskController::class)->except(['create', 'edit']);
