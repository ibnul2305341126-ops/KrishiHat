<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminCatalogueController extends Controller
{
    /**
     * Display product catalogue review table.
     */
    public function index(Request $request)
    {
        $query = Product::with(['farmer', 'photos', 'categoryRecord']);

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('district')) {
            $query->where('district', 'like', '%' . $request->district . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('suggested_correction', 'like', "%{$search}%");
            });
        }

        $products = $query->latest('id')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $districts = Product::whereNotNull('district')->where('district', '!=', '')->distinct()->pluck('district');

        $statusCounts = [
            'total' => Product::count(),
            'verified' => Product::where('verification_status', 'verified')->count(),
            'suggested' => Product::where('verification_status', 'suggested')->count(),
            'unverified' => Product::where('verification_status', 'unverified')->count(),
            'rejected' => Product::where('verification_status', 'rejected')->count(),
        ];

        return view('admin.catalogue.index', compact('products', 'categories', 'districts', 'statusCounts'));
    }

    /**
     * Update review, verify status, or set suggested correction.
     */
    public function updateReview(Request $request, Product $product)
    {
        $data = $request->validate([
            'verification_status' => 'required|in:unverified,suggested,verified,rejected',
            'suggested_correction' => 'nullable|string|max:1000',
            'reference_image' => 'nullable|string|max:500',
            'reference_image_source' => 'nullable|string|max:255',
            'reference_image_license' => 'nullable|string|max:255',
        ]);

        $data['last_reviewed_at'] = now();

        $product->update($data);

        return back()->with('success', "পণ্য #{$product->id} ({$product->name})-এর ক্যাটালগ রিভিউ ও স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।");
    }

    /**
     * Approve suggested correction and reference data.
     */
    public function approve(Request $request, Product $product)
    {
        $product->update([
            'verification_status' => 'verified',
            'last_reviewed_at' => now(),
        ]);

        return back()->with('success', "পণ্য #{$product->id} সফলভাবে অনুমোদিত এবং যাচাইকৃত (Verified) হিসেবে চিহ্নিত করা হয়েছে।");
    }

    /**
     * Reject or flag inaccurate crop listing data.
     */
    public function reject(Request $request, Product $product)
    {
        $product->update([
            'verification_status' => 'rejected',
            'last_reviewed_at' => now(),
        ]);

        return back()->with('success', "পণ্য #{$product->id}-এর রিভিউ স্ট্যাটাস 'বাতিল/পর্যালোচনাধীন' (Rejected) করা হয়েছে।");
    }
}
