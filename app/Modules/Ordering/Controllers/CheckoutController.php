<?php

namespace App\Modules\Ordering\Controllers;

use App\Http\Controllers\Controller;
use Domain\Ordering\Models\Order;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function checkout(Order $order)
    {
        $settings = $order->event->organizer->settings;

        return Inertia::render('orders/Checkout', [
            'order' => $order,
            'settings' => $settings,
        ]);
    }
}
