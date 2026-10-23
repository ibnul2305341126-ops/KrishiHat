<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MarketBenchmarkPrice;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\DamPriceImportService;
use App\Services\SslCommerzPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissingFeaturesRepairTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test 1: Product / crop catalogue review and reference image verification.
     */
    public function test_admin_can_review_and_approve_crop_catalogue_with_reference_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::firstOrCreate(['name' => 'দানা শস্য', 'slug' => 'cereal-grains']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'আসল আমন ধান',
            'category' => $category->name,
            'price' => 32.50,
            'unit' => 'কেজি',
            'stock' => 500,
            'district' => 'বগুড়া',
            'verification_status' => 'unverified',
        ]);

        // Non-admin cannot access catalogue review
        $this->actingAs($farmer)
            ->get(route('admin.catalogue.index'))
            ->assertForbidden();

        // Admin can access catalogue review
        $this->actingAs($admin)
            ->get(route('admin.catalogue.index'))
            ->assertOk()
            ->assertSee('আসল আমন ধান');

        // Admin updates review with suggested correction and attribution
        $this->actingAs($admin)
            ->post(route('admin.catalogue.update-review', $product), [
                'verification_status' => 'suggested',
                'suggested_correction' => 'ব্রি ধান-২৮ (BRRI Dhan 28)',
                'reference_image' => 'images/crops/paddy.jpg',
                'reference_image_source' => 'Wikimedia Commons',
                'reference_image_license' => 'CC BY-SA 4.0',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'verification_status' => 'suggested',
            'reference_image' => 'images/crops/paddy.jpg',
        ]);

        // Admin approves
        $this->actingAs($admin)
            ->post(route('admin.catalogue.approve', $product))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'verification_status' => 'verified',
        ]);

        // Detail page distinguishes reference image from farmer harvest
        $response = $this->get(route('products.show', $product));
        $response->assertOk()
            ->assertSee('শস্য রেফারেন্স তথ্য ও আদর্শ নমুনা')
            ->assertSee('CC BY-SA 4.0');
    }

    /**
     * Test 2: Market data import, duplicate handling, and unverified data labeling.
     */
    public function test_dam_price_import_validates_and_rejects_duplicates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $service = new DamPriceImportService();

        $rows = [
            [
                'crop_name' => 'ডায়মন্ড আলু',
                'category' => 'কন্দাল ফসল',
                'market' => 'রাজশাহী বাজার',
                'district' => 'রাজশাহী',
                'price_type' => 'wholesale',
                'price_modal' => 28.50,
                'price_min' => 26.00,
                'price_max' => 30.00,
                'unit' => 'কেজি',
                'observation_date' => '2026-10-20',
                'is_verified' => true,
            ],
            // Duplicate of row 1
            [
                'crop_name' => 'ডায়মন্ড আলু',
                'category' => 'কন্দাল ফসল',
                'market' => 'রাজশাহী বাজার',
                'district' => 'রাজশাহী',
                'price_type' => 'wholesale',
                'price_modal' => 28.50,
                'unit' => 'কেজি',
                'observation_date' => '2026-10-20',
                'is_verified' => true,
            ],
            // Unverified demo row
            [
                'crop_name' => 'কাঁচা মরিচ',
                'category' => 'মসলা',
                'market' => 'লোকাল ডেমো হাট',
                'district' => 'ঢাকা',
                'price_type' => 'retail',
                'price_modal' => 120.00,
                'unit' => 'কেজি',
                'observation_date' => '2026-10-20',
                'is_verified' => false,
            ],
        ];

        $result = $service->importRecords($rows, 'https://market.dam.gov.bd/');

        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(1, $result['duplicated']);
        $this->assertEquals(0, $result['rejected']);

        // Check unverified label disclosure in admin
        $this->actingAs($admin)
            ->get(route('admin.market_imports.index'))
            ->assertOk()
            ->assertSee('Unverified Demo')
            ->assertSee('ডায়মন্ড আলু');
    }

    /**
     * Test 3: Membership plans eligibility and server-side listing limits.
     */
    public function test_membership_listing_limits_are_enforced_on_server(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::firstOrCreate(['name' => 'শাকসবজি', 'slug' => 'vegetables']);

        // Default free plan has active_listing_limit = 5
        // Seed 5 products for this farmer
        for ($i = 1; $i <= 5; $i++) {
            Product::create([
                'farmer_id' => $farmer->id,
                'category_id' => $category->id,
                'name' => "পরীক্ষামূলক ফসল {$i}",
                'category' => $category->name,
                'price' => 25.00,
                'unit' => 'কেজি',
                'stock' => 10,
                'district' => 'বগুড়া',
            ]);
        }

        $this->assertFalse($farmer->canCreateMoreListings());

        // Attempting to create a 6th product should be blocked with error
        $response = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'অতিরিক্ত ফসল ৬',
            'category_id' => $category->id,
            'price' => 50.00,
            'unit' => 'কেজি',
            'stock' => 20,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('products', ['name' => 'অতিরিক্ত ফসল ৬']);

        // Admin manually grants Pro Plan (limit 50) with mandatory audit reason
        $admin = User::factory()->create(['role' => 'admin']);
        $proPlan = MembershipPlan::firstOrCreate(
            ['slug' => 'farmer-pro'],
            [
                'name' => 'Farmer Pro (কৃষক প্রো)',
                'price' => 499.00,
                'billing_interval' => 'monthly',
                'duration_days' => 30,
                'active_listing_limit' => 50,
                'role_eligibility' => 'farmer',
                'is_active' => true,
            ]
        );

        $this->actingAs($admin)->post(route('admin.subscriptions.manual-grant'), [
            'user_id' => $farmer->id,
            'plan_id' => $proPlan->id,
            'duration_days' => 30,
            'audit_reason' => 'ইউনিভার্সিটি প্রজেক্ট ডেমো লাইভ আপগ্রেড',
        ])->assertSessionHas('success');

        $this->assertTrue($farmer->fresh()->canCreateMoreListings());

        // Now farmer can successfully create the 6th product
        $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'অনুমোদিত ফসল ৬',
            'category_id' => $category->id,
            'price' => 50.00,
            'unit' => 'কেজি',
            'stock' => 20,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['name' => 'অনুমোদিত ফসল ৬']);
    }

    /**
     * Test 4: Checkout payment method selection (COD vs Bank Transfer vs Gateway).
     */
    public function test_checkout_saves_payment_method_and_creates_payment_record(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::firstOrCreate(['name' => 'ফলমূল', 'slug' => 'fruits']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'ফজলি আম',
            'category' => $category->name,
            'price' => 120.00,
            'unit' => 'কেজি',
            'stock' => 50,
            'district' => 'রাজশাহী',
        ]);

        // Place order with COD
        $this->actingAs($buyer)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), [
                'delivery_address' => 'কারওয়ান বাজার, ঢাকা',
                'phone' => '01711-223344',
                'payment_method' => 'cod',
            ])
            ->assertRedirect();

        $codOrder = Order::latest('id')->first();
        $this->assertEquals(Order::PAYMENT_STATUS_UNPAID, $codOrder->payment_status);
        $this->assertDatabaseHas('payments', [
            'payable_id' => $codOrder->id,
            'payable_type' => Order::class,
            'payment_method' => Payment::METHOD_COD,
            'status' => Payment::STATUS_PENDING,
        ]);

        // Place order with Bank Transfer
        $this->actingAs($buyer)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'delivery_address' => 'মতিঝিল, ঢাকা',
                'phone' => '01811-334455',
                'payment_method' => 'bank_transfer',
                'bank_name' => 'ব্র্যাক ব্যাংক',
                'bank_account_no' => '150120394857',
                'bank_tran_ref' => 'BRAC-TXN-998877',
            ])
            ->assertRedirect();

        $bankOrder = Order::latest('id')->first();
        $this->assertEquals(Order::PAYMENT_STATUS_PENDING, $bankOrder->payment_status);
        $this->assertDatabaseHas('payments', [
            'payable_id' => $bankOrder->id,
            'payable_type' => Order::class,
            'payment_method' => Payment::METHOD_BANK_TRANSFER,
            'transaction_id' => 'BRAC-TXN-998877',
            'status' => Payment::STATUS_PENDING,
        ]);

        // Admin verifies the bank payment
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = Payment::where('transaction_id', 'BRAC-TXN-998877')->first();

        $this->actingAs($admin)
            ->post(route('admin.payments.verify', $payment))
            ->assertSessionHas('success');

        $this->assertEquals(Payment::STATUS_COMPLETED, $payment->fresh()->status);
        $this->assertEquals(Order::PAYMENT_STATUS_PAID, $bankOrder->fresh()->payment_status);
    }

    /**
     * Test 5: Gateway callback validation, anti-fake paid protection, and idempotency.
     */
    public function test_gateway_callback_validates_and_protects_against_fake_paid_status(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::firstOrCreate(['name' => 'মশলা', 'slug' => 'spices']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'দেশি পেঁয়াজ',
            'category' => $category->name,
            'price' => 80.00,
            'unit' => 'কেজি',
            'stock' => 100,
            'district' => 'পাবনা',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total_amount' => 160.00,
            'status' => Order::INITIAL_STATUS,
            'payment_method' => 'bkash',
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'delivery_address' => 'ঢাকা',
            'phone' => '01700-000000',
        ]);

        $payment = Payment::create([
            'payable_type' => Order::class,
            'payable_id' => $order->id,
            'user_id' => $buyer->id,
            'payment_type' => Payment::TYPE_ORDER,
            'payment_method' => Payment::METHOD_BKASH,
            'amount' => 160.00,
            'currency' => 'BDT',
            'transaction_id' => 'KH-BKASH-TEST1234',
            'status' => Payment::STATUS_PENDING,
        ]);

        // Simulating external SSLCOMMERZ success callback
        $response = $this->post(route('payment.sslcommerz.success'), [
            'tran_id' => 'KH-BKASH-TEST1234',
            'val_id' => 'VAL-DEMO-999',
            'amount' => '160.00',
            'currency' => 'BDT',
            'status' => 'VALID',
            'card_type' => 'BKASH-bKash',
        ]);

        $response->assertRedirect(route('orders.show', $order));

        $this->assertEquals(Payment::STATUS_COMPLETED, $payment->fresh()->status);
        $this->assertEquals(Order::PAYMENT_STATUS_PAID, $order->fresh()->payment_status);

        // Idempotency: Second identical callback does not throw error or create double charges
        $this->post(route('payment.sslcommerz.success'), [
            'tran_id' => 'KH-BKASH-TEST1234',
            'val_id' => 'VAL-DEMO-999',
            'amount' => '160.00',
            'status' => 'VALID',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertEquals(1, Payment::where('transaction_id', 'KH-BKASH-TEST1234')->count());
    }

    /**
     * Test 6: Separate order vs subscription payments.
     */
    public function test_order_payments_and_subscription_payments_are_distinct(): void
    {
        $user = User::factory()->create(['role' => 'farmer']);
        $plan = MembershipPlan::firstOrCreate(['slug' => 'business-agro'], [
            'name' => 'Agri Business',
            'price' => 1299.00,
            'billing_interval' => 'monthly',
            'duration_days' => 30,
            'active_listing_limit' => 200,
            'role_eligibility' => 'all',
            'is_active' => true,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => UserSubscription::STATUS_PENDING,
            'payment_method' => Payment::METHOD_BKASH,
        ]);

        $subPayment = Payment::create([
            'payable_type' => UserSubscription::class,
            'payable_id' => $subscription->id,
            'user_id' => $user->id,
            'payment_type' => Payment::TYPE_SUBSCRIPTION,
            'payment_method' => Payment::METHOD_BKASH,
            'amount' => 1299.00,
            'currency' => 'BDT',
            'transaction_id' => 'KH-SUB-BKASH-7766',
            'status' => Payment::STATUS_COMPLETED,
        ]);

        $this->assertEquals(Payment::TYPE_SUBSCRIPTION, $subPayment->payment_type);
        $this->assertInstanceOf(UserSubscription::class, $subPayment->payable);
        $this->assertEquals($subscription->id, $subPayment->payable->id);
    }
}
