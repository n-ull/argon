<?php

namespace Domain\Ticketing\Actions;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;


class CreateCourtesyTicket
{
    use AsAction;

    public function handle(Event $event, Product $product, int $quantity, array $userIds, int $givenBy)
    {
        \Domain\Ticketing\Jobs\GenerateCourtesyTickets::dispatch(
            $event->id,
            $product->id,
            $quantity,
            $userIds,
            $givenBy,
            $product->ticket_type->value
        );
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

        $this->handle($event, $product, $validated['quantity'], $userIds, auth()->id());

        $totalTickets = count($userIds) * $validated['quantity'];
        return back();
    }
}
