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
            'events' => $promoter->events,
            'commissions' => $promoter->commissions()->completed()->get(),
        ]);
    }
}
