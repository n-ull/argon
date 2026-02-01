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
            'price' => $this->when(true, function () {
                $price = $this->price;
                if ($this->relationLoaded('product') && $this->product->relationLoaded('event') && $this->product->event->relationLoaded('taxesAndFees')) {
                    $integratedTaxes = $this->product->event->taxesAndFees
                        ->where('is_active', true)
                        ->where('display_mode', \Domain\EventManagement\Enums\DisplayMode::INTEGRATED);

                    foreach ($integratedTaxes as $tax) {
                        $price += $tax->calculateAmount($this->price);
                    }
                }
                return $price;
            }),
            'label' => $this->label,
            'stock' => $this->stock !== null ? max(0, $this->stock - $this->quantity_sold) : null,
            'sales_start_date' => $this->whenNotNull($this->start_sale_date),
            'sales_end_date' => $this->whenNotNull($this->end_sale_date),
            'sort_order' => $this->sort_order,
            'is_sold_out' => $this->stock !== null && $this->quantity_sold >= $this->stock,
            'limit_max_per_order' => $this->whenLoaded('product', function () {
                $availableStock = $this->stock !== null ? max(0, $this->stock - $this->quantity_sold) : null;
                $maxPerOrder = $this->product->max_per_order;

                if ($availableStock === null) {
                    return $maxPerOrder;
                }

                return min($maxPerOrder, $availableStock);
            }),
        ];
    }
}
