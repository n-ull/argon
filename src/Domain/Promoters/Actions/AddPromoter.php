<?php

namespace Domain\Promoters\Actions;

use Domain\Promoters\Models\Promoter;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class AddPromoter
{
    use AsAction;

    public function handle(int $eventId, array $validated)
    {
        $user = \App\Models\User::where('email', $validated['email'])->first();
        $referralCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));

        while (Promoter::where('referral_code', $referralCode)->exists()) {
            $referralCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));
        }

        if ($user) {
            $promoter = Promoter::firstOrCreate([
                'user_id' => $user->id,
                'referral_code' => $referralCode,
            ]);

            $promoter->events()->attach($eventId, [
                'commission_type' => $validated['commission_type'],
                'commission_value' => $validated['commission_value'],
            ]);

            return $promoter;
        }

        $promoter = Promoter::firstOrCreate([
            'user_id' => null,
            'referral_code' => $referralCode,
        ]);

        $promoter->events()->attach($eventId, [
            'commission_type' => $validated['commission_type'],
            'commission_value' => $validated['commission_value'],
        ]);

        return $promoter;
    }

    public function asController(int $eventId, Request $request)
    {
        $validated = $request->validate([
            'email' => ['email', 'required'],
            'commission_type' => ['required', 'in:percentage,fixed'],
            'commission_value' => ['required', 'numeric', 'min:0'],
        ]);

        $this->handle($eventId, $validated);

        return back()->with('message', flash_success('Promoter added successfully', 'The promoter has been added successfully'));

    }
}
