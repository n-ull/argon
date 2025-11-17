<?php

namespace Domain\Ordering\Data;

use Spatie\LaravelData\Data;

class CreateOrderProductData extends Data
{
    public function __construct(
        public int $productId,
        public int $selectedPriceId,
        public int $quantity
    ) {
        //
    }
}
