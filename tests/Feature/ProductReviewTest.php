<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_submit_review_for_product(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Tomatoes',
            'price' => 50.00,
            'unit' => 'kg',
            'stock' => 100,
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 150.00,
            'status' => Order::STATUS_DELIVERED,
            'delivery_address' => 'Mirpur, Dhaka',
            'phone' => '01700000000',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 3,
            'unit_price' => 50.00,
            'subtotal' => 150.00,
        ]);

        $response = $this->actingAs($buyer)->post(route('products.reviews.store', $product), [
            'rating' => 5,
            'comment' => 'অসাধারণ সুস্বাদু ও টাটকা টমেটো। প্যাকেজিং চমৎকার ছিল!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'is_verified_purchase' => true,
        ]);

        $this->assertSame(5.0, $product->averageRating());
        $this->assertSame(1, $product->reviewsCount());
    }

    public function test_duplicate_review_by_same_buyer_is_rejected(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $category = Category::create(['name' => 'Fruits', 'slug' => 'fruits']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Rajshahi Mangoes',
            'price' => 120.00,
            'unit' => 'kg',
            'stock' => 80,
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 4,
            'comment' => 'First review',
            'is_verified_purchase' => false,
        ]);

        $response = $this->actingAs($buyer)->post(route('products.reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Duplicate attempt',
        ]);

        $response->assertSessionHasErrors('rating');
        $this->assertSame(1, Review::where('product_id', $product->id)->count());
    }

    public function test_farmer_cannot_review_own_product(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Own Crop',
            'price' => 60.00,
            'unit' => 'kg',
            'stock' => 50,
        ]);

        $this->actingAs($farmer)
            ->post(route('products.reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Self praise',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_invalid_ratings_are_rejected(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Demo Product',
            'price' => 30.00,
            'unit' => 'kg',
            'stock' => 50,
        ]);

        // Rating = 0
        $this->actingAs($buyer)
            ->post(route('products.reviews.store', $product), ['rating' => 0])
            ->assertSessionHasErrors('rating');

        // Rating = 6
        $this->actingAs($buyer)
            ->post(route('products.reviews.store', $product), ['rating' => 6])
            ->assertSessionHasErrors('rating');

        // Rating missing
        $this->actingAs($buyer)
            ->post(route('products.reviews.store', $product), ['comment' => 'no rating'])
            ->assertSessionHasErrors('rating');
    }

    public function test_public_reviews_api_returns_paginated_reviews_and_average_rating(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Rated Wheat',
            'price' => 45.00,
            'unit' => 'kg',
            'stock' => 200,
        ]);

        $buyer1 = User::factory()->create(['role' => 'buyer', 'name' => 'Buyer One']);
        $buyer2 = User::factory()->create(['role' => 'buyer', 'name' => 'Buyer Two']);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $buyer1->id,
            'rating' => 4,
            'comment' => 'Good quality',
            'is_verified_purchase' => true,
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $buyer2->id,
            'rating' => 5,
            'comment' => 'Excellent quality',
            'is_verified_purchase' => true,
        ]);

        $response = $this->getJson(route('products.reviews.index', $product));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'average_rating' => 4.5,
            'total_reviews' => 2,
        ]);
    }
}
