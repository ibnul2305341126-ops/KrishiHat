<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAndOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_avatar_and_history_navigation_follow_the_authenticated_role(): void
    {
        foreach (['buyer', 'farmer'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'name' => ucfirst($role) . ' Member',
            ]);

            $response = $this->actingAs($user)->get(route('home'))->assertOk();
            $response->assertSee('href="' . route('profile.show') . '" aria-label="View your profile"', false);
            $response->assertSee('class="profile-avatar" aria-hidden="true">' . ucfirst($role[0]) . '</span>', false);

            $historyUrl = $role === 'buyer'
                ? route('orders.index')
                : route('farmer.dashboard') . '#farmer-orders';

            $response->assertSee('href="' . $historyUrl . '">History</a>', false);
            $response->assertSee('class="navbar-toggler"', false);
            $response->assertSee('class="collapse navbar-collapse" id="siteNav"', false);
        }
    }

    public function test_profile_shows_and_updates_only_the_authenticated_users_supported_fields(): void
    {
        $user = User::factory()->create(['role' => 'buyer', 'name' => 'Buyer One']);
        $otherUser = User::factory()->create(['role' => 'buyer', 'name' => 'Buyer Two']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Buyer One')
            ->assertSee($user->email);

        $this->put(route('profile.update'), [
            'name' => 'Updated Buyer',
            'email' => 'updated@example.test',
            'role' => 'farmer',
        ])->assertRedirect(route('profile.show'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Buyer',
            'email' => 'updated@example.test',
            'role' => 'buyer',
        ]);
        $this->assertDatabaseHas('users', ['id' => $otherUser->id, 'name' => 'Buyer Two']);

        $this->put(route('profile.update'), [
            'name' => 'Invalid Update',
            'email' => $otherUser->email,
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['id' => $user->id, 'name' => 'Invalid Update']);

        auth()->logout();
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_buyer_order_history_and_details_only_show_the_buyers_orders(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $otherBuyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Buyer One Rice',
            'price' => 40,
            'unit' => 'kg',
            'stock' => 10,
        ]);
        $ownOrder = $this->createOrder($buyer);
        $otherOrder = $this->createOrder($otherBuyer);
        $this->createOrderItem($ownOrder, $product, $farmer, 2, 40);
        $this->createOrderItem($otherOrder, $product, $farmer, 1, 40);

        $this->actingAs($buyer)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Order #' . $ownOrder->id)
            ->assertDontSee('Order #' . $otherOrder->id)
            ->assertSee('Placed ' . $ownOrder->created_at->format('M j, Y'));

        $this->get(route('orders.show', $ownOrder))
            ->assertOk()
            ->assertSee('Buyer One Rice')
            ->assertSee('Quantity: 2')
            ->assertSee('৳40.00')
            ->assertSee('৳80.00')
            ->assertSee(ucfirst($ownOrder->status));

        $this->get(route('orders.show', $otherOrder))->assertForbidden();

        $buyerWithoutOrders = User::factory()->create(['role' => 'buyer']);
        $this->actingAs($buyerWithoutOrders)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('No orders yet');
    }

    public function test_farmer_order_history_shows_only_orders_with_their_products_and_only_their_items(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $otherFarmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $ownProduct = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Farmer One Beans',
            'price' => 30,
            'unit' => 'kg',
            'stock' => 8,
        ]);
        $otherProduct = Product::create([
            'farmer_id' => $otherFarmer->id,
            'name' => 'Farmer Two Corn',
            'price' => 25,
            'unit' => 'kg',
            'stock' => 8,
        ]);
        $relevantOrder = $this->createOrder($buyer);
        $unrelatedOrder = $this->createOrder($buyer);
        $this->createOrderItem($relevantOrder, $ownProduct, $farmer, 2, 30);
        $this->createOrderItem($relevantOrder, $otherProduct, $otherFarmer, 1, 25);
        $this->createOrderItem($unrelatedOrder, $otherProduct, $otherFarmer, 3, 25);

        $this->actingAs($farmer)->get(route('farmer.dashboard'))
            ->assertOk()
            ->assertSee('Order #' . $relevantOrder->id)
            ->assertDontSee('Order #' . $unrelatedOrder->id);

        $this->get(route('farmer.orders.show', $relevantOrder))
            ->assertOk()
            ->assertSee('Farmer One Beans')
            ->assertDontSee('Farmer Two Corn')
            ->assertSee('2 kg')
            ->assertSee('৳30.00')
            ->assertSee('৳60.00')
            ->assertSee(ucfirst($relevantOrder->status));

        $this->get(route('farmer.orders.show', $unrelatedOrder))->assertForbidden();

        $farmerWithoutOrders = User::factory()->create(['role' => 'farmer']);
        $this->actingAs($farmerWithoutOrders)->get(route('farmer.dashboard'))
            ->assertOk()
            ->assertSee('No orders yet');
    }

    private function createOrder(User $buyer): Order
    {
        return Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 100,
            'status' => 'pending',
            'delivery_address' => 'Dhaka',
            'phone' => '01700000000',
        ]);
    }

    private function createOrderItem(Order $order, Product $product, User $farmer, int $quantity, float $unitPrice): void
    {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ]);
    }
}
