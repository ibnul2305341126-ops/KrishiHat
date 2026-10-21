<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinimumOrderAndNotificationPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_can_set_and_update_min_order_quantity(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);

        // Create product with MOQ = 20
        $response = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Wholesale Wheat',
            'category_id' => $category->id,
            'price' => 35.00,
            'unit' => 'kg',
            'stock' => 500,
            'min_order_quantity' => 20,
        ]);

        $response->assertRedirect();
        $product = Product::where('name', 'Wholesale Wheat')->first();
        $this->assertNotNull($product);
        $this->assertSame(20, $product->min_order_quantity);

        // Edit product to update MOQ = 50
        $updateResponse = $this->actingAs($farmer)->put(route('products.update', $product), [
            'name' => 'Wholesale Wheat Updated',
            'category_id' => $category->id,
            'price' => 36.00,
            'unit' => 'kg',
            'stock' => 500,
            'min_order_quantity' => 50,
        ]);

        $updateResponse->assertRedirect();
        $product->refresh();
        $this->assertSame(50, $product->min_order_quantity);
    }

    public function test_cart_and_offer_enforce_min_order_quantity(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains-moq']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Bulk Basmati Rice',
            'price' => 80.00,
            'unit' => 'kg',
            'stock' => 200,
            'min_order_quantity' => 25,
        ]);

        // 1. Product page shows MOQ alert
        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('সর্বনিম্ন অর্ডারের পরিমাণ (MOQ): 25 kg');

        // 2. Add to cart below MOQ is rejected
        $this->post(route('cart.add', $product), ['quantity' => 10])
            ->assertSessionHasErrors('quantity');
        $this->assertEmpty(session('cart'));

        // 3. Add to cart at or above MOQ succeeds
        $this->post(route('cart.add', $product), ['quantity' => 30])
            ->assertSessionHas('success');
        $this->assertSame(30, session('cart')[$product->id]);

        // 4. Cart update below MOQ is rejected
        $this->put(route('cart.update', $product), ['quantity' => 15])
            ->assertSessionHasErrors('quantity');

        // 5. Offer below MOQ is rejected
        $this->actingAs($buyer)->post(route('offers.store', $product), [
            'price' => 75.00,
            'quantity' => 10,
        ])->assertSessionHasErrors('quantity');

        // 6. Offer at MOQ succeeds
        $this->actingAs($buyer)->post(route('offers.store', $product), [
            'price' => 75.00,
            'quantity' => 25,
        ])->assertSessionHas('success');
    }

    public function test_checkout_rejects_cart_if_quantity_below_min_order_quantity(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $category = Category::create(['name' => 'Oil', 'slug' => 'oil']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Mustard Oil Barrel',
            'price' => 150.00,
            'unit' => 'liter',
            'stock' => 100,
            'min_order_quantity' => 10,
        ]);

        // Artificially put 5 in cart (less than MOQ 10)
        $this->withSession(['cart' => [$product->id => 5]]);

        $response = $this->actingAs($buyer)->post(route('checkout.store'), [
            'delivery_address' => 'Chittagong Port',
            'phone' => '01800000000',
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHasErrors('cart');
    }

    public function test_notification_panel_is_visible_and_accessible_when_no_unread(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);

        // User has 0 unread notifications, navbar still has notification bell and link
        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('🔔')
            ->assertSee('নোটিফিকেশন')
            ->assertSee('নতুন কোনো নোটিফিকেশন নেই');

        // User can visit notifications.index
        $this->actingAs($user)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('আপনার সকল নোটিফিকেশন')
            ->assertSee('কোনো নোটিফিকেশন পাওয়া যায়নি');
    }

    public function test_notification_redirection_sanitizes_absolute_urls_with_ports(): void
    {
        $user = User::factory()->create(['role' => 'farmer']);
        $user->notify(new WorkflowNotification('Price offer received', 'http://127.0.0.1:8090/farmer/dashboard#farmer-offers'));

        $notification = $user->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertTrue($notification->unread());

        // Opening notification marks as read and redirects safely without old port
        $res = $this->actingAs($user)->get(route('notifications.open', $notification->id));
        $res->assertRedirect('/farmer/dashboard#farmer-offers');

        $notification->refresh();
        $this->assertFalse($notification->unread());
    }

    public function test_admin_can_log_in_and_access_admin_panel(): void
    {
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin.test@krishihat.test',
            'password' => bcrypt('AdminSecret123!'),
        ]);
        $admin->role = 'admin';
        $admin->save();

        // Attempt login with valid credentials
        $loginRes = $this->post(route('login'), [
            'email' => 'admin.test@krishihat.test',
            'password' => 'AdminSecret123!',
        ]);

        $loginRes->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        // Access admin dashboard
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('সাম্প্রতিক অর্ডারসমূহ')
            ->assertSee('অ্যাডমিন প্যানেল');
    }

    public function test_non_admin_gets_helpful_403_page_and_login_prefills_admin(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        // Non-admin gets 403 with helpful credentials guidance
        $this->actingAs($buyer)->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('অ্যাডমিন প্যানেলে প্রবেশাধিকার সংরক্ষিত')
            ->assertSee('admin@demo.krishihat.test');

        // Login page with ?as=admin pre-fills credentials
        $this->get(route('login', ['as' => 'admin']))
            ->assertOk()
            ->assertSee('admin@demo.krishihat.test')
            ->assertSee('১-ক্লিকে ডেমো লগইন বাটন');
    }

    public function test_admin_can_view_transactions_page_without_403(): void
    {
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin.audit@krishihat.test',
            'password' => bcrypt('AdminSecret123!'),
        ]);
        $admin->role = 'admin';
        $admin->save();

        // Admin visiting /transactions must return 200 OK and not 403
        $this->actingAs($admin)->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('লেনদেন ও নিরাপত্তা অডিট রেকর্ড');
    }

    public function test_admin_transaction_history_contains_customer_farmer_amount_and_security_audit_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['name' => 'আল-আমিন ট্রেডার্স (Al-Amin Buyer)', 'role' => 'buyer']);
        $farmer = User::factory()->create(['name' => 'মো. রফিকুল ইসলাম (Rafiqul Farmer)', 'role' => 'farmer']);

        $product = \App\Models\Product::create([
            'name' => 'ব্রি-২৮ ধান প্রিমিয়াম লট',
            'farmer_id' => $farmer->id,
            'price' => 35.00,
            'stock' => 500,
            'unit' => 'kg',
            'min_order_quantity' => 50,
            'description' => 'প্রিমিয়াম কোয়ালিটি ধান',
        ]);

        $order = \App\Models\Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 17500.00,
            'status' => \App\Models\Order::STATUS_DELIVERED,
            'delivery_address' => 'কারওয়ান বাজার, ঢাকা',
            'phone' => '01711998877',
            'payment_method' => 'bkash',
            'payment_status' => \App\Models\Order::PAYMENT_STATUS_PAID,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 500,
            'unit_price' => 35.00,
            'subtotal' => 17500.00,
        ]);

        $payment = \App\Models\Payment::create([
            'payable_type' => \App\Models\Order::class,
            'payable_id' => $order->id,
            'user_id' => $buyer->id,
            'payment_type' => \App\Models\Payment::TYPE_ORDER,
            'payment_method' => \App\Models\Payment::METHOD_BKASH,
            'amount' => 17500.00,
            'currency' => 'BDT',
            'transaction_id' => 'KH-TXN-SEC-112233',
            'status' => \App\Models\Payment::STATUS_COMPLETED,
            'notes' => 'বিকাশ অনলাইন সিকিউরিটি পেমেন্ট অডিট',
        ]);

        $response = $this->actingAs($admin)->get(route('transactions.index'));

        $response->assertOk()
            ->assertSee('KH-TXN-SEC-112233')
            ->assertSee('আল-আমিন ট্রেডার্স')
            ->assertSee('মো. রফিকুল ইসলাম')
            ->assertSee('ব্রি-২৮ ধান প্রিমিয়াম লট')
            ->assertSee('17,500.00')
            ->assertSee('বিকাশ (bKash)')
            ->assertSee($payment->security_verification_code);
    }
}
