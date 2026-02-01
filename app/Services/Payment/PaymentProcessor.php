<?php

namespace App\Services\Payment;

use Domain\Ordering\Models\Order;

final class PaymentProcessor
{
    /**
     * Gateways should implement PaymentGateway interface
     *
     * @var PaymentGateway[]
     */
    protected array $gateways;

    public function __construct(array $gateways)
    {
        $this->gateways = $gateways;
    }

    /**
     * Summary of createIntent
     * @param Order $order
     * @param array $data
     * @param string $raiseMethod 'internal' or 'split'
     * @return string
     */
    public function createIntent(Order $order, array $data, string $raiseMethod): string
    {
        $method = $data['gateway'] ?? null;

        if (! $method || ! isset($this->gateways[$method])) {
            throw new \Exception('Payment gateway not supported');
        }

        return $this->gateways[$method]->process($data);
    }
}
