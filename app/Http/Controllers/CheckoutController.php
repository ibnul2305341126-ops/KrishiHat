<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    // Checkout page
    public function index()
    {
        abort_unless(auth()->user()->role === 'buyer', 403);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'তোমার Cart খালি।');
        }

        $products = Product::whereIn('id', array_keys($cart))->get();

        if ($products->count() !== count($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Cart-এর কোনো পণ্য আর পাওয়া যাচ্ছে না।');
        }

        $total = 0;

        foreach ($products as $product) {
            $total += $product->price * (int) $cart[$product->id];
        }

        return view('checkout', compact('products', 'cart', 'total'));
    }

    // Place order
    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'buyer', 403);

        $data = $request->validate([
            'delivery_address' => 'required|string|max:2000',
            'phone' => 'required|string|max:30',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'তোমার Cart খালি।');
        }

        $order = DB::transaction(function () use ($cart, $data, $request) {
            $products = Product::whereIn('id', array_keys($cart))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart-এর কোনো পণ্য পাওয়া যাচ্ছে না।',
                ]);
            }

            $total = 0;
            $items = [];

            foreach ($products as $product) {
                $quantity = filter_var(
                    $cart[$product->id] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (
                    !$quantity ||
                    $quantity < 1 ||
                    $product->stock < $quantity
                ) {
                    throw ValidationException::withMessages([
                        'cart' => $product->name .
                            ' পণ্যের পর্যাপ্ত Stock নেই।',
                    ]);
                }

                if (!$product->farmer_id) {
                    throw ValidationException::withMessages([
                        'cart' => $product->name .
                            ' পণ্যের Farmer নির্ধারিত নেই।',
                    ]);
                }

                $subtotal = round(
                    (float) $product->price * $quantity,
                    2
                );

                $total += $subtotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'buyer_id' => $request->user()->id,
                'total_amount' => $total,
                'status' => 'pending',
                'delivery_address' => $data['delivery_address'],
                'phone' => $data['phone'],
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'farmer_id' => $item['product']->farmer_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->decrement(
                    'stock',
                    $item['quantity']
                );
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('home')
            ->with('success', 'তোমার Order সফলভাবে সম্পন্ন হয়েছে। Order ID: ' . $order->id);
    }
}