
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'farmer_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('farmer_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'farmer_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['farmer_id']);
                $table->dropColumn('farmer_id');
            });
        }
    }
};
