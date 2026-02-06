<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScannerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        $userIsDoorman = $event->doormen()->where('user_id', auth()->user()->id)->where('is_active', true)->exists();

        if (! $userIsDoorman) {
            abort(403);
        }

        return Inertia::render('doormen/Scanner', [
            'event' => $event,
        ]);
    }
}
