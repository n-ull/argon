<?php

namespace App\Modules\Ordering\Controllers;

use App\Http\Controllers\Controller;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Resources\OrderResource;
use Inertia\Inertia;

class OrderDetailsController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['orderItems', 'event'])->withCount('tickets');

        return Inertia::render('orders/Show', [
            'order' => OrderResource::make($order)->resolve(),
        ]);
    }
}
