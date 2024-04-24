<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Catalogs\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiCatalogResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'position' => $this->position,
            'tasks' => $this->tasks,
        ];
    }
}
