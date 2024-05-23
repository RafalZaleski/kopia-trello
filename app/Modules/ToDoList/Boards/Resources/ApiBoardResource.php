<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Boards\Resources;

use App\Modules\ToDoList\Catalogs\Resources\ApiCatalogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiBoardResource extends JsonResource
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
            'catalogs' => ApiCatalogResource::collection($this->catalogs),
        ];
    }
}
