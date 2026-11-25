<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTrackingEvent;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BangladeshDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds idempotently.
     */
    public function run(): void
    {
        // 1. Categories
        $categoriesData = [
            ['name' => 'ধান্য ও দানা শস্য', 'slug' => 'grains-cereals', 'icon' => '🌾'],
            ['name' => 'কন্দ ও আলু জাতীয়', 'slug' => 'tubers-potatoes', 'icon' => '🥔'],
            ['name' => 'মসলা জাতীয় ফসল', 'slug' => 'spices', 'icon' => '🧅'],
            ['name' => 'মৌসুমি ফলমূল', 'slug' => 'fruits', 'icon' => '🥭'],
            ['name' => 'তাজা শাকসবজি', 'slug' => 'vegetables', 'icon' => '🥬'],
            ['name' => 'ডাল ও তৈলবীজ', 'slug' => 'pulses-oilseeds', 'icon' => '🫘'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'icon' => $cat['icon']]
            );
        }

        // 2. Demo Users (Separate demo accounts, safely hashed passwords)
        $hashedPassword = Hash::make('Password123!');

        $farmerMokbul = User::firstOrCreate(
            ['email' => 'farmer.mokbul@demo.krishihat.test'],
            [
                'name' => 'মো. মোখলেছুর রহমান (Mokbul Farmer - Demo)',
                'password' => $hashedPassword,
                'role' => 'farmer',
                'verification_status' => 'verified',
                'farmer_verified_at' => now(),
                'nid_number' => '19851012345678901',
                'trade_license_number' => 'TRAD/BOG/2026/0412',
                'farm_name' => 'বগুড়া মোখলেছুর এগ্রো ফার্ম',
            ]
        );
        $farmerMokbul->update([
            'verification_status' => 'verified',
            'farmer_verified_at' => $farmerMokbul->farmer_verified_at ?? now(),
            'nid_number' => '19851012345678901',
            'trade_license_number' => 'TRAD/BOG/2026/0412',
            'farm_name' => 'বগুড়া মোখলেছুর এগ্রো ফার্ম',
        ]);

        $farmerRahim = User::firstOrCreate(
            ['email' => 'farmer.rahim@demo.krishihat.test'],
            [
                'name' => 'হাফেজ আব্দুর রহিম (Rahim Farmer - Demo)',
                'password' => $hashedPassword,
                'role' => 'farmer',
                'verification_status' => 'verified',
                'farmer_verified_at' => now(),
                'nid_number' => '19881098765432100',
                'trade_license_number' => 'TRAD/RAJ/2026/0881',
                'farm_name' => 'রাজশাহী রহিম এগ্রো প্রজেক্ট',
            ]
        );
        $farmerRahim->update([
            'verification_status' => 'verified',
            'farmer_verified_at' => $farmerRahim->farmer_verified_at ?? now(),
            'nid_number' => '19881098765432100',
            'trade_license_number' => 'TRAD/RAJ/2026/0881',
            'farm_name' => 'রাজশাহী রহিম এগ্রো প্রজেক্ট',
        ]);

        $farmerSolaiman = User::firstOrCreate(
            ['email' => 'farmer.solaiman@demo.krishihat.test'],
            [
                'name' => 'মো. সুলেমান আলী (Solaiman Farmer - Demo)',
                'password' => $hashedPassword,
                'role' => 'farmer',
                'verification_status' => 'verified',
                'farmer_verified_at' => now(),
                'nid_number' => '19795412309876543',
                'trade_license_number' => 'TRAD/DIN/2026/0199',
                'farm_name' => 'দিনাজপুর সুলেমান গ্রিন ফার্ম',
            ]
        );
        $farmerSolaiman->update([
            'verification_status' => 'verified',
            'farmer_verified_at' => $farmerSolaiman->farmer_verified_at ?? now(),
            'nid_number' => '19795412309876543',
            'trade_license_number' => 'TRAD/DIN/2026/0199',
            'farm_name' => 'দিনাজপুর সুলেমান গ্রিন ফার্ম',
        ]);

        $farmerPending = User::firstOrCreate(
            ['email' => 'farmer.pending@demo.krishihat.test'],
            [
                'name' => 'মো. জহিরুল ইসলাম (Pending Farmer - Demo)',
                'password' => $hashedPassword,
                'role' => 'farmer',
                'verification_status' => 'pending',
                'nid_number' => '19922612345678902',
                'trade_license_number' => 'TRAD/KUS/2026/0333',
                'farm_name' => 'কুষ্টিয়া জহিরুল আধুনিক এগ্রো',
                'verification_submitted_at' => now(),
            ]
        );
        $farmerPending->update([
            'verification_status' => 'pending',
            'nid_number' => '19922612345678902',
            'trade_license_number' => 'TRAD/KUS/2026/0333',
            'farm_name' => 'কুষ্টিয়া জহিরুল আধুনিক এগ্রো',
            'verification_submitted_at' => now(),
        ]);

        $buyerKarim = User::firstOrCreate(
            ['email' => 'buyer.karim@demo.krishihat.test'],
            [
                'name' => 'করিম ট্রেডার্স (Karim Wholesale Buyer - Demo)',
                'password' => $hashedPassword,
                'role' => 'buyer',
            ]
        );

        $buyerAnisur = User::firstOrCreate(
            ['email' => 'buyer.anisur@demo.krishihat.test'],
            [
                'name' => 'আড়তদার আনিসুর রহমান (Anisur Agro Buyer - Demo)',
                'password' => $hashedPassword,
                'role' => 'buyer',
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@demo.krishihat.test'],
            [
                'name' => 'সিস্টেম অ্যাডমিন (System Administrator)',
                'password' => $hashedPassword,
                'role' => 'admin',
            ]
        );
        if ($adminUser->role !== 'admin') {
            $adminUser->role = 'admin';
            $adminUser->save();
        }

        // 3. Demo Products (With authentic regional contexts, verified public coordinates)
        $productsData = [
            [
                'farmer_id' => $farmerMokbul->id,
                'category_id' => $categories['grains-cereals']->id,
                'category' => $categories['grains-cereals']->name,
                'name' => 'সোনালী ব্রি ধান ২৮ (BRRI Dhan 28 Paddy - Demo)',
                'description' => 'বগুড়ার শেরপুর খামার থেকে সরাসরি সংগৃহীত ১৪% আর্দ্রতা-পরীক্ষিত সোনালী ব্রি ধান ২৮। পাইকারি চালকল ও বড় আড়তের জন্য আদর্শ।',
                'price' => 36.50,
                'unit' => 'kg',
                'stock' => 2500,
                'min_order_quantity' => 50,
                'image' => 'images/crops/paddy.jpg',
                'district' => 'Bogura',
                'upazila' => 'Sherpur',
                'latitude' => 24.67380000,
                'longitude' => 89.41840000,
            ],
            [
                'farmer_id' => $farmerRahim->id,
                'category_id' => $categories['tubers-potatoes']->id,
                'category' => $categories['tubers-potatoes']->name,
                'name' => 'ডায়মন্ড কোল্ড স্টোরেজ আলু (Diamant Potato - Demo)',
                'description' => 'দিনাজপুরের বীরগঞ্জের আধুনিক হিমাগারে সংরক্ষিত ডায়মন্ড জাতের বাছাইকৃত গোল আলু। সমআকৃতির ও দাগহীন।',
                'price' => 28.00,
                'unit' => 'kg',
                'stock' => 1800,
                'min_order_quantity' => 50,
                'image' => 'images/crops/potato.jpg',
                'district' => 'Dinajpur',
                'upazila' => 'Birganj',
                'latitude' => 25.85690000,
                'longitude' => 88.65750000,
            ],
            [
                'farmer_id' => $farmerSolaiman->id,
                'category_id' => $categories['spices']->id,
                'category' => $categories['spices']->name,
                'name' => 'তাহেরপুরী দেশি পেঁয়াজ (Taherpuri Onion - Demo)',
                'description' => 'রাজশাহীর বিখ্যাত তাহেরপুরী কড়া ঝাঁঝালো শুকনো দেশি পেঁয়াজ। পাতলা খোসা ও দীর্ঘ সংরক্ষণক্ষমতা সম্পন্ন।',
                'price' => 65.00,
                'unit' => 'kg',
                'stock' => 1200,
                'min_order_quantity' => 40,
                'image' => 'images/crops/onion.jpg',
                'district' => 'Rajshahi',
                'upazila' => 'Taherpur',
                'latitude' => 24.41670000,
                'longitude' => 88.83330000,
            ],
            [
                'farmer_id' => $farmerSolaiman->id,
                'category_id' => $categories['fruits']->id,
                'category' => $categories['fruits']->name,
                'name' => 'রাজশাহীর ফজলি আম (Fazli Mango - Demo)',
                'description' => 'বাঘা অঞ্চলের নিজস্ব বাগান থেকে গাছপাকা সুমিষ্ট ফজলি আম। কোনো ক্ষতিকারক রাসায়নিক বা কার্বাইডমুক্ত।',
                'price' => 85.00,
                'unit' => 'kg',
                'stock' => 800,
                'min_order_quantity' => 20,
                'image' => 'images/crops/mango.jpg',
                'district' => 'Rajshahi',
                'upazila' => 'Bagha',
                'latitude' => 24.18330000,
                'longitude' => 88.83330000,
            ],
            [
                'farmer_id' => $farmerMokbul->id,
                'category_id' => $categories['pulses-oilseeds']->id,
                'category' => $categories['pulses-oilseeds']->name,
                'name' => 'রংপুরের খাঁটি সরিষা বীজ (Mustard Seed - Demo)',
                'description' => 'রংপুরের মিঠাপুকুর হতে আহরিত তেল নিষ্কাশনযোগ্য দেশি মাঘী সরিষা। উচ্চ তেল উৎপাদনশীল জাত।',
                'price' => 95.00,
                'unit' => 'kg',
                'stock' => 600,
                'min_order_quantity' => 20,
                'image' => 'images/crops/mustard.jpg',
                'district' => 'Rangpur',
                'upazila' => 'Mithapukur',
                'latitude' => 25.57860000,
                'longitude' => 89.28280000,
            ],
            [
                'farmer_id' => $farmerRahim->id,
                'category_id' => $categories['vegetables']->id,
                'category' => $categories['vegetables']->name,
                'name' => 'যশোরের গোল বেগুন (Purple Brinjal - Demo)',
                'description' => 'যশোরের শার্শা থেকে সংগৃহীত সতেজ ও চকচকে বেগুনি গোল বেগুন। কীটনাশক অবশেষমুক্ত।',
                'price' => 35.00,
                'unit' => 'kg',
                'stock' => 450,
                'min_order_quantity' => 15,
                'image' => 'images/crops/brinjal.jpg',
                'district' => 'Jashore',
                'upazila' => 'Sharsha',
                'latitude' => 23.08330000,
                'longitude' => 88.88330000,
            ],
        ];

        $products = [];
        foreach ($productsData as $item) {
            $product = Product::firstOrCreate(
                ['farmer_id' => $item['farmer_id'], 'name' => $item['name']],
                $item
            );
            $product->update($item);
            $products[] = $product;
        }

        $paddy = $products[0];
        $potato = $products[1];
        $onion = $products[2];

        // 4. Bargaining Offers (Demonstration workflow: submitted -> countered -> accepted)
        $offerPaddy = Offer::firstOrCreate(
            [
                'buyer_id' => $buyerKarim->id,
                'farmer_id' => $farmerMokbul->id,
                'product_id' => $paddy->id,
            ],
            [
                'initial_price' => 34.00,
                'current_price' => 35.00,
                'quantity' => 500,
                'status' => Offer::STATUS_ACCEPTED,
            ]
        );

        if ($offerPaddy->events()->count() === 0) {
            OfferEvent::create([
                'offer_id' => $offerPaddy->id,
                'actor_id' => $buyerKarim->id,
                'actor_role' => 'buyer',
                'action' => 'created',
                'price' => 34.00,
                'created_at' => Carbon::now()->subDays(3),
            ]);
            OfferEvent::create([
                'offer_id' => $offerPaddy->id,
                'actor_id' => $farmerMokbul->id,
                'actor_role' => 'farmer',
                'action' => 'countered',
                'price' => 35.50,
                'created_at' => Carbon::now()->subDays(2)->addHours(2),
            ]);
            OfferEvent::create([
                'offer_id' => $offerPaddy->id,
                'actor_id' => $buyerKarim->id,
                'actor_role' => 'buyer',
                'action' => 'accepted',
                'price' => 35.00,
                'created_at' => Carbon::now()->subDays(2)->addHours(4),
            ]);
        }

        $offerPotato = Offer::firstOrCreate(
            [
                'buyer_id' => $buyerAnisur->id,
                'farmer_id' => $farmerRahim->id,
                'product_id' => $potato->id,
            ],
            [
                'initial_price' => 25.00,
                'current_price' => 26.50,
                'quantity' => 300,
                'status' => Offer::STATUS_COUNTERED,
            ]
        );

        if ($offerPotato->events()->count() === 0) {
            OfferEvent::create([
                'offer_id' => $offerPotato->id,
                'actor_id' => $buyerAnisur->id,
                'actor_role' => 'buyer',
                'action' => 'created',
                'price' => 25.00,
                'created_at' => Carbon::now()->subDay(),
            ]);
            OfferEvent::create([
                'offer_id' => $offerPotato->id,
                'actor_id' => $farmerRahim->id,
                'actor_role' => 'farmer',
                'action' => 'countered',
                'price' => 26.50,
                'created_at' => Carbon::now()->subHours(8),
            ]);
        }

        // 5. Demo Orders with Full Tracking Timeline
        // Order 1: Shipped & In Transit (Bogura -> Dhaka)
        $order1 = Order::firstOrCreate(
            ['tracking_code' => 'KH-TRK-DEMO01'],
            [
                'buyer_id' => $buyerKarim->id,
                'total_amount' => 17500.00, // 500 kg @ ৳35
                'status' => Order::STATUS_SHIPPED,
                'delivery_address' => 'প্লট ৪৪, কারওয়ান বাজার পাইকারি আড়ত, ঢাকা-১২১৫',
                'phone' => '01700-777888',
                'driver_name' => 'মো. রফিকুল ইসলাম (Rafiqul Islam)',
                'driver_phone' => '01800-123456',
                'vehicle_number' => 'ঢাকা-মেট্রো-ট-১১-২০৯১',
                'current_location_text' => 'সিরাজগঞ্জ হাইওয়ে যমুনা সেতু পশ্চিম গোলচত্বর',
                'current_latitude' => 24.39860000,
                'current_longitude' => 89.75480000,
                'estimated_delivery_at' => Carbon::now()->addHours(6),
                'accepted_offer_id' => $offerPaddy->id,
            ]
        );

        if ($order1->items()->count() === 0) {
            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $paddy->id,
                'farmer_id' => $farmerMokbul->id,
                'quantity' => 500,
                'unit_price' => 35.00,
                'subtotal' => 17500.00,
            ]);
        }

        if ($order1->trackingEvents()->count() === 0) {
            $order1->trackingEvents()->createMany([
                [
                    'status' => Order::STATUS_PENDING,
                    'title' => 'অর্ডার গৃহীত হয়েছে (Order Placed)',
                    'description' => 'গৃহীত দরদাম অনুযায়ী সফলভাবে অর্ডার প্লেস করা হয়েছে।',
                    'location' => 'বগুড়া শেরপুর খামার',
                    'actor_id' => $buyerKarim->id,
                    'created_at' => Carbon::now()->subDays(2),
                ],
                [
                    'status' => Order::STATUS_CONFIRMED,
                    'title' => 'খামারি সরবরাহ নিশ্চিত করেছেন (Order Confirmed)',
                    'description' => 'খামারি মো. মোখলেছুর রহমান অর্ডার গ্রহণ ও সরবরাহ নিশ্চিত করেছেন।',
                    'location' => 'শেরপুর উপজেলা, বগুড়া',
                    'actor_id' => $farmerMokbul->id,
                    'created_at' => Carbon::now()->subDay()->addHours(2),
                ],
                [
                    'status' => Order::STATUS_PROCESSING,
                    'title' => 'ফসল বাছাই, আর্দ্রতা পরীক্ষা ও বস্তাবন্দী (Packaging)',
                    'description' => 'ধানের আর্দ্রতা ১৩.৮% পরিমাপ করা হয়েছে। ৫০ কেজির বস্তায় লোডিং সম্পন্ন।',
                    'location' => 'শেরপুর সেন্ট্রাল গোডাউন',
                    'actor_id' => $farmerMokbul->id,
                    'created_at' => Carbon::now()->subDay()->addHours(6),
                ],
                [
                    'status' => Order::STATUS_SHIPPED,
                    'title' => 'ট্রাকে লোড হয়ে ঢাকার উদ্দেশ্যে রওনা দিয়েছে (In Transit)',
                    'description' => 'গাড়ি ঢাকা-মেট্রো-ট-১১-২০৯১ যমুনা সেতু পার হয়ে ঢাকার কারওয়ান বাজার অভিমুখে যাত্রা করছে।',
                    'location' => 'সিরাজগঞ্জ হাইওয়ে যমুনা সেতু পশ্চিম গোলচত্বর',
                    'actor_id' => $farmerMokbul->id,
                    'created_at' => Carbon::now()->subHours(3),
                ],
            ]);
        }

        Payment::firstOrCreate(
            ['payable_type' => Order::class, 'payable_id' => $order1->id],
            [
                'user_id' => $buyerKarim->id,
                'payment_type' => Payment::TYPE_ORDER,
                'payment_method' => Payment::METHOD_BKASH,
                'amount' => 17500.00,
                'currency' => 'BDT',
                'transaction_id' => 'KH-TXN-BKASH-771122',
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => Carbon::now()->subDay(),
                'notes' => 'বিকাশ অনলাইন পেমেন্ট — অর্ডার #1 (সোনালী ব্রি ধান ২৮, ৫০০ কেজি) মোকবুল কৃষক হতে করিম ট্রেডার্স।',
            ]
        );

        // Order 2: Delivered (Dinajpur -> Chittagong)
        $order2 = Order::firstOrCreate(
            ['tracking_code' => 'KH-TRK-DEMO02'],
            [
                'buyer_id' => $buyerKarim->id,
                'total_amount' => 5600.00, // 200 kg @ ৳28
                'status' => Order::STATUS_DELIVERED,
                'delivery_address' => 'খাতুনগঞ্জ পাইকারি বাজার, চট্টগ্রাম',
                'phone' => '01700-777888',
                'driver_name' => 'মো. কামাল উদ্দিন (Kamal Uddin)',
                'driver_phone' => '01800-456789',
                'vehicle_number' => 'দিনাজপুর-ট-১২-৮৮৭১',
                'current_location_text' => 'খাতুনগঞ্জ পাইকারি আড়ত, চট্টগ্রাম',
                'current_latitude' => 22.33840000,
                'current_longitude' => 91.83890000,
                'estimated_delivery_at' => Carbon::now()->subHours(2),
            ]
        );

        if ($order2->items()->count() === 0) {
            OrderItem::create([
                'order_id' => $order2->id,
                'product_id' => $potato->id,
                'farmer_id' => $farmerRahim->id,
                'quantity' => 200,
                'unit_price' => 28.00,
                'subtotal' => 5600.00,
            ]);
        }

        if ($order2->trackingEvents()->count() === 0) {
            $order2->trackingEvents()->createMany([
                [
                    'status' => Order::STATUS_PENDING,
                    'title' => 'অর্ডার গৃহীত হয়েছে (Order Placed)',
                    'description' => 'ক্রেতা কার্ট হতে অর্ডার প্লেস করেছেন।',
                    'location' => 'বীরগঞ্জ, দিনাজপুর',
                    'actor_id' => $buyerKarim->id,
                    'created_at' => Carbon::now()->subDays(4),
                ],
                [
                    'status' => Order::STATUS_CONFIRMED,
                    'title' => 'হিমাগার হতে সরবরাহ নিশ্চিত (Confirmed)',
                    'description' => 'বীরগঞ্জ কোল্ড স্টোরেজ হতে আলু উত্তোলনের অনুমোদন সম্পন্ন।',
                    'location' => 'বীরগঞ্জ, দিনাজপুর',
                    'actor_id' => $farmerRahim->id,
                    'created_at' => Carbon::now()->subDays(3),
                ],
                [
                    'status' => Order::STATUS_PROCESSING,
                    'title' => 'বাছাই ও জালের বস্তায় প্যাকেজিং (Processing)',
                    'description' => 'আলু সাইজ অনুযায়ী গ্রেডিং ও প্যাকিং সম্পন্ন।',
                    'location' => 'বীরগঞ্জ কোল্ড স্টোরেজ ডিপো',
                    'actor_id' => $farmerRahim->id,
                    'created_at' => Carbon::now()->subDays(2),
                ],
                [
                    'status' => Order::STATUS_SHIPPED,
                    'title' => 'চট্টগ্রামের উদ্দেশ্যে লং-রুটে পরিবহন (Shipped)',
                    'description' => 'ট্রাকে মালামাল বোঝাই হয়ে চট্টগ্রাম হাইওয়েতে ট্রানজিটে রয়েছে।',
                    'location' => 'ঢাকা-চট্টগ্রাম হাইওয়ে কুমিল্লা বাইপাস',
                    'actor_id' => $farmerRahim->id,
                    'created_at' => Carbon::now()->subDay(),
                ],
                [
                    'status' => Order::STATUS_DELIVERED,
                    'title' => 'সফলভাবে ডেলিভারি সম্পন্ন (Delivered)',
                    'description' => 'পণ্য নিরাপদে খাতুনগঞ্জ আড়তে আনলোড ও ক্রেতার রসিদ নিশ্চিত করা হয়েছে।',
                    'location' => 'খাতুনগঞ্জ পাইকারি বাজার, চট্টগ্রাম',
                    'actor_id' => $farmerRahim->id,
                    'created_at' => Carbon::now()->subHours(2),
                ],
            ]);
        }

        Payment::firstOrCreate(
            ['payable_type' => Order::class, 'payable_id' => $order2->id],
            [
                'user_id' => $buyerKarim->id,
                'payment_type' => Payment::TYPE_ORDER,
                'payment_method' => Payment::METHOD_COD,
                'amount' => 5600.00,
                'currency' => 'BDT',
                'transaction_id' => 'KH-TXN-COD-882233',
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => Carbon::now()->subHours(2),
                'notes' => 'ক্যাশ অন ডেলিভারি সফল সংগ্রহ — অর্ডার #2 (ডায়মন্ড আলু, ২০০ কেজি) রহিম কৃষক হতে করিম ট্রেডার্স।',
            ]
        );

        // Order 3: Confirmed (Rajshahi -> Bogura)
        $order3 = Order::firstOrCreate(
            ['tracking_code' => 'KH-TRK-DEMO03'],
            [
                'buyer_id' => $buyerAnisur->id,
                'total_amount' => 9750.00, // 150 kg @ ৳65
                'status' => Order::STATUS_CONFIRMED,
                'delivery_address' => 'সাতমাথা পাইকারি বাজার, বগুড়া',
                'phone' => '01800-999000',
                'current_location_text' => 'তাহেরপুর কৃষক সেবামঞ্চ, রাজশাহী',
                'current_latitude' => 24.41670000,
                'current_longitude' => 88.83330000,
                'estimated_delivery_at' => Carbon::now()->addDays(2),
            ]
        );

        if ($order3->items()->count() === 0) {
            OrderItem::create([
                'order_id' => $order3->id,
                'product_id' => $onion->id,
                'farmer_id' => $farmerSolaiman->id,
                'quantity' => 150,
                'unit_price' => 65.00,
                'subtotal' => 9750.00,
            ]);
        }

        if ($order3->trackingEvents()->count() === 0) {
            $order3->trackingEvents()->createMany([
                [
                    'status' => Order::STATUS_PENDING,
                    'title' => 'অর্ডার গৃহীত হয়েছে (Order Placed)',
                    'description' => 'সাতমাথা পাইকারি বাজারের জন্য অর্ডার নথিবদ্ধ হয়েছে।',
                    'location' => 'তাহেরপুর, রাজশাহী',
                    'actor_id' => $buyerAnisur->id,
                    'created_at' => Carbon::now()->subDay(),
                ],
                [
                    'status' => Order::STATUS_CONFIRMED,
                    'title' => 'খামারি অর্ডার নিশ্চিত করেছেন (Confirmed)',
                    'description' => 'মো. সুলেমান আলী শুকনো দেশি পেঁয়াজ সরবরাহের প্রস্তুতি নিচ্ছেন।',
                    'location' => 'তাহেরপুর, রাজশাহী',
                    'actor_id' => $farmerSolaiman->id,
                    'created_at' => Carbon::now()->subHours(5),
                ],
            ]);
        }

        Payment::firstOrCreate(
            ['payable_type' => Order::class, 'payable_id' => $order3->id],
            [
                'user_id' => $buyerAnisur->id,
                'payment_type' => Payment::TYPE_ORDER,
                'payment_method' => Payment::METHOD_BANK_TRANSFER,
                'amount' => 9750.00,
                'currency' => 'BDT',
                'transaction_id' => 'KH-TXN-BNK-993344',
                'status' => Payment::STATUS_PENDING,
                'notes' => 'অগ্রণী ব্যাংক ট্রান্সফার জমা — অর্ডার #3 (তাহেরপুরী পেঁয়াজ, ১৫০ কেজি) সুলেমান কৃষক হতে আনিসুর অ্যাগ্রো।',
            ]
        );

        // 6. Demo Conversations & Messaging
        $contextKey = 'product:' . $paddy->id . ':buyer:' . $buyerKarim->id;
        $conv1 = Conversation::firstOrCreate(
            ['context_key' => $contextKey],
            [
                'buyer_id' => $buyerKarim->id,
                'farmer_id' => $farmerMokbul->id,
                'product_id' => $paddy->id,
            ]
        );

        if ($conv1->messages()->count() === 0) {
            ConversationMessage::create([
                'conversation_id' => $conv1->id,
                'sender_id' => $buyerKarim->id,
                'body' => 'সালাম মোখলেছুর ভাই। ব্রি ধান ২৮-এর আর্দ্রতা কত শতাংশ আছে এবং কালকে কারওয়ান বাজারে ডেলিভারি দিতে পারবেন কি না?',
                'created_at' => Carbon::now()->subDays(2),
            ]);
            ConversationMessage::create([
                'conversation_id' => $conv1->id,
                'sender_id' => $farmerMokbul->id,
                'body' => 'ওয়ালাইকুমুস সালাম করিম ভাই। মেশিনে মাপা হয়েছে, আর্দ্রতা ১৪% এর নিচে আছে। কাল দুপুরেই ট্রাক ঢাকার উদ্দেশ্যে রওনা দিবে।',
                'created_at' => Carbon::now()->subDays(2)->addHours(1),
            ]);
        }
    }
}
