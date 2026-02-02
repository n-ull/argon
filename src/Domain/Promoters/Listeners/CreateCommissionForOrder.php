<?php

namespace Domain\Promoters\Listeners;

use Domain\Ordering\Events\OrderCreated;
use Domain\Promoters\Models\Commission;
use Domain\Promoters\Models\Promoter;

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

        // Check if promoter belongs to the event's organizer
        $organizerId = $event->order->event->organizer_id;
        $relationship = $promoter->organizers()->where('organizer_id', $organizerId)->first();

        if (! $relationship) {
            \Illuminate\Support\Facades\Log::info('Promoter does not belong to event organizer', [
                'promoter_id' => $promoter->id,
                'event_organizer' => $organizerId
            ]);
            return;
        }

        if (! $relationship->pivot->enabled) {
            \Illuminate\Support\Facades\Log::info('Promoter is disabled for this organizer', ['promoter_id' => $promoter->id]);
            return;
        }

        $amount = $this->calculateCommission($event->order, $relationship->pivot);
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

    protected function calculateCommission($order, $settings): float
    {
        if ($settings->commission_type === 'fixed') {
            return (float) $settings->commission_value;
        }

        // Percentage calculation based on total_gross
        return round(($order->total_gross * $settings->commission_value) / 100, 2);
    }
}
