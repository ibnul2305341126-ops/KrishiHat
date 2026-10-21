<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_open_category_management_routes(): void
    {
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);
        foreach (['buyer', 'farmer'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
            $this->get(route('admin.categories.create'))->assertForbidden();
            $this->post(route('admin.categories.store'), ['name' => 'Fruit', 'slug' => 'fruit'])->assertForbidden();
            $this->get(route('admin.categories.edit', $category))->assertForbidden();
            $this->put(route('admin.categories.update', $category), ['name' => 'Fruit', 'slug' => 'fruit'])->assertForbidden();
            $this->delete(route('admin.categories.destroy', $category))->assertForbidden();
        }
    }

    public function test_admin_can_create_update_and_delete_categories_with_unique_slugs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existing = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk()->assertSee('Manage categories');
        $this->post(route('admin.categories.store'), [
            'name' => 'Fruit', 'slug' => 'fruit', 'description' => 'Seasonal fruit', 'icon' => '🍎',
        ])->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Fruit', 'slug' => 'fruit']);

        $this->from(route('admin.categories.create'))->post(route('admin.categories.store'), [
            'name' => 'Duplicate slug', 'slug' => 'vegetables',
        ])->assertSessionHasErrors('slug');

        $this->put(route('admin.categories.update', $existing), [
            'name' => 'Fresh vegetables', 'slug' => 'fresh-vegetables', 'description' => 'Updated', 'icon' => '🥬',
        ])->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $existing->id, 'name' => 'Fresh vegetables', 'slug' => 'fresh-vegetables']);

        $this->delete(route('admin.categories.destroy', $existing))->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $existing->id]);
    }

    public function test_assigned_categories_cannot_be_deleted_and_category_search_is_scoped(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $vegetables = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);
        $fruit = Category::create(['name' => 'Fruit', 'slug' => 'fruit']);
        $assigned = Product::create(['farmer_id' => $farmer->id, 'name' => 'Red tomato', 'category_id' => $vegetables->id, 'category' => $vegetables->name, 'price' => 10, 'unit' => 'kg', 'stock' => 4]);
        Product::create(['farmer_id' => $farmer->id, 'name' => 'Green pepper', 'category_id' => $vegetables->id, 'category' => $vegetables->name, 'price' => 12, 'unit' => 'kg', 'stock' => 2]);
        Product::create(['farmer_id' => $farmer->id, 'name' => 'Red apple', 'category_id' => $fruit->id, 'category' => $fruit->name, 'price' => 18, 'unit' => 'kg', 'stock' => 6]);

        $this->get(route('categories.show', ['category' => $vegetables->slug, 'search' => 'Red']))
            ->assertOk()->assertSee('Red tomato')->assertDontSee('Green pepper')->assertDontSee('Red apple');
        $this->get(route('categories.show', $vegetables->slug))
            ->assertSee('Red tomato')->assertSee('Green pepper')->assertDontSee('Red apple');
        $this->get(route('home', ['category' => $vegetables->slug, 'search' => 'Red']))
            ->assertOk()->assertSee('Red tomato')->assertDontSee('Green pepper')->assertDontSee('Red apple')
            ->assertSee(route('categories.show', $vegetables->slug));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->from(route('admin.categories.index'))->delete(route('admin.categories.destroy', $vegetables))
            ->assertRedirect(route('admin.categories.index'))->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $assigned->id, 'category_id' => $vegetables->id]);
    }
}
