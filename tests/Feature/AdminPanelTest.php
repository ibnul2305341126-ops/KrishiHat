<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admins_and_guests_are_forbidden_from_admin_panel(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        foreach (['buyer', 'farmer'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
            $this->get(route('admin.users.index'))->assertForbidden();
            $this->get(route('admin.products.index'))->assertForbidden();
            $this->get(route('admin.orders.index'))->assertForbidden();
            $this->get(route('admin.offers.index'))->assertForbidden();
            $this->get(route('admin.reports.index'))->assertForbidden();
        }
    }

    public function test_admin_can_access_dashboard_with_database_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Boss']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);

        $category = Category::create(['name' => 'Cereals', 'slug' => 'cereals']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Demo Rice',
            'price' => 50,
            'unit' => 'kg',
            'stock' => 100,
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 500,
            'status' => Order::STATUS_DELIVERED,
            'delivery_address' => 'Dhaka Market',
            'phone' => '01700000000',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 10,
            'unit_price' => 50,
            'subtotal' => 500,
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Boss')
            ->assertSee('500.00')
            ->assertSee('Demo Rice');
    }

    public function test_admin_can_manage_users_safely(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'buyer', 'name' => 'Ordinary Buyer']);

        // List & Search
        $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Ordinary']))
            ->assertOk()
            ->assertSee('Ordinary Buyer');

        // Edit
        $this->get(route('admin.users.edit', $targetUser))
            ->assertOk()
            ->assertSee($targetUser->email);

        // Update role
        $this->put(route('admin.users.update', $targetUser), [
            'name' => 'Ordinary Farmer',
            'email' => $targetUser->email,
            'role' => 'farmer',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame('farmer', $targetUser->fresh()->role);

        // Delete user without orders succeeds
        $this->delete(route('admin.users.destroy', $targetUser))
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);

        // Attempting to delete oneself is blocked
        $this->delete(route('admin.users.destroy', $admin))
            ->assertSessionHas('error');
    }

    public function test_admin_cannot_delete_user_with_order_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 100,
            'status' => 'pending',
            'delivery_address' => 'Dhaka',
            'phone' => '01700000000',
        ]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $buyer))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $buyer->id]);
    }

    public function test_admin_can_manage_products_and_toggle_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Seasonal Mango',
            'price' => 80,
            'unit' => 'kg',
            'stock' => 50,
        ]);

        $this->actingAs($admin)->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Seasonal Mango');

        // Toggle stock to 0
        $this->patch(route('admin.products.toggle-status', $product))->assertRedirect();
        $this->assertSame(0, $product->fresh()->stock);

        // Toggle stock restore
        $this->patch(route('admin.products.toggle-status', $product))->assertRedirect();
        $this->assertSame(100, $product->fresh()->stock);
    }

    public function test_admin_can_inspect_orders_and_tracking_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Bogura Paddy',
            'price' => 35,
            'unit' => 'kg',
            'stock' => 500,
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 3500,
            'status' => Order::STATUS_SHIPPED,
            'delivery_address' => 'Kawran Bazar Wholesale Market, Dhaka',
            'phone' => '01700-112233',
            'tracking_code' => 'KH-TRK-TEST01',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 100,
            'unit_price' => 35,
            'subtotal' => 3500,
        ]);

        $order->recordTrackingEvent(
            Order::STATUS_SHIPPED,
            'Departed Bogura Warehouse',
            'Truck loaded with 100 kg paddy.',
            'Bogura Central Depot',
            actorId: $farmer->id
        );

        $this->actingAs($admin)->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('KH-TRK-TEST01');

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('KH-TRK-TEST01')
            ->assertSee('Kawran Bazar Wholesale Market')
            ->assertSee('Departed Bogura Warehouse');
    }

    public function test_admin_can_inspect_offers_and_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Taherpuri Onion',
            'price' => 70,
            'unit' => 'kg',
            'stock' => 200,
        ]);

        $offer = Offer::create([
            'buyer_id' => $buyer->id,
            'farmer_id' => $farmer->id,
            'product_id' => $product->id,
            'initial_price' => 60,
            'current_price' => 65,
            'quantity' => 50,
            'status' => Offer::STATUS_COUNTERED,
        ]);

        $this->actingAs($admin)->get(route('admin.offers.index'))
            ->assertOk()
            ->assertSee('Taherpuri Onion')
            ->assertSee('65.00');

        $this->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('মার্কেটপ্লেস রিপোর্ট ও অ্যানালিটিক্স')
            ->assertSee('ক্যাশ-অন-ডেলিভারি');
    }

    public function test_artisan_command_provisions_admin(): void
    {
        $this->artisan('krishihat:create-admin', [
            'email' => 'custom.admin@krishihat.test',
            '--name' => 'Super Admin',
            '--password' => 'SecretPass123!',
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'custom.admin@krishihat.test',
            'role' => 'admin',
        ]);
    }
}
