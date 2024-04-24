<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Comments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ToDoList\Comments\Requests\StoreCommentRequest;
use App\Modules\ToDoList\Comments\Requests\UpdateCommentRequest;
use App\Modules\ToDoList\Sync\Requests\SyncRequest;
use App\Modules\ToDoList\Comments\Resources\ApiCommentResource;
use App\Modules\ToDoList\Comments\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class CommentController extends Controller
{
    public function syncUpdated(SyncRequest $request): AnonymousResourceCollection
    {
        return ApiCommentResource::collection(
            Comment::select(['id', 'name'])
                ->where('updated_at', '>=', Carbon::createFromTimestamp($request->get('date')))
                ->get()
        );
    }

    public function syncDeleted(SyncRequest $request): AnonymousResourceCollection
    {
        return ApiCommentResource::collection(
            Comment::select(['id', 'name'])
                ->where('deleted_at', '>=', Carbon::createFromTimestamp($request->get('date')))
                ->onlyTrashed()
                ->get()
        );
    }

    public function indexAll(): AnonymousResourceCollection
    {
        return ApiCommentResource::collection(Comment::select(['id', 'name'])->get());
    }

    public function index(): AnonymousResourceCollection
    {
        $comments = Comment::select(['id', 'name'])->paginate();

        return ApiCommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request): ApiCommentResource
    {
        $data = $request->validated();
        $data['name'] = mb_strtolower(trim($data['name']));

        return new ApiCommentResource(Comment::create($data));
    }

    public function show(Comment $comment): ApiCommentResource
    {
        return new ApiCommentResource($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): ApiCommentResource
    {
        $data = $request->validated();
        $data['name'] = mb_strtolower(trim($data['name']));
        
        $comment->update($data);

        return new ApiCommentResource($comment);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        return response()->json(null, 204);
    }
}