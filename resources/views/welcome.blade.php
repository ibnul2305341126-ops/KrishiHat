@extends('layouts.app')

@section('title', 'KrishiHat — স্মার্ট কৃষি মার্কেটপ্লেস ও ফেয়ার-ট্রেড প্ল্যাটফর্ম')

@section('content')
<main class="marketplace-home">
    @if (session('success'))
        <div class="container pt-3">
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0" role="status">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="container pt-3">
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- KrishiMarket Inspired Hero Section (60 FPS Visuals) --}}
    <section class="hero-section py-4" aria-labelledby="marketplace-heading">
        <div class="container">
            <div class="hero-card-banner km-hero-banner p-4 p-lg-5 rounded-4 text-white position-relative overflow-hidden">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <div class="hero-tag-badge km-hero-tag mb-3 d-inline-flex align-items-center gap-2">
                            <span>✨</span> স্মার্ট কৃষি মার্কেটপ্লেস ও ফেয়ার-ট্রেড প্ল্যাটফর্ম
                        </div>
                        <h1 id="marketplace-heading" class="display-6 fw-bold mb-3 hero-main-title km-hero-title">
                            কৃষকের সরাসরি ফসল, মধ্যস্বত্বভোগীহীন পাইকারি কেনাবেচা
                        </h1>
                        <p class="lead fs-6 text-white-50 mb-4 hero-sub-desc km-hero-desc">
                            বগুড়া, রাজশাহী ও দিনাজপুরের যাচাইকৃত কৃষকদের কাছ থেকে তাজা ধান, আলু ও পেঁয়াজ কিনুন। প্রতি লিস্টিংয়ে রয়েছে মেশিনে আর্দ্রতা ও ন্যায্য মূল্য মূল্যায়ন।
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ route('forecast.index') }}" class="btn btn-warning btn-lg fw-bold px-4 py-3 text-dark d-inline-flex align-items-center justify-content-center gap-2 shadow" style="border-radius: 12px; transition: var(--km-transition);">
                                <span>⚡</span> এআই দর পূর্বাভাস দেখুন
                            </a>
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-light btn-lg fw-semibold px-4 py-3 d-inline-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; backdrop-filter: blur(8px); transition: var(--km-transition);">
                                <span>📦</span> আমার অর্ডার ট্র্যাক করুন
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-25 shadow-lg backdrop-blur" style="backdrop-filter: blur(12px);">
                            <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom border-white border-opacity-20">
                                <div>
                                    <div class="small text-white-50">জাতীয় কৃষি পোর্টাল ডাটাবেজ</div>
                                    <div class="h6 mb-0 text-white fw-bold">🇧🇩 বাংলাদেশ এগ্রো মার্কেট ডাটা</div>
                                </div>
                                <span class="badge bg-success text-white px-2 py-1 small">সক্রিয় মার্কেট</span>
                            </div>
                            <div class="row g-3 text-center">
                                <div class="col-6">
                                    <div class="p-2 rounded-3 bg-dark bg-opacity-25 border border-white border-opacity-10">
                                        <div class="h4 mb-0 fw-extrabold text-warning">৬৪+</div>
                                        <small class="text-white-50" style="font-size: 0.72rem;">জেলা কভারেজ</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-3 bg-dark bg-opacity-25 border border-white border-opacity-10">
                                        <div class="h4 mb-0 fw-extrabold text-success text-light">১০০%</div>
                                        <small class="text-white-50" style="font-size: 0.72rem;">এসক্রো সুরক্ষা</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-3 bg-dark bg-opacity-25 border border-white border-opacity-10">
                                        <div class="h4 mb-0 fw-extrabold text-info">DAM</div>
                                        <small class="text-white-50" style="font-size: 0.72rem;">সরকারি পাইকারি মান</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-3 bg-dark bg-opacity-25 border border-white border-opacity-10">
                                        <div class="h4 mb-0 fw-extrabold text-white">২৪/৭</div>
                                        <small class="text-white-50" style="font-size: 0.72rem;">হোয়াটসঅ্যাপ সাপোর্ট</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 text-center text-white-50 small border-top border-white border-opacity-10" style="font-size: 0.75rem;">
                                🔒 বিএডিসি ও কৃষি বিপণন অধিদপ্তরের মানসম্মত ডিজিটাল প্ল্যাটফর্ম
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4 Trust Pillars (Inspired by KrishiMarket) --}}
        <div class="container mt-4">
            <div class="km-trust-strip">
                <div class="row g-2">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="km-trust-card h-100">
                            <div class="km-trust-icon">👨‍🌾</div>
                            <div>
                                <div class="km-trust-title">সরাসরি কৃষক থেকে ক্রয়</div>
                                <p class="km-trust-sub">কোনো মধ্যস্বত্বভোগী নেই, কৃষক পান সর্বোচ্চ ন্যায্য মূল্য।</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="km-trust-card h-100">
                            <div class="km-trust-icon">⚖️</div>
                            <div>
                                <div class="km-trust-title">সরকারি পাইকারি MOQ মান</div>
                                <p class="km-trust-sub">DAM ও BADC অনুমোদিত সঠিক পাইকারি ন্যূনতম পরিমাণ।</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="km-trust-card h-100">
                            <div class="km-trust-icon">🚚</div>
                            <div>
                                <div class="km-trust-title">স্মার্ট এগ্রো লজিস্টিকস</div>
                                <p class="km-trust-sub">লাইভ জিপিএস রুট ট্র্যাকিং ও দ্রুত সরবরাহ কেন্দ্র নেটওয়ার্ক।</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="km-trust-card h-100">
                            <div class="km-trust-icon">🛡️</div>
                            <div>
                                <div class="km-trust-title">নিরাপদ ডিজিটাল এসক্রো</div>
                                <p class="km-trust-sub">সিকিউরিটি অডিট ভেরিফিকেশন কোডসহ সম্পূর্ণ সুরক্ষাবলয়।</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Featured Category Visual Showcase (KrishiMarket Style) --}}
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-success small fw-bold text-uppercase" style="letter-spacing: 0.05em;">বাছাইকৃত বিভাগসমূহ</span>
                    <h3 class="h5 fw-bold mb-0 text-dark">কৃষি পণ্যের প্রধান ক্যাটাগরি</h3>
                </div>
                <a href="#products" class="text-success text-decoration-none small fw-bold">সমস্ত বিভাগসমূহ দেখুন →</a>
            </div>

            <div class="row g-3">
                @php
                    $categoryIcons = [
                        'grains' => ['icon' => '🌾', 'subtitle' => 'ধান, গম ও ভুট্টা'],
                        'vegetables' => ['icon' => '🥬', 'subtitle' => 'তাজা শাকসবজি ও আলু'],
                        'spices' => ['icon' => '🧅', 'subtitle' => 'পেঁয়াজ, রসুন ও মরিচ'],
                        'fruits' => ['icon' => '🥭', 'subtitle' => 'বাগানপাকা মৌসুমী ফল'],
                        'pulses-oilseeds' => ['icon' => '🌻', 'subtitle' => 'সরিষা ও ডাল ফসল'],
                    ];
                @endphp
                @foreach($categories->take(5) as $cat)
                    @php
                        $meta = $categoryIcons[$cat->slug] ?? ['icon' => ($cat->icon ?: '🌱'), 'subtitle' => 'খামারের তাজা ফসল'];
                        $isActive = request('category') === $cat->slug || $selectedCategory?->is($cat);
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-lg">
                        <a href="{{ route('home', array_merge(request()->except('category', 'page'), ['category' => $cat->slug])) }}#products" 
                           class="km-category-card {{ $isActive ? 'active' : '' }}">
                            <div class="km-category-icon-circle">
                                <span>{{ $meta['icon'] }}</span>
                            </div>
                            <div class="km-category-title">{{ $cat->name }}</div>
                            <div class="km-category-count">{{ $meta['subtitle'] }}</div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Modern Search & Filter Bar --}}
        <div class="container mt-4">
            <form action="{{ route('home') }}" method="GET" class="market-search-bar p-3 p-md-4 bg-white rounded-4 shadow-sm border d-flex flex-wrap align-items-center gap-3" role="search">
                <div class="search-input-wrap flex-grow-1 position-relative" style="min-width: 240px;">
                    <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">🔍</span>
                    <input id="market-search" type="search" name="search" value="{{ request('search') }}" class="form-control ps-5 border-0 bg-light py-2" placeholder="ফসল বা জাত অনুসন্ধান করুন (যেমন: ব্রি ধান, ডায়মন্ড আলু, তাহেরপুরী পেঁয়াজ)...">
                </div>

                <div class="category-select-wrap" style="min-width: 170px;">
                    <label class="visually-hidden" for="category-filter">ক্যাটাগরি</label>
                    <select id="category-filter" name="category" class="form-select border-0 bg-light py-2">
                        <option value="">বিভাগ: সকল ফসল (All)</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug || $selectedCategory?->is($category))>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="district-select-wrap" style="min-width: 170px;">
                    <label class="visually-hidden" for="district-filter">জেলা</label>
                    <select id="district-filter" name="district" class="form-select border-0 bg-light py-2">
                        <option value="">জেলা: সকল জেলা (All)</option>
                        @php
                            $presetDistricts = [
                                'Bogura' => 'বগুড়া (Bogura)',
                                'Dinajpur' => 'দিনাজপুর (Dinajpur)',
                                'Rajshahi' => 'রাজশাহী (Rajshahi)',
                                'Rangpur' => 'রংপুর (Rangpur)',
                                'Kushtia' => 'কুষ্টিয়া (Kushtia)',
                                'Jashore' => 'যশোর (Jashore)',
                                'Cumilla' => 'কুমিল্লা (Cumilla)',
                                'Barishal' => 'বরিশাল (Barishal)',
                                'Dhaka' => 'ঢাকা (Dhaka)',
                            ];
                            $allDistricts = collect($presetDistricts);
                            if (isset($distinctDistricts)) {
                                foreach ($distinctDistricts as $d) {
                                    if ($d && !$allDistricts->has($d)) {
                                        $allDistricts->put($d, $d);
                                    }
                                }
                            }
                        @endphp
                        @foreach($allDistricts as $distKey => $distName)
                            <option value="{{ $distKey }}" @selected(strtolower((string) request('district')) === strtolower((string) $distKey) || request('district') === $distName)>
                                {{ $distName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="price-slider-wrap d-flex align-items-center gap-2 p-2 bg-light rounded-3" style="min-width: 220px;">
                    <label class="small text-muted text-nowrap fw-semibold" for="market-max-price">সর্বোচ্চ দর:</label>
                    <input id="market-max-price" type="range" name="max_price" min="0" max="{{ number_format($priceFilterMaximum, 2, '.', '') }}" step="0.01" value="{{ number_format($maxPrice, 2, '.', '') }}" data-market-price-range class="form-range" @disabled($maximumListingPrice <= 0)>
                    <output for="market-max-price" data-market-price-output class="small fw-bold text-success text-nowrap">৳{{ number_format($maxPrice, 2) }}</output>
                </div>

                <button class="btn btn-success px-4 py-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" type="submit" style="background: linear-gradient(135deg, #0B6A40 0%, #13814C 100%); border: 0; border-radius: 10px;">
                    <span>🔍</span> খুঁজুন
                </button>
                @if (request('search') || $selectedCategory || request()->filled('max_price') || request('district'))
                    <a href="{{ route('home') }}#products" class="btn btn-outline-secondary py-2 px-3" style="border-radius: 10px;">রিসেট</a>
                @endif
            </form>
        </div>
    </section>

    {{-- Product Listings Section matching screenshot --}}
    <section id="products" class="product-section py-5" style="background: #f8faf8;">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fs-5">🌾</span>
                        <h2 class="h4 fw-bold mb-0 text-dark">সরাসরি খামারের তাজা লিস্টিং</h2>
                    </div>
                    <p class="text-muted small mb-0">যাচাইকৃত খামার থেকে সেরা মানের তাজা কৃষিপণ্য ও পাইকারি লট</p>
                </div>
                <div class="badge bg-white text-dark border fs-6 px-3 py-2 fw-semibold shadow-xs rounded-pill">
                    {{ $products->count() }} টি ফসল উপলব্ধ
                </div>
            </div>

            @if (request('search'))
                <p class="filter-summary mb-3 p-2 bg-white rounded border d-inline-block small"><strong>“{{ request('search') }}”</strong> এর জন্য ফলাফল দেখানো হচ্ছে</p>
            @endif

            <div class="row g-4">
                @forelse ($products as $product)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        @include('components.product-card', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center border-dashed rounded-4 bg-white shadow-sm">
                            <span class="fs-1 d-block mb-3">🌱</span>
                            <h3 class="h5 fw-bold">কোনো ফসল পাওয়া যায়নি</h3>
                            <p class="text-muted small">অন্য ফিল্টার বা নাম দিয়ে পুনরায় সন্ধান করুন।</p>
                            <div><a href="{{ route('home') }}#products" class="btn btn-success px-4" style="border-radius: 10px;">সকল ফসল দেখুন</a></div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Google Maps Farm Locations Section --}}
    @php
        $productsWithCoordinates = $products->filter(function ($prod) {
            return $prod->latitude !== null && $prod->longitude !== null && is_numeric($prod->latitude) && is_numeric($prod->longitude);
        });
    @endphp
    @if ($productsWithCoordinates->isNotEmpty())
        <section class="farm-locations-section py-5 bg-white border-top border-bottom">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-4 text-success">📍</span>
                        <div>
                            <h2 class="h5 fw-bold mb-0 text-dark">খামারের অবস্থান ও সরবরাহ কেন্দ্র (Google Maps Locations)</h2>
                            <small class="text-muted">সরাসরি খামার পরিদর্শন ও সরবরাহ কেন্দ্র যাচাইয়ের সঠিক ভৌগোলিক অবস্থান</small>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small fw-semibold">
                        ✓ Google Maps মানচিত্র লিঙ্ক উপলব্ধ
                    </span>
                </div>

                <div class="row g-3">
                    @foreach ($productsWithCoordinates as $locProduct)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card p-3 border rounded-4 h-100 location-card shadow-xs" style="transition: var(--km-transition);">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong class="fs-6 text-truncate text-dark" title="{{ $locProduct->district }}{{ $locProduct->upazila ? ' (' . $locProduct->upazila . ')' : '' }}">
                                        📍 {{ $locProduct->district ? $locProduct->district . ($locProduct->upazila ? ' (' . $locProduct->upazila . ')' : '') : 'খামারের অবস্থান' }}
                                    </strong>
                                    <span class="badge bg-success-subtle text-success small">মানচিত্র যুক্ত</span>
                                </div>
                                <p class="small text-muted mb-3 text-truncate">{{ $locProduct->name }} • <span class="text-success fw-bold">৳{{ number_format((float) $locProduct->price, 2) }}</span>/{{ $locProduct->unit ?: 'kg' }}</p>
                                <a href="{{ $locProduct->google_maps_search_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary mt-auto d-inline-flex align-items-center justify-content-center gap-1 rounded-3">
                                    <span>📍</span> Google Maps-এ দেখুন ↗
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Category Pills Section --}}
    <section id="categories" class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="text-success small fw-bold text-uppercase" style="letter-spacing: 0.05em;">ক্যাটাগরি অনুযায়ী ফসল</span>
                <h2 class="h4 fw-bold mb-0 text-dark">ফসলের বিভাগসমূহ</h2>
            </div>
            <a href="#products" class="text-success text-decoration-none fw-semibold">সকল পণ্য দেখুন →</a>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @forelse ($categories as $category)
                <a class="btn btn-outline-success rounded-pill px-3 py-2 {{ $selectedCategory?->is($category) ? 'active' : '' }}" href="{{ route('categories.show', $category->slug) }}" style="transition: var(--km-transition);">
                    <span class="me-1">{{ $category->icon ?: '🌱' }}</span> {{ $category->name }}
                </a>
            @empty
                <p class="text-muted small mb-0">কোনো ক্যাটাগরি তৈরি করা হয়নি।</p>
            @endforelse
        </div>
    </section>
</main>
@endsection

