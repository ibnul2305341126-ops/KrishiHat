<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Offer;
use Illuminate\Http\Request;

class FarmerDashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'farmer', 403);

        $farmerId = $request->user()->id;
        $products = $request->user()->products()->latest()->get();
        $orders = Order::query()
            ->whereHas('items', fn ($query) => $query->where('farmer_id', $farmerId))
            ->with(['buyer', 'items' => fn ($query) => $query->where('farmer_id', $farmerId)->with('product')])
            ->latest()
            ->get();
        $offers = Offer::query()
            ->with(['product', 'buyer', 'events.actor'])
            ->where('farmer_id', $farmerId)
            ->latest()
            ->get();
        $unitsOrderedByUnit = OrderItem::with('product')->where('farmer_id', $farmerId)->get()
            ->groupBy(fn ($item) => $item->product?->unit ?: 'units')
            ->map(fn ($items) => $items->sum('quantity'));
        $stockByUnit = $products->groupBy(fn ($product) => $product->unit ?: 'units')
            ->map(fn ($items) => $items->sum('stock'));
        $statuses = Order::knownStatuses();

        return view('farmer.dashboard', compact('products', 'orders', 'offers', 'unitsOrderedByUnit', 'stockByUnit', 'statuses'));
    }
}
