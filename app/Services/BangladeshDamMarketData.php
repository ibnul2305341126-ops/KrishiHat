<?php

namespace App\Services;

class BangladeshDamMarketData
{
    /**
     * Agricultural market reference records based on the Department
     * of Agricultural Marketing (DAM) reporting schema.
     *
     * Academic / Audit Notice:
     * These records represent illustrative demonstration reference data.
     * Because live API integration with dam.gov.bd / market.dam.gov.bd is not active,
     * these records are explicitly marked as UNVERIFIED DEMO DATA.
     */
    public static function getVerifiedReferenceRecords(): array
    {
        return [
            [
                'crop_name_en' => 'BRRI Dhan 28 Paddy',
                'crop_name_bn' => 'ব্রি ধান ২৮ (আমন/বোরো)',
                'market_name' => 'বগুড়া পাইকারি মোকাম (Bogura Wholesale)',
                'district' => 'বগুড়া (Bogura)',
                'price_wholesale' => 35.50,
                'price_retail' => 38.00,
                'unit' => 'কেজি (kg)',
                'observation_date' => '2026-10-18',
                'retrieval_date' => '2026-10-21',
                'source_name' => 'Department of Agricultural Marketing (কৃষি বিপণন অধিদপ্তর)',
                'source_url' => 'https://market.dam.gov.bd/',
                'is_verified' => false,
                'verification_status' => 'Unverified Demo Data (নমুনা ডেমো ডাটা)',
                'notes' => 'ডেমো নমুনা বাজার তথ্য। লাইভ সরকারি এপিআই সংযোগ ছাড়া এটিকে প্রত্যয়িত অফিশিয়াল বেঞ্চমার্ক দাবি করা হয় না।'
            ],
            [
                'crop_name_en' => 'Diamant Potato',
                'crop_name_bn' => 'ডায়মন্ড গোল আলু',
                'market_name' => 'দিনাজপুর বাহাদুর বাজার (Dinajpur)',
                'district' => 'দিনাজপুর (Dinajpur)',
                'price_wholesale' => 28.00,
                'price_retail' => 32.00,
                'unit' => 'কেজি (kg)',
                'observation_date' => '2026-10-18',
                'retrieval_date' => '2026-10-21',
                'source_name' => 'Department of Agricultural Marketing (কৃষি বিপণন অধিদপ্তর)',
                'source_url' => 'https://market.dam.gov.bd/',
                'is_verified' => false,
                'verification_status' => 'Unverified Demo Data (নমুনা ডেমো ডাটা)',
                'notes' => 'ডেমো নমুনা বাজার তথ্য। হিমাগার গেট ও পাইকারি আড়ত কাঠামোভিত্তিক।'
            ],
            [
                'crop_name_en' => 'Taherpuri Local Onion',
                'crop_name_bn' => 'তাহেরপুরী দেশি পেঁয়াজ',
                'market_name' => 'রাজশাহী বানেশ্বর হাট (Rajshahi Baneswar)',
                'district' => 'রাজশাহী (Rajshahi)',
                'price_wholesale' => 67.00,
                'price_retail' => 74.00,
                'unit' => 'কেজি (kg)',
                'observation_date' => '2026-10-18',
                'retrieval_date' => '2026-10-21',
                'source_name' => 'Department of Agricultural Marketing (কৃষি বিপণন অধিদপ্তর)',
                'source_url' => 'https://market.dam.gov.bd/',
                'is_verified' => false,
                'verification_status' => 'Unverified Demo Data (নমুনা ডেমো ডাটা)',
                'notes' => 'ডেমো নমুনা বাজার তথ্য। উত্তরবঙ্গের হাট কাঠামোর উপর ভিত্তি করে প্রস্তুত।'
            ],
            [
                'crop_name_en' => 'Fazli Mango',
                'crop_name_bn' => 'রাজশাহীর ফজলি আম',
                'market_name' => 'কানসাট আম বাজার (Kansat, Chapainawabganj)',
                'district' => 'চাঁপাইনবাবগঞ্জ / রাজশাহী',
                'price_wholesale' => 95.00,
                'price_retail' => 110.00,
                'unit' => 'কেজি (kg)',
                'observation_date' => '2026-10-15',
                'retrieval_date' => '2026-10-21',
                'source_name' => 'Department of Agricultural Marketing (কৃষি বিপণন অধিদপ্তর)',
                'source_url' => 'https://market.dam.gov.bd/',
                'is_verified' => false,
                'verification_status' => 'Unverified Demo Data (নমুনা ডেমো ডাটা)',
                'notes' => 'ডেমো নমুনা বাজার তথ্য। মরশুমি কোল্ড স্টোর ফলের নমুনা রেট।'
            ],
            [
                'crop_name_en' => 'Mustard Seed (BARI 14)',
                'crop_name_bn' => 'সরিষা (বারি ১৪)',
                'market_name' => 'যশোর ও কুষ্টিয়া মোকাম',
                'district' => 'যশোর (Jashore)',
                'price_wholesale' => 85.00,
                'price_retail' => 92.00,
                'unit' => 'কেজি (kg)',
                'observation_date' => '2026-10-18',
                'retrieval_date' => '2026-10-21',
                'source_name' => 'Department of Agricultural Marketing (কৃষি বিপণন অধিদপ্তর)',
                'source_url' => 'https://market.dam.gov.bd/',
                'is_verified' => false,
                'verification_status' => 'Unverified Demo Data (নমুনা ডেমো ডাটা)',
                'notes' => 'ডেমো নমুনা বাজার তথ্য। তেলবীজ আড়ত কাঠামোভিত্তিক।'
            ],
            [
                'crop_name_en' => 'Purple Round Brinjal',
                'crop_name_bn' => 'গোল বেগুন',
                'market_name' => 'কারওয়ান বাজার পাইকারি আড়ত, ঢাকা',
                'district' => 'ঢাকা (Dhaka)',
                'price_wholesale' => 45.00,
                'price_retail' => 55.00,
                'unit' => 'কেজি (kg)',
                'observation_date' => '2026-10-19',
                'retrieval_date' => '2026-10-21',
                'source_name' => 'Department of Agricultural Marketing (কৃষি বিপণন অধিদপ্তর)',
                'source_url' => 'https://market.dam.gov.bd/',
                'is_verified' => false,
                'verification_status' => 'Unverified Demo Data (নমুনা ডেমো ডাটা)',
                'notes' => 'ডেমো নমুনা বাজার তথ্য। পাইকারি সবজি মোকামের কাঠামোভিত্তিক।'
            ],
        ];
    }
}
