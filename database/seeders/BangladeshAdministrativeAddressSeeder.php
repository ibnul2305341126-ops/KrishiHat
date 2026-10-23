<?php

namespace Database\Seeders;

use App\Services\BangladeshAddressImporterService;
use Illuminate\Database\Seeder;

class BangladeshAdministrativeAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = database_path('data/bangladesh_administrative_addresses.json');
        if (file_exists($file)) {
            $importer = new BangladeshAddressImporterService();
            $importer->importFromFile($file);
        }
    }
}
