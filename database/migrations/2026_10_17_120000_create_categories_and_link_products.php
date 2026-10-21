<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 64)->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('category')
                ->constrained('categories')->restrictOnDelete();
        });

        $categoryIds = [];
        $legacyCategories = DB::table('products')->select('category')->distinct()->pluck('category');

        foreach ($legacyCategories as $legacyName) {
            $name = trim((string) $legacyName);

            if ($name === '') {
                continue;
            }

            if (! array_key_exists($name, $categoryIds)) {
                $categoryIds[$name] = $this->createCategory($name);
            }

            DB::table('products')->where('category', $legacyName)->update([
                'category_id' => $categoryIds[$name],
            ]);
        }

        // Null and blank legacy categories remain unassigned in category_id.
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('categories');
    }

    private function createCategory(string $name): int
    {
        $baseSlug = Str::limit(Str::slug($name), 120, '');
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'category-'.substr(sha1($name), 0, 10);
        $slug = $baseSlug;
        $suffix = 2;

        while (DB::table('categories')->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return (int) DB::table('categories')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
