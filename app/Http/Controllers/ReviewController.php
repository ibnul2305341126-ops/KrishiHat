<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Get reviews and rating summary for a product.
     */
    public function index(Request $request, Product $product): JsonResponse
    {
        $reviews = $product->reviews()
            ->with(['user:id,name,role'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'average_rating' => $product->averageRating(),
            'total_reviews' => $product->reviewsCount(),
            'reviews' => $reviews,
        ]);
    }

    /**
     * Store a new product review from a buyer.
     */
    public function store(Request $request, Product $product)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'buyer', 403, 'শুধুমাত্র ক্রেতারা পণ্যের রিভিউ দিতে পারেন।');
        abort_if($product->farmer_id === $user->id, 403, 'নিজের পণ্যে নিজে রিভিউ দেওয়া অনুমোদিত নয়।');

        // Prevent duplicate review per buyer per product
        $existing = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'rating' => 'আপনি ইতোমধ্যে এই পণ্যটিতে রিভিউ প্রদান করেছেন।',
            ]);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'rating.required' => 'অনুগ্রহ করে ১ থেকে ৫-এর মধ্যে স্টার রেটিং নির্বাচন করুন।',
            'rating.between' => 'রেটিং মান অবশ্যই ১ থেকে ৫ স্টারের মধ্যে হতে হবে।',
        ]);

        // Check if user has purchased this product
        $orderItem = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('buyer_id', $user->id);
            })
            ->latest('id')
            ->first();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $orderItem?->order_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $orderItem !== null,
        ]);

        $review->load('user:id,name,role');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'আপনার মূল্যবান রিভিউ সফলভাবে গৃহীত হয়েছে!',
                'data' => $review,
                'average_rating' => $product->averageRating(),
            ], 201);
        }

        return back()->with('success', 'আপনার মূল্যবান রিভিউ সফলভাবে গৃহীত হয়েছে!');
    }
}
