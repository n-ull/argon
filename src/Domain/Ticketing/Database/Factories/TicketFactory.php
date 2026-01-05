<?php

namespace Domain\Ticketing\Database\Factories;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Models\Order;
use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Enums\TicketType;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use PragmaRX\Google2FA\Google2FA;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function dynamic()
    {
        return $this->state(function (array $attributes) {
            $google2fa = app(Google2FA::class);

            return [
                'token' => $google2fa->generateSecretKey(),
                'type' => TicketType::DYNAMIC,
            ];
        });
    }

    public function static()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TicketType::STATIC
            ];
        });
    }

    public function definition()
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'status' => TicketStatus::ACTIVE,
            'transfers_left' => 0,
            'is_courtesy' => false,
            'used_at' => null,
            'expired_at' => null,
        ];
    }
}
