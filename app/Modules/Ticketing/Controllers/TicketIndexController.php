<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use Domain\Ticketing\Resources\TicketResource;
use Inertia\Inertia;

class TicketIndexController extends Controller
{
    public function index()
    {
        $tickets = auth()->user()->tickets()->with(['event', 'order', 'product'])->get();

        return Inertia::render('tickets/Index', [
            'tickets' => TicketResource::collection($tickets)->resolve(),
        ]);
    }
}
