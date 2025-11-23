<?php

namespace Domain\ProductCatalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'description' => $this->description,
            'max_per_order' => $this->max_per_order,
            'min_per_order' => $this->min_per_order,
            'product_type' => $this->product_type,
            'product_price_type' => $this->product_price_type,
            'hide_before_sale_start_date' => $this->hide_before_sale_start_date,
            'hide_after_sale_end_date' => $this->hide_after_sale_end_date,
            'hide_when_sold_out' => $this->hide_when_sold_out,
            'show_stock' => $this->show_stock,
            'start_sale_date' => $this->start_sale_date,
            'end_sale_date' => $this->end_sale_date,
            'product_prices' => $this->whenLoaded('product_prices', function () {
                return ProductPriceResource::collection($this->product_prices)->resolve();
            }),
        ];
    }
}
