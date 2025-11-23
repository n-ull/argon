<?php

namespace Domain\EventManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'location' => $this->location_info,
            'organizer' => $this->whenLoaded('organizer', function () {
                return [
                    'name' => $this->organizer->name,
                    'email' => $this->organizer->email,
                    'phone' => $this->organizer->phone,
                    'logo' => $this->organizer->logo,
                ];
            }),
        ];
    }
}
