<?php

namespace Domain\Promoters\Actions;

use Domain\Promoters\Models\PromoterInvitation;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePromoterInvitation
{
    use AsAction;

    public function handle(int $organizerId, int $invitationId)
    {
        $invitation = PromoterInvitation::where('organizer_id', $organizerId)
            ->where('id', $invitationId)
            ->firstOrFail();

        $invitation->delete();
    }

    public function asController(int $organizerId, int $invitationId)
    {
        $this->handle($organizerId, $invitationId);

        return redirect()->back();
    }
}
