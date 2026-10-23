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
        Schema::table('products', function (Blueprint $table) {
            $table->string('verification_status')->default('verified')->after('image');
            $table->string('reference_image')->nullable()->after('verification_status');
            $table->string('reference_image_source')->nullable()->after('reference_image');
            $table->string('reference_image_license')->nullable()->after('reference_image_source');
            $table->text('suggested_correction')->nullable()->after('reference_image_license');
            $table->timestamp('last_reviewed_at')->nullable()->after('suggested_correction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'reference_image',
                'reference_image_source',
                'reference_image_license',
                'suggested_correction',
                'last_reviewed_at',
            ]);
        });
    }
};
