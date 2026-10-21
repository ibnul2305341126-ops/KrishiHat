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
            $table->string('payment_method')->default('cod')->after('phone');
            $table->string('payment_status')->default('unpaid')->after('payment_method'); // unpaid, pending, paid, failed, refunded
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable'); // payable_type, payable_id (Order or UserSubscription)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('payment_type')->default('order'); // order, subscription
            $table->string('payment_method')->default('cod'); // cod, sslcommerz, bkash, nagad, card, bank_transfer, free, manual_admin
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('transaction_id')->unique();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_session_key')->nullable();
            $table->string('status')->default('pending'); // pending, processing, successful, failed, cancelled, refunded
            $table->json('payment_details')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['payment_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status']);
        });
    }
};
