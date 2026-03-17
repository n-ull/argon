<?php

namespace Domain\Promoters\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Promoters\Models\Promoter;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Models\Ticket;
use Lorisleiva\Actions\Concerns\AsAction;

class GetScannedTicketsAnalytics
{
    use AsAction;

    public function handle(Event $event)
    {
        // 1. Get all scanned tickets for this event that belong to an order with a referral code
        $tickets = Ticket::query()
            ->where('event_id', $event->id)
            ->where('status', TicketStatus::USED)
            ->whereHas('order', function ($query) {
                $query->whereNotNull('referral_code')
                    ->where('referral_code', '!=', '');
            })
            ->with(['order:id,referral_code', 'product:id,name'])
            ->get();

        // 2. Group by referral code
        $ticketsByReferral = $tickets->groupBy(function ($ticket) {
            return $ticket->order->referral_code;
        });

        // 3. Get all promoters for these referral codes to get their names
        $referralCodes = $ticketsByReferral->keys()->toArray();
        $promoters = Promoter::whereIn('referral_code', $referralCodes)
            ->with('user:id,name,email')
            ->get()
            ->keyBy('referral_code');

        $result = [];

        foreach ($ticketsByReferral as $code => $ticketGroup) {
            $promoter = $promoters->get($code);

            // Handle potential prefix '+' if not found initially
            if (! $promoter) {
                if (str_starts_with($code, '+')) {
                    $promoter = Promoter::where('referral_code', substr($code, 1))->with('user:id,name,email')->first();
                } else {
                    $promoter = Promoter::where('referral_code', '+'.$code)->with('user:id,name,email')->first();
                }
            }

            $promoterName = $promoter ? $promoter->user->name : "Unknown ($code)";
            $promoterEmail = $promoter ? $promoter->user->email : "-";

            // 4. Group by ticket type (product)
            $breakdown = $ticketGroup->groupBy('product_id')->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'product_id' => $product ? $product->id : 0,
                    'product_name' => $product ? $product->name : 'Unknown Type',
                    'quantity' => $items->count(),
                ];
            })->values();

            $result[] = [
                'promoter_name' => $promoterName,
                'promoter_email' => $promoterEmail,
                'referral_code' => $code,
                'breakdown' => $breakdown,
                'total_scanned' => $ticketGroup->count(),
            ];
        }

        // Sort by total scanned desc
        return collect($result)->sortByDesc('total_scanned')->values();
    }
}
