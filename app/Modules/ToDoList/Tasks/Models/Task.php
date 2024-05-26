<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Tasks\Models;

use App\Modules\ToDoList\Catalogs\Models\Catalog;
use App\Modules\ToDoList\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Task extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;
    
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

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->with('media');
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    // todo skalowanie do max width 1920 lub max height 1080
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('media-webp')
            ->format(Manipulations::FORMAT_WEBP);
    }
}