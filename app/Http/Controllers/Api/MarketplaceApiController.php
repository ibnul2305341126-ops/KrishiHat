<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceApiController extends Controller
{
    /**
     * Search, filter, and paginate marketplace products.
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::query()->with([
            'farmer:id,name,role',
            'categoryRecord:id,name,slug',
            'photos',
            'division:id,name_en,name_bn',
            'districtRecord:id,name_en,name_bn',
            'upazilaRecord:id,name_en,name_bn',
        ]);

        // Keyword Search
        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $catInput = $request->input('category');
            if (is_numeric($catInput)) {
                $query->where('category_id', (int) $catInput);
            } else {
                $cat = Category::where('slug', $catInput)->orWhere('name', $catInput)->first();
                if ($cat) {
                    $query->where('category_id', $cat->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        // Location Hierarchy Filters
        if ($request->filled('division_id')) {
            $query->where('division_id', (int) $request->division_id);
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', (int) $request->district_id);
        }
        if ($request->filled('upazila_id')) {
            $query->where('upazila_id', (int) $request->upazila_id);
        }

        // Legacy string district filter with synonyms
        if ($request->filled('district')) {
            $districtInput = trim((string) $request->input('district'));
            $query->where(function ($q) use ($districtInput) {
                $q->where('district', $districtInput)
                  ->orWhere('district', 'like', "%{$districtInput}%");

                $map = [
                    'bogura' => ['বগুড়া', 'বগুড়া', 'Bogura', 'Bogra'],
                    'dinajpur' => ['দিনাজপুর', 'Dinajpur'],
                    'rajshahi' => ['রাজশাহী', 'Rajshahi'],
                    'rangpur' => ['রংপুর', 'Rangpur'],
                    'kushtia' => ['কুষ্টিয়া', 'কুষ্টিয়া', 'Kushtia'],
                    'jashore' => ['যশোর', 'Jashore', 'Jessore'],
                    'cumilla' => ['কুমিল্লা', 'Cumilla', 'Comilla'],
                    'barishal' => ['বরিশাল', 'Barishal', 'Barisal'],
                    'dhaka' => ['ঢাকা', 'Dhaka'],
                ];
                $lower = strtolower($districtInput);
                if (isset($map[$lower])) {
                    foreach ($map[$lower] as $synonym) {
                        $q->orWhere('district', 'like', "%{$synonym}%");
                    }
                }
            });
        }

        // Price Bounds
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // In Stock Filter
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'stock_desc' => $query->orderBy('stock', 'desc'),
            default => $query->latest(),
        };

        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'pagination' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'has_more' => $paginated->hasMorePages(),
            ],
        ]);
    }

    /**
     * Show detailed product information.
     */
    public function showProduct(Product $product): JsonResponse
    {
        $product->load([
            'farmer:id,name,role,email,farmer_verified_at',
            'categoryRecord:id,name,slug',
            'photos',
            'division:id,name_en,name_bn',
            'districtRecord:id,name_en,name_bn',
            'upazilaRecord:id,name_en,name_bn',
            'unionRecord:id,name_en,name_bn',
            'urbanAreaRecord:id,name_en,name_bn',
            'postOffice:id,name_en,name_bn,post_code',
        ]);

        return response()->json([
            'success' => true,
            'data' => array_merge($product->toArray(), [
                'average_rating' => $product->averageRating(),
                'reviews_count' => $product->reviewsCount(),
                'formatted_address' => $product->formatted_address,
                'google_maps_search_url' => $product->google_maps_search_url,
                'google_maps_directions_url' => $product->google_maps_directions_url,
            ]),
        ]);
    }

    /**
     * Get all active categories with product count.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get orders for the authenticated user.
     */
    public function orders(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $query = Order::with(['items.product', 'trackingEvents']);

        if ($user->role === 'buyer') {
            $query->where('buyer_id', $user->id);
        } elseif ($user->role === 'farmer') {
            $query->whereHas('items', fn ($q) => $q->where('farmer_id', $user->id));
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $orders = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Get single order details with tracking.
     */
    public function showOrder(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $isBuyer = $user->id === $order->buyer_id;
        $isFarmer = $order->items()->where('farmer_id', $user->id)->exists();
        $isAdmin = $user->role === 'admin';

        abort_unless($isBuyer || $isFarmer || $isAdmin, 403);

        $order->load(['items.product', 'trackingEvents.actor:id,name,role']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Get offers for the authenticated user.
     */
    public function offers(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $query = Offer::with(['product:id,name,price,stock,unit', 'buyer:id,name', 'farmer:id,name']);

        if ($user->role === 'buyer') {
            $query->where('buyer_id', $user->id);
        } elseif ($user->role === 'farmer') {
            $query->where('farmer_id', $user->id);
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $offers = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $offers->items(),
            'pagination' => [
                'total' => $offers->total(),
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
            ],
        ]);
    }
}
