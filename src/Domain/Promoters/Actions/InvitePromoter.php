<?php

namespace Domain\Promoters\Actions;

use Domain\Promoters\Models\PromoterInvitation;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class InvitePromoter
{
    use AsAction;

    public function handle(int $eventId, array $validated)
    {
        $event = \Domain\EventManagement\Models\Event::findOrFail($eventId);

        $promoter = null;
        $user = \App\Models\User::where('email', $validated['email'])->first();

        if ($user) {
            $promoter = \Domain\Promoters\Models\Promoter::where('user_id', $user->id)->first();
        }

        $invitation = PromoterInvitation::create([
            'event_id' => $event->id,
            'promoter_id' => $promoter?->id,
            'token' => \Str::random(60),
            'email' => $validated['email'],
            'commission_type' => $validated['commission_type'],
            'commission_value' => $validated['commission_value'],
        ]);

        return $invitation;
    }

    public function asController(int $eventId, Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'commission_type' => ['required', 'in:fixed,percentage'],
            'commission_value' => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->handle($eventId, $validated);

        return back()->with('message', flash_message('Promoter invited successfully', 'The promoter has been invited successfully', 'success'));
    }
}
