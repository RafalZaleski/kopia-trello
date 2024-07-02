<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Boards\Services;


use App\Modules\Assets\Sync\Models\RemovedItem;
use App\Modules\ToDoList\Boards\Models\Board;
use App\Modules\ToDoList\Boards\Resources\ApiBoardResource;
use App\Modules\ToDoList\Catalogs\Models\Catalog;
use App\Modules\ToDoList\Tasks\Models\Task;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class BoardService
{
    public function syncUpdated(Carbon $from): AnonymousResourceCollection
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
                ->where('boards.updated_at', '>=', $from)
                ->get()
        );
    }

    public function syncDeleted(Carbon $from): AnonymousResourceCollection
    {
        return ApiBoardResource::collection(
            RemovedItem::select(['model_id as id'])
                ->where([
                    'user_id' => auth()->user()->getAuthIdentifier(),
                    'model_type' => Board::class,
                ])
                ->where('updated_at', '>=', $from)
                ->get()
        );
    }

    public function checkAccessToBoard(Board $board): bool
    {
        return (bool)$board->users()->firstWhere('users.id', auth()->user()->getAuthIdentifier());
    }

    public function store(array $validatedData): Board
    {
        $validatedData['name'] = trim($validatedData['name']);
        $board = Board::create($validatedData);
        auth()->user()->boards()->attach($board->id);

        return $board;
    }

    public function destroy(Board $board): void
    {
        $board->users()->detach(auth()->user()->getAuthIdentifier());
        $board->removedItems()->create(['user_id' => auth()->user()->getAuthIdentifier()]);

        if ($board->users()->count() === 0) {
            $board->delete();
        }
    }

    public function copy(Board $board): Board
    {
        $copyBoard = $board->replicate()->toArray();
        $copyBoard['name'] .= ' - kopia';
        $copyBoard = Board::create($copyBoard);

        foreach ($board->catalogs as $catalog) {
            $newCatalog = $catalog->replicate()->toArray();
            $newCatalog['board_id'] = $copyBoard->id;
            // $newCatalog = $copyBoard->catalogs()->create($newCatalog);
            $newCatalog = Catalog::create($newCatalog);
            
            foreach ($catalog->tasks as $task) {
                $newTask = $task->replicate()->toArray();
                $newTask['catalog_id'] = $newCatalog->id;
                // $newTask = $newCatalog->tasks()->create($newTask);
                $newTask = Task::create($newTask);
            }

        }

        $copyBoard->users()->attach(auth()->user()->getAuthIdentifier());

        return $copyBoard;
    }

    public function share(Board $board, int $userId): bool
    {
        if (
            auth()->user()->getAuthIdentifier() !== $userId
            && auth()->user()->isFriend($userId)
        ) {
            if (!$board->users()->find($userId)) {
                $board->users()->attach($userId);
                $board->touch();
            }
            
            return true;
        }

        return false;
    }
}