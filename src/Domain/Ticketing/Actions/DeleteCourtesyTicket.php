<?php

namespace Domain\Ticketing\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ticketing\Models\Ticket;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCourtesyTicket
{
    use AsAction;

    public function handle(Ticket $ticket)
    {
        $ticket->delete();
    }

    public function asController(int $eventId, Ticket $courtesy)
    {
        if ($courtesy->event_id !== $eventId || !$courtesy->is_courtesy) {
            abort(404);
        }

        $this->handle($courtesy);

        return back()->with('message', flash_success('Ticket deleted successfully', 'The courtesy ticket has been deleted.'));
    }
}
