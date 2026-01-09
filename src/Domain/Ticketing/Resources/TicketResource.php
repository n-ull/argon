<?php

namespace Domain\Ticketing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'token' => $this->token,
            'type' => $this->type,
            'status' => $this->status,
            'is_courtesy' => $this->is_courtesy,
            'used_at' => $this->used_at,
            'expired_at' => $this->expired_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'transfers_left' => $this->transfers_left,
            'event' => $this->whenLoaded('event'),
            'order' => $this->whenLoaded('order'),
            'product' => $this->whenLoaded('product'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}
