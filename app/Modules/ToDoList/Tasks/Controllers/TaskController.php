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
        return ApiTaskResource::collection(Task::select(['id', 'name'])->orderBy('position')->get());
    }

    public function index(): AnonymousResourceCollection
    {
        $tasks = Task::select(['id', 'name'])->paginate();

        return ApiTaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): ApiTaskResource
    {
        $data = $request->validated();

        $data['position'] = Task::select('position')
            ->where('catalog_id', $data['catalog_id'])
            ->max('position') + 1;

        return new ApiTaskResource(Task::create($data));
    }

    public function show(Task $task): ApiTaskResource
    {
        return new ApiTaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): ApiTaskResource
    {
        $data = $request->validated();

        if ($data['catalog_id'] == $task->catalog_id) {
            if ($data['position'] < $task->position) {
                Task::where('catalog_id', $data['catalog_id'])
                    ->where('position', '>=', $data['position'])
                    ->where('position', '<', $task->position)
                    ->increment('position');
            } else {
                Task::where('catalog_id', $data['catalog_id'])
                    ->where('position', '<=', $data['position'])
                    ->where('position', '>', $task->position)
                    ->decrement('position');
            }
        } else {
            Task::where('catalog_id', $data['catalog_id'])
                ->where('position', '>=', $data['position'])
                ->increment('position');
            
            Task::where('catalog_id', $task->catalog_id)
                ->where('position', '>', $task->position)
                ->decrement('position');
        }
        
        $task->update($data);

        return new ApiTaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $catalogId = $task->catalog_id;
        $position = $task->position;

        $task->delete();

        Task::where('catalog_id', $catalogId)
            ->where('position', '>', $position)
            ->decrement('position');

        return response()->json(null, 204);
    }

    public function addAttachment(Task $task): JsonResponse
    {
        if(request()->hasFile('file') && request()->file('file')->isValid()){
            $task->addMediaFromRequest('file')->toMediaCollection('task_attachments');
            return response()->json(null, 204);
        }

        return response()->json(null, 500);
    }

    public function removeAttachment(Task $task, int $id): JsonResponse
    {
        $task->deleteMedia($id);
        return response()->json(null, 204);
    }
}