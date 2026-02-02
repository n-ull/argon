<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentGateway;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

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
        $accessToken = $data['access_token'];
        MercadoPagoConfig::setAccessToken($accessToken);

        try {
            $preference = new PreferenceClient;
            $order = $data['order'];
            $items = [];

            $item = new \MercadoPago\Resources\Preference\Item;
            $item->title = "Moovin Order #{$order->reference_id}";
            $item->quantity = 1;
            $item->unit_price = $order->total_gross;
            $item->currency_id = 'ARS';
            $item->description = "Productos Totales: {$order->orderItems()->count()}";
            $item->id = $order->id;
            $items[] = $item;

            $payer = new \MercadoPago\Resources\Preference\Payer;
            $payer->email = $order->user->email;
            $payer->name = $order->user->name ?? 'No Name';
            $payer->surname = $order->user->last_name ?? 'No Last Name';

            $response = $preference->create([
                'items' => $items,
                'payer' => $payer,
                'back_urls' => [
                    'success' => route('orders.show', $order->id),
                    'failure' => route('orders.show', $order->id),
                    'pending' => route('orders.show', $order->id),
                ],
                'auto_return' => 'approved',
                'external_reference' => $order->reference_id,
                'expiration_date_from' => date('c'),
                'expiration_date_to' => date('c', strtotime('+10 minutes')),
                'marketplace_fee' => $data['service_fee'],
                'marketplace' => config('services.mercadopago.app_id'),
            ]);

            return $response->init_point;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function createIntent(array $data): string
    {
        $accessToken = env('APP_ENV') === 'production' ? config('services.mercadopago.access_token') : config('services.mercadopago.test_access_token');
        MercadoPagoConfig::setAccessToken($accessToken);

        try {
            $preference = new PreferenceClient;
            $order = $data['order'];
            $items = [];

            $item = new \MercadoPago\Resources\Preference\Item;
            $item->title = "Moovin Order #{$order->reference_id}";
            $item->quantity = 1;
            $item->unit_price = $order->total_gross;
            $item->currency_id = 'ARS';
            $item->description = "Productos Totales: {$order->orderItems()->count()}";
            $item->id = $order->id;
            $items[] = $item;

            $payer = new \MercadoPago\Resources\Preference\Payer;
            $payer->email = $order->user->email;
            $payer->name = $order->user->name ?? 'No Name';
            $payer->surname = $order->user->last_name ?? 'No Last Name';

            $response = $preference->create([
                'items' => $items,
                'payer' => $payer,
                'back_urls' => [
                    'success' => route('orders.show', $order->id),
                    'failure' => route('orders.show', $order->id),
                    'pending' => route('orders.show', $order->id),
                ],
                'auto_return' => 'approved',
                'external_reference' => $order->reference_id,
                'expiration_date_from' => date('c'),
                'expiration_date_to' => date('c', strtotime('+10 minutes')),
            ]);

            return $response->init_point;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
