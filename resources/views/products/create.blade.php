@extends('layouts.app')
@section('title', 'Add a product | KrishiHat')
@section('content')
<main class="dashboard-main">
    <div class="container py-4 py-lg-5" style="max-width:900px">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('farmer.dashboard') }}">Farmer dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add product</li>
            </ol>
        </nav>
        <div class="mb-4">
            <span class="eyebrow">MARKETPLACE LISTING</span>
            <h1 class="dashboard-title">Add a product</h1>
            <p class="text-secondary mb-0">Share clear details so buyers know what is available.</p>
        </div>
        <section class="dashboard-card p-4 p-md-5">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('products.store', [], false) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Product name <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" class="form-control form-control-lg" value="{{ old('name') }}" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                                {{ $category->icon ? $category->icon.' ' : '' }}{{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" maxlength="5000">{{ old('description') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Price (৳) <span class="text-danger">*</span></label>
                        <input id="price" type="number" name="price" class="form-control" min="0" max="99999999.99" step="0.01" value="{{ old('price') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <input id="unit" type="text" name="unit" class="form-control" value="{{ old('unit', 'kg') }}" maxlength="50" placeholder="kg, piece, bag" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="stock" class="form-label">Available stock <span class="text-danger">*</span></label>
                        <input id="stock" type="number" name="stock" class="form-control" min="0" step="1" value="{{ old('stock', 0) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="min_order_quantity" class="form-label">সর্বনিম্ন ক্রয়ের পরিমাণ (Minimum Order Quantity) <span class="text-danger">*</span></label>
                        <input id="min_order_quantity" type="number" name="min_order_quantity" class="form-control" min="1" step="1" value="{{ old('min_order_quantity', 1) }}" required>
                        <div class="form-text small text-muted">পাইকারি বিক্রয়ের জন্য ক্রেতাকে কমপক্ষে কতটুকু অর্ডার করতে হবে (ডিফল্ট: ১)</div>
                    </div>
                </div>

                <div class="border rounded-3 p-3 mb-4 bg-light" id="bd-address-selector">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0 text-success">📍 খামারের অবস্থান ও প্রশাসনিক ঠিকানা (Administrative Address)</h2>
                        <span class="badge bg-warning-subtle text-dark border d-none" data-geo="loading">⏳ তথ্য লোড হচ্ছে...</span>
                    </div>
                    <p class="text-secondary small mb-3">
                        বাংলাদেশ জাতীয় ওয়েব পোর্টাল ও ডাক বিভাগ কাঠামোভিত্তিক বিভাগ, জেলা, উপজেলা, ইউনিয়ন/পৌরসভা ও পোস্ট কোড নির্বাচন করুন।
                    </p>

                    <div class="row g-3">
                        {{-- 1. Division --}}
                        <div class="col-md-6">
                            <label for="division_id" class="form-label fw-semibold">বিভাগ (Division)</label>
                            <select id="division_id" name="division_id" class="form-select" data-geo="division" data-placeholder="বিভাগ নির্বাচন করুন">
                                <option value="">-- বিভাগ নির্বাচন করুন --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}" @selected((string) old('division_id') === (string) $div->id)>
                                        {{ $div->name_bn }} ({{ $div->name_en }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2. District --}}
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="district_id" class="form-label fw-semibold">জেলা (District)</label>
                                <input type="text" class="form-control form-control-sm py-0 px-2" style="max-width: 140px; font-size: 0.75rem;" placeholder="🔍 জেলা খুঁজুন..." data-geo-search="district">
                            </div>
                            <select id="district_id" name="district_id" class="form-select" data-geo="district" data-placeholder="জেলা নির্বাচন করুন" data-initial-value="{{ old('district_id') }}" disabled>
                                <option value="">-- আগে বিভাগ নির্বাচন করুন --</option>
                            </select>
                        </div>

                        {{-- 3. Upazila / Thana --}}
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="upazila_id" class="form-label fw-semibold">উপজেলা বা থানা (Upazila / Thana)</label>
                                <input type="text" class="form-control form-control-sm py-0 px-2" style="max-width: 140px; font-size: 0.75rem;" placeholder="🔍 উপজেলা খুঁজুন..." data-geo-search="upazila">
                            </div>
                            <select id="upazila_id" name="upazila_id" class="form-select" data-geo="upazila" data-placeholder="উপজেলা নির্বাচন করুন" data-initial-value="{{ old('upazila_id') }}" disabled>
                                <option value="">-- আগে জেলা নির্বাচন করুন --</option>
                            </select>
                        </div>

                        {{-- 4. Union (Rural) OR Urban Area (Paurashava/City Corp) --}}
                        <div class="col-md-6">
                            <label for="union_id" class="form-label fw-semibold">ইউনিয়ন পরিষদ (Union - পল্লী অঞ্চল)</label>
                            <select id="union_id" name="union_id" class="form-select" data-geo="union" data-placeholder="ইউনিয়ন নির্বাচন করুন" data-initial-value="{{ old('union_id') }}" disabled>
                                <option value="">-- আগে উপজেলা নির্বাচন করুন --</option>
                            </select>
                            <div class="form-text">শহরাঞ্চল হলে ইউনিয়ন ফাঁকা রেখে পাশের পৌরসভা নির্বাচন করতে পারেন।</div>
                        </div>

                        <div class="col-md-6">
                            <label for="urban_area_id" class="form-label fw-semibold">পৌরসভা / সিটি কর্পোরেশন (Urban Area / Municipality)</label>
                            <select id="urban_area_id" name="urban_area_id" class="form-select" data-geo="urban_area" data-placeholder="পৌরসভা নির্বাচন করুন" data-initial-value="{{ old('urban_area_id') }}" disabled>
                                <option value="">-- আগে জেলা নির্বাচন করুন --</option>
                            </select>
                        </div>

                        {{-- 5. Post Office & Postal Code --}}
                        <div class="col-md-4">
                            <label for="post_office_id" class="form-label fw-semibold">ডাকঘর (Post Office)</label>
                            <select id="post_office_id" name="post_office_id" class="form-select" data-geo="post_office" data-placeholder="ডাকঘর নির্বাচন করুন" data-initial-value="{{ old('post_office_id') }}" disabled>
                                <option value="">-- আগে জেলা নির্বাচন করুন --</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="postal_code" class="form-label fw-semibold">পোস্ট কোড</label>
                            <input id="postal_code" type="text" name="postal_code" class="form-control" data-geo="postal_code" value="{{ old('postal_code') }}" placeholder="যেমন: ৫৮৪০" maxlength="20">
                        </div>

                        {{-- 6. GPS Coordinates (Optional) --}}
                        <div class="col-md-3">
                            <label for="latitude" class="form-label fw-semibold">অক্ষাংশ (Latitude - ঐচ্ছিক)</label>
                            <input id="latitude" type="number" step="any" name="latitude" class="form-control" value="{{ old('latitude') }}" min="-90" max="90" placeholder="24.6738">
                        </div>

                        <div class="col-md-3">
                            <label for="longitude" class="form-label fw-semibold">দ্রাঘিমাংশ (Longitude - ঐচ্ছিক)</label>
                            <input id="longitude" type="number" step="any" name="longitude" class="form-control" value="{{ old('longitude') }}" min="-180" max="180" placeholder="89.4184">
                        </div>

                        {{-- Fallback input for manual text entry if needed --}}
                        <input type="hidden" name="district" id="legacy_district" value="{{ old('district') }}">
                        <input type="hidden" name="upazila" id="legacy_upazila" value="{{ old('upazila') }}">
                    </div>

                    <div class="mt-2 text-muted small">
                        💡 ঠিকানা ও ডাকঘর নির্বাচন করলে পোস্ট কোড স্বয়ংক্রিয়ভাবে পূরণ হবে। ক্রেতারা Google Maps-এ আপনার খামারের সঠিক স্থান দেখতে পাবেন।
                    </div>
                </div>

                <div class="mb-4">
                    <label for="photos" class="form-label">Product photos (up to five, 5 MB each)</label>
                    <input id="photos" type="file" name="photos[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple data-product-photo-input>
                    <div data-product-photo-previews class="d-flex flex-wrap gap-2 mt-2"></div>
                    <div class="form-text">Optional legacy image URL</div>
                    <input type="url" name="image" class="form-control mt-2" value="{{ old('image') }}" maxlength="2048">
                </div>
                <button type="submit" class="btn btn-brand btn-lg">Publish listing</button>
                <a href="{{ route('farmer.dashboard') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
            </form>
        </section>
    </div>
</main>
@endsection
