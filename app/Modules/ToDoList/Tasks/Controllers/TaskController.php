<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Tasks\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ToDoList\Tasks\Requests\StoreTaskRequest;
use App\Modules\ToDoList\Tasks\Requests\UpdateTaskRequest;
use App\Modules\ToDoList\Sync\Requests\SyncRequest;
use App\Modules\ToDoList\Tasks\Resources\ApiTaskResource;
use App\Modules\ToDoList\Tasks\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class TaskController extends Controller
{
    public function syncUpdated(SyncRequest $request): AnonymousResourceCollection
    {
        return ApiTaskResource::collection(
            Task::select(['id', 'name'])
                ->where('updated_at', '>=', Carbon::createFromTimestamp($request->get('date')))
                ->get()
        );
    }

    public function syncDeleted(SyncRequest $request): AnonymousResourceCollection
    {
        return ApiTaskResource::collection(
            Task::select(['id', 'name'])
                ->where('deleted_at', '>=', Carbon::createFromTimestamp($request->get('date')))
                ->onlyTrashed()
                ->get()
        );
    }

    public function indexAll(): AnonymousResourceCollection
    {
        return ApiTaskResource::collection(Task::select(['id', 'name'])->get());
    }

    public function index(): AnonymousResourceCollection
    {
        $tasks = Task::select(['id', 'name'])->paginate();

        return ApiTaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): ApiTaskResource
    {
        $data = $request->validated();
        $data['name'] = mb_strtolower(trim($data['name']));

        return new ApiTaskResource(Task::create($data));
    }

    public function show(Task $task): ApiTaskResource
    {
        return new ApiTaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): ApiTaskResource
    {
        $data = $request->validated();
        $data['name'] = mb_strtolower(trim($data['name']));
        
        $task->update($data);

        return new ApiTaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(null, 204);
    }
}