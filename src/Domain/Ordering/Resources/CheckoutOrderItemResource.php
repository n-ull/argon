<?php

namespace Domain\Ordering\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutOrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->whenNotNull($this->product?->name, $this->combo?->name),
            'price_name' => $this->whenNotNull($this->productPrice?->label, $this->combo?->name),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->unit_price * $this->quantity,
        ];
    }
}
