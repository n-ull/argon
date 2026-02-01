<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentGateway;

class MercadoPagoGateway implements PaymentGateway
{
    public bool $allowSplit;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->allowSplit = true;
    }

    public function process(array $data): string
    {
        if ($data['method'] === 'split') {
            return $this->createSplitIntent($data);
        }

        return $this->createIntent($data);
    }

    private function createSplitIntent(array $data): string
    {
        throw new \Exception('Split payment not implemented');
    }

    private function createIntent(array $data): string
    {
        throw new \Exception('Payment not implemented');
    }
}
