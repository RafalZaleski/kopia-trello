<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Tasks\Resources;

use App\Modules\ToDoList\Comments\Resources\ApiCommentResource;
use App\Modules\ToDoList\Media\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiTaskResource extends JsonResource
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
            'catalog_id' => $this->catalog_id,
            'name' => $this->name,
            'description' => $this->description,
            'date' => $this->date,
            'place' => $this->place,
            'position' => $this->position,
            'comments' => ApiCommentResource::collection($this->comments),
            'attachments' => MediaResource::collection($this->getMedia('task_attachments')),
        ];
    }
}
