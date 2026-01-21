<?php

namespace Domain\Promoters\Actions;

use Domain\Promoters\Models\PromoterInvitation;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class DeclinePromoterInvitation
{
    use AsAction;

    public function handle(string $token)
    {
        $invitation = PromoterInvitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $invitation->update([
            'status' => 'declined',
        ]);

        return $invitation;
    }

    public function asController(string $token, Request $request)
    {
        $this->handle($token);

        return back()->with('message', flash_success('Invitation declined', 'You have declined the promoter invitation.'));
    }
}
