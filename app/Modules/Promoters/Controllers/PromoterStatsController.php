<?php

namespace App\Modules\Promoters\Controllers;

use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;

class PromoterStatsController
{
    public function __invoke(Request $request, int $eventId)
    {
        $promoter = $request->user()->promoter;

        if (! $promoter) {
            abort(403, 'User is not a promoter');
        }

        $event = Event::findOrFail($eventId);

        // Ensure the promoter is associated with the event? 
        // Logic in ManageEventController didn't strictly check association for stats calculation 
        // but relied on referral code matching. 
        // Ideally we should check if $event->promoters->contains($promoter) but for now let's stick to the sales logic.

        $stats = $event->orders()
            ->where('referral_code', $promoter->referral_code)
            ->where('status', \Domain\Ordering\Enums\OrderStatus::COMPLETED)
            ->with(['orderItems.product'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems;
            })
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $items->sum('quantity'),
                ];
            })
            ->values();

        return response()->json($stats);
    }
}
