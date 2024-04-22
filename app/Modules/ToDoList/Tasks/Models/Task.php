<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Tasks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;
    
    protected $table = 'tasks';

    protected $fillable = [
        'name',
    ];
}