<?php

namespace App\Services;

use Domain\OrganizerManagement\Models\MercadoPagoAccount;
use MercadoPago\Client\OAuth\OAuthClient;
use MercadoPago\Client\OAuth\OAuthCreateRequest;
use MercadoPago\MercadoPagoConfig;

final class MercadoPagoService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function OAuthVinculation($code, $organizerId)
    {
        $accessToken = config('services.mercadopago.access_token');
        MercadoPagoConfig::setAccessToken($accessToken);

        $client = new OAuthClient;
        $request = new OAuthCreateRequest;
        $request->client_id = config('services.mercadopago.app_id');
        $request->client_secret = config('services.mercadopago.client_secret');
        $request->code = $code;
        $request->redirect_uri = route('mp.oauth');

        try {
            $response = $client->create($request);

            MercadoPagoAccount::create([
                'access_token' => $response->access_token,
                'refresh_token' => $response->refresh_token,
                'organizer_id' => $organizerId,
                'public_key' => $response->public_key,
                'expires_in' => $response->expires_in,
                'mp_user_id' => $response->user_id,
                'code' => $code,
            ]);

            \Log::info('MercadoPagoService: OAuth Vinculation', [
                $response
            ]);

            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
