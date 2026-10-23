<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) { $table->string('profile_photo_path')->nullable(); });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('accepted_offer_id')->nullable()->unique()->constrained('offers')->restrictOnDelete();
        });
        Schema::create('product_photos', function (Blueprint $table) {
            $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path'); $table->unsignedSmallInteger('position')->default(0); $table->timestamps();
            $table->index(['product_id', 'position']);
        });
        Schema::create('conversations', function (Blueprint $table) {
            $table->id(); $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('context_key')->unique(); $table->timestamps();
            $table->index(['buyer_id', 'updated_at']); $table->index(['farmer_id', 'updated_at']);
        });
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id(); $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $table->text('body')->nullable(); $table->string('attachment_path')->nullable(); $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->string('type'); $table->morphs('notifiable');
            $table->text('data'); $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications'); Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations'); Schema::dropIfExists('product_photos');
        Schema::table('orders', function (Blueprint $table) { $table->dropConstrainedForeignId('accepted_offer_id'); });
        Schema::table('users', function (Blueprint $table) { $table->dropColumn('profile_photo_path'); });
    }
};
