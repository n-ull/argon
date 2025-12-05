<?php

namespace Domain\Ordering\Data;

class OrderItemData
{
    public function __construct(
        public readonly int $productId,
        public readonly int $productPriceId,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly string $productPriceLabel,
        public readonly string $productName
    ) {}

    public function getSubtotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'product_price_id' => $this->productPriceId,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'subtotal' => $this->getSubtotal(),
            'product_price_label' => $this->productPriceLabel,
            'product_name' => $this->productName,
        ];
    }
}
