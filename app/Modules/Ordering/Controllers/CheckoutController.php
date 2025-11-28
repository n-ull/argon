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
            return redirect(route('events.show', $order->event->slug))
                ->with('message', flash_error(
                    'Order unavailable.',
                    'This order has expired or been cancelled.'
                ));
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
            return back()->with('message', flash_error(
                'Order cannot be cancelled.',
                'Order is not in pending state.'
            ));
        }

        $order->update(['status' => OrderStatus::CANCELLED]);

        return redirect()->route('events.show', $order->event->slug)
            ->with('message', flash_success(
                'Order cancelled.',
                'Your order has been cancelled successfully.'
            ));
    }
}
