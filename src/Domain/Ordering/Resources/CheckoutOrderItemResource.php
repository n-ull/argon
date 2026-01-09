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
            'name' => $this->product->name,
            'price_name' => $this->productPrice->label,
            'subtotal' => $this->unit_price * $this->quantity
        ];
    }
}
