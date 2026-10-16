<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function create()
    {
        abort_unless(auth()->user()->role === 'farmer', 403);

        return view('products.create');
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'farmer', 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|url|max:2048',
        ]);

        $request->user()->products()->create($data);

        return redirect()
            ->route('farmer.dashboard')
            ->with('success', 'নতুন পণ্য সফলভাবে যোগ হয়েছে।');
    }

    public function edit(Product $product)
    {
        abort_unless(auth()->user()->role === 'farmer', 403);
        abort_unless($product->farmer_id == auth()->id(), 403);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($request->user()->role === 'farmer', 403);
        abort_unless($product->farmer_id == $request->user()->id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|url|max:2048',
        ]);

        $product->update($data);

        return redirect()
            ->route('farmer.dashboard')
            ->with('success', 'পণ্যের তথ্য আপডেট হয়েছে।');
    }
}
