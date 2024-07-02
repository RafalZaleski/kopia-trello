<?php

use App\Modules\Assets\Sync\Controllers\SyncController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Friends\Controllers\FriendsController;
use App\Modules\ToDoList\Boards\Controllers\BoardController;
use App\Modules\ToDoList\Catalogs\Controllers\CatalogController;
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

Route::get('/isLogged', function (Request $request) {
    return response()->json(is_null($request->user()) ? false : true);
});

Route::middleware(['guest'])->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    Route::get('/friends/get-all', [FriendsController::class, 'indexAll']);
    Route::get('/friends', [FriendsController::class, 'index']);
    Route::get('/friends/{userId}', [FriendsController::class, 'show']);
    Route::post('/friends/invite', [FriendsController::class, 'invite']);
    Route::patch('/friends/{userId}', [FriendsController::class, 'confirm']);
    Route::delete('/friends/{userId}', [FriendsController::class, 'destroy']);

    Route::get('/sync', [SyncController::class, 'sync']);

    Route::get('/get-boards-all', [BoardController::class, 'indexAll']);
    Route::get('/boards/{board}/copy', [BoardController::class, 'copy']);
    Route::get('/boards/{board}/share/{userId}', [BoardController::class, 'share']);
    Route::resource('boards', BoardController::class)->except(['create', 'edit']);
    
    Route::resource('catalogs', CatalogController::class)->except(['create', 'edit', 'index']);
    
    Route::resource('tasks', TaskController::class)->except(['create', 'edit', 'index']);
    Route::post('/tasks/{task}/addAttachment', [TaskController::class, 'addAttachment']);
    Route::delete('/tasks/{task}/deleteAttachment/{id}', [TaskController::class, 'removeAttachment']);
    
    Route::post('/tasks/{task}/comment', [TaskController::class, 'addComment']);
    Route::get('/tasks/{task}/comment/{comment}', [TaskController::class, 'getComment']);
    Route::patch('/tasks/{task}/comment/{comment}', [TaskController::class, 'editComment']);
    Route::delete('/tasks/{task}/comment/{comment}', [TaskController::class, 'removeComment']);
    Route::post('/tasks/{task}/comment/{comment}/addAttachment', [TaskController::class, 'addCommentAttachment']);
    Route::delete('/tasks/{task}/comment/{comment}/deleteAttachment/{id}', [TaskController::class, 'removeCommentAttachment']);
    
});