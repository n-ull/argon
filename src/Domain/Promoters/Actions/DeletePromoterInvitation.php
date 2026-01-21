<?php

namespace Domain\Promoters\Actions;

use Domain\Promoters\Models\PromoterInvitation;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePromoterInvitation
{
    use AsAction;

    public function handle(int $eventId, int $invitationId)
    {
        $invitation = PromoterInvitation::where('event_id', $eventId)
            ->where('id', $invitationId)
            ->firstOrFail();

        $invitation->delete();
    }

    public function asController(int $eventId, int $invitationId)
    {
        $this->handle($eventId, $invitationId);

        return redirect()->back();
    }
}
