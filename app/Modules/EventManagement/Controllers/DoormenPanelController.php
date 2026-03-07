<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Resources\EventResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoormenPanelController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (!auth()->user()->doormen()->exists()) {
            return redirect()->route('dashboard');
        }

        $events = auth()->user()->doormen()->with('event')->get()->map(function ($doormen) {
            return $doormen->event;
        });

        return Inertia::render('doormen/Panel', [
            'events' => EventResource::collection($events)->resolve()
        ]);
    }
}
