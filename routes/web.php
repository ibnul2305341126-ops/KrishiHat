
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FarmerDashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BuyerDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\AcceptedOfferOrderController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AdminCatalogueController;
use App\Http\Controllers\AdminMarketImportController;
use App\Http\Controllers\AdminPlanController;
use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminFarmerVerificationController;
use App\Http\Controllers\FarmerVerificationController;
use App\Http\Controllers\GeoAddressController;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

// Home Page, Search and Category Filter
Route::get('/', function () {
    $query = Product::query();
    $maximumListingPrice = (float) (Product::query()->max('price') ?? 0);
    $maxPrice = $maximumListingPrice;

    if (request()->filled('max_price')) {
        $maxPrice = (float) request()->validate([
            'max_price' => ['numeric', 'min:0', 'max:99999999.99'],
        ])['max_price'];
        $query->where('price', '<=', $maxPrice);
    }

    $priceFilterMaximum = max(1, $maximumListingPrice, $maxPrice);
    $categoriesAvailable = Schema::hasTable('categories')
        && Schema::hasColumn('products', 'category_id');

    if (request()->filled('search')) {
        $query->where('name', 'like', '%' . request('search') . '%');
    }

    $selectedCategory = null;
    if (request()->filled('category')) {
        if ($categoriesAvailable) {
            $selectedCategory = Category::query()
                ->where('slug', request('category'))
                ->orWhere('name', request('category'))
                ->first();

            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            // Keep legacy category filters usable while the normalized schema is unavailable.
            $query->where('category', request('category'));
        }
    }

    if (request()->filled('district')) {
        $districtInput = trim((string) request('district'));
        $query->where(function ($q) use ($districtInput) {
            $q->where('district', $districtInput)
              ->orWhere('district', 'like', '%' . $districtInput . '%');

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
                    $q->orWhere('district', 'like', '%' . $synonym . '%');
                }
            }
        });
    }

    $relations = ['farmer', 'photos'];
    if ($categoriesAvailable) {
        $relations[] = 'categoryRecord';
    } else {
        Log::error('Category schema is unavailable: expected categories table and products.category_id. Apply the pending categories migration.');
    }

    $products = $query->with($relations)->latest()->get();
    $categories = $categoriesAvailable ? Category::query()->orderBy('name')->get() : collect();
    $distinctDistricts = Product::query()
        ->whereNotNull('district')
        ->where('district', '!=', '')
        ->distinct()
        ->pluck('district');

    return view('welcome', compact(
        'products',
        'categories',
        'selectedCategory',
        'maximumListingPrice',
        'maxPrice',
        'priceFilterMaximum',
        'distinctDistricts',
    ));
})->name('home');

Route::get('/price-forecast', function () {
    $damRecords = \App\Services\BangladeshDamMarketData::getVerifiedReferenceRecords();
    return view('forecast.index', compact('damRecords'));
})->name('forecast.index');
Route::get('/plans', [SubscriptionController::class, 'index'])->name('plans.index');

// Payment Gateway and Sandbox Simulation Routes
Route::get('/payment/sandbox/simulator', [PaymentController::class, 'simulator'])->name('payment.sandbox.simulator');
Route::post('/payment/sandbox/action', [PaymentController::class, 'handleSimulatedAction'])->name('payment.sandbox.action');
Route::post('/payment/sslcommerz/success', [PaymentController::class, 'sslcommerzSuccess'])->name('payment.sslcommerz.success');
Route::post('/payment/sslcommerz/fail', [PaymentController::class, 'sslcommerzFail'])->name('payment.sslcommerz.fail');
Route::post('/payment/sslcommerz/cancel', [PaymentController::class, 'sslcommerzCancel'])->name('payment.sslcommerz.cancel');
Route::post('/payment/sslcommerz/ipn', [PaymentController::class, 'sslcommerzIpn'])->name('payment.sslcommerz.ipn');

Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Bangladesh Administrative Address Geo API
Route::prefix('api/geo')->name('geo.')->group(function () {
    Route::get('/divisions', [GeoAddressController::class, 'divisions'])->name('divisions');
    Route::get('/districts', [GeoAddressController::class, 'districts'])->name('districts');
    Route::get('/upazilas', [GeoAddressController::class, 'upazilas'])->name('upazilas');
    Route::get('/unions', [GeoAddressController::class, 'unions'])->name('unions');
    Route::get('/urban-areas', [GeoAddressController::class, 'urbanAreas'])->name('urban-areas');
    Route::get('/post-offices', [GeoAddressController::class, 'postOffices'])->name('post-offices');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Catalogue Review and Photo Attribution
    Route::get('/catalogue', [AdminCatalogueController::class, 'index'])->name('catalogue.index');
    Route::post('/catalogue/{product}/review', [AdminCatalogueController::class, 'updateReview'])->name('catalogue.update-review');
    Route::post('/catalogue/{product}/approve', [AdminCatalogueController::class, 'approve'])->name('catalogue.approve');
    Route::post('/catalogue/{product}/reject', [AdminCatalogueController::class, 'reject'])->name('catalogue.reject');

    // DAM Market Benchmark Imports & Verification
    Route::get('/market-imports', [AdminMarketImportController::class, 'index'])->name('market_imports.index');
    Route::post('/market-imports', [AdminMarketImportController::class, 'store'])->name('market_imports.store');

    // Membership Plans & Criteria
    Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
    Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
    Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
    Route::post('/plans/{plan}/toggle', [AdminPlanController::class, 'toggle'])->name('plans.toggle');

    // User Subscriptions & Audit Trail
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/manual-grant', [AdminSubscriptionController::class, 'manualGrant'])->name('subscriptions.manual-grant');
    Route::post('/subscriptions/{subscription}/update-status', [AdminSubscriptionController::class, 'updateStatus'])->name('subscriptions.update-status');

    // Payments & Gateway Monitoring
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');

    // Farmer Profile Verification (NID & Trade License Review)
    Route::get('/verifications', [AdminFarmerVerificationController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/{user}', [AdminFarmerVerificationController::class, 'show'])->name('verifications.show');
    Route::post('/verifications/{user}/approve', [AdminFarmerVerificationController::class, 'approve'])->name('verifications.approve');
    Route::post('/verifications/{user}/reject', [AdminFarmerVerificationController::class, 'reject'])->name('verifications.reject');

    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

    Route::get('/products', [AdminController::class, 'products'])->name('products.index');
    Route::patch('/products/{product}/toggle-status', [AdminController::class, 'toggleProductStatus'])->name('products.toggle-status');
    Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');

    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [AdminController::class, 'showOrder'])->name('orders.show');

    Route::get('/offers', [AdminController::class, 'offers'])->name('offers.index');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

// Register
Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.store');

// Login
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.store');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Switch Account (Demo / Fast Role Switching)
Route::post('/switch-account', [AuthController::class, 'switchAccount'])
    ->middleware('auth')
    ->name('switch-account');

// Product Details
Route::get('/products/{product}', function (Product $product) {
    $product->load(['farmer', 'photos', 'categoryRecord']);
    return view('products.show', compact('product'));
})->name('products.show');

// Add to Cart
Route::post('/cart/add/{product}', function (Request $request, Product $product) {
    if ($product->stock < 1) {
        return back()->with('error', 'পণ্যটি স্টকে নেই।');
    }

    $cart = session()->get('cart', []);
    $quantity = $cart[$product->id] ?? 0;
    $minOrder = (int) ($product->min_order_quantity ?? 1);
    $minRequired = $quantity > 0 ? 1 : $minOrder;

    $requestedQuantity = $request->validate([
        'quantity' => ['nullable', 'integer', 'min:' . $minRequired, 'max:' . $product->stock],
    ], [
        'quantity.min' => "এই পণ্যটির সর্বনিম্ন অর্ডারের পরিমাণ {$minOrder} {$product->unit} হতে হবে।",
    ])['quantity'] ?? $minRequired;

    if ($quantity === 0 && $requestedQuantity < $minOrder) {
        return back()->with('error', "এই পণ্যটির সর্বনিম্ন অর্ডারের পরিমাণ {$minOrder} {$product->unit} হতে হবে।");
    }

    if ($quantity + $requestedQuantity > $product->stock) {
        return back()->with('error', 'স্টকের চেয়ে বেশি পণ্য যোগ করা যাবে না।');
    }

    $cart[$product->id] = $quantity + $requestedQuantity;
    session()->put('cart', $cart);

    return back()->with('success', 'পণ্যটি কার্টে যোগ হয়েছে!');
})->name('cart.add');

Route::put('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');

// Cart Page
Route::get('/cart', function () {
    $cart = session()->get('cart', []);
    $products = Product::whereIn('id', array_keys($cart))->get();

    return view('cart', compact('cart', 'products'));
})->name('cart.index');

// Checkout
Route::middleware('auth')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::post('/products/{product}/conversations', [ConversationController::class, 'startProduct'])->name('conversations.start-product');
    Route::post('/offers/{offer}/conversations', [ConversationController::class, 'startOffer'])->name('conversations.start-offer');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages'])->name('conversations.messages.index');
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'send'])->name('conversations.messages.store');
    Route::get('/conversation-messages/{message}/attachment', [ConversationController::class, 'attachment'])->name('conversations.attachments.show');
    Route::post('/offers/{offer}/order', [AcceptedOfferOrderController::class, 'store'])->name('offers.order');
    Route::get('/notifications', function (Request $request) {
        $notifications = $request->user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');
    Route::post('/notifications/mark-all-read', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'সকল নোটিফিকেশন পঠিত হিসেবে চিহ্নিত করা হয়েছে।');
    })->name('notifications.mark-all-read');
    Route::get('/notifications/{notification}', function (Request $request, string $notification) {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();
        $targetUrl = $item->data['url'] ?? route('dashboard');
        if (str_starts_with($targetUrl, 'http://') || str_starts_with($targetUrl, 'https://')) {
            $parsed = parse_url($targetUrl);
            $path = $parsed['path'] ?? '/';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
            return redirect($path . $query . $fragment);
        }
        return redirect($targetUrl);
    })->name('notifications.open');
});

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])
        ->name('checkout.index');

    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('checkout.store');
});

// Farmer Dashboard
Route::get('/farmer/dashboard', [
    FarmerDashboardController::class,
    'index'
])->middleware('auth')->name('farmer.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/buyer/dashboard', [BuyerDashboardController::class, 'index'])->name('buyer.dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/farmer-verification', [FarmerVerificationController::class, 'submit'])->name('profile.farmer-verification');
    Route::get('/transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
    Route::post('/plans/{plan}/subscribe', [SubscriptionController::class, 'subscribe'])->name('plans.subscribe');
});

// Farmer Product Management
Route::middleware('auth')->group(function () {
    Route::post('/products/{product}/offers', [OfferController::class, 'store'])
        ->name('offers.store');
    Route::patch('/offers/{offer}/counter-response', [OfferController::class, 'respondToCounterOffer'])
        ->name('offers.counter-response');
    Route::patch('/offers/{offer}/withdraw', [OfferController::class, 'withdraw'])
        ->name('offers.withdraw');
    Route::patch('/farmer/offers/{offer}/respond', [OfferController::class, 'respondAsFarmer'])
        ->name('offers.farmer.respond');
});

Route::middleware('auth')->group(function () {
    Route::get('/farmer/products/create', [
        ProductController::class,
        'create'
    ])->name('products.create');

    Route::post('/farmer/products', [
        ProductController::class,
        'store'
    ])->name('products.store');

    Route::get('/farmer/products/{product}/edit', [
        ProductController::class,
        'edit'
    ])->name('products.edit');

    Route::put('/farmer/products/{product}', [
        ProductController::class,
        'update'
    ])->name('products.update');

    Route::delete('/farmer/products/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/farmer/orders/{order}', [OrderController::class, 'farmerShow'])
        ->name('farmer.orders.show');
    Route::put('/farmer/orders/{order}', [OrderController::class, 'updateFarmerStatus'])
        ->name('farmer.orders.update');
});

// Buyer: My Orders and Order Tracking
Route::middleware('auth')->group(function () {
    Route::get('/my-orders', [OrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/my-orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');

    Route::get('/my-orders/{order}/tracking-data', [OrderController::class, 'trackingData'])
        ->name('orders.tracking-data');
});
