<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Boards\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ToDoList\Boards\Requests\StoreBoardRequest;
use App\Modules\ToDoList\Boards\Requests\UpdateBoardRequest;
use App\Modules\ToDoList\Sync\Requests\SyncRequest;
use App\Modules\ToDoList\Boards\Resources\ApiBoardResource;
use App\Modules\ToDoList\Boards\Models\Board;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class BoardController extends Controller
{
    public function syncUpdated(SyncRequest $request): AnonymousResourceCollection
    {
        return ApiBoardResource::collection(
            Board::select(['id', 'name'])
                ->where('updated_at', '>=', Carbon::createFromTimestamp($request->get('date')))
                ->get()
        );
    }

    public function syncDeleted(SyncRequest $request): AnonymousResourceCollection
    {
        return ApiBoardResource::collection(
            Board::select(['id', 'name'])
                ->where('deleted_at', '>=', Carbon::createFromTimestamp($request->get('date')))
                ->onlyTrashed()
                ->get()
        );
    }

    public function indexAll(): AnonymousResourceCollection
    {
        return ApiBoardResource::collection(Board::select(['id', 'name'])->get());
    }

    public function index(): AnonymousResourceCollection
    {
        $boards = Board::select(['id', 'name'])->paginate();

        return ApiBoardResource::collection($boards);
    }

    public function store(StoreBoardRequest $request): ApiBoardResource
    {
        $data = $request->validated();
        $data['name'] = mb_strtolower(trim($data['name']));

        return new ApiBoardResource(Board::create($data));
    }

    public function show(Board $board): ApiBoardResource
    {
        return new ApiBoardResource($board);
    }

    public function update(UpdateBoardRequest $request, Board $board): ApiBoardResource
    {
        $data = $request->validated();
        $data['name'] = mb_strtolower(trim($data['name']));
        
        $board->update($data);

        return new ApiBoardResource($board);
    }

    public function destroy(Board $board): JsonResponse
    {
        $board->delete();

        return response()->json(null, 204);
    }
}