<?php

namespace Domain\Ordering\Data;

use Spatie\LaravelData\Data;

class CreateOrderData extends Data
{
    public function __construct(
        public int $eventId,
        public array $products // productId + quantity
    )
    {
        //
    }
}
