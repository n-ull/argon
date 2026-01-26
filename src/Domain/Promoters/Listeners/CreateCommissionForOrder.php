<?php

namespace Domain\Promoters\Listeners;

use Domain\Ordering\Events\OrderCreated;
use Domain\Promoters\Models\Commission;
use Domain\Promoters\Models\Promoter;
use Domain\Promoters\Models\PromoterEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateCommissionForOrder implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        \Illuminate\Support\Facades\Log::info('OrderCreated listener fired', ['order_id' => $event->order->id, 'referral_code' => $event->referralCode]);

        if (! $event->referralCode) {
            \Illuminate\Support\Facades\Log::info('No referral code in event');
            return;
        }

        $promoter = Promoter::where('referral_code', $event->referralCode)->first();

        if (! $promoter) {
            \Illuminate\Support\Facades\Log::info('Promoter not found', ['code' => $event->referralCode]);
            return;
        }

        $promoterEvent = PromoterEvent::query()
            ->where('promoter_id', $promoter->id)
            ->where('event_id', $event->order->event_id)
            ->where('enabled', true)
            ->first();

        if (! $promoterEvent) {
            \Illuminate\Support\Facades\Log::info('PromoterEvent rule not found or disabled', [
                'promoter_id' => $promoter->id,
                'event_id' => $event->order->event_id
            ]);
            return;
        }

        $amount = $this->calculateCommission($event->order, $promoterEvent);
        \Illuminate\Support\Facades\Log::info('Calculated amount', ['amount' => $amount]);

        Commission::create([
            'promoter_id' => $promoter->id,
            'order_id' => $event->order->id,
            'event_id' => $event->order->event_id,
            'amount' => $amount,
            'status' => 'pending',
        ]);
        \Illuminate\Support\Facades\Log::info('Commission created');
    }

    protected function calculateCommission($order, $promoterEvent): float
    {
        if ($promoterEvent->commission_type === 'fixed') {
            return (float) $promoterEvent->commission_value;
        }

        // Percentage calculation based on total_gross
        return round(($order->total_gross * $promoterEvent->commission_value) / 100, 2);
    }
}
