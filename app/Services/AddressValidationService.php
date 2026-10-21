<?php

namespace App\Services;

use App\Models\District;
use App\Models\Division;
use App\Models\PostOffice;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\UrbanArea;
use Illuminate\Validation\ValidationException;

class AddressValidationService
{
    /**
     * Validate an entire administrative address chain.
     *
     * @throws ValidationException
     */
    public function validateChain(array $addressData): array
    {
        $divisionId = !empty($addressData['division_id']) ? (int) $addressData['division_id'] : null;
        $districtId = !empty($addressData['district_id']) ? (int) $addressData['district_id'] : null;
        $upazilaId = !empty($addressData['upazila_id']) ? (int) $addressData['upazila_id'] : null;
        $unionId = !empty($addressData['union_id']) ? (int) $addressData['union_id'] : null;
        $urbanAreaId = !empty($addressData['urban_area_id']) ? (int) $addressData['urban_area_id'] : null;
        $postOfficeId = !empty($addressData['post_office_id']) ? (int) $addressData['post_office_id'] : null;

        $errors = [];

        // 1. Division check
        $division = null;
        if ($divisionId) {
            $division = Division::find($divisionId);
            if (!$division) {
                $errors['division_id'] = 'নির্বাচিত বিভাগটি ডাটাবেসে পাওয়া যায়নি।';
            }
        }

        // 2. District check
        $district = null;
        if ($districtId) {
            $district = District::find($districtId);
            if (!$district) {
                $errors['district_id'] = 'নির্বাচিত জেলাটি ডাটাবেসে পাওয়া যায়নি।';
            } elseif ($divisionId && $district->division_id !== $divisionId) {
                $errors['district_id'] = "নির্বাচিত জেলা '{$district->name_bn}' নির্ধারিত বিভাগের অন্তর্ভুক্ত নয়।";
            }
        }

        // 3. Upazila check
        $upazila = null;
        if ($upazilaId) {
            $upazila = Upazila::find($upazilaId);
            if (!$upazila) {
                $errors['upazila_id'] = 'নির্বাচিত উপজেলা বা থানাটি ডাটাবেসে পাওয়া যায়নি।';
            } elseif (!$districtId) {
                $errors['upazila_id'] = 'উপজেলা নির্বাচনের পূর্বে জেলা নির্বাচন করা আবশ্যক।';
            } elseif ($district && $upazila->district_id !== $district->id) {
                $errors['upazila_id'] = "নির্বাচিত উপজেলা '{$upazila->name_bn}' জেলা '{$district->name_bn}'-এর অন্তর্ভুক্ত নয়।";
            }
        }

        // 4. Union check (Optional, for rural locations)
        if ($unionId) {
            $union = Union::find($unionId);
            if (!$union) {
                $errors['union_id'] = 'নির্বাচিত ইউনিয়নটি ডাটাবেসে পাওয়া যায়নি।';
            } elseif (!$upazilaId) {
                $errors['union_id'] = 'ইউনিয়ন নির্বাচনের পূর্বে সংশ্লিষ্ট উপজেলা নির্বাচন করা আবশ্যক।';
            } elseif ($upazila && $union->upazila_id !== $upazila->id) {
                $errors['union_id'] = "নির্বাচিত ইউনিয়ন '{$union->name_bn}' উপজেলা '{$upazila->name_bn}'-এর অন্তর্ভুক্ত নয়।";
            }
        }

        // 5. Urban area check (Optional, for municipalities/city corporations)
        if ($urbanAreaId) {
            $urbanArea = UrbanArea::find($urbanAreaId);
            if (!$urbanArea) {
                $errors['urban_area_id'] = 'নির্বাচিত পৌরসভা বা শহরাঞ্চলটি ডাটাবেসে পাওয়া যায়নি।';
            } elseif (!$districtId) {
                $errors['urban_area_id'] = 'পৌরসভা/সিটি কর্পোরেশন নির্বাচনের পূর্বে জেলা নির্বাচন আবশ্যক।';
            } elseif ($district && $urbanArea->district_id !== $district->id) {
                $errors['urban_area_id'] = "নির্বাচিত পৌরসভা/শহরাঞ্চল '{$urbanArea->name_bn}' জেলা '{$district->name_bn}'-এর অন্তর্ভুক্ত নয়।";
            } elseif ($upazilaId && $urbanArea->upazila_id && $urbanArea->upazila_id !== $upazilaId) {
                $errors['urban_area_id'] = "নির্বাচিত পৌরসভা '{$urbanArea->name_bn}' সংশ্লিষ্ট উপজেলার সাথে সংগতিপূর্ণ নয়।";
            }
        }

        // 6. Post office check
        if ($postOfficeId) {
            $postOffice = PostOffice::find($postOfficeId);
            if (!$postOffice) {
                $errors['post_office_id'] = 'নির্বাচিত ডাকঘরটি ডাটাবেসে পাওয়া যায়নি।';
            } elseif (!$districtId) {
                $errors['post_office_id'] = 'ডাকঘর নির্বাচনের পূর্বে জেলা নির্বাচন আবশ্যক।';
            } elseif ($district && $postOffice->district_id !== $district->id) {
                $errors['post_office_id'] = "নির্বাচিত ডাকঘর '{$postOffice->name_bn}' জেলা '{$district->name_bn}'-এর অন্তর্ভুক্ত নয়।";
            } elseif ($upazilaId && $postOffice->upazila_id && $postOffice->upazila_id !== $upazilaId) {
                $errors['post_office_id'] = "নির্বাচিত ডাকঘর '{$postOffice->name_bn}' সংশ্লিষ্ট উপজেলার সাথে সংগতিপূর্ণ নয়।";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return [
            'division' => $division,
            'district' => $district,
            'upazila' => $upazila,
            'union' => $unionId ? Union::find($unionId) : null,
            'urban_area' => $urbanAreaId ? UrbanArea::find($urbanAreaId) : null,
            'post_office' => $postOfficeId ? PostOffice::find($postOfficeId) : null,
        ];
    }
}
