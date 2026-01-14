<?php

namespace Domain\Ticketing\Actions;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;


class CreateCourtesyTicket
{
    use AsAction;

    public function handle(Event $event, Product $product, int $quantity, array $userIds, int $givenBy)
    {
        $allTickets = collect();

        $tokenGenerator = app(\Domain\Ticketing\Support\TokenGenerator::class);

        foreach ($userIds as $userId) {
            for ($i = 0; $i < $quantity; $i++) {
                $ticket = Ticket::factory()->create([
                    'event_id' => $event->id,
                    'product_id' => $product->id,
                    'user_id' => $userId,
                    'is_courtesy' => true,
                    'given_by' => $givenBy,
                    'type' => $product->ticket_type,
                    'token' => $tokenGenerator->generate($product->ticket_type),
                ]);

                $allTickets->push($ticket);
            }
        }

        return $allTickets;
    }

    public function asController(int $eventId, Request $request)
    {
        $validated = $request->validate([
            'emails' => 'required|array|min:1',
            'emails.*' => 'required|email|exists:users,email',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1|max:10',
        ]);

        $event = Event::findOrFail($eventId);
        $product = Product::findOrFail($validated['product_id']);

        if ($product->product_type !== ProductType::TICKET) {
            throw ValidationException::withMessages([
                'product_id' => 'Invalid product type',
            ]);
        }

        // Find or create users for each email
        $userIds = [];
        foreach ($validated['emails'] as $email) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $email],
                ['name' => explode('@', $email)[0]] // Use email prefix as default name
            );
            $userIds[] = $user->id;
        }

        $tickets = $this->handle($event, $product, $validated['quantity'], $userIds, auth()->id());

        $totalTickets = count($userIds) * $validated['quantity'];
        return back()->with('message', flash_success('You succesfully created ' . $totalTickets . ' tickets', 'You have created ' . $totalTickets . ' tickets for ' . count($userIds) . ' users'));
    }
}
