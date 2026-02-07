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

        if ($order->status === OrderStatus::COMPLETED) {
            return redirect()->route('orders.show', $order->id);
        }

        if ($order->status === OrderStatus::EXPIRED || $order->status === OrderStatus::CANCELLED) {
            return redirect()->route('events.show', $order->event->slug)
                ->with('message', flash_error(
                    __('order.order_unavailable'),
                    __('order.order_unavailable.description')
                ));
        }

        if ($order->user_id !== auth()->id()) {
            return redirect()->route('events.show', $order->event->slug)
                ->with('message', flash_error(
                    __('order.order_unavailable'),
                    __('order.order_unavailable.description')
                ));
        }

        return Inertia::render('orders/Checkout', [
            'order' => CheckoutResource::make($order)->resolve(),
            'settings' => [
                'is_modo_active' => $settings->is_modo_active,
                'is_mercadopago_active' => $settings->is_mercadopago_active,
                'raise_money_method' => $settings->raise_money_method,
                'is_account_linked' => $order->event->organizer->owner->mercadoPagoAccount()->exists(),
            ],
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->status !== OrderStatus::PENDING) {
            return back()->with('message', flash_error(
                __('order.cannot_be_cancelled'),
                __('order.not_in_pending_state')
            ));
        }

        $order->update(['status' => OrderStatus::CANCELLED]);

        return redirect()->route('events.show', $order->event->slug)
            ->with('message', flash_success(
                __('order.cancelled_order'),
                __('order.cancelled_order.description')
            ));
    }
}
