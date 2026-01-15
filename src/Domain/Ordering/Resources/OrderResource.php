<?php

namespace Domain\Ordering\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'reference_id' => $this->reference_id,
            'subtotal' => $this->subtotal,
            'taxes_total' => $this->taxes_total,
            'fees_total' => $this->fees_total,
            'total' => $this->total_gross,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'order_items' => CheckoutOrderItemResource::collection($this->orderItems)->resolve(),
            'event' => [
                'id' => $this->event->id,
                'title' => $this->event->title,
                'slug' => $this->event->slug,
                'start_date' => $this->event->start_date,
                'location_info' => $this->event->location_info,
                'horizontal_image_url' => $this->event->horizontal_image_url,
            ],
            // We'll add tickets later if needed, but for now let's keep it simple
            'tickets_count' => $this->tickets()->count(),
        ];
    }
}
