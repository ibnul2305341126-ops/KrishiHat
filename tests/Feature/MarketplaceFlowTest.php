<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_filters_listings_by_maximum_price(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $affordable = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Affordable rice',
            'price' => 18.50,
            'unit' => 'kg',
            'stock' => 10,
        ]);
        Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Premium rice',
            'price' => 35,
            'unit' => 'kg',
            'stock' => 10,
        ]);

        $this->get(route('home', ['max_price' => '20.00']))
            ->assertOk()
            ->assertSee($affordable->name)
            ->assertDontSee('Premium rice')
            ->assertSee('name="max_price"', false)
            ->assertSee('value="20.00"', false);
    }

    public function test_homepage_rejects_invalid_maximum_price_filters(): void
    {
        $this->get(route('home', ['max_price' => '-1']))
            ->assertSessionHasErrors('max_price');
    }

    public function test_product_stock_is_visible_and_cart_controls_respect_available_stock(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $products = collect([12, 1, 0])->map(fn (int $stock) => Product::create([
            'farmer_id' => $farmer->id,
            'name' => "Stock {$stock}",
            'price' => 10,
            'unit' => 'kg',
            'stock' => $stock,
        ]));
        [$twelve, $one, $zero] = $products;

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Available stock: 12 kg')
            ->assertSee('Available stock: 1 kg')
            ->assertSee('Sold Out')
            ->assertSee('max="12"', false)
            ->assertSee('max="1"', false)
            ->assertSee('disabled', false);

        $this->get(route('products.show', $zero))
            ->assertOk()
            ->assertSee('Sold Out')
            ->assertSee('disabled', false);

        $this->post(route('cart.add', $twelve), ['quantity' => 12])->assertRedirect();
        $this->assertSame(12, session('cart')[$twelve->id]);
        $this->post(route('cart.add', $twelve), ['quantity' => 1])->assertSessionHas('error');

        $this->post(route('cart.add', $one), ['quantity' => 2])->assertSessionHasErrors('quantity');
        $this->post(route('cart.add', $one), ['quantity' => 1])->assertRedirect();
        $this->assertSame(1, session('cart')[$one->id]);

        $this->post(route('cart.add', $zero), ['quantity' => 1])->assertRedirect();
        $this->assertArrayNotHasKey($zero->id, session('cart', []));
    }

    public function test_farmer_dashboard_scopes_products_orders_and_status_changes_to_the_farmer(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $otherFarmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::create(['farmer_id' => $farmer->id, 'name' => 'Farmer A crop', 'price' => 10, 'unit' => 'kg', 'stock' => 4]);
        $otherProduct = Product::create(['farmer_id' => $otherFarmer->id, 'name' => 'Farmer B crop', 'price' => 20, 'unit' => 'bag', 'stock' => 8]);
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 30,
            'status' => 'pending',
            'delivery_address' => 'Dhaka',
            'phone' => '01700000000',
        ]);
        $item = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 40,
            'status' => 'pending',
            'delivery_address' => 'Chattogram',
            'phone' => '01800000000',
        ]);
        \App\Models\OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'farmer_id' => $farmer->id, 'quantity' => 3, 'unit_price' => 10, 'subtotal' => 30]);
        \App\Models\OrderItem::create(['order_id' => $item->id, 'product_id' => $otherProduct->id, 'farmer_id' => $otherFarmer->id, 'quantity' => 2, 'unit_price' => 20, 'subtotal' => 40]);

        $this->actingAs($farmer)->get(route('farmer.dashboard'))
            ->assertOk()
            ->assertSee('Farmer A crop')
            ->assertDontSee('Farmer B crop')
            ->assertSee('Overview')
            ->assertSee('My products')
            ->assertSee('Active listings')
            ->assertSee('Pending orders')
            ->assertSee('Sales totals are not shown because KrishiHat does not record payment transactions.')
            ->assertSee('Review order');

        $this->get(route('farmer.orders.show', $order))->assertOk()->assertSee('Dhaka');
        $this->put(route('farmer.orders.update', $order), ['status' => 'pending'])->assertRedirect();
        $this->get(route('farmer.orders.show', $item))->assertForbidden();
        $this->delete(route('products.destroy', $otherProduct))->assertForbidden();
        $this->delete(route('products.destroy', $product))->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_buyer_dashboard_profile_and_payment_history_use_real_records_without_claiming_payment(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 75,
            'status' => 'pending',
            'delivery_address' => 'Dhaka address',
            'phone' => '01700000000',
        ]);

        $this->actingAs($buyer)->get(route('dashboard'))
            ->assertRedirect(route('buyer.dashboard'));
        $this->get(route('buyer.dashboard'))->assertOk()->assertSee('Order value recorded')->assertSee('75.00');
        $this->get(route('profile.show'))->assertOk()->assertSee('Dhaka address')->assertSee('01700000000');
        $this->put(route('profile.update'), ['name' => 'Buyer Updated', 'email' => $buyer->email])
            ->assertRedirect(route('profile.show'));
        $this->assertDatabaseHas('users', ['id' => $buyer->id, 'name' => 'Buyer Updated']);
        $this->get(route('transactions.index'))->assertOk()->assertSee('No payment transactions are recorded');
        $this->get(route('farmer.orders.show', $order))->assertForbidden();
    }

    public function test_product_detail_adds_product_to_cart_and_cart_quantity_can_be_updated(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Rice',
            'price' => 55,
            'unit' => 'kg',
            'stock' => 5,
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee(route('cart.add', $product));

        $this->post(route('cart.add', $product))
            ->assertRedirect();

        $this->put(route('cart.update', $product), ['quantity' => 3])
            ->assertRedirect();

        $this->assertSame(3, session('cart')[$product->id]);

        $this->delete(route('cart.remove', $product))
            ->assertRedirect();
        $this->assertArrayNotHasKey($product->id, session('cart'));
    }

    public function test_buyer_checkout_creates_order_decrements_stock_and_clears_cart(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Rice',
            'price' => 55,
            'unit' => 'kg',
            'stock' => 5,
        ]);

        $response = $this->actingAs($buyer)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), [
                'delivery_address' => 'Dhaka',
                'phone' => '01700000000',
            ]);

        $order = Order::where('buyer_id', $buyer->id)->latest('id')->first();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('orders', [
            'buyer_id' => $buyer->id,
            'total_amount' => '110.00',
            'status' => 'pending',
        ]);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame([], session('cart', []));
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 2,
            'unit_price' => '55.00',
        ]);
    }

    public function test_checkout_rejects_stock_changes_without_creating_an_order(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Rice',
            'price' => 55,
            'unit' => 'kg',
            'stock' => 1,
        ]);

        $this->actingAs($buyer)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), [
                'delivery_address' => 'Dhaka',
                'phone' => '01700000000',
            ])
            ->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_buyers_cannot_open_farmer_dashboard_or_manage_products(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($buyer)
            ->get(route('farmer.dashboard'))
            ->assertForbidden();

        $this->actingAs($buyer)
            ->get(route('products.create'))
            ->assertForbidden();
    }
}
