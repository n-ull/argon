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
            'stock' => $this->whenNotNull($this->stock),
            'sales_start_date' => $this->whenNotNull($this->start_sale_date),
            'sales_end_date' => $this->whenNotNull($this->end_sale_date),
            'sort_order' => $this->sort_order,
            'is_sold_out' => $this->when($this->stock === $this->quantity_sold, true) ?? false,
        ];
    }
}
