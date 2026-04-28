<?php

namespace Domain\OrganizerManagement\Actions;

use App\Models\User;
use Domain\OrganizerManagement\Models\MercadoPagoAccount;
use Domain\OrganizerManagement\Models\Organizer;
use Lorisleiva\Actions\Concerns\AsAction;

class UnlinkMercadoPagoAccount
{
    use AsAction;

    public function handle(MercadoPagoAccount $mercadoPagoAccount)
    {
        $mercadoPagoAccount->deleteOrFail();
    }

    public function asController(Organizer $organizer)
    {
        if ($organizer->owner_id !== auth()->id()) {
            abort(403);
        }

        if (!$organizer->owner?->mercadoPagoAccount) {
            abort(404);
        }

        $this->handle($organizer->owner->mercadoPagoAccount);

        return back()->with('success', 'Cuenta desvinculada correctamente.');
    }
}
