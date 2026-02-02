<?php

namespace Domain\Promoters\Actions;

use Domain\Promoters\Models\PromoterInvitation;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class InvitePromoter
{
    use AsAction;

    public function handle(int $organizerId, array $validated)
    {
        $organizer = \Domain\OrganizerManagement\Models\Organizer::findOrFail($organizerId);

        $promoter = null;
        $user = \App\Models\User::where('email', $validated['email'])->first();

        if ($user) {
            $promoter = \Domain\Promoters\Models\Promoter::where('user_id', $user->id)->first();

            // Check if user is already a promoter for this organizer
            if ($promoter && $promoter->organizers()->where('organizer_id', $organizer->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'This user is already a promoter for this organization.',
                ]);
            }
        }

        // Check for pending invitations
        if (\Domain\Promoters\Models\PromoterInvitation::where('organizer_id', $organizer->id)->where('email', $validated['email'])->where('status', 'pending')->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'This email already has a pending invitation for this organization.',
            ]);
        }

        $invitation = PromoterInvitation::create([
            'organizer_id' => $organizer->id,
            'promoter_id' => $promoter?->id,
            'token' => \Str::random(60),
            'email' => $validated['email'],
            'commission_type' => $validated['commission_type'],
            'commission_value' => $validated['commission_value'],
        ]);

        \Illuminate\Support\Facades\Mail::to($validated['email'])->send(new \App\Mail\PromoterInvitationMail($invitation));

        return $invitation;
    }

    public function asController(int $organizerId, Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'commission_type' => ['required', 'in:fixed,percentage'],
            'commission_value' => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->handle($organizerId, $validated);

        return back()->with('message', flash_message('Promoter invited successfully', 'The promoter has been invited successfully', 'success'));
    }
}
