<?php

namespace Domain\Ordering\Data;

use Spatie\LaravelData\Data;

class CreateOrderData extends Data
{
    /**
     * Summary of __construct
     * @param int $eventId
     * @param array $items
     * @param int|null $userId
     * @param string|null $gateway
     * @param string|null $referral_code
     */
    public function __construct(
        public int $eventId,
        public array $items,
        public ?int $userId = null,
        public ?string $gateway = null,
        public ?string $referral_code = null,
    ) {
    }

}
