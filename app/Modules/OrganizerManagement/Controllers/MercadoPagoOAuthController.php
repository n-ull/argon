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

        if (! $code) {
            return response()->json(['message' => 'Invalid code'], 400);
        }

        MercadoPagoService::OAuthVinculation($code, auth()->user());

        return redirect()->route('dashboard')->with('message', flash_message('MercadoPago account linked successfully', 'The MercadoPago account has been linked successfully.'));
    }
}
