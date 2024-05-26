<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Comments\Models;

use App\Modules\ToDoList\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Comment extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;
    
    protected $table = 'comments';

    protected $fillable = [
        'description',
        'task_id',
    ];

    public static function boot ()
    {
        parent::boot();

        self::deleting(function (self $comment) {
            foreach ($comment->getMedia('comment_attachments') as $media) {
                $media->delete();
            }
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    // todo skalowanie do max width 1920 lub max height 1080
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('media-webp')
            ->format(Manipulations::FORMAT_WEBP);
    }
}