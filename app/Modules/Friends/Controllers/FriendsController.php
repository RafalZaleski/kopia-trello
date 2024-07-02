<?php

declare(strict_types=1);

namespace App\Modules\Friends\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Friends\Requests\FriendsInviteRequest;
use App\Modules\Friends\Resources\FriendsResource;
use App\Modules\Friends\Services\FriendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FriendsController extends Controller
{
    public function __construct(private readonly FriendService $friendService)
    {
    }

    public function indexAll(): AnonymousResourceCollection
    {
        return FriendsResource::collection(
            auth()->user()->friends()->get()
        );
    }

    public function index(): AnonymousResourceCollection
    {
        $friends = auth()->user()->friends()->paginate(10);

        return FriendsResource::collection($friends);
    }

    public function show(int $userId): FriendsResource
    {
        return new FriendsResource(auth()->user()->friends()->where('friend_id', $userId)->firstOrFail());
    }

    public function invite(FriendsInviteRequest $request): FriendsResource|JsonResponse
    {
        $validateData = $request->validated();
        
        $userId = $this->friendService->invite($validateData);

        return new FriendsResource(auth()->user()->friends()->where('friend_id', $userId)->firstOrFail());
    }

    public function confirm(int $userId): FriendsResource
    {
        $this->friendService->confirm($userId);

        return new FriendsResource(auth()->user()->friends()->where('friend_id', $userId)->firstOrFail());
    }

    public function destroy(int $userId): JsonResponse
    {
        $this->friendService->destroy($userId);

        return response()->json(null, 204);
    }
}