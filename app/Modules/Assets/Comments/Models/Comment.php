<?php

declare(strict_types=1);

namespace App\Modules\Assets\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Comment extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;
    
    protected $table = 'comments';

    protected $fillable = [
        'model_type',
        'model_id',
        'description',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public static function boot ()
    {
        parent::boot();

        self::deleting(function (self $comment) {
            foreach ($comment->getMedia('comment_attachments') as $media) {
                $media->delete();
            }
        });
    }

    // todo skalowanie do max width 1920 lub max height 1080
    // public function registerMediaConversions(?Media $media = null): void
    // {
    //     $this->addMediaConversion('media-webp')
    //         ->format('webp');
    // }
}