<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceRestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_products_list_and_filters(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'name' => 'Kuddus Ali']);
        $catRice = Category::create(['name' => 'Rice', 'slug' => 'rice']);
        $catSpice = Category::create(['name' => 'Spices', 'slug' => 'spices']);

        $p1 = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $catRice->id,
            'name' => 'Miniket Rice',
            'price' => 70.00,
            'unit' => 'kg',
            'stock' => 500,
            'district' => 'Dinajpur',
        ]);

        $p2 = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $catRice->id,
            'name' => 'Nazirshail Rice',
            'price' => 85.00,
            'unit' => 'kg',
            'stock' => 200,
            'district' => 'Barishal',
        ]);

        $p3 = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $catSpice->id,
            'name' => 'Pure Chili Powder',
            'price' => 350.00,
            'unit' => 'kg',
            'stock' => 30,
            'district' => 'Bogura',
        ]);

        // 1. All products
        $resAll = $this->getJson(route('api.products.index'));
        $resAll->assertOk();
        $resAll->assertJsonCount(3, 'data');
        $resAll->assertJsonStructure(['success', 'data', 'pagination']);

        // 2. Keyword Search
        $resSearch = $this->getJson(route('api.products.index', ['search' => 'Miniket']));
        $resSearch->assertOk();
        $resSearch->assertJsonCount(1, 'data');
        $this->assertSame('Miniket Rice', $resSearch->json('data.0.name'));

        // 3. Category Filter
        $resCat = $this->getJson(route('api.products.index', ['category' => 'spices']));
        $resCat->assertOk();
        $resCat->assertJsonCount(1, 'data');
        $this->assertSame('Pure Chili Powder', $resCat->json('data.0.name'));

        // 4. District Filter
        $resDist = $this->getJson(route('api.products.index', ['district' => 'dinajpur']));
        $resDist->assertOk();
        $resDist->assertJsonCount(1, 'data');
        $this->assertSame('Miniket Rice', $resDist->json('data.0.name'));

        // 5. Price Max Filter
        $resPrice = $this->getJson(route('api.products.index', ['max_price' => 80]));
        $resPrice->assertOk();
        $resPrice->assertJsonCount(1, 'data');
        $this->assertSame('Miniket Rice', $resPrice->json('data.0.name'));
    }

    public function test_api_product_detail_endpoint(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'name' => 'Farmer Ansar']);
        $cat = Category::create(['name' => 'Fruits', 'slug' => 'fruits']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $cat->id,
            'name' => 'Rajshahi Fazli Mango',
            'price' => 110.00,
            'unit' => 'kg',
            'stock' => 150,
            'district' => 'Rajshahi',
            'upazila' => 'Bagha',
            'latitude' => 24.198,
            'longitude' => 88.835,
        ]);

        $response = $this->getJson(route('api.products.show', $product));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => 'Rajshahi Fazli Mango',
                'price' => '110.00',
                'stock' => 150,
                'average_rating' => 0.0,
            ],
        ]);

        $this->assertStringContainsString('maps/search', $response->json('data.google_maps_search_url'));
    }

    public function test_api_categories_list(): void
    {
        Category::create(['name' => 'Cereals', 'slug' => 'cereals']);
        Category::create(['name' => 'Pulses', 'slug' => 'pulses']);

        $response = $this->getJson(route('api.categories.index'));

        $response->assertOk();
        $response->assertJsonStructure(['success', 'data']);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_api_addresses_and_orders_require_authentication(): void
    {
        $this->getJson(route('api.addresses.index'))->assertUnauthorized();
        $this->getJson(route('api.orders.index'))->assertUnauthorized();
        $this->getJson(route('api.offers.index'))->assertUnauthorized();
    }

    public function test_authenticated_buyer_can_fetch_addresses_orders_and_offers_via_api(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $cat = Category::create(['name' => 'Dairy', 'slug' => 'dairy']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $cat->id,
            'name' => 'Cow Milk',
            'price' => 80.00,
            'unit' => 'liter',
            'stock' => 50,
        ]);

        // Address
        UserAddress::create([
            'user_id' => $buyer->id,
            'title' => 'Home',
            'recipient_name' => 'Buyer API User',
            'phone' => '01700000000',
            'street_address' => 'Mirpur 10, Dhaka',
            'is_default' => 1,
        ]);

        // Order
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 160.00,
            'status' => Order::STATUS_CONFIRMED,
            'delivery_address' => 'Mirpur 10, Dhaka',
            'phone' => '01700000000',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 2,
            'unit_price' => 80.00,
            'subtotal' => 160.00,
        ]);

        // Offer
        Offer::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'farmer_id' => $farmer->id,
            'initial_price' => 75.00,
            'current_price' => 75.00,
            'quantity' => 10,
            'status' => Offer::STATUS_PENDING,
        ]);

        // 1. Fetch addresses
        $addrRes = $this->actingAs($buyer)->getJson(route('api.addresses.index'));
        $addrRes->assertOk();
        $addrRes->assertJsonCount(1, 'data');
        $this->assertSame('Buyer API User', $addrRes->json('data.0.recipient_name'));

        // 2. Fetch orders
        $orderRes = $this->actingAs($buyer)->getJson(route('api.orders.index'));
        $orderRes->assertOk();
        $orderRes->assertJsonCount(1, 'data');
        $this->assertEquals(160.00, $orderRes->json('data.0.total_amount'));

        // 3. Fetch single order
        $singleOrderRes = $this->actingAs($buyer)->getJson(route('api.orders.show', $order));
        $singleOrderRes->assertOk();
        $this->assertSame($order->id, $singleOrderRes->json('data.id'));

        // 4. Fetch offers
        $offerRes = $this->actingAs($buyer)->getJson(route('api.offers.index'));
        $offerRes->assertOk();
        $offerRes->assertJsonCount(1, 'data');
        $this->assertEquals(75.00, $offerRes->json('data.0.current_price'));
    }
}
