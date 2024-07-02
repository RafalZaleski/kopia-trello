<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Tasks\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Assets\Comments\Models\Comment;
use App\Modules\Assets\Comments\Requests\StoreCommentRequest;
use App\Modules\Assets\Comments\Requests\UpdateCommentRequest;
use App\Modules\Assets\Comments\Resources\ApiCommentResource;
use App\Modules\Assets\Media\Requests\MediaRequest;
use App\Modules\Assets\Media\Resources\MediaResource;
use App\Modules\ToDoList\Catalogs\Models\Catalog;
use App\Modules\ToDoList\Catalogs\Services\CatalogService;
use App\Modules\ToDoList\Tasks\Requests\StoreTaskRequest;
use App\Modules\ToDoList\Tasks\Requests\UpdateTaskRequest;
use App\Modules\ToDoList\Tasks\Resources\ApiTaskResource;
use App\Modules\ToDoList\Tasks\Models\Task;
use App\Modules\ToDoList\Tasks\Services\TaskService;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly CatalogService $catalogService,
    ) {
    }

    public function store(StoreTaskRequest $request): ApiTaskResource|JsonResponse
    {
        $validatedData = $request->validated();

        $catalog = Catalog::where(['id' => $validatedData['catalog_id']])->firstOrFail();

        if (!$this->catalogService->checkAccessToCatalog($catalog)) {
            return response()->json(null, 403);
        }

        $task = $this->taskService->store($validatedData);

        return new ApiTaskResource($task);
    }

    public function show(Task $task): ApiTaskResource|JsonResponse
    {
        if (!$this->taskService->checkAccessToTask($task)) {
            return response()->json(null, 403);
        }

        return new ApiTaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): ApiTaskResource|JsonResponse
    {
        $validatedData = $request->validated();

        if (
            !$this->taskService->checkAccessToTask($task)
            || !$this->catalogService->checkAccessToCatalog(Catalog::where(['id' => $validatedData['catalog_id']])->firstOrFail())
        ) {
            return response()->json(null, 403);
        }

        $task = $this->taskService->update($validatedData, $task);

        return new ApiTaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        if (!$this->taskService->checkAccessToTask($task)) {
            return response()->json(null, 403);
        }

        $this->taskService->destroy($task);

        return response()->json(null, 204);
    }

    public function getComment(Task $task, Comment $comment): JsonResponse|ApiCommentResource
    {
        if (!$this->taskService->checkAccessToComment($comment)) {
            return response()->json(null, 403);
        }

        return new ApiCommentResource($comment);
    }

    public function addComment(Task $task, StoreCommentRequest $request): JsonResponse|ApiCommentResource
    {
        if (!$this->taskService->checkAccessToTask($task)) {
            return response()->json(null, 403);
        }

        $validatedData = $request->validated();

        $comment = $this->taskService->addComment($task, $validatedData);

        return new ApiCommentResource($comment);
    }

    public function editComment(Task $task, Comment $comment, UpdateCommentRequest $request): JsonResponse|ApiCommentResource
    {
        if (!$this->taskService->checkAccessToComment($comment)) {
            return response()->json(null, 403);
        }

        $validatedData = $request->validated();

        $comment = $this->taskService->editComment($comment, $validatedData);

        return new ApiCommentResource($comment);
    }

    public function removeComment(Task $task, Comment $comment): JsonResponse
    {
        if (!$this->taskService->checkAccessToComment($comment)) {
            return response()->json(null, 403);
        }

        $this->taskService->removeComment($comment);

        return response()->json(null, 204);
    }

    public function addCommentAttachment(Task $task, Comment $comment, MediaRequest $request): JsonResponse|MediaResource
    {
        if (!$this->taskService->checkAccessToComment($comment)) {
            return response()->json(null, 403);
        }

        $ans = $this->taskService->addCommentAttachment($comment, $request);

        if (!$ans) {
            return response()->json(null, 500);
        }

        return $ans;
    }

    public function removeCommentAttachment(Task $task, Comment $comment, int $id): JsonResponse
    {
        if (!$this->taskService->checkAccessToComment($comment)) {
            return response()->json(null, 403);
        }
        
        $this->taskService->removeCommentAttachment($comment, $id);

        return response()->json(null, 204);
    }

    public function addAttachment(Task $task, MediaRequest $request): JsonResponse|MediaResource
    {
        if (!$this->taskService->checkAccessToTask($task)) {
            return response()->json(null, 403);
        }

        $ans = $this->taskService->addAttachment($task, $request);

        if (!$ans) {
            return response()->json(null, 500);
        }

        return $ans;
    }

    public function removeAttachment(Task $task, int $id): JsonResponse
    {
        if (!$this->taskService->checkAccessToTask($task)) {
            return response()->json(null, 403);
        }
        
        $this->taskService->removeAttachment($task, $id);

        return response()->json(null, 204);
    }
}