<?php

namespace App\Services\Payment;

interface PaymentGateway
{
    public function process(array $data): string;
}