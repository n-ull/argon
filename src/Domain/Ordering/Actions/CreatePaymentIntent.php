<?php

namespace Domain\Ordering\Actions;

use App\Services\Payment\PaymentProcessor;
use Domain\Ordering\Models\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePaymentIntent
{
    use AsAction;

    public function __construct(
        private PaymentProcessor $paymentProcessor,
        private \Domain\Ordering\Services\OrderService $orderService
    ) {
    }

    /**
     * Summary of handle
     * @param Order $order
     * @param array $data
     * @param string $raiseMethod 'internal' or 'split'
     */
    public function handle(Order $order, array $data, string $raiseMethod)
    {
        // Check if order is free
        if ($order->total_gross <= 0) {
            $this->orderService->completePendingOrder($order->id);
            return redirect()->route('orders.show', $order);
        }

        // dd($order, $data, $raiseMethod);
        return $this->paymentProcessor->createIntent($order, $data, $raiseMethod);
    }

    public function asController(int $orderId)
    {
        $order = Order::with('event.organizer.settings')->find($orderId);
        $raiseMethod = $order->event->organizer->settings->raise_money_method;

        return $this->handle($order, [], $raiseMethod);
    }
}
