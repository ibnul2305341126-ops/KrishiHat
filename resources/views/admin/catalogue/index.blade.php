@extends('layouts.app')
@section('title', 'ক্যাটালগ রিভিউ ও শস্য ফটো অডিট | KrishiHat Admin')

@section('content')
<main class="container py-4 py-lg-5">
    @include('admin._nav')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">🔍 শস্য ক্যাটালগ রিভিউ ও ফটো অডিট (Catalogue Review)</h2>
            <p class="text-secondary small mb-0">
                কৃষকের আপলোডকৃত মূল তথ্য ও ছবি অক্ষুণ্ণ রেখে সঠিক জাত ও রেফারেন্স ফটো রিভিউ করুন।
            </p>
        </div>
        <div>
            <a href="{{ asset('images/crops/ATTRIBUTION.md') }}" target="_blank" class="btn btn-outline-info btn-sm">
                📄 লাইসেন্স ও এট্রিবিউশন গাইড
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">মোট ক্যাটালগ পণ্য</span>
                <h3 class="fw-bold mb-0 text-dark">{{ $statusCounts['total'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">যাচাইকৃত (Verified)</span>
                <h3 class="fw-bold mb-0 text-success">{{ $statusCounts['verified'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">প্রস্তাবিত সংশোধন (Suggested)</span>
                <h3 class="fw-bold mb-0 text-warning">{{ $statusCounts['suggested'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">অযাচাইকৃত / পর্যালোচনাধীন</span>
                <h3 class="fw-bold mb-0 text-secondary">{{ $statusCounts['unverified'] + $statusCounts['rejected'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('admin.catalogue.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="পণ্যের নাম বা জাত অনুসন্ধান..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">সকল ক্যাটাগরি</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="district" class="form-select form-select-sm">
                    <option value="">সকল জেলা</option>
                    @foreach($districts as $district)
                        <option value="{{ $district }}" {{ request('district') == $district ? 'selected' : '' }}>{{ $district }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="verification_status" class="form-select form-select-sm">
                    <option value="">সকল ভেরিফিকেশন স্ট্যাটাস</option>
                    <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Verified (যাচাইকৃত)</option>
                    <option value="suggested" {{ request('verification_status') === 'suggested' ? 'selected' : '' }}>Suggested (সংশোধন প্রস্তাবিত)</option>
                    <option value="unverified" {{ request('verification_status') === 'unverified' ? 'selected' : '' }}>Unverified (অযাচাইকৃত)</option>
                    <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected (পর্যালোচনাধীন)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100">ফিল্টার</button>
                <a href="{{ route('admin.catalogue.index') }}" class="btn btn-outline-secondary btn-sm">রিসেট</a>
            </div>
        </form>
    </div>

    {{-- Review Table --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>কৃষকের লিস্টিং ও আসল ছবি</th>
                        <th>প্রস্তাবিত জাত ও রেফারেন্স ফটো</th>
                        <th>ক্যাটাগরি ও জেলা</th>
                        <th>স্ট্যাটাস</th>
                        <th>রিভিউয়ের তারিখ</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><strong>#{{ $product->id }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $primaryPhoto = $product->photos->first();
                                        $farmerImgUrl = $primaryPhoto ? asset('storage/' . $primaryPhoto->path) : ($product->image ?: asset('images/crops/rice.jpg'));
                                    @endphp
                                    <img src="{{ $farmerImgUrl }}" alt="{{ $product->name }}" class="rounded border" style="width: 55px; height: 55px; object-fit: cover;" onerror="this.src='/images/crops/rice.jpg'">
                                    <div>
                                        <div class="fw-bold">{{ $product->name }}</div>
                                        <small class="text-muted">খামারি: {{ $product->farmer?->name ?? 'অজ্ঞাত' }} | ৳{{ number_format((float)$product->price, 2) }}/{{ $product->unit }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($product->reference_image)
                                        <img src="{{ asset($product->reference_image) }}" alt="রেফারেন্স ফটো" class="rounded border border-primary" style="width: 55px; height: 55px; object-fit: cover;" onerror="this.src='/images/crops/rice.jpg'">
                                    @else
                                        <span class="badge bg-secondary">ছবি নেই</span>
                                    @endif
                                    <div>
                                        <div class="fw-semibold text-primary">{{ $product->suggested_correction ?: 'কোনো পরিবর্তন নেই' }}</div>
                                        <small class="text-muted d-block">
                                            উৎস: {{ $product->reference_image_source ?: 'পর্যালোচনাধীন' }}
                                            @if($product->reference_image_license)
                                                ({{ $product->reference_image_license }})
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><span class="badge bg-light text-dark border">{{ $product->categoryRecord?->name ?? $product->category }}</span></div>
                                <small class="text-secondary">{{ $product->district ?: 'জেলা নির্ধারিত নেই' }}</small>
                            </td>
                            <td>
                                @if($product->verification_status === 'verified')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">✓ Verified</span>
                                @elseif($product->verification_status === 'suggested')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Suggested</span>
                                @elseif($product->verification_status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Unverified</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $product->last_reviewed_at ? \Carbon\Carbon::parse($product->last_reviewed_at)->format('d M Y, h:i A') : 'কখনও নয়' }}
                                </small>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $product->id }}">
                                    ✏️ রিভিউ
                                </button>
                                @if($product->verification_status !== 'verified')
                                    <form action="{{ route('admin.catalogue.approve', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="অনুমোদন করুন">✓</button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        {{-- Review Edit Modal --}}
                        <div class="modal fade" id="editModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('admin.catalogue.update-review', $product) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">পণ্য পর্যালোচনা: {{ $product->name }} (#{{ $product->id }})</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">ভেরিফিকেশন স্ট্যাটাস</label>
                                                    <select name="verification_status" class="form-select" required>
                                                        <option value="verified" {{ $product->verification_status === 'verified' ? 'selected' : '' }}>Verified (যাচাইকৃত ও অনুমোদিত)</option>
                                                        <option value="suggested" {{ $product->verification_status === 'suggested' ? 'selected' : '' }}>Suggested (সংশোধন প্রস্তাবিত)</option>
                                                        <option value="unverified" {{ $product->verification_status === 'unverified' ? 'selected' : '' }}>Unverified (অযাচাইকৃত)</option>
                                                        <option value="rejected" {{ $product->verification_status === 'rejected' ? 'selected' : '' }}>Rejected (অসঠিক তথ্য/বাতিল)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">রেফারেন্স ছবির লোকাল পাথ</label>
                                                    <input type="text" name="reference_image" class="form-control" value="{{ $product->reference_image }}" placeholder="images/crops/rice.jpg">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">প্রস্তাবিত জাত ও সঠিক বিবরণ (Suggested Correction)</label>
                                                    <textarea name="suggested_correction" class="form-control" rows="2">{{ $product->suggested_correction }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">ছবির উৎস ও লেখক (Image Source / Author)</label>
                                                    <input type="text" name="reference_image_source" class="form-control" value="{{ $product->reference_image_source }}" placeholder="Wikimedia Commons / DAM">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">লাইসেন্স (License)</label>
                                                    <input type="text" name="reference_image_license" class="form-control" value="{{ $product->reference_image_license }}" placeholder="CC BY-SA 4.0 / CC0 Public Domain">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                                            <button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">কোনো পণ্য পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-3 border-top">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
