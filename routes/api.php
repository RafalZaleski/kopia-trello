<?php

use App\Modules\ToDoList\Boards\Controllers\BoardController;
use App\Modules\ToDoList\Catalogs\Controllers\CatalogController;
use App\Modules\ToDoList\Comments\Controllers\CommentController;
use App\Modules\ToDoList\Tasks\Controllers\TaskController;
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

Route::get('/get-boards-all', [BoardController::class, 'indexAll']);
Route::get('/boards/sync-updated', [BoardController::class, 'syncUpdated']);
Route::get('/boards/sync-deleted', [BoardController::class, 'syncDeleted']);
Route::resource('boards', BoardController::class)->except(['create', 'edit']);

Route::get('/get-catalogs-all', [CatalogController::class, 'indexAll']);
Route::get('/catalogs/sync-updated', [CatalogController::class, 'syncUpdated']);
Route::get('/catalogs/sync-deleted', [CatalogController::class, 'syncDeleted']);
Route::resource('catalogs', CatalogController::class)->except(['create', 'edit']);

Route::get('/get-tasks-all', [TaskController::class, 'indexAll']);
Route::get('/tasks/sync-updated', [TaskController::class, 'syncUpdated']);
Route::get('/tasks/sync-deleted', [TaskController::class, 'syncDeleted']);
Route::resource('tasks', TaskController::class)->except(['create', 'edit']);
Route::post('/tasks/{task}/addAttachment', [TaskController::class, 'addAttachment']);
Route::delete('/tasks/{task}/deleteAttachment/{id}', [TaskController::class, 'removeAttachment']);

Route::get('/get-comments-all', [CommentController::class, 'indexAll']);
Route::get('/comments/sync-updated', [CommentController::class, 'syncUpdated']);
Route::get('/comments/sync-deleted', [CommentController::class, 'syncDeleted']);
Route::resource('comments', CommentController::class)->except(['create', 'edit']);
Route::post('/comments/{comment}/addAttachment', [CommentController::class, 'addAttachment']);
Route::delete('/comments/{comment}/deleteAttachment/{id}', [CommentController::class, 'removeAttachment']);
