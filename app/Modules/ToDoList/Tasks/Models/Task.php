<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Tasks\Models;

use App\Modules\Assets\Comments\Traits\InteractsWithComment;
use App\Modules\ToDoList\Catalogs\Models\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Task extends Model implements HasMedia
{
    use InteractsWithComment, InteractsWithMedia, SoftDeletes;
    
    protected $table = 'tasks';

    protected $fillable = [
        'catalog_id',
        'name',
        'description',
        'date',
        'place',
        'position',
    ];

    public static function boot ()
    {
        parent::boot();

        self::deleting(function (self $task) {
            foreach ($task->comments as $comment) {
                $comment->delete();
            }
            
            foreach ($task->getMedia('task_attachments') as $media) {
                $media->delete();
            }
        });
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    // todo skalowanie do max width 1920 lub max height 1080
    // public function registerMediaConversions(?Media $media = null): void
    // {
    //     $this->addMediaConversion('media-webp')
    //         ->format('webp');
    // }
}