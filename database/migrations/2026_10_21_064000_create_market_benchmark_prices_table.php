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
        Schema::create('market_benchmark_prices', function (Blueprint $table) {
            $table->id();
            $table->string('crop_name_en');
            $table->string('crop_name_bn');
            $table->string('crop_variety')->nullable();
            $table->string('category')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('market_name')->nullable();
            $table->string('district')->nullable();
            $table->string('upazila')->nullable();
            $table->string('price_type')->default('wholesale'); // grower, wholesale, retail
            $table->decimal('price', 10, 2);
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->string('unit')->default('কেজি (kg)');
            $table->date('observation_date');
            $table->date('retrieval_date');
            $table->string('source_name')->default('Department of Agricultural Marketing (কৃষি বিপণন অধিদপ্তর)');
            $table->string('source_url')->default('https://market.dam.gov.bd/');
            $table->boolean('is_verified')->default(false);
            $table->string('verification_status')->default('Unverified Demo Data');
            $table->text('notes')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index(['crop_name_bn', 'price_type', 'observation_date'], 'mbp_crop_type_date_idx');
            $table->index(['district', 'observation_date'], 'mbp_district_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_benchmark_prices');
    }
};
