<?php

namespace Domain\Ordering\Actions;

use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Services\OrderService;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrder
{
    use AsAction;

    public function __construct(
        private OrderService $orderService
    ) {}

    public function handle(CreateOrderData $orderData): Order
    {
        return $this->orderService->createPendingOrder($orderData);
    }
}
