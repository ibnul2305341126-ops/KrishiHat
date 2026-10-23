<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FarmerVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_farmer_cannot_open_product_create_and_is_redirected_to_profile_with_error(): void
    {
        $unverifiedFarmer = User::factory()->unverifiedFarmer()->create([
            'name' => 'Unverified Farmer',
        ]);

        $response = $this->actingAs($unverifiedFarmer)->get(route('products.create'));

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error');
    }

    public function test_unverified_farmer_cannot_store_product(): void
    {
        $unverifiedFarmer = User::factory()->unverifiedFarmer()->create();
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $response = $this->actingAs($unverifiedFarmer)->post(route('products.store'), [
            'name' => 'Fresh Tomato',
            'category_id' => $category->id,
            'price' => 45,
            'unit' => 'kg',
            'stock' => 100,
            'min_order_quantity' => 10,
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('products', ['name' => 'Fresh Tomato']);
    }

    public function test_farmer_can_submit_nid_and_trade_license_verification_documents(): void
    {
        Storage::fake('public');
        $farmer = User::factory()->unverifiedFarmer()->create();

        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'System Admin',
        ]);

        $response = $this->actingAs($farmer)->post(route('profile.farmer-verification'), [
            'farm_name' => 'গ্রিন এগ্রো খামার',
            'nid_number' => '19951012345678901',
            'trade_license_number' => 'TRAD/BOG/2026/9999',
            'nid_front_photo' => UploadedFile::fake()->image('nid_front.jpg'),
            'nid_back_photo' => UploadedFile::fake()->image('nid_back.jpg'),
            'trade_license_doc' => UploadedFile::fake()->image('license.png'),
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $farmer->refresh();
        $this->assertEquals(User::VERIFY_STATUS_PENDING, $farmer->verification_status);
        $this->assertEquals('19951012345678901', $farmer->nid_number);
        $this->assertEquals('TRAD/BOG/2026/9999', $farmer->trade_license_number);
        $this->assertEquals('গ্রিন এগ্রো খামার', $farmer->farm_name);
        $this->assertNotNull($farmer->nid_front_path);
        $this->assertNotNull($farmer->nid_back_path);
        $this->assertNotNull($farmer->trade_license_path);
        $this->assertNotNull($farmer->verification_submitted_at);

        // Verification section in profile displays pending alert
        $profileResponse = $this->actingAs($farmer)->get(route('profile.show'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('যাচাই অপেক্ষমাণ');
    }

    public function test_admin_can_view_verification_list_and_filter_by_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingFarmer = User::factory()->pendingVerification()->create([
            'name' => 'Pending Farmer One',
            'nid_number' => '19921098765432109',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.verifications.index', ['status' => 'pending']));
        $response->assertOk();
        $response->assertSee('Pending Farmer One');
        $response->assertSee('19921098765432109');
        $response->assertSee('কৃষক প্রোফাইল ও ডকুমেন্টস ভেরিফিকেশন');
    }

    public function test_admin_can_approve_farmer_verification_and_farmer_can_then_create_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->pendingVerification()->create();
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);

        $response = $this->actingAs($admin)->post(route('admin.verifications.approve', $farmer));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $farmer->refresh();
        $this->assertTrue($farmer->isFarmerVerified());
        $this->assertEquals(User::VERIFY_STATUS_VERIFIED, $farmer->verification_status);
        $this->assertEquals($admin->id, $farmer->verified_by_admin_id);
        $this->assertNotNull($farmer->farmer_verified_at);

        // Verified farmer can now open create page and store product
        $this->actingAs($farmer)->get(route('products.create'))->assertOk();

        $storeResponse = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Approved Golden Rice',
            'category_id' => $category->id,
            'price' => 60,
            'unit' => 'kg',
            'stock' => 500,
            'min_order_quantity' => 20,
        ]);

        $storeResponse->assertRedirect(route('farmer.dashboard'));
        $this->assertDatabaseHas('products', ['name' => 'Approved Golden Rice']);
    }

    public function test_admin_can_reject_farmer_verification_with_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->pendingVerification()->create();

        $response = $this->actingAs($admin)->post(route('admin.verifications.reject', $farmer), [
            'reason' => 'ট্রেড লাইসেন্স কপি অস্পষ্ট ও পড়তে পারা যাচ্ছে না।',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $farmer->refresh();
        $this->assertEquals(User::VERIFY_STATUS_REJECTED, $farmer->verification_status);
        $this->assertNull($farmer->farmer_verified_at);
        $this->assertEquals('ট্রেড লাইসেন্স কপি অস্পষ্ট ও পড়তে পারা যাচ্ছে না।', $farmer->verification_rejection_reason);

        // Farmer sees the rejection reason on profile
        $profileResponse = $this->actingAs($farmer)->get(route('profile.show'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('ট্রেড লাইসেন্স কপি অস্পষ্ট ও পড়তে পারা যাচ্ছে না।');
    }

    public function test_non_admin_cannot_access_admin_verification_routes(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($farmer)->get(route('admin.verifications.index'))->assertForbidden();
        $this->actingAs($buyer)->get(route('admin.verifications.index'))->assertForbidden();
    }
}
