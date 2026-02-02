<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Services\OrderService;
use Illuminate\Http\Request;
use MercadoPago\Exceptions\MPApiException;

class MercadoPagoWebhookController extends Controller
{

    public function __construct(
        private OrderService $orderService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $id = $request->input('data')['id'];
        $mp = app(MercadoPagoService::class);

        try {
            $payment = $mp->getPayment($id);
        } catch (MPApiException $e) {
            \Log::info('MP Api Exception', [$e->getApiResponse()->getContent()]);
            return response()->json(['message' => $e->getMessage(), 'api_response' => $e->getApiResponse()->getContent()], 400);
        }

        if ($payment->status === 'approved') {
            // Update order status
            $order = Order::where('reference_id', $payment->external_reference)->first();

            if ($order) {
                try {
                    $this->orderService->completePendingOrder($order);
                    $order->update([
                        'used_payment_gateway_snapshot' => 'MercadoPago',
                    ]);
                    $order->save();

                    return response()->json(['message' => 'Order updated successfully'], 200);
                } catch (MPApiException $e) {
                    \Log::info('MP Api Exception', [$e->getApiResponse()->getContent()]);
                    return response()->json(['message' => $e->getMessage(), 'api_response' => $e->getApiResponse()->getContent()], 400);
                }
            }

        }

        return response()->json(['message' => 'Order already completed'], 200);
    }
}
