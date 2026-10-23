<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Admin Dashboard with real database-derived metrics.
     */
    public function dashboard()
    {
        $metrics = [
            'total_users' => User::count(),
            'total_farmers' => User::where('role', 'farmer')->count(),
            'verified_farmers' => User::where('role', 'farmer')->where('verification_status', User::VERIFY_STATUS_VERIFIED)->count(),
            'pending_verifications' => User::where('role', 'farmer')->where('verification_status', User::VERIFY_STATUS_PENDING)->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'total_admins' => User::where('role', 'admin')->count(),

            'total_products' => Product::count(),
            'in_stock_products' => Product::where('stock', '>', 0)->count(),
            'sold_out_products' => Product::where('stock', '<=', 0)->count(),
            'total_categories' => Category::count(),

            'total_orders' => Order::count(),
            'total_revenue' => (float) Order::sum('total_amount'),
            'delivered_orders' => Order::where('status', Order::STATUS_DELIVERED)->count(),
            'in_transit_orders' => Order::where('status', Order::STATUS_SHIPPED)->count(),
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),

            'total_offers' => Offer::count(),
            'accepted_offers' => Offer::where('status', Offer::STATUS_ACCEPTED)->count(),
            'countered_offers' => Offer::where('status', Offer::STATUS_COUNTERED)->count(),
            'pending_offers' => Offer::where('status', Offer::STATUS_PENDING)->count(),
            'rejected_offers' => Offer::where('status', Offer::STATUS_REJECTED)->count(),
        ];

        $recentOrders = Order::with(['buyer', 'items.product', 'items.farmer'])->latest()->take(6)->get();
        $recentOffers = Offer::with(['buyer', 'farmer', 'product'])->latest()->take(6)->get();
        $recentUsers = User::latest()->take(6)->get();

        return view('admin.dashboard', compact('metrics', 'recentOrders', 'recentOffers', 'recentUsers'));
    }

    /**
     * User Management: List, Search, and Filter Users.
     */
    public function users(Request $request)
    {
        $query = User::query()->withCount(['products', 'orders']);

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role')) {
            $role = $request->input('role');
            if (in_array($role, ['admin', 'farmer', 'buyer'])) {
                $query->where('role', $role);
            }
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Edit a user.
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user details and role.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,farmer,buyer'],
        ]);

        // Safety: If this user is the currently logged in admin and they are demoting themselves
        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            $otherAdmins = User::where('role', 'admin')->where('id', '!=', $user->id)->count();
            if ($otherAdmins === 0) {
                return back()->with('error', 'আপনি সিস্টেমের একমাত্র অ্যাডমিন। নিজের রোল পরিবর্তন করার আগে অন্য কাউকে অ্যাডমিন হিসেবে নিযুক্ত করুন।');
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "ব্যবহারকারী '{$user->name}'-এর তথ্য সফলভাবে আপডেট হয়েছে।");
    }

    /**
     * Safely delete an ordinary user (protecting records with order or conversation history).
     */
    public function destroyUser(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'আপনি নিজের অ্যাকাউন্ট ডিলিট করতে পারবেন না।');
        }

        // Check for order history
        if ($user->orders()->exists() || OrderItem::where('farmer_id', $user->id)->exists()) {
            return back()->with('error', 'এই ব্যবহারকারীর সাথে সম্পর্কিত সক্রিয় অর্ডার ও লেনদেনের ইতিহাস রয়েছে, তাই অ্যাকাউন্টটি ডিলিট করা সম্ভব নয়।');
        }

        // Check for offers or conversations
        if (Offer::where('buyer_id', $user->id)->orWhere('farmer_id', $user->id)->exists()) {
            return back()->with('error', 'এই ব্যবহারকারীর দরদাম (Offer) ইতিহাস রয়েছে। তথ্যের স্বচ্ছতার স্বার্থে এটি মোছা যাবে না।');
        }

        if (Conversation::where('buyer_id', $user->id)->orWhere('farmer_id', $user->id)->exists()) {
            return back()->with('error', 'এই ব্যবহারকারীর সাথে সম্পর্কিত চ্যাট বা বার্তা সংরক্ষিত রয়েছে।');
        }

        $userName = $user->name;
        $user->products()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "ব্যবহারকারী '{$userName}' সফলভাবে মুছে ফেলা হয়েছে।");
    }

    /**
     * Product Management: View and Filter Products.
     */
    public function products(Request $request)
    {
        $query = Product::query()->with(['farmer', 'categoryRecord', 'photos']);

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where('name', 'like', "%{$term}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('stock_status')) {
            if ($request->input('stock_status') === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($request->input('stock_status') === 'sold_out') {
                $query->where('stock', '<=', 0);
            }
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Toggle product availability / stock state.
     */
    public function toggleProductStatus(Request $request, Product $product)
    {
        if ($product->stock > 0) {
            $product->stock = 0;
            $product->save();
            $msg = "'{$product->name}' এখন স্টক আউট (অপ্রাপ্য) করা হয়েছে।";
        } else {
            $product->stock = 100;
            $product->save();
            $msg = "'{$product->name}' পুনরায় স্টকে যুক্ত করা হয়েছে (১০০ ইউনিট)।";
        }

        return back()->with('success', $msg);
    }

    /**
     * Safely destroy or archive product.
     */
    public function destroyProduct(Request $request, Product $product)
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'এই পণ্যের সাথে সম্পন্নকৃত অর্ডার বা লেনদেনের রেকর্ড সংযুক্ত রয়েছে। এটি মুছে ফেলার পরিবর্তে স্টক ০ (আর্কাইভ) করে রাখুন।');
        }

        if ($product->offers()->exists()) {
            return back()->with('error', 'এই পণ্যের জন্য অতীতের দরদাম (Offer) রেকর্ড রয়েছে। রেকর্ড অখণ্ডতার স্বার্থে পণ্যটি মোছা যাবে না।');
        }

        $name = $product->name;
        $product->photos()->delete();
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "পণ্য '{$name}' তালিকা থেকে মুছে ফেলা হয়েছে।");
    }

    /**
     * Orders & Tracking Management.
     */
    public function orders(Request $request)
    {
        $query = Order::query()->with(['buyer', 'items.product', 'items.farmer']);

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('tracking_code', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%")
                  ->orWhereHas('buyer', function ($b) use ($term) {
                      $b->where('name', 'like', "%{$term}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Inspect order details, items, and tracking events.
     */
    public function showOrder(Order $order)
    {
        $order->load(['buyer', 'items.product', 'items.farmer', 'trackingEvents.actor', 'acceptedOffer']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Offers / Bargaining Inspection.
     */
    public function offers(Request $request)
    {
        $query = Offer::query()->with(['buyer', 'farmer', 'product', 'events.actor', 'order']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->whereHas('product', function ($p) use ($term) {
                $p->where('name', 'like', "%{$term}%");
            });
        }

        $offers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.offers.index', compact('offers'));
    }

    /**
     * System Reports & Settings.
     */
    public function reports()
    {
        $totalRevenue = (float) Order::sum('total_amount');
        $ordersCount = Order::count();
        $avgOrderValue = $ordersCount > 0 ? round($totalRevenue / $ordersCount, 2) : 0;

        $topSellingProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->take(8)
            ->with('product')
            ->get();

        $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $damRecords = \App\Services\BangladeshDamMarketData::getVerifiedReferenceRecords();

        return view('admin.reports.index', compact('totalRevenue', 'ordersCount', 'avgOrderValue', 'topSellingProducts', 'ordersByStatus', 'damRecords'));
    }
}
