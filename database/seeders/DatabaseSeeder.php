<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database idempotently.
     */
    public function run(): void
    {
        $this->call([
            MembershipPlansSeeder::class,
            MarketBenchmarkPricesSeeder::class,
            BangladeshAdministrativeAddressSeeder::class,
            BangladeshDemoDataSeeder::class,
        ]);
    }
}
