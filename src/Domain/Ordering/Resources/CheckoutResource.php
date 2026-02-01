<?php

namespace Domain\Ordering\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
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
            'total' => $this->total,
            'total_gross' => $this->total_gross,
            'items_snapshot' => $this->items_snapshot,
            'taxes_snapshot' => $this->taxes_snapshot,
            'fees_snapshot' => $this->fees_snapshot,
            'expires_at' => $this->expires_at,
            'order_items' => CheckoutOrderItemResource::collection($this->orderItems),
            'event' => [
                'slug' => $this->event->slug,
            ],
            'referral_code' => $this->whenNotNull($this->referral_code),
        ];
    }
}
