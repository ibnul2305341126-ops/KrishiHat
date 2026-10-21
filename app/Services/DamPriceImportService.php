<?php

namespace App\Services;

use App\Models\Category;
use App\Models\MarketBenchmarkPrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DamPriceImportService
{
    /**
     * Import records using normalized keys and return standard reporting counts.
     */
    public function importRecords(array $records, string $sourceUrl = 'https://market.dam.gov.bd/'): array
    {
        $normalized = [];
        foreach ($records as $row) {
            $normalized[] = [
                'crop_name_bn' => $row['crop_name_bn'] ?? $row['crop_name'] ?? $row['commodity_bn'] ?? null,
                'crop_name_en' => $row['crop_name_en'] ?? $row['commodity_en'] ?? ($row['crop_name'] ?? null),
                'crop_variety' => $row['crop_variety'] ?? $row['variety'] ?? null,
                'category' => $row['category'] ?? null,
                'market_name' => $row['market_name'] ?? $row['market'] ?? null,
                'district' => $row['district'] ?? null,
                'upazila' => $row['upazila'] ?? null,
                'price_type' => $row['price_type'] ?? 'wholesale',
                'price' => $row['price'] ?? $row['price_modal'] ?? $row['price_wholesale'] ?? null,
                'price_min' => $row['price_min'] ?? null,
                'price_max' => $row['price_max'] ?? null,
                'unit' => $row['unit'] ?? 'কেজি (kg)',
                'observation_date' => $row['observation_date'] ?? null,
                'source_url' => $row['source_url'] ?? $sourceUrl,
                'is_verified' => isset($row['is_verified']) ? (bool) $row['is_verified'] : null,
            ];
        }

        $res = $this->import($normalized, 'admin_upload', false);

        return [
            'imported' => $res['imported'],
            'rejected' => $res['rejected'],
            'duplicated' => $res['duplicated'],
            'review_needed' => $res['marked_for_review'],
            'marked_for_review' => $res['marked_for_review'],
            'errors' => $res['errors'],
        ];
    }

    /**
     * Import market benchmark prices from an array of raw records.
     *
     * @param array $records
     * @param string $sourceType 'file_csv', 'file_json', 'official_api_sync', or 'seeder'
     * @param bool $isVerified Whether this source has been officially verified
     * @return array Summary of import operations
     */
    public function import(array $records, string $sourceType = 'file_csv', bool $isVerified = false): array
    {
        $imported = 0;
        $rejected = 0;
        $duplicated = 0;
        $markedForReview = 0;
        $errors = [];

        foreach ($records as $index => $row) {
            $validation = $this->validateRow($row, $index);
            if (!$validation['valid']) {
                $rejected++;
                $errors[] = "Row #{$index}: " . implode(', ', $validation['errors']);
                continue;
            }

            $clean = $validation['data'];

            // Duplicate detection: same crop (Bangla), same market, same price type, same observation date
            $existing = MarketBenchmarkPrice::where('crop_name_bn', $clean['crop_name_bn'])
                ->where('market_name', $clean['market_name'])
                ->where('price_type', $clean['price_type'])
                ->whereDate('observation_date', $clean['observation_date'])
                ->first();

            if ($existing) {
                $duplicated++;
                continue;
            }

            // Flag for review if price has anomalous deviation or unusual unit
            $needsReview = false;
            if ($clean['price'] <= 0 || $clean['price'] > 50000) {
                $needsReview = true;
                $markedForReview++;
            }

            // Map category_id if available
            $categoryId = null;
            if (!empty($clean['category'])) {
                $cat = Category::where('name', $clean['category'])->first();
                if ($cat) {
                    $categoryId = $cat->id;
                }
            }

            $rowIsVerified = isset($clean['is_verified']) && $clean['is_verified'] !== null
                ? (bool) $clean['is_verified']
                : $isVerified;

            MarketBenchmarkPrice::create([
                'crop_name_en' => $clean['crop_name_en'],
                'crop_name_bn' => $clean['crop_name_bn'],
                'crop_variety' => $clean['crop_variety'] ?? null,
                'category' => $clean['category'] ?? 'কৃষিপণ্য',
                'category_id' => $categoryId,
                'market_name' => $clean['market_name'],
                'district' => $clean['district'],
                'upazila' => $clean['upazila'] ?? null,
                'price_type' => $clean['price_type'],
                'price' => $clean['price'],
                'price_min' => $clean['price_min'] ?? null,
                'price_max' => $clean['price_max'] ?? null,
                'unit' => $clean['unit'] ?? 'কেজি (kg)',
                'observation_date' => $clean['observation_date'],
                'retrieval_date' => now()->toDateString(),
                'source_name' => $clean['source_name'] ?? 'Department of Agricultural Marketing (কৃষি বিপণন অধিদপ্তর)',
                'source_url' => $clean['source_url'] ?? 'https://market.dam.gov.bd/',
                'is_verified' => $rowIsVerified,
                'verification_status' => $rowIsVerified ? 'Verified Official Report' : 'Unverified Demo Data',
                'notes' => $needsReview ? 'অস্বাভাবিক মূল্যের কারণে পর্যালোচনার জন্য চিহ্নিত।' : ($clean['notes'] ?? null),
                'raw_data' => $row,
            ]);

            $imported++;
        }

        return [
            'imported' => $imported,
            'rejected' => $rejected,
            'duplicated' => $duplicated,
            'marked_for_review' => $markedForReview,
            'errors' => $errors,
        ];
    }

    /**
     * Validate an individual row of market data.
     */
    protected function validateRow(array $row, int $index): array
    {
        $errors = [];

        $cropBn = trim((string) ($row['crop_name_bn'] ?? $row['commodity_bn'] ?? ''));
        $cropEn = trim((string) ($row['crop_name_en'] ?? $row['commodity_en'] ?? ''));

        if (empty($cropBn) && empty($cropEn)) {
            $errors[] = 'Crop name (Bangla or English) is required';
        }

        if (empty($cropEn)) {
            $cropEn = $cropBn;
        }
        if (empty($cropBn)) {
            $cropBn = $cropEn;
        }

        $price = filter_var($row['price'] ?? $row['price_wholesale'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($price === false || $price <= 0) {
            $errors[] = 'A valid positive price is required';
        }

        $priceType = strtolower(trim((string) ($row['price_type'] ?? 'wholesale')));
        if (!in_array($priceType, ['wholesale', 'retail', 'grower'], true)) {
            $priceType = 'wholesale';
        }

        $obsDate = null;
        if (!empty($row['observation_date'])) {
            try {
                $obsDate = Carbon::parse($row['observation_date'])->toDateString();
            } catch (\Throwable $e) {
                $errors[] = 'Invalid observation date format';
            }
        } else {
            $obsDate = now()->toDateString();
        }

        $market = trim((string) ($row['market_name'] ?? 'সাধারণ পাইকারি মোকাম'));
        $district = trim((string) ($row['district'] ?? 'জাতীয় গড়'));

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => [
                'crop_name_en' => $cropEn,
                'crop_name_bn' => $cropBn,
                'crop_variety' => $row['crop_variety'] ?? null,
                'category' => $row['category'] ?? null,
                'market_name' => $market,
                'district' => $district,
                'upazila' => $row['upazila'] ?? null,
                'price_type' => $priceType,
                'price' => (float) $price,
                'price_min' => isset($row['price_min']) ? (float) $row['price_min'] : null,
                'price_max' => isset($row['price_max']) ? (float) $row['price_max'] : null,
                'unit' => $row['unit'] ?? 'কেজি (kg)',
                'observation_date' => $obsDate,
                'source_name' => $row['source_name'] ?? null,
                'source_url' => $row['source_url'] ?? null,
                'is_verified' => $row['is_verified'] ?? null,
                'notes' => $row['notes'] ?? null,
            ],
        ];
    }

    /**
     * Parse uploaded CSV file into rows.
     */
    public function parseCsvFile(string $filePath): array
    {
        $rows = [];
        if (!file_exists($filePath)) {
            return $rows;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return $rows;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return $rows;
        }

        // Clean headers
        $cleanHeader = array_map(fn($h) => strtolower(trim((string) $h)), $header);

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) === count($cleanHeader)) {
                $rows[] = array_combine($cleanHeader, $data);
            }
        }

        fclose($handle);
        return $rows;
    }
}
