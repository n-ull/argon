<?php

namespace Domain\OrganizerManagement\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class VinculateMercadoPagoAccount
{
    use AsAction;

    public function handle(\Domain\OrganizerManagement\Models\Organizer $organizer)
    {
        $appId = config('services.mercadopago.app_id');
        $redirectUri = urlencode(route('mp.oauth'));
        $state = $organizer->id;
        $vinculateURL = "https://auth.mercadopago.com/authorization?client_id=$appId&redirect_uri=$redirectUri&response_type=code&platform_id=mp&state=$state";

        if (request()->wantsJson()) {
            return response()->json(['url' => $vinculateURL]);
        }

        return redirect()->to($vinculateURL);
    }

    public function asController(\Domain\OrganizerManagement\Models\Organizer $organizer)
    {
        return $this->handle($organizer);
    }
}
