<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Product;
use App\Services\AddressValidationService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function create()
    {
        abort_unless(auth()->user()->role === 'farmer', 403);

        if (! auth()->user()->isFarmerVerified()) {
            return redirect()->route('profile.show')
                ->with('error', 'পণ্য আপলোড বা বেচাকেনা করার পূর্বে আপনার জাতীয় পরিচয়পত্র (NID) ও ট্রেড লাইসেন্স জমা দিয়ে প্রোফাইল ভেরিফাই করা বাধ্যতামূলক।');
        }

        return view('products.create', [
            'categories' => Category::query()->orderBy('name')->get(),
            'divisions' => Division::query()->orderBy('name_bn')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'farmer', 403);

        if (! $request->user()->isFarmerVerified()) {
            return redirect()->route('profile.show')
                ->with('error', 'পণ্য আপলোড বা বেচাকেনা করার পূর্বে আপনার জাতীয় পরিচয়পত্র (NID) ও ট্রেড লাইসেন্স জমা দিয়ে প্রোফাইল ভেরিফাই করা বাধ্যতামূলক।');
        }

        if (! $request->user()->canCreateMoreListings()) {
            $limit = $request->user()->activeListingLimit();
            $planName = $request->user()->currentPlan()?->name ?? 'Free';
            return back()
                ->withInput()
                ->with('error', "আপনার বর্তমান সাবস্ক্রিপশন প্ল্যানে ({$planName}) সর্বোচ্চ {$limit}টি সক্রিয় লিস্টিং অনুমোদিত। আরও পণ্য যোগ করতে প্ল্যান আপগ্রেড করুন।");
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'unit' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
            'min_order_quantity' => 'nullable|integer|min:1',
            'district' => 'nullable|string|max:100',
            'upazila' => 'nullable|string|max:100',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'upazila_id' => 'nullable|integer|exists:upazilas,id',
            'union_id' => 'nullable|integer|exists:unions,id',
            'urban_area_id' => 'nullable|integer|exists:urban_areas,id',
            'post_office_id' => 'nullable|integer|exists:post_offices,id',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|url|max:2048',
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Validate parent-child administrative chain
        $validationService = app(AddressValidationService::class);
        $resolvedAddress = $validationService->validateChain($data);

        // Auto-synchronize legacy string fields if structured IDs are provided
        if (!empty($resolvedAddress['district'])) {
            $data['district'] = $resolvedAddress['district']->name_en;
        }
        if (!empty($resolvedAddress['upazila'])) {
            $data['upazila'] = $resolvedAddress['upazila']->name_en;
        }
        if (!empty($resolvedAddress['post_office']) && empty($data['postal_code'])) {
            $data['postal_code'] = $resolvedAddress['post_office']->post_code;
        }

        // Auto-match structured IDs if legacy strings were passed without IDs
        if (empty($data['district_id']) && !empty($data['district'])) {
            $matchingDistrict = District::where('name_en', 'like', $data['district'])
                ->orWhere('name_bn', 'like', $data['district'])
                ->first();
            if ($matchingDistrict) {
                $data['district_id'] = $matchingDistrict->id;
                $data['division_id'] = $data['division_id'] ?? $matchingDistrict->division_id;
            }
        }
        if (empty($data['upazila_id']) && !empty($data['upazila']) && !empty($data['district_id'])) {
            $matchingUpazila = Upazila::where('district_id', $data['district_id'])
                ->where(function ($q) use ($data) {
                    $q->where('name_en', 'like', $data['upazila'])
                      ->orWhere('name_bn', 'like', $data['upazila']);
                })->first();
            if ($matchingUpazila) {
                $data['upazila_id'] = $matchingUpazila->id;
            }
        }

        unset($data['photos']);
        $data['min_order_quantity'] = max(1, (int) ($data['min_order_quantity'] ?? 1));
        $data['category'] = Category::findOrFail($data['category_id'])->name;
        $product = $request->user()->products()->create($data);
        foreach ($request->file('photos', []) as $position => $photo) {
            $product->photos()->create(['path' => $photo->store('products', 'public'), 'position' => $position]);
        }

        return redirect()
            ->route('farmer.dashboard')
            ->with('success', 'নতুন পণ্য সফলভাবে যোগ হয়েছে।');
    }

    public function edit(Product $product)
    {
        abort_unless(auth()->user()->role === 'farmer', 403);
        abort_unless($product->farmer_id == auth()->id(), 403);

        $product->load(['division', 'districtRecord', 'upazilaRecord', 'union', 'urbanArea', 'postOffice']);

        return view('products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
            'divisions' => Division::query()->orderBy('name_bn')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($request->user()->role === 'farmer', 403);
        abort_unless($product->farmer_id == $request->user()->id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'unit' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
            'min_order_quantity' => 'nullable|integer|min:1',
            'district' => 'nullable|string|max:100',
            'upazila' => 'nullable|string|max:100',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'upazila_id' => 'nullable|integer|exists:upazilas,id',
            'union_id' => 'nullable|integer|exists:unions,id',
            'urban_area_id' => 'nullable|integer|exists:urban_areas,id',
            'post_office_id' => 'nullable|integer|exists:post_offices,id',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|url|max:2048',
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => ['integer', Rule::exists('product_photos', 'id')->where('product_id', $product->id)],
        ]);

        // Validate parent-child administrative chain
        $validationService = app(AddressValidationService::class);
        $resolvedAddress = $validationService->validateChain($data);

        // Auto-synchronize legacy string fields if structured IDs are provided
        if (!empty($resolvedAddress['district'])) {
            $data['district'] = $resolvedAddress['district']->name_en;
        }
        if (!empty($resolvedAddress['upazila'])) {
            $data['upazila'] = $resolvedAddress['upazila']->name_en;
        }
        if (!empty($resolvedAddress['post_office']) && empty($data['postal_code'])) {
            $data['postal_code'] = $resolvedAddress['post_office']->post_code;
        }

        // Auto-match structured IDs if legacy strings were passed without IDs
        if (empty($data['district_id']) && !empty($data['district'])) {
            $matchingDistrict = District::where('name_en', 'like', $data['district'])
                ->orWhere('name_bn', 'like', $data['district'])
                ->first();
            if ($matchingDistrict) {
                $data['district_id'] = $matchingDistrict->id;
                $data['division_id'] = $data['division_id'] ?? $matchingDistrict->division_id;
            }
        }
        if (empty($data['upazila_id']) && !empty($data['upazila']) && !empty($data['district_id'])) {
            $matchingUpazila = Upazila::where('district_id', $data['district_id'])
                ->where(function ($q) use ($data) {
                    $q->where('name_en', 'like', $data['upazila'])
                      ->orWhere('name_bn', 'like', $data['upazila']);
                })->first();
            if ($matchingUpazila) {
                $data['upazila_id'] = $matchingUpazila->id;
            }
        }

        $uploads = $request->file('photos', []);
        $removeIds = $data['remove_photo_ids'] ?? [];
        unset($data['photos'], $data['remove_photo_ids']);
        $data['min_order_quantity'] = max(1, (int) ($data['min_order_quantity'] ?? 1));
        $data['category'] = Category::findOrFail($data['category_id'])->name;
        if ($product->photos()->count() - count($removeIds) + count($uploads) > 5) {
            return back()->withErrors(['photos' => 'A product may have at most five photos.'])->withInput();
        }
        $product->update($data);
        foreach ($product->photos()->whereIn('id', $removeIds)->get() as $photo) {
            Storage::disk('public')->delete($photo->path);
            $photo->delete();
        }
        foreach ($uploads as $photo) {
            $product->photos()->create(['path' => $photo->store('products', 'public'), 'position' => $product->photos()->count()]);
        }

        return redirect()
            ->route('farmer.dashboard')
            ->with('success', 'পণ্যের তথ্য আপডেট হয়েছে।');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($request->user()->role === 'farmer', 403);
        abort_unless($product->farmer_id === $request->user()->id, 403);

        if ($product->orderItems()->exists()) {
            return back()->with('error', 'This product has order history and cannot be deleted. Set its stock to zero instead.');
        }

        if ($product->offers()->exists()) {
            return back()->with('error', 'This product has offer history and cannot be deleted. Set its stock to zero instead.');
        }

        if ($product->conversations()->exists()) {
            return back()->with('error', 'This product has private conversation history and cannot be deleted. Set its stock to zero instead.');
        }

        foreach ($product->photos as $photo) Storage::disk('public')->delete($photo->path);

        $product->delete();

        return redirect()->route('farmer.dashboard')->with('success', 'Product listing deleted.');
    }
}
