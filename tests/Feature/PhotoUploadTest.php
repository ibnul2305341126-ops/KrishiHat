<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_photo_schema_and_relationship_support_position_ordering(): void
    {
        $this->assertTrue(Schema::hasTable('product_photos'));
        $this->assertTrue(Schema::hasColumns('product_photos', ['product_id', 'path', 'position', 'created_at', 'updated_at']));
        $farmer=User::factory()->create(['role'=>'farmer']);
        $category=Category::create(['name'=>'Vegetables','slug'=>'vegetables']);
        $product=Product::create(['farmer_id'=>$farmer->id,'name'=>'Crop','category_id'=>$category->id,'category'=>'Vegetables','price'=>10,'unit'=>'kg','stock'=>3]);
        $product->photos()->create(['path'=>'second.jpg','position'=>2]);
        $product->photos()->create(['path'=>'first.jpg','position'=>1]);
        $this->assertSame(['first.jpg','second.jpg'],$product->photos->pluck('path')->all());
    }

    public function test_farmer_can_upload_multiple_product_photos_and_profile_photo(): void
    {
        Storage::fake('public');
        $farmer=User::factory()->create(['role'=>'farmer']);
        $category=Category::create(['name'=>'Vegetables','slug'=>'vegetables']);
        $this->actingAs($farmer)->post(route('products.store'), [
            'name'=>'Tomatoes','category_id'=>$category->id,'price'=>'40','unit'=>'kg','stock'=>10,
            'photos'=>[UploadedFile::fake()->image('one.jpg'),UploadedFile::fake()->image('two.png')],
        ])->assertRedirect(route('farmer.dashboard'));
        $product=Product::sole();
        $this->assertDatabaseCount('product_photos',2);
        foreach($product->photos as $photo) Storage::disk('public')->assertExists($photo->path);
        $this->put(route('profile.update'), ['name'=>$farmer->name,'email'=>$farmer->email,'profile_photo'=>UploadedFile::fake()->image('avatar.png')])
            ->assertRedirect(route('profile.show'));
        Storage::disk('public')->assertExists($farmer->fresh()->profile_photo_path);
    }

    public function test_invalid_and_excessive_product_photo_uploads_are_rejected(): void
    {
        $farmer=User::factory()->create(['role'=>'farmer']);
        $category=Category::create(['name'=>'Vegetables','slug'=>'vegetables']);
        $fields=['name'=>'Tomatoes','category_id'=>$category->id,'price'=>'40','unit'=>'kg','stock'=>10];
        $this->actingAs($farmer)->post(route('products.store'), $fields+['photos'=>[UploadedFile::fake()->create('bad.txt',10,'text/plain')]])
            ->assertSessionHasErrors('photos.0');
        $this->post(route('products.store'), $fields+['photos'=>array_fill(0,6,UploadedFile::fake()->image('too-many.jpg'))])
            ->assertSessionHasErrors('photos');
        $this->assertDatabaseCount('products',0);
    }

    public function test_another_farmer_cannot_update_product_photos(): void
    {
        $owner=User::factory()->create(['role'=>'farmer']); $other=User::factory()->create(['role'=>'farmer']);
        $category=Category::create(['name'=>'Vegetables','slug'=>'vegetables']);
        $product=Product::create(['farmer_id'=>$owner->id,'name'=>'Crop','category_id'=>$category->id,'category'=>'Vegetables','price'=>10,'unit'=>'kg','stock'=>3]);
        $this->actingAs($other)->put(route('products.update',$product), [
            'name'=>$product->name,'category_id'=>$product->category_id,'price'=>10,'unit'=>'kg','stock'=>3,
            'photos'=>[UploadedFile::fake()->image('forged.png')],
        ])->assertForbidden();
        $this->assertDatabaseCount('product_photos',0);
    }
}
