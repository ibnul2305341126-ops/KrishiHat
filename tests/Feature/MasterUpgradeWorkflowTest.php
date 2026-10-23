<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\BangladeshDamMarketData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterUpgradeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_switch_allows_switching_roles_and_redirects_appropriately(): void
    {
        $farmer = User::factory()->create([
            'email' => 'farmer.mokbul@demo.krishihat.test',
            'role' => 'farmer',
            'name' => 'Mokbul Farmer',
        ]);

        $buyer = User::factory()->create([
            'email' => 'buyer.karim@demo.krishihat.test',
            'role' => 'buyer',
            'name' => 'Karim Buyer',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin@demo.krishihat.test',
            'role' => 'admin',
            'name' => 'Super Admin',
        ]);

        // 1. Non-admin (buyer) CANNOT switch to admin (Privilege Escalation Prevention)
        $this->actingAs($buyer)
            ->post(route('switch-account'), ['target_role' => 'admin'])
            ->assertForbidden();

        // 2. Buyer can switch to demo farmer
        $resFarmer = $this->actingAs($buyer)->post(route('switch-account'), ['target_role' => 'farmer']);
        $resFarmer->assertRedirect(route('farmer.dashboard'));
        $this->assertAuthenticatedAs($farmer);

        // 3. Farmer CANNOT switch to admin
        $this->actingAs($farmer)
            ->post(route('switch-account'), ['target_role' => 'admin'])
            ->assertForbidden();

        // 4. Farmer can switch to demo buyer
        $resBuyer = $this->actingAs($farmer)->post(route('switch-account'), ['target_role' => 'buyer']);
        $resBuyer->assertRedirect(route('profile.show'));
        $this->assertAuthenticatedAs($buyer);

        // 5. Authenticated admin can switch to admin dashboard
        $resAdmin = $this->actingAs($admin)->post(route('switch-account'), ['target_role' => 'admin']);
        $resAdmin->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        // 6. Arbitrary target_email or unknown role parameters are rejected
        $this->actingAs($buyer)
            ->post(route('switch-account'), ['target_role' => 'superhacker'])
            ->assertSessionHasErrors('target_role');
    }

    public function test_profile_page_displays_switcher_and_logout_options(): void
    {
        $buyer = User::factory()->create([
            'email' => 'buyer.karim@demo.krishihat.test',
            'role' => 'buyer',
            'name' => 'Karim Buyer',
        ]);

        $response = $this->actingAs($buyer)->get(route('profile.show'));

        $response->assertOk()
            ->assertSee('আপনার প্রোফাইল (Profile)')
            ->assertSee('দ্রুত অ্যাকাউন্ট পরিবর্তন (Switch Account)')
            ->assertSee('farmer.mokbul@demo.krishihat.test')
            ->assertSee('admin@demo.krishihat.test')
            ->assertSee('🔒 অ্যাডমিন অ্যাকাউন্টে লগইন')
            ->assertSee('লগ আউট (Log Out)');
    }

    public function test_order_show_routes_assigned_farmer_and_admin_without_403(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $stranger = User::factory()->create(['role' => 'buyer']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Paddy',
            'price' => 35,
            'unit' => 'kg',
            'stock' => 100,
            'district' => 'Bogura',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 350.00,
            'status' => Order::STATUS_PENDING,
            'delivery_address' => 'Kawran Bazar, Dhaka',
            'phone' => '01711111111',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'quantity' => 10,
            'unit_price' => 35.00,
            'subtotal' => 350.00,
        ]);

        // Buyer opens order show -> 200 OK
        $this->actingAs($buyer)->get(route('orders.show', $order))->assertOk();

        // Farmer opens order show -> redirects to farmer.orders.show
        $this->actingAs($farmer)->get(route('orders.show', $order))
            ->assertRedirect(route('farmer.orders.show', $order));

        // Admin opens order show -> redirects to admin.orders.show
        $this->actingAs($admin)->get(route('orders.show', $order))
            ->assertRedirect(route('admin.orders.show', $order));

        // Unauthorized stranger is blocked with 403
        $this->actingAs($stranger)->get(route('orders.show', $order))
            ->assertForbidden();
    }

    public function test_google_maps_url_generation_for_exact_and_approximate_locations(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);

        // 1. Product with exact coordinates
        $geoProduct = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'BRRI Dhan 28',
            'price' => 36,
            'unit' => 'kg',
            'stock' => 50,
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
            'latitude' => 24.6738,
            'longitude' => 89.4184,
        ]);

        $this->assertTrue($geoProduct->hasExactCoordinates());
        $this->assertStringContainsString('query=24.6738%2C89.4184', $geoProduct->google_maps_search_url);
        $this->assertStringContainsString('destination=24.6738%2C89.4184', $geoProduct->google_maps_directions_url);

        // 2. Product without coordinates (approximate address search)
        $approxProduct = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Diamant Potato',
            'price' => 28,
            'unit' => 'kg',
            'stock' => 80,
            'district' => 'Dinajpur',
            'upazila' => 'Kotwali',
        ]);

        $this->assertFalse($approxProduct->hasExactCoordinates());
        $this->assertStringContainsString('query=Kotwali%2C%20Dinajpur%2C%20Bangladesh', $approxProduct->google_maps_search_url);

        // 3. Order delivery google maps URL
        $order = new Order([
            'delivery_address' => 'Kawran Bazar, Dhaka',
        ]);
        $this->assertStringContainsString('query=Kawran%20Bazar%2C%20Dhaka%2C%20Bangladesh', $order->delivery_google_maps_url);
        $this->assertStringContainsString('destination=Kawran%20Bazar%2C%20Dhaka%2C%20Bangladesh', $order->delivery_google_maps_directions_url);
    }

    public function test_unverified_dam_market_records_truthfully_labeled_on_forecast_page(): void
    {
        $records = BangladeshDamMarketData::getVerifiedReferenceRecords();
        $this->assertNotEmpty($records);
        $this->assertFalse($records[0]['is_verified']);
        $this->assertStringContainsString('Unverified Demo Data', $records[0]['verification_status']);

        $response = $this->get(route('forecast.index'));
        $response->assertOk()
            ->assertSee('কৃষি বিপণন অধিদপ্তর (DAM) কাঠামোভিত্তিক ডেমো রেফারেন্স দর')
            ->assertSee('Unverified Demo Data')
            ->assertSee('market.dam.gov.bd')
            ->assertSee('বগুড়া পাইকারি মোকাম')
            ->assertSee('35.50');
    }

    public function test_admin_can_manage_membership_plans(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->create(['role' => 'farmer']);

        // Non-admin is forbidden
        $this->actingAs($farmer)->get(route('admin.plans.index'))->assertForbidden();

        // Admin can view plans index
        $this->actingAs($admin)->get(route('admin.plans.index'))->assertOk();

        // Admin creates a new membership plan
        $this->actingAs($admin)->post(route('admin.plans.store'), [
            'name' => 'Custom Agro Plan',
            'slug' => 'custom-agro',
            'description' => 'Special tier for agro exporters',
            'price' => 999.00,
            'billing_interval' => 'monthly',
            'duration_days' => 30,
            'active_listing_limit' => 75,
            'role_eligibility' => 'farmer',
            'can_view_analytics' => '1',
            'can_bulk_purchase' => '0',
            'priority_support' => '1',
            'is_active' => '1',
            'features_text' => "75 Active listings\nPriority support\nMarket analytics",
        ])->assertSessionHas('success');

        $plan = MembershipPlan::where('slug', 'custom-agro')->firstOrFail();
        $this->assertEquals(999.00, $plan->price);
        $this->assertEquals(75, $plan->active_listing_limit);
        $this->assertTrue($plan->can_view_analytics);
        $this->assertCount(3, $plan->features);

        // Admin updates the plan
        $this->actingAs($admin)->put(route('admin.plans.update', $plan), [
            'name' => 'Custom Agro Plan Premium',
            'description' => 'Updated tier description',
            'price' => 1199.00,
            'billing_interval' => 'monthly',
            'duration_days' => 30,
            'active_listing_limit' => 100,
            'role_eligibility' => 'farmer',
            'can_view_analytics' => '1',
            'can_bulk_purchase' => '1',
            'priority_support' => '1',
            'is_active' => '1',
            'features_text' => "100 Active listings\nBulk purchase\nVIP priority",
        ])->assertSessionHas('success');

        $this->assertEquals('Custom Agro Plan Premium', $plan->fresh()->name);
        $this->assertEquals(1199.00, $plan->fresh()->price);
        $this->assertEquals(100, $plan->fresh()->active_listing_limit);

        // Admin toggles active status
        $this->actingAs($admin)->post(route('admin.plans.toggle', $plan))->assertSessionHas('success');
        $this->assertFalse($plan->fresh()->is_active);

        $this->actingAs($admin)->post(route('admin.plans.toggle', $plan))->assertSessionHas('success');
        $this->assertTrue($plan->fresh()->is_active);
    }

    public function test_admin_can_reject_catalogue_crop_and_reject_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Suspicious Variety',
            'price' => 20,
            'unit' => 'kg',
            'stock' => 10,
            'verification_status' => 'unverified',
        ]);

        // Admin rejects crop catalogue entry
        $this->actingAs($admin)->post(route('admin.catalogue.reject', $product))->assertSessionHas('success');
        $this->assertEquals('rejected', $product->fresh()->verification_status);

        // Order & Payment rejection
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 200,
            'status' => Order::INITIAL_STATUS,
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'delivery_address' => 'Dhaka',
            'phone' => '01700-000000',
        ]);

        $payment = Payment::create([
            'payable_type' => Order::class,
            'payable_id' => $order->id,
            'user_id' => $buyer->id,
            'payment_type' => Payment::TYPE_ORDER,
            'payment_method' => Payment::METHOD_BANK_TRANSFER,
            'amount' => 200.00,
            'currency' => 'BDT',
            'transaction_id' => 'KH-BNK-REJECT-01',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.payments.reject', $payment), [
            'reason' => 'ভুয়া ব্যাংক ট্রানজেকশন স্লিপ জমা দেওয়া হয়েছে।',
        ])->assertSessionHas('success');

        $this->assertEquals(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertEquals(Order::PAYMENT_STATUS_FAILED, $order->fresh()->payment_status);
        $this->assertStringContainsString('ভুয়া ব্যাংক ট্রানজেকশন', $payment->fresh()->notes);
    }

    public function test_transaction_history_displays_completed_transactions(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        Payment::create([
            'payable_type' => Order::class,
            'payable_id' => 99,
            'user_id' => $buyer->id,
            'payment_type' => Payment::TYPE_ORDER,
            'payment_method' => Payment::METHOD_BKASH,
            'amount' => 450.00,
            'currency' => 'BDT',
            'transaction_id' => 'KH-TXN-SUCCESS-99',
            'status' => Payment::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($buyer)->get(route('transactions.index'));
        $response->assertOk()
            ->assertSee('KH-TXN-SUCCESS-99')
            ->assertSee('450.00')
            ->assertSee('Order #99')
            ->assertSee('bkash');
    }
}
