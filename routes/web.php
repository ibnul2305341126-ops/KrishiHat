
<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FarmerDashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;

// Home Page, Search and Category Filter
Route::get('/', function () {
    $query = Product::query();

    if (request()->filled('search')) {
        $query->where('name', 'like', '%' . request('search') . '%');
    }

    if (request()->filled('category')) {
        $query->where('category', request('category'));
    }

    $products = $query->get();

    $categories = Product::query()
        ->whereNotNull('category')
        ->where('category', '!=', '')
        ->distinct()
        ->pluck('category');

    return view('welcome', compact('products', 'categories'));
})->name('home');

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

// Product Details
Route::get('/products/{product}', function (Product $product) {
    return view('products.show', compact('product'));
})->name('products.show');

// Add to Cart
Route::post('/cart/add/{product}', function (Product $product) {
    if ($product->stock < 1) {
        return back()->with('error', 'পণ্যটি স্টকে নেই।');
    }

    $cart = session()->get('cart', []);
    $quantity = $cart[$product->id] ?? 0;

    if ($quantity >= $product->stock) {
        return back()->with('error', 'স্টকের চেয়ে বেশি পণ্য যোগ করা যাবে না।');
    }

    $cart[$product->id] = $quantity + 1;
    session()->put('cart', $cart);

    return back()->with('success', 'পণ্যটি কার্টে যোগ হয়েছে!');
})->name('cart.add');

// Cart Page
Route::get('/cart', function () {
    $cart = session()->get('cart', []);
    $products = Product::whereIn('id', array_keys($cart))->get();

    return view('cart', compact('cart', 'products'));
})->name('cart.index');

// Checkout
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

// Farmer Product Management
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
});

// Buyer: My Orders and Order Tracking
Route::middleware('auth')->group(function () {
    Route::get('/my-orders', [OrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/my-orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');
});