<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function update(Request $request, Product $product)
    {
        $minOrder = (int) ($product->min_order_quantity ?? 1);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:' . $minOrder, 'max:4294967295'],
        ], [
            'quantity.min' => "এই পণ্যটির সর্বনিম্ন অর্ডারের পরিমাণ {$minOrder} {$product->unit} হতে হবে।",
        ]);

        if ($validated['quantity'] < $minOrder) {
            return back()->withErrors([
                'quantity' => "এই পণ্যটির সর্বনিম্ন অর্ডারের পরিমাণ {$minOrder} {$product->unit} হতে হবে।",
            ]);
        }

        if ($validated['quantity'] > $product->stock) {
            return back()->withErrors([
                'quantity' => 'স্টকের চেয়ে বেশি পণ্য কার্টে রাখা যাবে না।',
            ]);
        }

        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = (int) $validated['quantity'];
        $request->session()->put('cart', $cart);

        return back()->with('success', 'কার্ট আপডেট হয়েছে।');
    }

    public function remove(Request $request, Product $product)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('success', 'পণ্যটি কার্ট থেকে সরানো হয়েছে।');
    }
}
