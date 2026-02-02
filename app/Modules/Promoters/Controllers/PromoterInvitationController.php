<?php

namespace App\Modules\Promoters\Controllers;

use App\Http\Controllers\Controller;
use Domain\Promoters\Models\PromoterInvitation;
use Inertia\Inertia;

class PromoterInvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = PromoterInvitation::where('token', $token)
            ->with(['organizer'])
            ->firstOrFail();

        $isEmailRegistered = \App\Models\User::where('email', $invitation->email)->exists();

        return Inertia::render('promoters/invitations/Show', [
            'invitation' => $invitation,
            'organizer' => $invitation->organizer,
            'isEmailRegistered' => $isEmailRegistered,
        ]);
    }
}
