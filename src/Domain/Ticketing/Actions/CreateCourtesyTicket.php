<?php

namespace Domain\Ticketing\Actions;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Pluralizer;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCourtesyTicket
{
    use AsAction;

    public function handle(Event $event, Product $product, int $quantity, int $userId)
    {
        $tickets = Ticket::factory()->count($quantity)->create([
            'event_id' => $event->id,
            'product_id' => $product->id,
            'user_id' => $userId,
            'is_courtesy' => true,
            'type' => $product->ticket_type,
        ]);

        return $tickets;
    }

    public function asController(int $eventId, Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required,numeric,min:1,max:10',
        ]);

        $event = Event::findOrFail($eventId);
        $product = Product::findOrFail($validated['product_id']);

        if ($product->type !== ProductType::TICKET) {
            throw ValidationException::withMessages([
                'product_id' => 'Invalid product type',
            ]);
        }

        $tickets = $this->handle($event, $product, $validated['quantity'], $validated['user_id']);

        return back()->with('message', flash_success('Ticket created successfully', 'You have successfully created ' . $validated['quantity'] . ' ' . Pluralizer::pluralize($validated['quantity'], 'ticket')));
    }
}
