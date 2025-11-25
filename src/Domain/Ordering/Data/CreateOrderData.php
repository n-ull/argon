<?php

namespace Domain\Ordering\Data;

use Spatie\LaravelData\Data;

class CreateOrderData extends Data
{
    public function __construct(
        public int $eventId,
        public array $items,
        public ?int $userId = null,
        public ?string $gateway = null,
    ) {}

}
