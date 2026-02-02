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

        \Log::info('Creating split intent', $data);

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
            $payer->email = $order->client->email;
            $payer->name = $order->client->name ?? 'No Name';
            $payer->surname = $order->client->last_name ?? 'No Last Name';

            $backUrls = array(
                'success' => route('orders.show', $order->id),
                'failure' => route('orders.show', $order->id),
            );

            $response = $preference->create([
                'items' => $items,
                'payer' => $payer,
                'back_urls' => $backUrls,
                'external_reference' => $order->reference_id,
                'expiration_date_from' => date('c'),
                'expiration_date_to' => date('c', strtotime('+15 minutes')),
                'marketplace_fee' => $order->service_fee_snapshot,
                'token' => config('services.mercadopago.client_secret')
            ]);

            \Log::info('Preference Created', [$response]);

            return $response->init_point;
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            \Log::info('API RESPONSE MP', [$e->getApiResponse()]);
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function createIntent(array $data): string
    {
        $accessToken = env('APP_ENV') === 'production' ? config('services.mercadopago.access_token') : config('services.mercadopago.test_access_token');
        MercadoPagoConfig::setAccessToken($accessToken);

        \Log::info('Creating intent', $data);

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
            $payer->email = $order->client->email;
            $payer->name = $order->client->name ?? 'No Name';
            $payer->surname = $order->client->last_name ?? 'No Last Name';


            $backUrls = array(
                'success' => route('orders.show', $order->id),
                'failure' => route('orders.show', $order->id)
            );


            $response = $preference->create([
                'items' => $items,
                'payer' => $payer,
                'back_urls' => $backUrls,
                'external_reference' => $order->reference_id,
                'expiration_date_from' => date('c'),
                'expiration_date_to' => date('c', strtotime('+10 minutes')),
            ]);

            \Log::info('Preference Created', [$response]);

            return $response->init_point;
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            \Log::info('API RESPONSE MP', [$e->getApiResponse()]);
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
