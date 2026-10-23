<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Offer;
use Illuminate\Http\Request;

class BuyerDashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'buyer', 403);

        $orders = Order::with('items.product')
            ->where('buyer_id', $request->user()->id)
            ->latest()
            ->get();
        $offers = Offer::query()
            ->with(['product', 'farmer', 'events.actor'])
            ->where('buyer_id', $request->user()->id)
            ->latest()
            ->get();
        $orderCount = $orders->count();
        $totalOrdered = $orders->sum('total_amount');
        $latestOrder = $orders->first();

        return view('buyer.dashboard', compact('orders', 'offers', 'orderCount', 'totalOrdered', 'latestOrder'));
    }
}
