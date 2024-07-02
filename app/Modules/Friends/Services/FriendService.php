<?php

declare(strict_types=1);

namespace App\Modules\Friends\Services;

use App\Modules\Assets\Sync\Models\RemovedItem;
use App\Modules\Auth\Models\User;
use App\Modules\Friends\Resources\FriendsResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class FriendService
{
    public function syncUpdated(Carbon $from): AnonymousResourceCollection
    {
        return FriendsResource::collection(
            auth()->user()->friends()
                ->where('users_friends.updated_at', '>=', $from)
                ->get()
        );
    }

    public function syncDeleted(Carbon $from): AnonymousResourceCollection
    {
        return FriendsResource::collection(
            RemovedItem::select(['model_id as id'])
                ->where([
                    'user_id' => auth()->user()->getAuthIdentifier(),
                    'model_type' => User::class,
                ])
                ->where('updated_at', '>=', $from)
                ->get()
        );
    }

    public function invite(array $validateData): int
    {
        $user = User::where('email', $validateData['email'])->firstOrFail();

        if ($user->id === auth()->user()->getAuthIdentifier()) {
            throw new \RuntimeException('Nie można wysłać zaproszenia do samego siebie.');
        }

        if (auth()->user()->friends()->where('friend_id', $user->id)->first()) {
            throw new \RuntimeException('Zaproszenie już zostało wysłane.');
        }

        auth()->user()->friends()->attach(
            $user->id,
            [
                'is_accepted' => false,
                'who_send' => auth()->user()->getAuthIdentifier()
            ]
        );
        auth()->user()->friends2()->attach(
            $user->id, 
            [
                'is_accepted' => false,
                'who_send' => auth()->user()->getAuthIdentifier()
            ]
        );

        return $user->id;
    }

    public function confirm(int $userId): void
    {
        $friend = auth()->user()->friends()->where(['friend_id' => $userId, 'who_send' => $userId])->firstOrFail();
        auth()->user()->friends()->detach($userId);
        auth()->user()->friends()->attach($friend->id, ['is_accepted' => true, 'who_send' => $userId]);
        
        $friend = auth()->user()->friends2()->where(['user_id' => $userId, 'who_send' => $userId])->firstOrFail();
        auth()->user()->friends2()->detach($userId);
        auth()->user()->friends2()->attach($friend->id, ['is_accepted' => true, 'who_send' => $userId]);
    }

    public function destroy(int $userId): void
    {
        auth()->user()->friends()->detach($userId);
        auth()->user()->friends2()->detach($userId);
        auth()->user()->removedItems()->create(['user_id' => $userId]);
        auth()->user()->removedItems()->create(['user_id' => auth()->user()->getAuthIdentifier()]);
    }
}