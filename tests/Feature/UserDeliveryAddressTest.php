<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\District;
use App\Models\Division;
use App\Models\PostOffice;
use App\Models\Product;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\UrbanArea;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\BangladeshAdministrativeAddressSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeliveryAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BangladeshAdministrativeAddressSeeder::class);
    }

    public function test_buyer_can_store_valid_delivery_address_with_hierarchy(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $rajshahi = Division::where('name_en', 'Rajshahi')->firstOrFail();
        $bogura = District::where('name_en', 'Bogura')->where('division_id', $rajshahi->id)->firstOrFail();
        $sherpur = Upazila::where('name_en', 'Sherpur')->where('district_id', $bogura->id)->firstOrFail();
        $kusumbi = Union::where('name_en', 'Kusumbi')->where('upazila_id', $sherpur->id)->firstOrFail();
        $po = PostOffice::where('upazila_id', $sherpur->id)->firstOrFail();

        $response = $this->actingAs($buyer)->post(route('addresses.store'), [
            'title' => 'Home',
            'recipient_name' => 'Mizanur Rahman',
            'phone' => '01711223344',
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'union_id' => $kusumbi->id,
            'post_office_id' => $po->id,
            'postal_code' => $po->post_code,
            'street_address' => 'House 14, Kusumbi Bazar Road',
            'is_default' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $buyer->id,
            'recipient_name' => 'Mizanur Rahman',
            'phone' => '01711223344',
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'union_id' => $kusumbi->id,
            'is_default' => 1,
        ]);

        $savedAddress = UserAddress::where('user_id', $buyer->id)->firstOrFail();
        $this->assertStringContainsString('Kusumbi', $savedAddress->formatted_address);
    }

    public function test_mismatched_address_hierarchy_is_rejected(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $dhakaDiv = Division::where('name_en', 'Dhaka')->firstOrFail();
        $dhakaDist = District::where('name_en', 'Dhaka')->where('division_id', $dhakaDiv->id)->firstOrFail();

        // Bogura's Sherpur belongs to Rajshahi, not Dhaka!
        $sherpur = Upazila::where('name_en', 'Sherpur')->whereHas('district', fn ($q) => $q->where('name_en', 'Bogura'))->firstOrFail();

        $response = $this->actingAs($buyer)->post(route('addresses.store'), [
            'title' => 'Office',
            'recipient_name' => 'Mizanur Rahman',
            'phone' => '01711223344',
            'division_id' => $dhakaDiv->id,
            'district_id' => $dhakaDist->id,
            'upazila_id' => $sherpur->id, // Mismatch!
            'street_address' => 'Dhanmondi Road 27',
        ]);

        $response->assertSessionHasErrors('upazila_id');
        $this->assertDatabaseCount('user_addresses', 0);
    }

    public function test_urban_delivery_address_without_union_is_accepted(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $dhakaDiv = Division::where('name_en', 'Dhaka')->firstOrFail();
        $dhakaDist = District::where('name_en', 'Dhaka')->firstOrFail();
        $dhanmondi = Upazila::where('name_en', 'Dhanmondi')->where('district_id', $dhakaDist->id)->firstOrFail();

        $response = $this->actingAs($buyer)->post(route('addresses.store'), [
            'title' => 'Dhaka Office',
            'recipient_name' => 'Corporate Buyer',
            'phone' => '01811223344',
            'division_id' => $dhakaDiv->id,
            'district_id' => $dhakaDist->id,
            'upazila_id' => $dhanmondi->id,
            'street_address' => 'House 45, Road 9A, Dhanmondi',
            'is_default' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $buyer->id,
            'upazila_id' => $dhanmondi->id,
            'union_id' => null,
            'is_default' => 1,
        ]);
    }

    public function test_buyer_can_update_and_delete_own_address(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $rajshahi = Division::where('name_en', 'Rajshahi')->firstOrFail();
        $bogura = District::where('name_en', 'Bogura')->firstOrFail();
        $sherpur = Upazila::where('name_en', 'Sherpur')->firstOrFail();

        $address = UserAddress::create([
            'user_id' => $buyer->id,
            'title' => 'Home',
            'recipient_name' => 'Old Name',
            'phone' => '01700000000',
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'street_address' => 'Old Street',
            'is_default' => 1,
        ]);

        $response = $this->actingAs($buyer)->put(route('addresses.update', $address), [
            'title' => 'Updated Home',
            'recipient_name' => 'New Name',
            'phone' => '01711111111',
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'street_address' => 'New Street 123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_addresses', [
            'id' => $address->id,
            'recipient_name' => 'New Name',
            'street_address' => 'New Street 123',
        ]);

        // Delete address
        $delResponse = $this->actingAs($buyer)->delete(route('addresses.destroy', $address));
        $delResponse->assertRedirect();
        $this->assertDatabaseMissing('user_addresses', ['id' => $address->id]);
    }

    public function test_unauthorized_user_cannot_modify_another_users_address(): void
    {
        $owner = User::factory()->create(['role' => 'buyer']);
        $attacker = User::factory()->create(['role' => 'buyer']);

        $rajshahi = Division::where('name_en', 'Rajshahi')->firstOrFail();
        $bogura = District::where('name_en', 'Bogura')->firstOrFail();
        $sherpur = Upazila::where('name_en', 'Sherpur')->firstOrFail();

        $address = UserAddress::create([
            'user_id' => $owner->id,
            'title' => 'Private Address',
            'recipient_name' => 'Owner Name',
            'phone' => '01700000000',
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'street_address' => 'Private Road',
            'is_default' => 1,
        ]);

        $this->actingAs($attacker)->put(route('addresses.update', $address), [
            'title' => 'Hacked',
            'recipient_name' => 'Hacker',
            'phone' => '01900000000',
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'street_address' => 'Hacked Street',
        ])->assertForbidden();

        $this->actingAs($attacker)->delete(route('addresses.destroy', $address))->assertForbidden();
        $this->assertDatabaseHas('user_addresses', ['id' => $address->id]);
    }

    public function test_checkout_allows_selecting_saved_address_by_id(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Carrots',
            'price' => 40.00,
            'unit' => 'kg',
            'stock' => 100,
        ]);

        $address = UserAddress::create([
            'user_id' => $buyer->id,
            'title' => 'Warehouse',
            'recipient_name' => 'Rahim Store',
            'phone' => '01799887766',
            'street_address' => 'Warehouse 4, Karwan Bazar, Dhaka',
            'is_default' => 1,
        ]);

        $response = $this->actingAs($buyer)
            ->withSession(['cart' => [$product->id => 5]])
            ->post(route('checkout.store'), [
                'delivery_address_id' => $address->id,
                'payment_method' => 'cod',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'buyer_id' => $buyer->id,
            'phone' => '01799887766',
            'delivery_address' => 'Warehouse 4, Karwan Bazar, Dhaka',
        ]);
    }
}
