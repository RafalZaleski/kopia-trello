<?php

declare(strict_types=1);

namespace App\Modules\Friends\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendsResource extends JsonResource
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
            'email' => $this->email,
            'is_accepted' => $this->pivot?->is_accepted,
            'my_invitation' => (int)$this->pivot?->who_send === (int)auth()->user()->getAuthIdentifier(),
        ];
    }
}
