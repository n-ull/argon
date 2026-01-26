<?php

namespace App\Modules\Ordering\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ordering\Requests\StoreOrderRequest;
use Domain\Ordering\Actions\CreateOrder;
use Domain\Ordering\Data\CreateOrderData;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CreateOrder $createOrder)
    {
        try {
            $data = $request->validated();
            $data['userId'] = $request->user()?->id;
            $data['referral_code'] = session('referral_code_'.$data['eventId']);

            $orderDTO = CreateOrderData::from($data);

            $order = $createOrder->handle($orderDTO);

            if ($order->status === \Domain\Ordering\Enums\OrderStatus::COMPLETED) {
                return redirect(route('orders.show', $order->id));
            }

            return redirect(route('orders.checkout', $order->id));
        } catch (\Domain\Ordering\Exceptions\OrderAlreadyPendingException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'orderId' => $e->orderId,
            ]);
        }
    }
}
