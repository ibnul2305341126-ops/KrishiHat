<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\BangladeshDemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomatedDemonstrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_demonstration_seeder_is_repeatable_and_idempotent(): void
    {
        // First run
        $this->seed(BangladeshDemoDataSeeder::class);
        $userCount1 = User::count();
        $productCount1 = Product::count();
        $orderCount1 = Order::count();
        $offerCount1 = Offer::count();

        $this->assertGreaterThanOrEqual(5, $userCount1);
        $this->assertGreaterThanOrEqual(6, $productCount1);
        $this->assertGreaterThanOrEqual(3, $orderCount1);

        // Second run must be completely idempotent (no duplicates created)
        $this->seed(BangladeshDemoDataSeeder::class);
        $this->assertSame($userCount1, User::count());
        $this->assertSame($productCount1, Product::count());
        $this->assertSame($orderCount1, Order::count());
        $this->assertSame($offerCount1, Offer::count());
    }

    public function test_demonstration_marketplace_district_filtering(): void
    {
        $this->seed(BangladeshDemoDataSeeder::class);

        // Filter by Bogura
        $response = $this->get(route('home', ['district' => 'Bogura']));
        $response->assertOk()
            ->assertSee('BRRI Dhan 28')
            ->assertDontSee('Diamant Potato');

        // Filter by Dinajpur
        $response = $this->get(route('home', ['district' => 'Dinajpur']));
        $response->assertOk()
            ->assertSee('Diamant Potato')
            ->assertDontSee('BRRI Dhan 28');

        // Filter by Rajshahi
        $response = $this->get(route('home', ['district' => 'Rajshahi']));
        $response->assertOk()
            ->assertSee('Taherpuri')
            ->assertDontSee('Mustard Seed');
    }

    public function test_demonstration_bargaining_workflow_to_order(): void
    {
        $this->seed(BangladeshDemoDataSeeder::class);

        $buyer = User::where('email', 'buyer.karim@demo.krishihat.test')->firstOrFail();
        $farmer = User::where('email', 'farmer.mokbul@demo.krishihat.test')->firstOrFail();
        $product = Product::where('farmer_id', $farmer->id)->firstOrFail();

        // 1. Buyer submits offer
        $this->actingAs($buyer)->post(route('offers.store', $product), [
            'price' => '34.50',
            'quantity' => 100,
        ])->assertRedirect();

        $offer = Offer::where('buyer_id', $buyer->id)
            ->where('product_id', $product->id)
            ->where('initial_price', '34.50')
            ->firstOrFail();

        $this->assertSame(Offer::STATUS_PENDING, $offer->status);

        // 2. Farmer accepts the offer
        $this->actingAs($farmer)->patch(route('offers.farmer.respond', $offer), [
            'action' => 'accept',
        ])->assertRedirect();

        $offer->refresh();
        $this->assertSame(Offer::STATUS_ACCEPTED, $offer->status);

        // 3. Buyer converts accepted offer into confirmed order
        $this->actingAs($buyer)->post(route('offers.order', $offer), [
            'delivery_address' => 'কারওয়ান বাজার পাইকারি আড়ত, ঢাকা',
            'phone' => '01700-777888',
        ])->assertRedirect();

        $order = Order::where('accepted_offer_id', $offer->id)->firstOrFail();
        $this->assertNotNull($order->tracking_code);
        $this->assertDatabaseHas('order_tracking_events', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_demonstration_live_tracking_and_farmer_status_updates(): void
    {
        $this->seed(BangladeshDemoDataSeeder::class);

        $buyer = User::where('email', 'buyer.karim@demo.krishihat.test')->firstOrFail();
        $farmer = User::where('email', 'farmer.mokbul@demo.krishihat.test')->firstOrFail();
        $order = Order::where('buyer_id', $buyer->id)->where('status', Order::STATUS_SHIPPED)->firstOrFail();

        // Buyer checks order show tracking page
        $response = $this->actingAs($buyer)->get(route('orders.show', $order));
        $response->assertOk()
            ->assertSee($order->tracking_code)
            ->assertSee('In Transit')
            ->assertSee('Rafiqul Islam')
            ->assertSee('লাইভ ট্র্যাকিং সক্রিয়');

        // Farmer updates status to delivered
        $this->actingAs($farmer)->put(route('farmer.orders.update', $order), [
            'status' => Order::STATUS_DELIVERED,
            'title' => 'সফলভাবে ডেলিভারি সম্পন্ন (Delivered)',
            'description' => 'পণ্য নিরাপদে কারওয়ান বাজার আড়তে আনলোড সম্পন্ন হয়েছে।',
            'location' => 'কারওয়ান বাজার পাইকারি আড়ত, ঢাকা',
        ])->assertRedirect(route('farmer.orders.show', $order));

        $order->refresh();
        $this->assertSame(Order::STATUS_DELIVERED, $order->status);

        // Buyer polls tracking data JSON
        $json = $this->actingAs($buyer)->get(route('orders.tracking-data', $order))->json();
        $this->assertSame('delivered', $json['status']);
        $this->assertNotEmpty($json['events']);
    }

    public function test_demonstration_farmer_product_management_with_location(): void
    {
        $this->seed(BangladeshDemoDataSeeder::class);

        $farmer = User::where('email', 'farmer.mokbul@demo.krishihat.test')->firstOrFail();
        $category = Category::firstOrFail();

        // Farmer dashboard loads dynamically
        $this->actingAs($farmer)->get(route('farmer.dashboard'))
            ->assertOk()
            ->assertSee('FARMER WORKSPACE')
            ->assertSee('Active listings');

        // Farmer adds new product with location
        $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'নতুন আমন ধান (Aman Paddy - Demo)',
            'category_id' => $category->id,
            'price' => '38.00',
            'unit' => 'kg',
            'stock' => 1500,
            'district' => 'Bogura',
            'upazila' => 'Kahaloo',
            'latitude' => '24.8239',
            'longitude' => '89.2625',
        ])->assertRedirect(route('farmer.dashboard'));

        $this->assertDatabaseHas('products', [
            'name' => 'নতুন আমন ধান (Aman Paddy - Demo)',
            'district' => 'Bogura',
            'upazila' => 'Kahaloo',
        ]);
    }
}
