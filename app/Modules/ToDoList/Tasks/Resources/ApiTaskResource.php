<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Tasks\Resources;

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
            'comments' => $this->comments,
            'attachments' => $this->getMedia('task_attachments')->toArray(),
        ];
    }
}
