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

            $orderDTO = CreateOrderData::from($data);

            $order = $createOrder->handle($orderDTO);

            return redirect(route('orders.checkout', $order->id));
        } catch (\Domain\Ordering\Exceptions\OrderAlreadyPendingException $e) {
            return back()->withErrors([
                'orderId' => $e->orderId,
            ]);
        }
    }
}
