<?php

namespace App\Modules\Ordering\Controllers;

use App\Http\Controllers\Controller;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Resources\CheckoutResource;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function checkout(Order $order)
    {
        $settings = $order->event->organizer->settings;

        if ($order->status === OrderStatus::EXPIRED || $order->status === OrderStatus::CANCELLED) {
            return redirect(route('events.show', $order->event->slug))->with('message', 'Order is expired.');
        }

        return Inertia::render('orders/Checkout', [
            'order' => CheckoutResource::make($order)->resolve(),
            'settings' => [
                'is_modo_active' => $settings->is_modo_active,
                'is_mercadopago_active' => $settings->is_mercadopago_active,
            ],
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->status !== OrderStatus::PENDING) {
            return back()->with('error', 'Order cannot be cancelled.');
        }

        $order->update(['status' => OrderStatus::CANCELLED]);

        return redirect()->route('events.show', $order->event->slug)
            ->with('success', 'Order cancelled successfully.');
    }
}
