<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\District;
use App\Models\Division;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PostOffice;
use App\Models\Product;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\UrbanArea;
use App\Models\User;
use Database\Seeders\BangladeshAdministrativeAddressSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BangladeshAddressWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BangladeshAdministrativeAddressSeeder::class);
    }

    public function test_division_list_api_returns_all_eight_divisions(): void
    {
        $response = $this->getJson(route('geo.divisions'));

        $response->assertOk();
        $divisions = $response->json();

        $this->assertIsArray($divisions);
        $this->assertCount(8, $divisions);

        $namesEn = collect($divisions)->pluck('name_en')->all();
        $this->assertContains('Dhaka', $namesEn);
        $this->assertContains('Rajshahi', $namesEn);
        $this->assertContains('Chattogram', $namesEn);
        $this->assertContains('Khulna', $namesEn);
        $this->assertContains('Barishal', $namesEn);
        $this->assertContains('Sylhet', $namesEn);
        $this->assertContains('Rangpur', $namesEn);
        $this->assertContains('Mymensingh', $namesEn);
    }

    public function test_division_to_district_filtering(): void
    {
        $rajshahi = Division::where('name_en', 'Rajshahi')->firstOrFail();
        $dhaka = Division::where('name_en', 'Dhaka')->firstOrFail();

        // 1. Rajshahi Division districts
        $rajshahiResponse = $this->getJson(route('geo.districts', ['division_id' => $rajshahi->id]));
        $rajshahiResponse->assertOk();
        $rajshahiDistrictNames = collect($rajshahiResponse->json())->pluck('name_en')->all();

        $this->assertContains('Bogura', $rajshahiDistrictNames);
        $this->assertContains('Rajshahi', $rajshahiDistrictNames);
        $this->assertNotContains('Dhaka', $rajshahiDistrictNames);
        $this->assertNotContains('Gazipur', $rajshahiDistrictNames);

        // 2. Dhaka Division districts
        $dhakaResponse = $this->getJson(route('geo.districts', ['division_id' => $dhaka->id]));
        $dhakaResponse->assertOk();
        $dhakaDistrictNames = collect($dhakaResponse->json())->pluck('name_en')->all();

        $this->assertContains('Dhaka', $dhakaDistrictNames);
        $this->assertContains('Gazipur', $dhakaDistrictNames);
        $this->assertNotContains('Bogura', $dhakaDistrictNames);
    }

    public function test_district_to_upazila_filtering(): void
    {
        $bogura = District::where('name_en', 'Bogura')->firstOrFail();
        $dhaka = District::where('name_en', 'Dhaka')->firstOrFail();

        // 1. Bogura Upazilas
        $boguraResponse = $this->getJson(route('geo.upazilas', ['district_id' => $bogura->id]));
        $boguraResponse->assertOk();
        $boguraUpazilaNames = collect($boguraResponse->json())->pluck('name_en')->all();

        $this->assertContains('Sherpur', $boguraUpazilaNames);
        $this->assertContains('Bogura Sadar', $boguraUpazilaNames);
        $this->assertNotContains('Savar', $boguraUpazilaNames);

        // 2. Dhaka Upazilas / Thanas
        $dhakaResponse = $this->getJson(route('geo.upazilas', ['district_id' => $dhaka->id]));
        $dhakaResponse->assertOk();
        $dhakaUpazilaNames = collect($dhakaResponse->json())->pluck('name_en')->all();

        $this->assertContains('Savar', $dhakaUpazilaNames);
        $this->assertNotContains('Sherpur', $dhakaUpazilaNames);
    }

    public function test_upazila_to_union_and_urban_area_filtering(): void
    {
        $bogura = District::where('name_en', 'Bogura')->firstOrFail();
        $sherpur = Upazila::where('name_en', 'Sherpur')->where('district_id', $bogura->id)->firstOrFail();

        // 1. Unions for Sherpur
        $unionResponse = $this->getJson(route('geo.unions', ['upazila_id' => $sherpur->id]));
        $unionResponse->assertOk();
        $unions = collect($unionResponse->json())->pluck('name_en')->all();
        $this->assertContains('Kusumbi', $unions);

        // 2. Urban Areas (Municipality) for Bogura / Sherpur
        $urbanResponse = $this->getJson(route('geo.urban-areas', [
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
        ]));
        $urbanResponse->assertOk();
        $urbanNames = collect($urbanResponse->json())->pluck('name_bn')->all();
        $this->assertContains('শেরপুর পৌরসভা', $urbanNames);
    }

    public function test_post_office_and_postal_code_selection(): void
    {
        $bogura = District::where('name_en', 'Bogura')->firstOrFail();
        $sherpur = Upazila::where('name_en', 'Sherpur')->where('district_id', $bogura->id)->firstOrFail();

        $poResponse = $this->getJson(route('geo.post-offices', ['upazila_id' => $sherpur->id]));
        $poResponse->assertOk();

        $postOffices = collect($poResponse->json());
        $sherpurPo = $postOffices->firstWhere('name_en', 'Sherpur');

        $this->assertNotNull($sherpurPo);
        $this->assertSame('5840', $sherpurPo['post_code']);
        $this->assertIsString($sherpurPo['post_code']);
    }

    public function test_invalid_or_mismatched_parent_child_ids_are_rejected(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $dhakaDivision = Division::where('name_en', 'Dhaka')->firstOrFail();
        $boguraDistrict = District::where('name_en', 'Bogura')->firstOrFail(); // Belongs to Rajshahi
        $sherpurUpazila = Upazila::where('name_en', 'Sherpur')->where('district_id', $boguraDistrict->id)->firstOrFail();
        $dhakaDistrict = District::where('name_en', 'Dhaka')->firstOrFail();

        // 1. Division and District mismatch (Dhaka Division + Bogura District)
        $mismatchDistResponse = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Mismatched Product 1',
            'category_id' => $category->id,
            'price' => '35.00',
            'unit' => 'kg',
            'stock' => 50,
            'division_id' => $dhakaDivision->id,
            'district_id' => $boguraDistrict->id,
        ]);
        $mismatchDistResponse->assertSessionHasErrors('district_id');

        // 2. District and Upazila mismatch (Dhaka District + Sherpur Upazila)
        $mismatchUpazilaResponse = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Mismatched Product 2',
            'category_id' => $category->id,
            'price' => '35.00',
            'unit' => 'kg',
            'stock' => 50,
            'division_id' => $dhakaDivision->id,
            'district_id' => $dhakaDistrict->id,
            'upazila_id' => $sherpurUpazila->id,
        ]);
        $mismatchUpazilaResponse->assertSessionHasErrors('upazila_id');

        // 3. Union mismatch (Union belonging to Sherpur, but Upazila is Savar)
        $savarUpazila = Upazila::where('name_en', 'Savar')->firstOrFail();
        $kusumbiUnion = Union::where('name_en', 'Kusumbi')->firstOrFail(); // Belongs to Sherpur

        $mismatchUnionResponse = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Mismatched Product 3',
            'category_id' => $category->id,
            'price' => '35.00',
            'unit' => 'kg',
            'stock' => 50,
            'division_id' => $dhakaDivision->id,
            'district_id' => $dhakaDistrict->id,
            'upazila_id' => $savarUpazila->id,
            'union_id' => $kusumbiUnion->id,
        ]);
        $mismatchUnionResponse->assertSessionHasErrors('union_id');
    }

    public function test_farmer_can_create_product_with_valid_rural_address(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);

        $rajshahi = Division::where('name_en', 'Rajshahi')->firstOrFail();
        $bogura = District::where('name_en', 'Bogura')->firstOrFail();
        $sherpur = Upazila::where('name_en', 'Sherpur')->where('district_id', $bogura->id)->firstOrFail();
        $kusumbi = Union::where('name_en', 'Kusumbi')->where('upazila_id', $sherpur->id)->firstOrFail();
        $postOffice = PostOffice::where('upazila_id', $sherpur->id)->firstOrFail();

        $response = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Bogura Shonali Dhan',
            'category_id' => $category->id,
            'price' => '48.50',
            'unit' => 'kg',
            'stock' => 120,
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'union_id' => $kusumbi->id,
            'post_office_id' => $postOffice->id,
            'postal_code' => $postOffice->post_code,
        ]);

        $response->assertRedirect(route('farmer.dashboard'));

        $this->assertDatabaseHas('products', [
            'name' => 'Bogura Shonali Dhan',
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'union_id' => $kusumbi->id,
            'post_office_id' => $postOffice->id,
            'postal_code' => $postOffice->post_code,
            // Legacy columns synchronized automatically
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
        ]);

        $product = Product::where('name', 'Bogura Shonali Dhan')->firstOrFail();
        $this->assertStringContainsString('কুসুম্বী', $product->formatted_address);
        $this->assertStringContainsString('বগুড়া জেলা', $product->formatted_address);
    }

    public function test_farmer_can_create_product_with_valid_urban_address_without_forcing_union(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Fruits', 'slug' => 'fruits']);

        $rajshahi = Division::where('name_en', 'Rajshahi')->firstOrFail();
        $bogura = District::where('name_en', 'Bogura')->firstOrFail();
        $sherpur = Upazila::where('name_en', 'Sherpur')->where('district_id', $bogura->id)->firstOrFail();
        $municipality = UrbanArea::where('district_id', $bogura->id)->where('name_bn', 'like', '%শেরপুর%')->firstOrFail();
        $postOffice = PostOffice::where('upazila_id', $sherpur->id)->firstOrFail();

        $response = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Sherpur Urban Strawberries',
            'category_id' => $category->id,
            'price' => '250.00',
            'unit' => 'kg',
            'stock' => 20,
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'urban_area_id' => $municipality->id,
            'union_id' => null, // Explicitly no union for urban address
            'post_office_id' => $postOffice->id,
            'postal_code' => $postOffice->post_code,
        ]);

        $response->assertRedirect(route('farmer.dashboard'));

        $this->assertDatabaseHas('products', [
            'name' => 'Sherpur Urban Strawberries',
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'urban_area_id' => $municipality->id,
            'union_id' => null,
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
        ]);

        $product = Product::where('name', 'Sherpur Urban Strawberries')->firstOrFail();
        $this->assertStringContainsString('শেরপুর পৌরসভা', $product->formatted_address);
    }

    public function test_farmer_can_edit_product_address_and_update_chain(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $rajshahi = Division::where('name_en', 'Rajshahi')->firstOrFail();
        $bogura = District::where('name_en', 'Bogura')->firstOrFail();
        $sherpur = Upazila::where('name_en', 'Sherpur')->where('district_id', $bogura->id)->firstOrFail();

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Original Potato',
            'category' => 'Vegetables',
            'price' => 30.00,
            'unit' => 'kg',
            'stock' => 50,
            'division_id' => $rajshahi->id,
            'district_id' => $bogura->id,
            'upazila_id' => $sherpur->id,
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
        ]);

        // Edit to Dhaka -> Dhaka -> Savar
        $dhakaDivision = Division::where('name_en', 'Dhaka')->firstOrFail();
        $dhakaDistrict = District::where('name_en', 'Dhaka')->firstOrFail();
        $savarUpazila = Upazila::where('name_en', 'Savar')->where('district_id', $dhakaDistrict->id)->firstOrFail();
        $savarPo = PostOffice::where('upazila_id', $savarUpazila->id)->first();

        $response = $this->actingAs($farmer)->put(route('products.update', $product), [
            'name' => 'Updated Savar Potato',
            'category_id' => $category->id,
            'price' => '32.00',
            'unit' => 'kg',
            'stock' => 60,
            'division_id' => $dhakaDivision->id,
            'district_id' => $dhakaDistrict->id,
            'upazila_id' => $savarUpazila->id,
            'post_office_id' => $savarPo?->id,
            'postal_code' => $savarPo?->post_code ?? '1340',
        ]);

        $response->assertRedirect(route('farmer.dashboard'));

        $product->refresh();
        $this->assertSame($dhakaDivision->id, $product->division_id);
        $this->assertSame($dhakaDistrict->id, $product->district_id);
        $this->assertSame($savarUpazila->id, $product->upazila_id);
        $this->assertSame('Dhaka', $product->district);
        $this->assertSame('Savar', $product->upazila);
    }

    public function test_unauthorized_users_cannot_create_or_modify_products(): void
    {
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $farmer1 = User::factory()->create(['role' => 'farmer']);
        $farmer2 = User::factory()->create(['role' => 'farmer']);

        $product = Product::create([
            'farmer_id' => $farmer1->id,
            'category_id' => $category->id,
            'name' => 'Protected Product',
            'category' => 'Grains',
            'price' => 50.00,
            'unit' => 'kg',
            'stock' => 10,
        ]);

        // 1. Guest cannot create product
        $this->post(route('products.store'), [
            'name' => 'Guest Product',
            'category_id' => $category->id,
            'price' => '40.00',
            'unit' => 'kg',
            'stock' => 5,
        ])->assertRedirect(route('login'));

        // 2. Buyer cannot create product
        $this->actingAs($buyer)->post(route('products.store'), [
            'name' => 'Buyer Product',
            'category_id' => $category->id,
            'price' => '40.00',
            'unit' => 'kg',
            'stock' => 5,
        ])->assertForbidden();

        // 3. Farmer2 cannot edit Farmer1's product
        $this->actingAs($farmer2)->put(route('products.update', $product), [
            'name' => 'Hijacked Product',
            'category_id' => $category->id,
            'price' => '10.00',
            'unit' => 'kg',
            'stock' => 5,
        ])->assertForbidden();
    }

    public function test_existing_product_and_order_data_preservation_without_structured_address_ids(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $category = Category::create(['name' => 'Legacy Produce', 'slug' => 'legacy-produce']);

        // Legacy product with only free-text district/upazila and no foreign keys
        $legacyProduct = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Old Vintage Crop',
            'category' => 'Legacy Produce',
            'price' => 20.00,
            'unit' => 'kg',
            'stock' => 40,
            'district' => 'Dinajpur',
            'upazila' => 'Birganj',
            'division_id' => null,
            'district_id' => null,
            'upazila_id' => null,
            'union_id' => null,
            'post_office_id' => null,
        ]);

        // Detail page renders smoothly and displays location
        $this->get(route('products.show', $legacyProduct))
            ->assertOk()
            ->assertSee('Dinajpur')
            ->assertSee('Birganj');

        $this->assertSame('Birganj, Dinajpur', $legacyProduct->formatted_address);

        // Legacy product can be added to cart and ordered
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'farmer_id' => $farmer->id,
            'total_amount' => 40.00,
            'status' => 'pending',
            'phone' => '01711223344',
            'delivery_address' => 'House 12, Road 4, Dhanmondi, Dhaka',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $legacyProduct->id,
            'farmer_id' => $farmer->id,
            'quantity' => 2,
            'unit_price' => 20.00,
            'subtotal' => 40.00,
        ]);

        // Order history loads without issue, preserving delivery address snapshot
        $this->actingAs($buyer)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('House 12, Road 4, Dhanmondi, Dhaka');
    }

    public function test_address_importer_is_idempotent_and_does_not_create_duplicates(): void
    {
        $divisionCountBefore = Division::count();
        $districtCountBefore = District::count();
        $upazilaCountBefore = Upazila::count();

        // Run import artisan command again
        $exitCode = Artisan::call('krishihat:import-bangladesh-addresses', ['--quiet' => true]);
        $this->assertSame(0, $exitCode);

        // Counts must be identical (no duplicates)
        $this->assertSame($divisionCountBefore, Division::count());
        $this->assertSame($districtCountBefore, District::count());
        $this->assertSame($upazilaCountBefore, Upazila::count());
    }
}
