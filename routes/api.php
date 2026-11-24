<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| KrishiHat REST API  —  base URL: /api/v1
|--------------------------------------------------------------------------
| Authentication: "Authorization: Bearer <token>" (token from /auth/login).
| All responses are JSON.
*/

Route::prefix('v1')->name('v1.')->middleware('throttle:krishihat-api')->group(function () {

    // ---- Public ------------------------------------------------------
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('categories', [CatalogController::class, 'categories'])->name('categories');
    Route::get('market-prices', [CatalogController::class, 'marketPrices'])->name('market-prices');

    // ---- Auth (stricter rate limit against password guessing) ---------
    Route::middleware('throttle:krishihat-auth')->prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    });

    // ---- Authenticated -----------------------------------------------
    Route::middleware('auth:api')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('offers', [OfferController::class, 'index'])->name('offers.index');

        // Farmer only
        Route::middleware('role:farmer')->prefix('farmer')->group(function () {
            Route::get('products', [ProductController::class, 'mine'])->name('farmer.products.index');
            Route::post('products', [ProductController::class, 'store'])->name('farmer.products.store');
            Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update'])->name('farmer.products.update');
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('farmer.products.destroy');
        });
    });
});
