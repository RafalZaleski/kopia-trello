<?php

namespace App\Modules\Assets\Comments\Traits;

use App\Modules\Assets\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithComment
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'model')->with('media');
    }
}