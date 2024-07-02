<?php

declare(strict_types=1);

namespace App\Modules\Assets\Sync\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Assets\Sync\Requests\SyncRequest;
use App\Modules\Assets\Sync\Services\SyncService;
use Illuminate\Support\Carbon;

class SyncController extends Controller
{
    public function __construct(
        private readonly SyncService $syncService,
    ) {}

    public function sync(SyncRequest $request): array
    {
        $from = Carbon::createFromTimestamp($request->get('date'))->addHours(2);

        return $this->syncService->sync($from);
    }
}