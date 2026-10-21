<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 20);
            $table->string('action', 30);
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['offer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_events');
    }
};
