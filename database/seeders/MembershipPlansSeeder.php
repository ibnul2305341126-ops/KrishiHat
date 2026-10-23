<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlansSeeder extends Seeder
{
    /**
     * Seed membership plans with configurable criteria, limits and eligibility rules.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Farmer / সাধারণ অ্যাকাউন্ট',
                'slug' => 'free',
                'description' => 'স্ট্যান্ডার্ড খামারি ও ক্রেতা অ্যাকাউন্ট। সাধারণ অর্ডার, দরদাম ও সর্বোচ্চ ৫টি সক্রিয় লিস্টিং।',
                'role_requirement' => 'all',
                'price' => 0.00,
                'billing_cycle_days' => 365,
                'active_listing_limit' => 5,
                'featured_listings_allowed' => 0,
                'can_view_analytics' => false,
                'can_bulk_purchase' => false,
                'priority_support' => false,
                'is_active' => true,
                'is_demo' => false,
                'eligibility_notes' => 'সকল নিবন্ধিত খামারি এবং ক্রেতার জন্য স্বয়ংক্রিয়ভাবে প্রযোজ্য। কোনো ফি বা অতিরিক্ত ভেরিফিকেশন প্রযোজ্য নয়।',
            ],
            [
                'name' => 'Farmer Pro / প্রো কৃষক',
                'slug' => 'farmer-pro',
                'description' => 'উন্নত বাণিজ্যিক খামারিদের জন্য ৫০টি পর্যন্ত সক্রিয় লিস্টিং, ৫টি ফিচারড লিস্টিং ও সেলস অ্যানালিটিক্স রিপোর্ট।',
                'role_requirement' => 'farmer',
                'price' => 499.00,
                'billing_cycle_days' => 30,
                'active_listing_limit' => 50,
                'featured_listings_allowed' => 5,
                'can_view_analytics' => true,
                'can_bulk_purchase' => false,
                'priority_support' => true,
                'is_active' => true,
                'is_demo' => true,
                'eligibility_notes' => 'কৃষক (Farmer) রোল থাকা বাধ্যতামূলক। প্রোফাইলে বৈধ মোবাইল নম্বর, জেলা এবং অন্তত ১টি সক্রিয় পণ্য তালিকাভুক্তি আবশ্যক।',
            ],
            [
                'name' => 'Business Wholesale / পাইকারি ও প্রতিষ্ঠান',
                'slug' => 'business-wholesale',
                'description' => 'পাইকারি আড়তদার ও প্রাতিষ্ঠানিক ক্রেতাদের জন্য বাল্ক অর্ডার, ৫০০টি পণ্য সক্ষমতা ও ডেডিকেটেড অগ্রাধিকার সুবিধা।',
                'role_requirement' => 'buyer',
                'price' => 1299.00,
                'billing_cycle_days' => 30,
                'active_listing_limit' => 500,
                'featured_listings_allowed' => 20,
                'can_view_analytics' => true,
                'can_bulk_purchase' => true,
                'priority_support' => true,
                'is_active' => true,
                'is_demo' => true,
                'eligibility_notes' => 'ক্রেতা (Buyer) রোল থাকা আবশ্যক। বড় অর্ডারের জন্য ব্যাংক ভেরিফিকেশন বা কর্পোরেট ডেলিভারি ঠিকানা সংযুক্ত থাকতে হবে।',
            ],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
