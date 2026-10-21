<?php

namespace App\Services;

use App\Models\Product;

class CatalogueVerificationService
{
    /**
     * Map of catalogue crop keywords to accurate reference images, licenses and botanical details.
     */
    public static function getCropReferenceMap(): array
    {
        return [
            'ধান' => [
                'standard_en' => 'BRRI Dhan 28 Paddy',
                'standard_bn' => 'সোনালী ব্রি ধান ২৮ (আমন/বোরো)',
                'variety' => 'BRRI Dhan 28',
                'category' => 'ধান্য ও দানা শস্য',
                'image' => 'images/crops/paddy.jpg',
                'source' => 'Wikimedia Commons (Rocky Masum)',
                'license' => 'CC BY-SA 4.0',
            ],
            'চাল' => [
                'standard_en' => 'Aromatic Milled Rice',
                'standard_bn' => 'সুগন্ধি চাল (চিনিগুঁড়া / কালার চাল)',
                'variety' => 'Chinigura / Aromatic Rice',
                'category' => 'শস্য ও খাদ্যশস্য',
                'image' => 'images/crops/rice.jpg',
                'source' => 'KrishiHat Open Agro Archive',
                'license' => 'CC BY-SA 4.0',
            ],
            'আলু' => [
                'standard_en' => 'Diamant Potato',
                'standard_bn' => 'ডায়মন্ড গোল আলু',
                'variety' => 'Diamant / Cardinal',
                'category' => 'কন্দ ও আলু জাতীয়',
                'image' => 'images/crops/potato.jpg',
                'source' => 'KrishiHat Public Agricultural Archive',
                'license' => 'CC0 / Public Domain',
            ],
            'পেঁয়াজ' => [
                'standard_en' => 'Taherpuri Local Onion',
                'standard_bn' => 'তাহেরপুরী দেশি পেঁয়াজ',
                'variety' => 'Taherpuri Local',
                'category' => 'মসলা জাতীয় ফসল',
                'image' => 'images/crops/onion.jpg',
                'source' => 'Wikimedia Commons (Rocky Masum)',
                'license' => 'CC BY-SA 4.0',
            ],
            'রসুন' => [
                'standard_en' => 'Native Garlic',
                'standard_bn' => 'দেশি রসুন',
                'variety' => 'Native Local Garlic',
                'category' => 'মসলা জাতীয় ফসল',
                'image' => 'images/crops/garlic.jpg',
                'source' => 'KrishiHat Open Agro Archive',
                'license' => 'CC BY-SA 4.0',
            ],
            'আম' => [
                'standard_en' => 'Fazli Mango',
                'standard_bn' => 'রাজশাহীর ফজলি আম',
                'variety' => 'Fazli / Himsagar',
                'category' => 'ফলমূল ও বাগান ফসল',
                'image' => 'images/crops/mango.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 4.0',
            ],
            'টমেটো' => [
                'standard_en' => 'Field Fresh Tomato',
                'standard_bn' => 'টাটকা লাল টমেটো',
                'variety' => 'BARI Tomato',
                'category' => 'সবজি',
                'image' => 'images/crops/tomato.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 3.0',
            ],
            'বেগুন' => [
                'standard_en' => 'Purple Round Brinjal',
                'standard_bn' => 'যশোরের গোল বেগুন',
                'variety' => 'Purple Round Brinjal',
                'category' => 'সবজি',
                'image' => 'images/crops/brinjal.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 3.0',
            ],
            'মরিচ' => [
                'standard_en' => 'Fresh Green Chili',
                'standard_bn' => 'তাজা কাঁচা মরিচ',
                'variety' => 'Native Green Chili',
                'category' => 'সবজি ও মসলা',
                'image' => 'images/crops/chili.jpg',
                'source' => 'KrishiHat Open Agro Archive',
                'license' => 'CC BY-SA 4.0',
            ],
            'সরিষা' => [
                'standard_en' => 'Mustard Seed (BARI 14)',
                'standard_bn' => 'বারি ১৪ সরিষা বীজ',
                'variety' => 'BARI 14',
                'category' => 'ডাল ও তৈলবীজ',
                'image' => 'images/crops/mustard.jpg',
                'source' => 'Wikimedia Commons (Sanjay Acharya)',
                'license' => 'CC BY-SA 3.0',
            ],
            'ডাল' => [
                'standard_en' => 'Native Red Lentil',
                'standard_bn' => 'দেশি মসুর ডাল',
                'variety' => 'BARI Masur',
                'category' => 'ডাল ও তৈলবীজ',
                'image' => 'images/crops/lentil.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 3.0',
            ],
            'পেয়ারা' => [
                'standard_en' => 'Kazi Guava',
                'standard_bn' => 'কাজী পেয়ারা',
                'variety' => 'Kazi Peyara',
                'category' => 'ফলমূল',
                'image' => 'images/crops/guava.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 3.0',
            ],
            'পেঁপে' => [
                'standard_en' => 'Green Papaya',
                'standard_bn' => 'দেশি কাঁচা পেঁপে',
                'variety' => 'Shahi Papaya',
                'category' => 'সবজি ও ফল',
                'image' => 'images/crops/papaya.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 3.0',
            ],
            'লাউ' => [
                'standard_en' => 'Bottle Gourd / Calabash',
                'standard_bn' => 'দেশি কচি লাউ',
                'variety' => 'BARI Lau 1',
                'category' => 'সবজি',
                'image' => 'images/crops/lau.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 3.0',
            ],
            'বাঁধাকপি' => [
                'standard_en' => 'Fresh Green Cabbage',
                'standard_bn' => 'সবুজ বাঁধাকপি',
                'variety' => 'Green Express',
                'category' => 'সবজি',
                'image' => 'images/crops/cabbage.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 3.0',
            ],
            'ফুলকপি' => [
                'standard_en' => 'Fresh Cauliflower',
                'standard_bn' => 'টাটকা ফুলকপি',
                'variety' => 'Snowball',
                'category' => 'সবজি',
                'image' => 'images/crops/cauliflower.jpg',
                'source' => 'Wikimedia Commons',
                'license' => 'CC BY-SA 3.0',
            ],
        ];
    }

    /**
     * Audit and synchronize catalogue reference metadata for all existing products.
     * Preserves farmer-uploaded images without overwriting them.
     */
    public function auditAndSyncAllProducts(): array
    {
        $map = self::getCropReferenceMap();
        $updated = 0;

        foreach (Product::all() as $product) {
            $refData = null;
            foreach ($map as $key => $data) {
                if (\Illuminate\Support\Str::contains($product->name, $key)) {
                    $refData = $data;
                    break;
                }
            }

            if ($refData) {
                $product->reference_image = $refData['image'];
                $product->reference_image_source = $refData['source'];
                $product->reference_image_license = $refData['license'];
                $product->suggested_correction = "যাচাইকৃত জাত: {$refData['variety']} | রেফারেন্স ছবি: {$refData['source']}";
                $product->verification_status = 'verified';
                $product->last_reviewed_at = now();

                // If seller didn't provide a photo and product photo is empty, set fallback reference image
                if (empty($product->image) && $product->photos()->count() === 0) {
                    $product->image = $refData['image'];
                }

                $product->save();
                $updated++;
            }
        }

        return ['updated' => $updated];
    }
}
