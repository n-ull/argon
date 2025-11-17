<?php

namespace Domain\Ordering\Data;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class CreateOrderProductData extends Data
{
    public function __construct(
        public int $productId,
        public int $selectedPriceId,
        #[Min(1)]
        public int $quantity
    ) {
        //
    }
}
