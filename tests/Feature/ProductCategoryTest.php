<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_product_forms_use_database_categories_and_validate_the_selected_category(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Cereals & Grains', 'slug' => 'cereals-grains', 'icon' => '🌾']);

        $this->actingAs($farmer)->get(route('products.create'))
            ->assertOk()
            ->assertSee('name="category_id"', false)
            ->assertSee('Cereals &amp; Grains', false);

        $this->post(route('products.store'), [
            'name' => 'Published rice', 'category_id' => $category->id, 'price' => 50, 'unit' => 'kg', 'stock' => 12,
        ])->assertRedirect(route('farmer.dashboard'));

        $this->assertDatabaseHas('products', [
            'farmer_id' => $farmer->id, 'name' => 'Published rice', 'category_id' => $category->id, 'category' => 'Cereals & Grains',
        ]);

        $this->from(route('products.create'))->post(route('products.store'), [
            'name' => 'Invalid crop', 'category_id' => 99999, 'price' => 10, 'unit' => 'kg', 'stock' => 1,
        ])->assertSessionHasErrors('category_id');

        $buyer = User::factory()->create(['role' => 'buyer']);
        $this->actingAs($buyer)->get(route('products.create'))->assertForbidden();
    }

    public function test_edit_selects_current_category_and_updates_the_normalized_relation(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $category = Category::create(['name' => 'Legacy category', 'slug' => 'legacy-category']);
        $product = Product::create([
            'farmer_id' => $farmer->id, 'name' => 'Crop', 'category_id' => $category->id,
            'category' => $category->name, 'price' => 10, 'unit' => 'kg', 'stock' => 2,
        ]);

        $this->actingAs($farmer)->get(route('products.edit', $product))
            ->assertOk()->assertSee('value="'.$category->id.'" selected', false);

        $replacement = Category::create(['name' => 'New category', 'slug' => 'new-category']);
        $this->put(route('products.update', $product), [
            'name' => $product->name, 'category_id' => $replacement->id, 'price' => 10, 'unit' => 'kg', 'stock' => 2,
        ])->assertRedirect(route('farmer.dashboard'));

        $this->assertSame($replacement->id, $product->fresh()->category_id);
        $this->assertSame($replacement->name, $product->fresh()->category);
    }
}
