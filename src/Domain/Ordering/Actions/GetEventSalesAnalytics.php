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

        // SQLite compatibility check (since local dev might be sqlite)
        // Ideally should support both mysql and sqlite but for now assuming MySQL/Postgres standard or using raw date functions carefully.
        // Assuming MySQL for production usually, but let's check what DB driver they use?
        // Ah, the user didn't specify DB, but assuming standard Laravel usage.
        // Let's use Carbon to post-process if we want to be safe, OR use DB raw.
        // Using DB::raw for grouping is more efficient.

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
