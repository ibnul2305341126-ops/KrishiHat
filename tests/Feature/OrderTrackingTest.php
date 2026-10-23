<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_view_own_order_tracking_details(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Bogura Potato',
            'price' => 30.00,
            'unit' => 'kg',
            'stock' => 50,
            'district' => 'Bogura',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 60.00,
            'status' => Order::STATUS_PENDING,
            'delivery_address' => 'Mirpur, Dhaka',
            'phone' => '01711000000',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 2,
            'unit_price' => 30.00,
            'subtotal' => 60.00,
        ]);

        $response = $this->actingAs($buyer)->get(route('orders.show', $order));

        $response->assertOk()
            ->assertSee('Mirpur, Dhaka')
            ->assertSee('Bogura Potato')
            ->assertSee('Quantity: 2')
            ->assertSee('Pending');

        $order->refresh();
        $this->assertNotNull($order->tracking_code);
        $this->assertDatabaseHas('order_tracking_events', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_unauthorized_user_cannot_view_or_poll_order_tracking(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $unrelatedBuyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 100.00,
            'status' => Order::STATUS_PENDING,
            'delivery_address' => 'Uttara, Dhaka',
            'phone' => '01711222333',
        ]);

        // Unrelated buyer cannot view HTML show page
        $this->actingAs($unrelatedBuyer)->get(route('orders.show', $order))
            ->assertForbidden();

        // Unrelated buyer cannot poll tracking JSON
        $this->actingAs($unrelatedBuyer)->get(route('orders.tracking-data', $order))
            ->assertForbidden();
    }

    public function test_farmer_can_update_order_status_and_records_tracking_event(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $otherFarmer = User::factory()->create(['role' => 'farmer']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Fresh Carrots',
            'price' => 20.00,
            'unit' => 'kg',
            'stock' => 100,
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 100.00,
            'status' => Order::STATUS_PENDING,
            'delivery_address' => 'Banani, Dhaka',
            'phone' => '01711444555',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 5,
            'unit_price' => 20.00,
            'subtotal' => 100.00,
        ]);

        // Other farmer cannot view or update this order
        $this->actingAs($otherFarmer)->get(route('farmer.orders.show', $order))
            ->assertForbidden();

        $this->actingAs($otherFarmer)->put(route('farmer.orders.update', $order), [
            'status' => Order::STATUS_CONFIRMED,
        ])->assertForbidden();

        // Authorized farmer updates status to confirmed
        $response = $this->actingAs($farmer)->put(route('farmer.orders.update', $order), [
            'status' => Order::STATUS_CONFIRMED,
            'title' => 'ফসল নিশ্চিত করা হয়েছে',
            'description' => 'খামারি মোস্তফা ফসল প্রস্তুত শুরু করেছেন।',
            'location' => 'Bogura Sherpur Farm',
        ]);

        $response->assertRedirect(route('farmer.orders.show', $order));

        $order->refresh();
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);

        $this->assertDatabaseHas('order_tracking_events', [
            'order_id' => $order->id,
            'status' => 'confirmed',
            'title' => 'ফসল নিশ্চিত করা হয়েছে',
            'location' => 'Bogura Sherpur Farm',
            'actor_id' => $farmer->id,
        ]);

        // Farmer updates to shipped with driver details
        $this->actingAs($farmer)->put(route('farmer.orders.update', $order), [
            'status' => Order::STATUS_SHIPPED,
            'driver_name' => 'Rafiqul Islam',
            'driver_phone' => '01812345678',
            'vehicle_number' => 'DHAKA-METRO-TA-11-2091',
            'location' => 'Sirajganj Highway Depot',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame(Order::STATUS_SHIPPED, $order->status);
        $this->assertSame('Rafiqul Islam', $order->driver_name);
        $this->assertSame('DHAKA-METRO-TA-11-2091', $order->vehicle_number);

        // Buyer polls tracking data and receives live status + events
        $jsonResponse = $this->actingAs($buyer)->get(route('orders.tracking-data', $order));
        $jsonResponse->assertOk()
            ->assertJsonPath('status', 'shipped')
            ->assertJsonPath('driver_name', 'Rafiqul Islam')
            ->assertJsonPath('vehicle_number', 'DHAKA-METRO-TA-11-2091');

        $data = $jsonResponse->json();
        $this->assertCount(3, $data['events']); // initial + confirmed + shipped
    }
}
