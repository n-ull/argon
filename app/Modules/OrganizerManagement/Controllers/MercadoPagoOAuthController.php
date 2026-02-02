<?php

namespace App\Modules\OrganizerManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;

class MercadoPagoOAuthController extends Controller
{
    public function __invoke(Request $request)
    {
        $code = $request->query('code');
        $organizerId = $request->query('state');

        if (! $code) {
            return response()->json(['message' => 'Invalid code'], 400);
        }

        if (! $organizerId) {
            return response()->json(['message' => 'Invalid state'], 400);
        }

        try {
            MercadoPagoService::OAuthVinculation($code, $organizerId);

            return redirect()->route('manage.organizer.settings', ['organizer' => $organizerId])
                ->withFragment('payment');
        } catch (\Exception $e) {
            return redirect()->route('manage.organizer.settings', ['organizer' => $organizerId])
                ->with('error', 'Failed to link MercadoPago account. '.$e->getMessage());
        }
    }
}
