<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Resources\TicketResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Ticket $ticket, Request $request)
    {
        $ticket->load(['product', 'event', 'order']);

        return Inertia::render('tickets/Details', [
            'ticket' => TicketResource::make($ticket)->resolve(),
        ]);
    }
}
