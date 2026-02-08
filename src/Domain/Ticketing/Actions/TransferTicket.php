<?php

namespace Domain\Ticketing\Actions;

use App\Models\User;
use Domain\Ticketing\Facades\TokenGenerator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class TransferTicket
{
    use AsAction;

    public function handle($ticketId, $userEmail)
    {
        $ticket = \Domain\Ticketing\Models\Ticket::findOrFail($ticketId);
        $user = User::where('email', $userEmail)->firstOrFail();

        if ($ticket->transfers_left <= 0) {
            throw ValidationException::withMessages([
                'email' => __('tickets.no_transfers_left'),
            ]);
        }

        $ticket->user_id = $user->id;
        $ticket->token = TokenGenerator::generate($ticket->type);
        $ticket->transfers_left -= 1;
        $ticket->save();
    }

    public function asController(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'exists:users,email'],
        ]);

        if (auth()->user()->tickets()->where('id', $request->ticket)->count() == 0) {
            throw ValidationException::withMessages([
                'email' => __('tickets.not_your_ticket'),
            ]);
        }

        if (auth()->user()->email == $validated['email']) {
            throw ValidationException::withMessages([
                'email' => __('tickets.cant_transfer_to_yourself'),
            ]);
        }

        try {
            $this->handle($request->ticket, $validated['email']);

            return redirect()->route('tickets.index')->with('message', flash_success(__('tickets.transfer_success'), __('tickets.transfer_success_description')));
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }
    }
}
