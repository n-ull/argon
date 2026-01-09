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
        // Ensure user owns the order or is an organizer of the event
        // For now, let's just check if it's the authenticated user's order
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('orders/Show', [
            'order' => OrderResource::make($order)->resolve(),
        ]);
    }
}
