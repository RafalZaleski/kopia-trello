<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Catalogs\Models;

use App\Modules\ToDoList\Boards\Models\Board;
use App\Modules\ToDoList\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Catalog extends Model
{
    use SoftDeletes;
    
    protected $table = 'catalogs';

    protected $fillable = [
        'board_id',
        'name',
        'description',
        'position',
    ];

    public static function boot ()
    {
        parent::boot();

        self::deleting(function (self $catalog) {
            foreach($catalog->tasks as $task) {
                $task->delete();
            }
        });
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)
            ->with('comments')
            ->with('media')
            ->orderBy('position');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
}