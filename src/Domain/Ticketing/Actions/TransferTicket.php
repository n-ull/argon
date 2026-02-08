<?php

namespace Domain\Ticketing\Actions;

use App\Models\User;
use Domain\Ticketing\Facades\TokenGenerator;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class TransferTicket
{
    use AsAction;

    public function handle($ticketId, $userEmail)
    {
        $ticket = \Domain\Ticketing\Models\Ticket::findOrFail($ticketId);
        $user = User::where('email', $userEmail)->firstOrFail();

        if ($ticket->transfers_left <= 0) {
            throw new \Exception(__('tickets.no_transfers_left'));
        }

        $ticket->user_id = $user->id;
        $ticket->token = TokenGenerator::generateToken($ticket->type);
        $ticket->transfers_left -= 1;
        $ticket->save();
    }

    public function asController(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => ['required', 'exists:ticketing_tickets,id'],
            'user_email' => ['required', 'exists:users,email'],
        ]);

        try {
            $this->handle($validated['ticket_id'], $validated['user_email']);

            return response()->json([
                'message' => __('tickets.transfer_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
