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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('role_requirement')->default('all'); // all, farmer, buyer
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('billing_cycle_days')->default(30);
            $table->integer('active_listing_limit')->default(5);
            $table->integer('featured_listings_allowed')->default(0);
            $table->boolean('can_view_analytics')->default(false);
            $table->boolean('can_bulk_purchase')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->text('eligibility_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('membership_plans')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, pending, expired, cancelled, payment_failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->foreignId('granted_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_grant_reason')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('membership_plans');
    }
};
