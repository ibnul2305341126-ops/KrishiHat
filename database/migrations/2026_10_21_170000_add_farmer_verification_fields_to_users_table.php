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
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_status', 30)->default('unverified')->after('role'); // unverified, pending, verified, rejected
            $table->string('nid_number', 50)->nullable()->after('verification_status');
            $table->string('nid_front_path', 255)->nullable()->after('nid_number');
            $table->string('nid_back_path', 255)->nullable()->after('nid_front_path');
            $table->string('trade_license_number', 100)->nullable()->after('nid_back_path');
            $table->string('trade_license_path', 255)->nullable()->after('trade_license_number');
            $table->string('farm_name', 255)->nullable()->after('trade_license_path');
            $table->timestamp('verification_submitted_at')->nullable()->after('farm_name');
            $table->timestamp('farmer_verified_at')->nullable()->after('verification_submitted_at');
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('users')->nullOnDelete()->after('farmer_verified_at');
            $table->text('verification_rejection_reason')->nullable()->after('verified_by_admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by_admin_id']);
            $table->dropColumn([
                'verification_status',
                'nid_number',
                'nid_front_path',
                'nid_back_path',
                'trade_license_number',
                'trade_license_path',
                'farm_name',
                'verification_submitted_at',
                'farmer_verified_at',
                'verified_by_admin_id',
                'verification_rejection_reason',
            ]);
        });
    }
};
