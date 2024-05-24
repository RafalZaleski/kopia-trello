<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Boards\Models;

use App\Modules\ToDoList\Catalogs\Models\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use SoftDeletes;
    
    protected $table = 'boards';

    protected $fillable = [
        'name',
        'description',
    ];

    public function catalogs(): HasMany
    {
        return $this->hasMany(Catalog::class)
            ->with('tasks')
            ->orderBy('position');
    }
}