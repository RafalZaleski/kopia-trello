<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Modules\Assets\Sync\Models\RemovedItem;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\ToDoList\Boards\Models\Board;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'users_boards', 'user_id', 'board_id')
            ->withTimestamps();
    }

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_friends', 'user_id', 'friend_id')
            ->withPivot(['is_accepted', 'who_send'])
            ->withTimestamps();
    }

    public function friends2(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_friends', 'friend_id', 'user_id')
            ->withPivot(['is_accepted', 'who_send'])
            ->withTimestamps();
    }

    public function isFriend(int $userId): bool
    {
        return (bool) auth()->user()
            ->friends()->where('friend_id', $userId)
            ->wherePivot('is_accepted', true)
            ->first();
    }

    public function removedItems(): MorphMany
    {
        return $this->morphMany(RemovedItem::class, 'model');
    }
}
