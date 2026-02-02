<?php

namespace App\Modules\Promoters\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PromoterDashboardController
{
    public function __invoke(Request $request)
    {
        $promoter = $request->user()->promoter;

        if (! $promoter) {
            abort(403, 'User is not a promoter');
        }


        return Inertia::render('promoters/Dashboard', [
            'events' => \Domain\EventManagement\Models\Event::whereIn('organizer_id', $promoter->organizers()->pluck('organizers.id'))->get(),
            'commissions' => $promoter->commissions()->get(),
            'referral_code' => $promoter->referral_code,
        ]);
    }
}
