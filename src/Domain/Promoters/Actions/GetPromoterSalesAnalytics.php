<?php

namespace Domain\Promoters\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Domain\Promoters\Models\Promoter;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPromoterSalesAnalytics
{
    use AsAction;

    public function handle(Event $event)
    {
        // 1. Get all completed orders for this event that have a referral code
        // and eager load items + product
        $orders = Order::query()
            ->where('event_id', $event->id)
            ->where('status', OrderStatus::COMPLETED)
            ->whereNotNull('referral_code')
            ->where('referral_code', '!=', '')
            ->with(['orderItems.product'])
            ->get();

        // 2. Group by referral code
        $ordersByCode = $orders->groupBy('referral_code');

        // 3. Find Promoters for these codes
        $referralCodes = $ordersByCode->keys()->toArray();

        // We need to handle potential format differences (e.g. with/without '+')
        // Ideally we normalize, but for now let's query WHERE IN
        // If the referral code in Order is "+ABC", and Promoter is "ABC", we need to match them.
        // But usually they should match. Let's assume they match for now based on debug output.
        $promoters = Promoter::whereIn('referral_code', $referralCodes)
            ->with('user')
            ->get()
            ->keyBy('referral_code');

        $result = [];

        foreach ($ordersByCode as $code => $ordersGroup) {
            $promoter = $promoters->get($code);

            // If not found, maybe due to case sensitivity or '+' prefix?
            if (! $promoter) {
                // Try looking for match without '+' if code starts with +
                if (str_starts_with($code, '+')) {
                    $cleanCode = substr($code, 1);
                    $promoter = Promoter::where('referral_code', $cleanCode)->with('user')->first();
                }
                // Or vice versa
                if (! $promoter) {
                    $promoter = Promoter::where('referral_code', '+'.$code)->with('user')->first();
                }
            }

            if (! $promoter) {
                // Determine name for unknown promoter
                $name = 'Unknown ('.$code.')';
                $email = '-';
                $promoterId = null;
            } else {
                $name = $promoter->user->name;
                $email = $promoter->user->email;
                $promoterId = $promoter->id;
            }

            // Calculate breakdowns
            $breakdown = $ordersGroup->flatMap(function ($order) {
                return $order->orderItems;
            })
                ->groupBy('product_id')
                ->map(function ($items) {
                    $product = $items->first()->product;
                    return [
                        'product_id' => $product ? $product->id : 0,
                        'product_name' => $product ? $product->name : 'Unknown Product',
                        'quantity' => $items->sum('quantity'),
                    ];
                })
                ->values();

            $totalSales = $breakdown->sum('quantity');

            $result[] = [
                'id' => $promoterId,
                'name' => $name,
                'email' => $email,
                'referral_code' => $code, // include code for debug/display
                'sales_breakdown' => $breakdown,
                'total_sales' => $totalSales,
            ];
        }

        // Sort by total sales desc
        return collect($result)->sortByDesc('total_sales')->values();
    }
}
