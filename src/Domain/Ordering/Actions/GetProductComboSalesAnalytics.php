<?php

namespace Domain\Ordering\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\OrderItem;
use Domain\ProductCatalog\Models\Combo;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProductComboSalesAnalytics
{
    use AsAction;

    public function handle(Event $event): array
    {
        // ---------------------------------------------------------------
        // PRODUCTS analytics (order items that are NOT part of a combo)
        // ---------------------------------------------------------------
        $productRows = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.event_id', $event->id)
            ->whereNull('order_items.combo_id')
            ->whereNull('order_items.deleted_at')
            ->whereNull('orders.deleted_at')
            ->select([
                'order_items.product_id',
                'products.name as product_name',
                DB::raw('SUM(CASE WHEN orders.status = "' . OrderStatus::COMPLETED->value . '" THEN order_items.quantity ELSE 0 END) as completed_quantity'),
                DB::raw('SUM(CASE WHEN orders.status = "' . OrderStatus::CANCELLED->value . '" THEN order_items.quantity ELSE 0 END) as cancelled_quantity'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
            ])
            ->groupBy('order_items.product_id', 'products.name')
            ->orderByDesc('completed_quantity')
            ->get()
            ->map(fn ($row) => [
                'id'                   => $row->product_id,
                'name'                 => $row->product_name,
                'completed_quantity'   => (int) $row->completed_quantity,
                'cancelled_quantity'   => (int) $row->cancelled_quantity,
                'total_quantity'       => (int) $row->total_quantity,
            ])
            ->values()
            ->toArray();

        // ---------------------------------------------------------------
        // COMBOS analytics (order items that belong to a combo)
        // ---------------------------------------------------------------
        $comboRows = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('combos', 'order_items.combo_id', '=', 'combos.id')
            ->where('orders.event_id', $event->id)
            ->whereNotNull('order_items.combo_id')
            ->whereNull('order_items.deleted_at')
            ->whereNull('orders.deleted_at')
            ->select([
                'order_items.combo_id',
                'combos.name as combo_name',
                // Count each order item as 1 unit of the combo sold (the combo_id groups them)
                DB::raw('COUNT(DISTINCT CASE WHEN orders.status = "' . OrderStatus::COMPLETED->value . '" THEN orders.id END) as completed_quantity'),
                DB::raw('COUNT(DISTINCT CASE WHEN orders.status = "' . OrderStatus::CANCELLED->value . '" THEN orders.id END) as cancelled_quantity'),
                DB::raw('COUNT(DISTINCT orders.id) as total_quantity'),
            ])
            ->groupBy('order_items.combo_id', 'combos.name')
            ->orderByDesc('completed_quantity')
            ->get()
            ->map(fn ($row) => [
                'id'                   => $row->combo_id,
                'name'                 => $row->combo_name,
                'completed_quantity'   => (int) $row->completed_quantity,
                'cancelled_quantity'   => (int) $row->cancelled_quantity,
                'total_quantity'       => (int) $row->total_quantity,
            ])
            ->values()
            ->toArray();

        return [
            'products' => $productRows,
            'combos'   => $comboRows,
        ];
    }
}
