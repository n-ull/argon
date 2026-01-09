<?php

namespace Domain\ProductCatalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceResource extends JsonResource
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
            'price' => $this->price,
            'label' => $this->label,
            'stock' => $this->stock !== null ? max(0, $this->stock - $this->quantity_sold) : null,
            'sales_start_date' => $this->whenNotNull($this->start_sale_date),
            'sales_end_date' => $this->whenNotNull($this->end_sale_date),
            'sort_order' => $this->sort_order,
            'is_sold_out' => $this->stock !== null && $this->quantity_sold >= $this->stock,
            'limit_max_per_order' => $this->stock !== null
                ? min($this->product->max_per_order, max(0, $this->stock - $this->quantity_sold))
                : $this->product->max_per_order,
        ];
    }
}
