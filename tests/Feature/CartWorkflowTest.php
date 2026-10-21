<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_to_cart_and_view_cart_page(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Dinajpur Potato',
            'price' => 28,
            'unit' => 'kg',
            'stock' => 50,
            'image' => 'images/crops/potato.jpg',
        ]);

        // Add 2 kg
        $response = $this->post(route('cart.add', $product), ['quantity' => 2]);
        $response->assertRedirect();
        $this->assertSame(2, session('cart')[$product->id]);

        // View Cart Page
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Dinajpur Potato')
            ->assertSee('28.00')
            ->assertSee('56.00')
            ->assertSee('Log in to checkout');
    }

    public function test_cart_validates_quantity_against_available_stock(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Limited Brinjal',
            'price' => 40,
            'unit' => 'kg',
            'stock' => 5,
        ]);

        // Try adding 6 kg when stock is 5 (validation rejects with error on quantity)
        $this->post(route('cart.add', $product), ['quantity' => 6])
            ->assertSessionHasErrors('quantity');

        // Add 3 kg
        $this->post(route('cart.add', $product), ['quantity' => 3])
            ->assertSessionHas('success');

        // Try adding 3 more (total 6 > 5 stock, rejected with session error)
        $this->post(route('cart.add', $product), ['quantity' => 3])
            ->assertSessionHas('error');
    }

    public function test_cart_quantity_update_and_remove_controls(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Taherpuri Onion',
            'price' => 65,
            'unit' => 'kg',
            'stock' => 100,
        ]);

        $this->withSession(['cart' => [$product->id => 10]])
            ->put(route('cart.update', $product), ['quantity' => 15])
            ->assertSessionHas('success');

        $this->assertSame(15, session('cart')[$product->id]);

        // Remove item
        $this->delete(route('cart.remove', $product))
            ->assertSessionHas('success');

        $this->assertArrayNotHasKey($product->id, session('cart', []));

        // Empty cart page view
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Your cart is empty');
    }

    public function test_guest_cart_is_preserved_when_logging_in(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'email' => 'persisted.buyer@example.test',
            'password' => 'password123',
        ]);

        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'BRRI Paddy',
            'price' => 36,
            'unit' => 'kg',
            'stock' => 200,
        ]);

        // Add item as guest
        $this->post(route('cart.add', $product), ['quantity' => 20]);
        $this->assertSame(20, session('cart')[$product->id]);

        // Log in
        $response = $this->post(route('login.store'), [
            'email' => $buyer->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($buyer);

        // Verify cart is preserved
        $this->assertSame(20, session('cart')[$product->id]);

        // Visit cart as authenticated buyer
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Continue to checkout →');
    }

    public function test_guest_cart_is_preserved_when_registering(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'BRRI Paddy',
            'price' => 36,
            'unit' => 'kg',
            'stock' => 200,
        ]);

        // Add item as guest
        $this->post(route('cart.add', $product), ['quantity' => 15]);

        // Register new buyer account
        $response = $this->post(route('register.store'), [
            'name' => 'New Buyer Account',
            'email' => 'new.buyer.cart@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSame(15, session('cart')[$product->id]);
    }
}
