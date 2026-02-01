<?php

namespace App\Providers;

use App\Services\Payment\Gateways\MercadoPagoGateway;
use App\Services\Payment\PaymentProcessor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentProcessor::class, function (Application $app) {
            return new PaymentProcessor([
                'mercadopago' => new MercadoPagoGateway(),
            ]);
        });
    }
}
