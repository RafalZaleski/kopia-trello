<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Boards\Models;

use App\Modules\Assets\Sync\Models\RemovedItem;
use App\Modules\Auth\Models\User;
use App\Modules\ToDoList\Catalogs\Models\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use SoftDeletes;
    
    protected $table = 'boards';

    protected $fillable = [
        'name',
        'description',
    ];

    public static function boot ()
    {
        parent::boot();

        self::deleting(function (self $board) {
            foreach($board->catalogs as $catalog) {
                $catalog->delete();
            }
        });
    }

    public function catalogs(): HasMany
    {
        return $this->hasMany(Catalog::class)
            ->with('tasks')
            ->orderBy('position');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_boards', 'board_id', 'user_id')
            ->withTimestamps();
    }

    public function removedItems(): MorphMany
    {
        return $this->morphMany(RemovedItem::class, 'model');
    }
}