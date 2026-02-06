<?php

namespace Domain\Ticketing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScannedTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'user' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'used_at' => now()->format('H:i:s'),
            'doorman' => [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ]
        ];
    }
}
