<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_can_create_product_with_optional_location(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);

        $response = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'BRRI Dhan 28',
            'category_id' => $category->id,
            'price' => '45.00',
            'unit' => 'kg',
            'stock' => 100,
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
            'latitude' => '24.6738',
            'longitude' => '89.4184',
        ]);

        $response->assertRedirect(route('farmer.dashboard'));

        $this->assertDatabaseHas('products', [
            'name' => 'BRRI Dhan 28',
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
            'latitude' => 24.6738,
            'longitude' => 89.4184,
        ]);
    }

    public function test_farmer_can_create_product_without_location(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $response = $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Fresh Tomatoes',
            'category_id' => $category->id,
            'price' => '30.00',
            'unit' => 'kg',
            'stock' => 50,
        ]);

        $response->assertRedirect(route('farmer.dashboard'));

        $this->assertDatabaseHas('products', [
            'name' => 'Fresh Tomatoes',
            'district' => null,
            'upazila' => null,
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_farmer_can_update_product_location(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Tubers', 'slug' => 'tubers']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Diamant Potato',
            'category' => 'Tubers',
            'price' => 25.00,
            'unit' => 'kg',
            'stock' => 200,
            'district' => 'Dinajpur',
            'upazila' => 'Birganj',
        ]);

        $response = $this->actingAs($farmer)->put(route('products.update', $product), [
            'name' => 'Diamant Potato Premium',
            'category_id' => $category->id,
            'price' => '28.00',
            'unit' => 'kg',
            'stock' => 180,
            'district' => 'Dinajpur',
            'upazila' => 'Sadar',
            'latitude' => '25.6279',
            'longitude' => '88.6332',
        ]);

        $response->assertRedirect(route('farmer.dashboard'));

        $product->refresh();
        $this->assertSame('Diamant Potato Premium', $product->name);
        $this->assertSame('Dinajpur', $product->district);
        $this->assertSame('Sadar', $product->upazila);
        $this->assertEquals(25.6279, $product->latitude);
        $this->assertEquals(88.6332, $product->longitude);
    }

    public function test_invalid_coordinates_are_rejected(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Spices', 'slug' => 'spices']);

        // Latitude > 90
        $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Spicy Chili',
            'category_id' => $category->id,
            'price' => '60.00',
            'unit' => 'kg',
            'stock' => 20,
            'latitude' => '95.1234',
            'longitude' => '89.0000',
        ])->assertSessionHasErrors('latitude');

        // Longitude > 180
        $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Spicy Chili',
            'category_id' => $category->id,
            'price' => '60.00',
            'unit' => 'kg',
            'stock' => 20,
            'latitude' => '24.0000',
            'longitude' => '195.0000',
        ])->assertSessionHasErrors('longitude');

        // Non-numeric latitude
        $this->actingAs($farmer)->post(route('products.store'), [
            'name' => 'Spicy Chili',
            'category_id' => $category->id,
            'price' => '60.00',
            'unit' => 'kg',
            'stock' => 20,
            'latitude' => 'not-a-number',
            'longitude' => '89.0000',
        ])->assertSessionHasErrors('latitude');
    }

    public function test_marketplace_filters_listings_by_district(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Crops', 'slug' => 'crops']);

        $boguraProduct = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Bogura Paddy Special',
            'category' => 'Crops',
            'price' => 38.00,
            'unit' => 'kg',
            'stock' => 50,
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
        ]);

        $dinajpurProduct = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Dinajpur Litchi Harvest',
            'category' => 'Crops',
            'price' => 120.00,
            'unit' => 'kg',
            'stock' => 30,
            'district' => 'Dinajpur',
            'upazila' => 'Birganj',
        ]);

        // Filter by Bogura
        $this->get(route('home', ['district' => 'Bogura']))
            ->assertOk()
            ->assertSee('Bogura Paddy Special')
            ->assertDontSee('Dinajpur Litchi Harvest');

        // Filter by lowercase bogura
        $this->get(route('home', ['district' => 'bogura']))
            ->assertOk()
            ->assertSee('Bogura Paddy Special')
            ->assertDontSee('Dinajpur Litchi Harvest');

        // Filter by Dinajpur
        $this->get(route('home', ['district' => 'Dinajpur']))
            ->assertOk()
            ->assertSee('Dinajpur Litchi Harvest')
            ->assertDontSee('Bogura Paddy Special');
    }

    public function test_product_card_displays_actual_location_or_unspecified_notice(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Fruits', 'slug' => 'fruits']);

        $locatedProduct = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Rajshahi Fazli Mango',
            'category' => 'Fruits',
            'price' => 90.00,
            'unit' => 'kg',
            'stock' => 15,
            'district' => 'Rajshahi',
            'upazila' => 'Bagha',
        ]);

        $unlocatedProduct = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Unlocated Local Crop',
            'category' => 'Fruits',
            'price' => 40.00,
            'unit' => 'kg',
            'stock' => 20,
            'district' => null,
            'upazila' => null,
        ]);

        $response = $this->get(route('home'));
        $response->assertOk();

        // Product with location displays its actual district & upazila
        $response->assertSee('Rajshahi (Bagha)');

        // Product without location displays exactly "অবস্থান দেওয়া হয়নি"
        $response->assertSee('অবস্থান দেওয়া হয়নি');
    }

    public function test_google_maps_is_only_navigation_provider_and_openstreetmap_is_not_displayed(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Grains', 'slug' => 'grains']);

        $geoProduct = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Geo-tagged Rice',
            'category' => 'Grains',
            'price' => 50.00,
            'unit' => 'kg',
            'stock' => 10,
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
            'latitude' => 24.6738,
            'longitude' => 89.4184,
        ]);

        $nonGeoProduct = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Plain Rice Without GPS',
            'category' => 'Grains',
            'price' => 50.00,
            'unit' => 'kg',
            'stock' => 10,
            'district' => 'Bogura',
            'upazila' => 'Sherpur',
            'latitude' => null,
            'longitude' => null,
        ]);

        // Detail page of product with coordinates has Google Maps link and NO OpenStreetMap
        $this->get(route('products.show', $geoProduct))
            ->assertOk()
            ->assertSee('https://www.google.com/maps/search/?api=1&query=24.6738%2C89.4184', false)
            ->assertSee('https://www.google.com/maps/dir/?api=1&destination=24.6738%2C89.4184', false)
            ->assertSee('Google Maps-এ দেখুন')
            ->assertDontSee('openstreetmap.org')
            ->assertDontSee('OpenStreetMap');

        // Detail page of product without coordinates has approximate Google Maps and NO OpenStreetMap
        $this->get(route('products.show', $nonGeoProduct))
            ->assertOk()
            ->assertSee('https://www.google.com/maps/search/?api=1&query=Sherpur%2C%20Bogura%2C%20Bangladesh', false)
            ->assertDontSee('openstreetmap.org')
            ->assertDontSee('OpenStreetMap');

        // Home page has NO OpenStreetMap references
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('openstreetmap.org')
            ->assertDontSee('OpenStreetMap');
    }
}
