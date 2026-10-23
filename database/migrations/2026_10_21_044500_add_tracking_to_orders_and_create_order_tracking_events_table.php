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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_code', 50)->nullable()->unique()->after('status');
            $table->string('driver_name', 100)->nullable()->after('phone');
            $table->string('driver_phone', 30)->nullable()->after('driver_name');
            $table->string('vehicle_number', 50)->nullable()->after('driver_phone');
            $table->string('current_location_text', 255)->nullable()->after('vehicle_number');
            $table->decimal('current_latitude', 10, 8)->nullable()->after('current_location_text');
            $table->decimal('current_longitude', 11, 8)->nullable()->after('current_latitude');
            $table->timestamp('estimated_delivery_at')->nullable()->after('current_longitude');
        });

        Schema::create('order_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('status', 50);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('location', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tracking_events');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_code',
                'driver_name',
                'driver_phone',
                'vehicle_number',
                'current_location_text',
                'current_latitude',
                'current_longitude',
                'estimated_delivery_at',
            ]);
        });
    }
};
