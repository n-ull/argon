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

        // 1. Ensure Promoter Profile linked to Organizer
        // We need to check if a promoter for this user + organizer already exists?
        // Logic: Create if not exists.

        $promoter = Promoter::firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'referral_code' => $this->generateReferralCode(),
                'enabled' => true,
            ]
        );

        $promoter->organizers()->syncWithoutDetaching([
            $invitation->organizer_id => [
                'commission_type' => $invitation->commission_type,
                'commission_value' => $invitation->commission_value,
                'enabled' => true,
            ]
        ]);

        // 2. No longer need to link to specific events (PromoterEvent).

        // 3. Update Invitation
        $invitation->update([
            'status' => 'accepted',
            'promoter_id' => $promoter->id,
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
        // 1. Fetch Invitation First
        $invitation = PromoterInvitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        // 2. Guest Flow
        if (! \Illuminate\Support\Facades\Auth::check()) {
            // Check if email already exists
            $existingUser = \App\Models\User::where('email', $invitation->email)->first();

            if ($existingUser) {
                return redirect()->route('login')
                    ->with('message', flash_message('Please log in to accept the invitation.', 'info'));
            }

            // Auto-register and login
            $newUser = \App\Actions\Auth\RegisterGuestUser::run($invitation->email);
            \Illuminate\Support\Facades\Auth::login($newUser);

            $user = $newUser;
        } else {
            // 3. Authenticated Flow
            $user = $request->user();

            // Security Check: Ensure email matches
            if ($user->email !== $invitation->email) {
                // Logout the user and redirect with error, OR just forbid.
                // The requirement says: "I don't want other emails to accept an invitation, just the email of the invitation"
                // It's friendlier to show a meaningful error or redirect.
                // Let's redirect to invitation page (which shares the logic) but maybe with a flash error?
                // Or abort 403.
                // Let's abort 403 for security, or redirect back with error.
                if ($request->wantsJson()) {
                    abort(403, 'This invitation is not for your email address.');
                }

                return back()->with('message', flash_error('Unauthorized', 'This invitation is for '.$invitation->email.'. You are logged in as '.$user->email.'.'));
            }
        }

        // 4. Handle Acceptance
        $this->handle($token, $user);

        return back()->with('message', flash_success('Invitation accepted', 'You have accepted the promoter invitation.'));
    }
}
