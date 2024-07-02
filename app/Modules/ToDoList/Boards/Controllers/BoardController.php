<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Boards\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ToDoList\Boards\Requests\StoreBoardRequest;
use App\Modules\ToDoList\Boards\Requests\UpdateBoardRequest;
use App\Modules\ToDoList\Boards\Services\BoardService;
use App\Modules\ToDoList\Boards\Resources\ApiBoardResource;
use App\Modules\ToDoList\Boards\Models\Board;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BoardController extends Controller
{
    public function __construct(private readonly BoardService $boardService)
    {
    }

    public function indexAll(): AnonymousResourceCollection
    {
        return ApiBoardResource::collection(
            auth()->user()->boards()
                ->select(
                    [
                        'boards.id',
                        'boards.name',
                        'boards.description',
                    ]
                )->with('catalogs')
                ->get()
        );
    }

    public function index(): AnonymousResourceCollection
    {
        $boards = auth()->user()->boards()
            ->select(
                [
                    'boards.id',
                    'boards.name',
                    'boards.description',
                ]
            )->with('catalogs')
            ->paginate(10);

        return ApiBoardResource::collection($boards);
    }

    public function store(StoreBoardRequest $request): ApiBoardResource
    {
        $validatedData = $request->validated();
        
        $board = $this->boardService->store($validatedData);

        return new ApiBoardResource($board);
    }

    public function show(Board $board): ApiBoardResource|JsonResponse
    {
        if (!$this->boardService->checkAccessToBoard($board)) {
            return response()->json(null, 403);
        }
        
        return new ApiBoardResource($board);
    }

    public function update(UpdateBoardRequest $request, Board $board): ApiBoardResource|JsonResponse
    {
        if (!$this->boardService->checkAccessToBoard($board)) {
            return response()->json(null, 403);
        }
         
        $data = $request->validated();
        $data['name'] = trim($data['name']);
        
        $board->update($data);

        return new ApiBoardResource($board);
    }

    public function destroy(Board $board): JsonResponse
    {
        if (!$this->boardService->checkAccessToBoard($board)) {
            return response()->json(null, 403);
        }

        $this->boardService->destroy($board);

        return response()->json(null, 204);
    }

    public function copy(Board $board): ApiBoardResource|JsonResponse
    {
        if (!$this->boardService->checkAccessToBoard($board)) {
            return response()->json(null, 403);
        }

        $copyBoard = $this->boardService->copy($board);
        
        return new ApiBoardResource($copyBoard);
    }

    public function share(Board $board, int $userId): JsonResponse
    {
        if (!$this->boardService->checkAccessToBoard($board)) {
            return response()->json(null, 403);
        }
        
        return $this->boardService->share($board, $userId)
            ? response()->json(null, 204)
            : response()->json(null, 403);
    }
}