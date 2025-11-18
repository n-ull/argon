<?php

namespace Domain\Ordering\Data;

use Illuminate\Validation\Rules\Exists;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class CreateOrderProductData extends Data
{
    public function __construct(
        #[Exists("products")]
        public int $productId,
        #[Exists("product_prices")]
        public int $selectedPriceId,
        #[Min(1)]
        public int $quantity
    ) {
        //
    }
}
