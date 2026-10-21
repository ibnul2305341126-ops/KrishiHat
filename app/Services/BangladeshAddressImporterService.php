<?php

namespace App\Services;

use App\Models\District;
use App\Models\Division;
use App\Models\PostOffice;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\UrbanArea;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BangladeshAddressImporterService
{
    /**
     * Import official address hierarchy from dataset file or array.
     */
    public function importFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Dataset file not found at: {$filePath}");
        }

        $raw = file_get_contents($filePath);
        $data = json_decode($raw, true);

        if (!is_array($data) || empty($data['divisions'])) {
            throw new \InvalidArgumentException("Invalid address dataset format: missing divisions list.");
        }

        return $this->importData($data);
    }

    /**
     * Import address dataset array idempotently with full conflict tracking.
     */
    public function importData(array $data): array
    {
        $stats = [
            'divisions' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
            'districts' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
            'upazilas' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
            'unions' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
            'urban_areas' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
            'post_offices' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
            'conflicts' => [],
        ];

        $verifiedAt = Carbon::now();

        DB::transaction(function () use ($data, &$stats, $verifiedAt) {
            foreach ($data['divisions'] as $divData) {
                $divKey = ['name_en' => trim($divData['name_en'])];
                $divAttrs = [
                    'name_bn' => trim($divData['name_bn']),
                    'bbs_code' => $divData['code'] ?? null,
                    'source_url' => $divData['source_url'] ?? 'https://bangladesh.gov.bd/views/division-list',
                    'verified_at' => $verifiedAt,
                    'is_verified' => true,
                ];

                $division = Division::firstOrNew($divKey);
                if (!$division->exists) {
                    $division->fill($divAttrs)->save();
                    $stats['divisions']['inserted']++;
                } else {
                    $needsUpdate = false;
                    foreach ($divAttrs as $k => $v) {
                        if ($k !== 'verified_at' && (string) $division->{$k} !== (string) $v) {
                            $needsUpdate = true;
                            break;
                        }
                    }
                    if ($needsUpdate) {
                        $division->update($divAttrs);
                        $stats['divisions']['updated']++;
                    } else {
                        $stats['divisions']['skipped']++;
                    }
                }

                foreach ($divData['districts'] as $distData) {
                    $distKey = [
                        'division_id' => $division->id,
                        'name_en' => trim($distData['name_en']),
                    ];
                    $distAttrs = [
                        'name_bn' => trim($distData['name_bn']),
                        'bbs_code' => $distData['code'] ?? null,
                        'latitude' => $distData['latitude'] ?? null,
                        'longitude' => $distData['longitude'] ?? null,
                        'source_url' => 'https://bangladesh.gov.bd/site/view/district-list/',
                        'verified_at' => $verifiedAt,
                        'is_verified' => true,
                    ];

                    $district = District::firstOrNew($distKey);
                    if (!$district->exists) {
                        $district->fill($distAttrs)->save();
                        $stats['districts']['inserted']++;
                    } else {
                        $needsUpdate = false;
                        foreach ($distAttrs as $k => $v) {
                            if ($k !== 'verified_at' && (string) $district->{$k} !== (string) $v) {
                                $needsUpdate = true;
                                break;
                            }
                        }
                        if ($needsUpdate) {
                            $district->update($distAttrs);
                            $stats['districts']['updated']++;
                        } else {
                            $stats['districts']['skipped']++;
                        }
                    }

                    // Map of upazilas by name for post office and urban area linking
                    $upazilaMap = [];

                    // Upazilas
                    foreach ($distData['upazilas'] ?? [] as $upzData) {
                        $upzKey = [
                            'district_id' => $district->id,
                            'name_en' => trim($upzData['name_en']),
                        ];
                        $upzAttrs = [
                            'name_bn' => trim($upzData['name_bn']),
                            'bbs_code' => $upzData['code'] ?? null,
                            'is_thana' => (bool) ($upzData['is_thana'] ?? false),
                            'source_url' => 'https://bangladesh.gov.bd/views/upazila-list/',
                            'verified_at' => $verifiedAt,
                            'is_verified' => true,
                        ];

                        $upazila = Upazila::firstOrNew($upzKey);
                        if (!$upazila->exists) {
                            $upazila->fill($upzAttrs)->save();
                            $stats['upazilas']['inserted']++;
                        } else {
                            $needsUpdate = false;
                            foreach ($upzAttrs as $k => $v) {
                                if ($k !== 'verified_at' && (string) $upazila->{$k} !== (string) $v) {
                                    $needsUpdate = true;
                                    break;
                                }
                            }
                            if ($needsUpdate) {
                                $upazila->update($upzAttrs);
                                $stats['upazilas']['updated']++;
                            } else {
                                $stats['upazilas']['skipped']++;
                            }
                        }

                        $upazilaMap[strtolower(trim($upzData['name_en']))] = $upazila;
                        $upazilaMap[strtolower(trim($upzData['name_bn']))] = $upazila;

                        // Unions under upazila
                        foreach ($upzData['unions'] ?? [] as $unData) {
                            $unKey = [
                                'upazila_id' => $upazila->id,
                                'name_en' => trim($unData['name_en']),
                            ];
                            $unAttrs = [
                                'name_bn' => trim($unData['name_bn']),
                                'bbs_code' => $unData['code'] ?? null,
                                'source_url' => $unData['url'] ? "https://{$unData['url']}" : 'https://bangladesh.gov.bd/views/union-list',
                                'verified_at' => $verifiedAt,
                                'is_verified' => true,
                            ];

                            $union = Union::firstOrNew($unKey);
                            if (!$union->exists) {
                                $union->fill($unAttrs)->save();
                                $stats['unions']['inserted']++;
                            } else {
                                $needsUpdate = false;
                                foreach ($unAttrs as $k => $v) {
                                    if ($k !== 'verified_at' && (string) $union->{$k} !== (string) $v) {
                                        $needsUpdate = true;
                                        break;
                                    }
                                }
                                if ($needsUpdate) {
                                    $union->update($unAttrs);
                                    $stats['unions']['updated']++;
                                } else {
                                    $stats['unions']['skipped']++;
                                }
                            }
                        }
                    }

                    // Urban Areas (Municipalities / City Corporations)
                    foreach ($distData['urban_areas'] ?? [] as $urbData) {
                        $matchedUpazilaId = null;
                        if (!empty($urbData['upazila'])) {
                            $uKey = strtolower(trim($urbData['upazila']));
                            $matchedUpazilaId = $upazilaMap[$uKey]->id ?? null;
                        }

                        $urbKey = [
                            'district_id' => $district->id,
                            'name_en' => trim($urbData['name_en']),
                        ];
                        $urbAttrs = [
                            'upazila_id' => $matchedUpazilaId,
                            'name_bn' => trim($urbData['name_bn']),
                            'type' => $urbData['type'] ?? 'municipality',
                            'ward_number' => $urbData['ward_number'] ?? null,
                            'source_url' => 'https://bangladesh.gov.bd/',
                            'verified_at' => $verifiedAt,
                            'is_verified' => true,
                        ];

                        $urban = UrbanArea::firstOrNew($urbKey);
                        if (!$urban->exists) {
                            $urban->fill($urbAttrs)->save();
                            $stats['urban_areas']['inserted']++;
                        } else {
                            $needsUpdate = false;
                            foreach ($urbAttrs as $k => $v) {
                                if ($k !== 'verified_at' && (string) $urban->{$k} !== (string) $v) {
                                    $needsUpdate = true;
                                    break;
                                }
                            }
                            if ($needsUpdate) {
                                $urban->update($urbAttrs);
                                $stats['urban_areas']['updated']++;
                            } else {
                                $stats['urban_areas']['skipped']++;
                            }
                        }
                    }

                    // Post Offices & Postal Codes
                    foreach ($distData['post_offices'] ?? [] as $poData) {
                        $matchedUpazilaId = null;
                        if (!empty($poData['upazila'])) {
                            $uKey = strtolower(trim($poData['upazila']));
                            $matchedUpazilaId = $upazilaMap[$uKey]->id ?? null;
                        }

                        // Leading zero preservation with string casting
                        $postCode = str_pad(trim((string) $poData['post_code']), 4, '0', STR_PAD_LEFT);

                        $poKey = [
                            'district_id' => $district->id,
                            'name_en' => trim($poData['name_en']),
                            'post_code' => $postCode,
                        ];
                        $poAttrs = [
                            'upazila_id' => $matchedUpazilaId,
                            'name_bn' => trim($poData['name_bn']),
                            'source_url' => 'https://bdpost.gov.bd/',
                            'verified_at' => $verifiedAt,
                            'is_verified' => true,
                        ];

                        $postOffice = PostOffice::firstOrNew($poKey);
                        if (!$postOffice->exists) {
                            $postOffice->fill($poAttrs)->save();
                            $stats['post_offices']['inserted']++;
                        } else {
                            $needsUpdate = false;
                            foreach ($poAttrs as $k => $v) {
                                if ($k !== 'verified_at' && (string) $postOffice->{$k} !== (string) $v) {
                                    $needsUpdate = true;
                                    break;
                                }
                            }
                            if ($needsUpdate) {
                                $postOffice->update($poAttrs);
                                $stats['post_offices']['updated']++;
                            } else {
                                $stats['post_offices']['skipped']++;
                            }
                        }
                    }
                }
            }
        });

        return $stats;
    }
}
