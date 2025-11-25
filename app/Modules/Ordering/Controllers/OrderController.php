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

        $orderDTO = CreateOrderData::from($request->validated());

        $order = $createOrder->handle($orderDTO);

        return redirect(route('orders.checkout', $order->id));
    }
}
