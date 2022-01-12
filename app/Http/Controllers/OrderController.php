<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function orders()
    {
        $orders = Order::query()
            ->with('user')
            ->with('product')
            ->get();

        return OrderResource::collection($orders);
    }
}
