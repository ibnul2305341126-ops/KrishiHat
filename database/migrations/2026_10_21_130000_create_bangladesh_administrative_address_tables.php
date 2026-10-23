<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Divisions (৮টি প্রশাসনিক বিভাগ)
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 100)->index();
            $table->string('name_bn', 100)->index();
            $table->string('bbs_code', 20)->nullable()->unique();
            $table->string('source_url', 500)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();
        });

        // 2. Districts (৬৪টি জেলা)
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->string('name_en', 100)->index();
            $table->string('name_bn', 100)->index();
            $table->string('bbs_code', 20)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->index(['division_id', 'name_en']);
        });

        // 3. Upazilas / Thanas (উপজেলা বা প্রযোজ্য থানা)
        Schema::create('upazilas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('name_en', 100)->index();
            $table->string('name_bn', 100)->index();
            $table->string('bbs_code', 20)->nullable()->index();
            $table->boolean('is_thana')->default(false);
            $table->string('source_url', 500)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->index(['district_id', 'name_en']);
        });

        // 4. Unions (ইউনিয়ন পরিষদসমূহ)
        Schema::create('unions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upazila_id')->constrained('upazilas')->cascadeOnDelete();
            $table->string('name_en', 150)->index();
            $table->string('name_bn', 150)->index();
            $table->string('bbs_code', 20)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->index(['upazila_id', 'name_en']);
        });

        // 5. Urban Areas (পৌরসভা / সিটি কর্পোরেশন / শহরাঞ্চল)
        Schema::create('urban_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas')->nullOnDelete();
            $table->string('name_en', 150)->index();
            $table->string('name_bn', 150)->index();
            $table->string('type', 50)->default('municipality'); // municipality, paurashava, city_corporation, thana_ward
            $table->string('ward_number', 50)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->index(['district_id', 'type']);
        });

        // 6. Post Offices (ডাকঘর ও পোস্ট কোড)
        Schema::create('post_offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas')->nullOnDelete();
            $table->string('name_en', 150)->index();
            $table->string('name_bn', 150)->index();
            $table->string('post_code', 20)->index(); // string to preserve leading zeros
            $table->string('source_url', 500)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->index(['district_id', 'upazila_id', 'post_code']);
        });

        // 7. Add structured address foreign keys to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('category_id')->constrained('divisions')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('division_id')->constrained('districts')->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->after('district_id')->constrained('upazilas')->nullOnDelete();
            $table->foreignId('union_id')->nullable()->after('upazila_id')->constrained('unions')->nullOnDelete();
            $table->foreignId('urban_area_id')->nullable()->after('union_id')->constrained('urban_areas')->nullOnDelete();
            $table->foreignId('post_office_id')->nullable()->after('urban_area_id')->constrained('post_offices')->nullOnDelete();
            $table->string('postal_code', 20)->nullable()->after('post_office_id');

            $table->index(['division_id', 'district_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['upazila_id']);
            $table->dropForeign(['union_id']);
            $table->dropForeign(['urban_area_id']);
            $table->dropForeign(['post_office_id']);
            $table->dropColumn([
                'division_id',
                'district_id',
                'upazila_id',
                'union_id',
                'urban_area_id',
                'post_office_id',
                'postal_code',
            ]);
        });

        Schema::dropIfExists('post_offices');
        Schema::dropIfExists('urban_areas');
        Schema::dropIfExists('unions');
        Schema::dropIfExists('upazilas');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('divisions');
    }
};
