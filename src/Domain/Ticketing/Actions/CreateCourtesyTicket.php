<?php

namespace Domain\Ticketing\Actions;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;


class CreateCourtesyTicket
{
    use AsAction;

    public function handle(Event $event, Product $product, int $quantity, array $userIds, int $givenBy, int $transfersLeft)
    {
        \Domain\Ticketing\Jobs\GenerateCourtesyTickets::dispatch(
            $event->id,
            $product->id,
            $quantity,
            $userIds,
            $givenBy,
            $product->ticket_type->value,
            $transfersLeft
        );
    }

    public function asController(int $eventId, Request $request)
    {
        $validated = $request->validate([
            'emails' => 'required|array|min:1',
            'emails.*' => 'required|email',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1|max:10',
            'transfersLeft' => 'required|numeric|min:0|max:10',
        ]);

        $event = Event::findOrFail($eventId);
        $product = Product::findOrFail($validated['product_id']);

        if ($product->product_type !== ProductType::TICKET) {
            throw ValidationException::withMessages([
                'product_id' => 'Invalid product type',
            ]);
        }

        // Separate existing users from non-registered emails
        $userIds = [];
        foreach ($validated['emails'] as $email) {
            $user = \App\Models\User::where('email', $email)->first();
            
            if ($user) {
                $userIds[] = $user->id;
            } else {
                // Check if there is already an active (unexpired, unaccepted) invitation for this email
                $activeInvitation = \Domain\Ticketing\Models\TicketInvitation::where('email', $email)
                    ->whereNull('accepted_at')
                    ->where('expires_at', '>', now())
                    ->exists();

                // Create the invitation record
                $invitation = \Domain\Ticketing\Models\TicketInvitation::create([
                    'email' => $email,
                    'event_id' => $event->id,
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'transfers_left' => $validated['transfersLeft'],
                    'given_by' => auth()->id(),
                    'ticket_type' => $product->ticket_type->value,
                    'expires_at' => now()->addDays(7), // Default to 7 days
                ]);

                // Only send the invitation email if there isn't an active one already
                if (!$activeInvitation) {
                    Mail::to($email)->send(new \Domain\Ticketing\Mail\CourtesyTicketInvitationGenerated($invitation));
                }
            }
        }

        if (!empty($userIds)) {
            $this->handle($event, $product, $validated['quantity'], $userIds, auth()->id(), $validated['transfersLeft']);
        }

        return back();
    }
}
