<?php

namespace Domain\Promoters\Actions;

use Domain\Promoters\Models\Promoter;
use Domain\Promoters\Models\PromoterInvitation;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class AcceptPromoterInvitation
{
    use AsAction;

    public function handle(string $token, \App\Models\User $user)
    {
        $invitation = PromoterInvitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        // 1. Ensure Promoter Profile
        $promoter = Promoter::firstOrCreate(
            ['user_id' => $user->id],
            ['referral_code' => $this->generateReferralCode()]
        );

        // 2. Link to Event (Create PromoterEvent)
        // Check if already linked to avoid duplicates/errors
        if (! $promoter->events()->where('event_id', $invitation->event_id)->exists()) {
            $promoter->events()->attach($invitation->event_id, [
                'commission_type' => $invitation->commission_type,
                'commission_value' => $invitation->commission_value,
                'enabled' => true,
            ]);
        }

        // 3. Update Invitation
        $invitation->update([
            'status' => 'accepted',
            'promoter_id' => $promoter->id, // Ensure it's linked now
        ]);

        return $invitation;
    }

    protected function generateReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Promoter::where('referral_code', $code)->exists());

        return $code;
    }

    public function asController(string $token, \Illuminate\Http\Request $request)
    {
        $this->handle($token, $request->user());

        return back()->with('message', flash_success('Invitation accepted', 'You have accepted the promoter invitation.'));
    }
}
