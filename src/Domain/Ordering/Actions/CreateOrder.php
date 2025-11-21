<?php

namespace Domain\Ordering\Actions;

use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Services\OrderService;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrder
{
    use AsAction;

    public function __construct(
        private OrderService $orderService
    ) {}

    public function handle(CreateOrderData $orderData)
    {
        $this->orderService->createPendingOrder($orderData);
    }
}
