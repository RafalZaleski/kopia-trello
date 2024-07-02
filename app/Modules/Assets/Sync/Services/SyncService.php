<?php

declare(strict_types=1);

namespace App\Modules\Assets\Sync\Services;

use App\Modules\Friends\Services\FriendService;
use App\Modules\ToDoList\Boards\Services\BoardService;
use Illuminate\Support\Carbon;


class SyncService
{
    public function __construct(
        private readonly FriendService $friendService,
        private readonly BoardService $boardService,
    ) {}

    public function sync(Carbon $from): array
    {
        return [
            'toUpdate' => [
                'friends' => $this->friendService->syncUpdated($from),
                'boards' => $this->boardService->syncUpdated($from),
            ],
            'toDelete' => [
                'friends' => $this->friendService->syncDeleted($from),
                'boards' => $this->boardService->syncDeleted($from),
            ]
        ];
    }
}