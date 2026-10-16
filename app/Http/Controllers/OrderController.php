<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'buyer', 403);

        $orders = Order::with('items.product')
            ->where('buyer_id', $request->user()->id)
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($request->user()->role === 'buyer', 403);
        abort_unless($order->buyer_id === $request->user()->id, 403);

        $order->load('items.product');

        return view('orders.show', compact('order'));
    }
}