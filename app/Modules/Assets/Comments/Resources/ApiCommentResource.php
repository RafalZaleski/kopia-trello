<?php

declare(strict_types=1);

namespace App\Modules\Assets\Comments\Resources;

use App\Modules\Assets\Media\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'attachments' => MediaResource::collection($this->getMedia('comment_attachments')),
        ];
    }
}
