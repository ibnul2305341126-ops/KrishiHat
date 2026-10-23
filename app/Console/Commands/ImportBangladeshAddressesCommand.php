<?php

namespace App\Console\Commands;

use App\Services\BangladeshAddressImporterService;
use Illuminate\Console\Command;

class ImportBangladeshAddressesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'krishihat:import-bangladesh-addresses {--file= : Path to the verified address JSON dataset}';

    /**
     * The console command description.
     */
    protected $description = 'Import official Bangladesh administrative divisions, districts, upazilas, unions, urban areas, and postal codes';

    /**
     * Execute the console command.
     */
    public function handle(BangladeshAddressImporterService $importer): int
    {
        $file = $this->option('file') ?: database_path('data/bangladesh_administrative_addresses.json');

        $this->info("Starting official Bangladesh administrative address import...");
        $this->line("Source file: {$file}");

        if (!file_exists($file)) {
            $this->error("Dataset file not found at: {$file}");
            return Command::FAILURE;
        }

        try {
            $stats = $importer->importFromFile($file);

            $this->newLine();
            $this->info("Import completed successfully!");
            $this->newLine();

            $rows = [
                ['Divisions (বিভাগ)', $stats['divisions']['inserted'], $stats['divisions']['updated'], $stats['divisions']['skipped']],
                ['Districts (জেলা)', $stats['districts']['inserted'], $stats['districts']['updated'], $stats['districts']['skipped']],
                ['Upazilas / Thanas (উপজেলা/থানা)', $stats['upazilas']['inserted'], $stats['upazilas']['updated'], $stats['upazilas']['skipped']],
                ['Unions (ইউনিয়ন পরিষদ)', $stats['unions']['inserted'], $stats['unions']['updated'], $stats['unions']['skipped']],
                ['Urban Areas (পৌরসভা/সিটি কর্পোরেশন)', $stats['urban_areas']['inserted'], $stats['urban_areas']['updated'], $stats['urban_areas']['skipped']],
                ['Post Offices (ডাকঘর ও কোড)', $stats['post_offices']['inserted'], $stats['post_offices']['updated'], $stats['post_offices']['skipped']],
            ];

            $this->table(['Administrative Level', 'Inserted', 'Updated', 'Skipped'], $rows);

            if (!empty($stats['conflicts'])) {
                $this->warn("Conflicting records requiring review:");
                foreach ($stats['conflicts'] as $conflict) {
                    $this->line(" - " . json_encode($conflict, JSON_UNESCAPED_UNICODE));
                }
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Import failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
