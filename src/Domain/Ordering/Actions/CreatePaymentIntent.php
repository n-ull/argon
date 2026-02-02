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

        $data['order'] = $order;
        $data['method'] = $raiseMethod;

        if ($data['method'] == 'split') {
            $data['access_token'] = $order->event->organizer->mercadoPagoAccount->access_token;
            $data['service_fee'] = $order->event->organizer->settings->service_fee;
        }

        // dd($order, $data, $raiseMethod);
        return $this->paymentProcessor->createIntent($order, $data, $raiseMethod);
    }

    public function asController(int $orderId, \Illuminate\Http\Request $request)
    {
        $order = Order::with(['event.organizer.settings', 'client'])->find($orderId);
        $raiseMethod = $order->event->organizer->settings->raise_money_method;

        $data = [
            'gateway' => $request->input('gateway', 'mp'), // Default to mp or get from request
        ];

        return response()->json([
            'url' => $this->handle($order, $data, $raiseMethod)
        ]);
    }
}
