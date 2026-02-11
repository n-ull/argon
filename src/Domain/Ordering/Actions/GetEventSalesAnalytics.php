<?php

namespace Domain\Ordering\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetEventSalesAnalytics
{
    use AsAction;

    public function handle(Event $event, string $period = 'day', ?string $startDate = null, ?string $endDate = null)
    {
        $query = Order::query()
            ->where('event_id', $event->id);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Determine format based on period
        $dateFormat = match ($period) {
            'month' => '%Y-%m',
            'week' => '%x-%v', // Year-Week
            default => '%Y-%m-%d',
        };

        $selectDate = match (config('database.default')) {
            'sqlite' => match ($period) {
                    'month' => "strftime('%Y-%m', created_at)",
                    'week' => "strftime('%Y-%W', created_at)",
                    default => "strftime('%Y-%m-%d', created_at)",
                },
            default => match ($period) { // MySQL
                    'month' => "DATE_FORMAT(created_at, '%Y-%m')",
                    'week' => "DATE_FORMAT(created_at, '%x-%v')",
                    default => "DATE_FORMAT(created_at, '%Y-%m-%d')",
                },
        };

        $orders = $query->select([
            DB::raw("$selectDate as date"),
            DB::raw('count(*) as total_orders'),
            DB::raw('sum(case when status = "'.OrderStatus::COMPLETED->value.'" then 1 else 0 end) as completed_orders'),
            DB::raw('sum(case when status = "'.OrderStatus::COMPLETED->value.'" then total_gross else 0 end) as revenue')
        ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $orders;
    }
}
