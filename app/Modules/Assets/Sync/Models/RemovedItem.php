<?php

declare(strict_types=1);

namespace App\Modules\Assets\Sync\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemovedItem extends Model
{ 
    protected $table = 'removed_items';

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTimestamps();
    }
}