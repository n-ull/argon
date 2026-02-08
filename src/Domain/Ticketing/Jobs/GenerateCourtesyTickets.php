<?php

namespace Domain\Ticketing\Jobs;

use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Facades\TokenGenerator;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateCourtesyTickets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $eventId,
        public int $productId,
        public int $quantity,
        public array $userIds,
        public int $givenBy,
        public string $ticketType,
        public ?int $transfersLeft = 0
    ) {
    }

    public function handle(): void
    {
        DB::transaction(function () {
            foreach ($this->userIds as $userId) {
                for ($i = 0; $i < $this->quantity; $i++) {
                    Ticket::create([
                        'event_id' => $this->eventId,
                        'product_id' => $this->productId,
                        'user_id' => $userId,
                        'is_courtesy' => true,
                        'given_by' => $this->givenBy,
                        'type' => $this->ticketType,
                        'token' => TokenGenerator::generate(\Domain\Ticketing\Enums\TicketType::from($this->ticketType)),
                        'status' => TicketStatus::ACTIVE,
                        'transfers_left' => $this->transfersLeft,
                        'used_at' => null,
                        'expired_at' => null,
                    ]);
                }
            }
        });
    }
}
