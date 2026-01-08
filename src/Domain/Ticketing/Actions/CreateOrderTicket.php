<?php

namespace Domain\Ticketing\Actions;

use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Enums\TicketType;
use Domain\Ticketing\Models\Ticket;
use Lorisleiva\Actions\Concerns\AsAction;
use PragmaRX\Google2FA\Google2FA;

class CreateOrderTicket
{
    use AsAction;

    public function __construct(
        private Google2FA $google2fa,
    ) {
    }

    public function handle(Product $product, ?int $orderId)
    {
        $ticket = Ticket::create([
            'token' => $this->google2fa->generateSecretKey(),
            'event_id' => $product->event_id,
            'product_id' => $product->id,
            'type' => TicketType::DYNAMIC,
            'status' => TicketStatus::ACTIVE,
            'transfers_left' => 0, // TODO: Implement transfers
            'is_courtesy' => false,
            'used_at' => null,
            'expired_at' => null,
            'order_id' => $orderId ?? null
        ]);

        return $ticket;
    }

    public function asController(Product $product, $orderId)
    {
        $this->handle($product, $orderId);

        return back();
    }
}
