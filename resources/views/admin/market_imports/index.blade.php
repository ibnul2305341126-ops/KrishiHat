@extends('layouts.app')
@section('title', 'কৃষি বিপণন অধিদপ্তর (DAM) বাজার দর আমদানি ও ভেরিফিকেশন | KrishiHat Admin')

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

    {{-- Title and Sources Notice --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">🌾 সরকারি কৃষি বাজার দর আমদানি ও যাচাইকরণ (DAM Data)</h2>
            <p class="text-secondary small mb-0">
                অফিসিয়াল উৎস: <a href="https://market.dam.gov.bd/" target="_blank" rel="noopener">market.dam.gov.bd</a> ও <a href="https://dam.gov.bd/" target="_blank" rel="noopener">dam.gov.bd</a>। কৃষকের লিস্টিং মূল্য থেকে সম্পূর্ণ পৃথক সরকারি রেফারেন্স সূচক।
            </p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
            📥 নতুন ডাটা রিপোর্ট আমদানি করুন
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">মোট বাজার রেকর্ড</span>
                <h3 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h3>
                <small class="text-muted mt-1">{{ $stats['distinct_crops'] }}টি শস্য | {{ $stats['distinct_markets'] }}টি বাজার</small>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">যাচাইকৃত অফিসিয়াল রেকর্ড</span>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['verified'] }}</h3>
                <small class="text-success mt-1">DAM অফিসিয়াল পোর্টাল সংরক্ষিত</small>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">অযাচাইকৃত / ডেমো রেকর্ড</span>
                <h3 class="fw-bold mb-0 text-warning">{{ $stats['unverified'] }}</h3>
                <small class="text-secondary mt-1">সতর্কতাবার্তাসহ প্রদর্শিত</small>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">মূল্য ধরণ বিভাজন</span>
                <div class="d-flex justify-content-between mt-2 small">
                    <span>কৃষক: <strong>{{ $stats['grower'] }}</strong></span>
                    <span>পাইকারি: <strong>{{ $stats['wholesale'] }}</strong></span>
                    <span>খুচরা: <strong>{{ $stats['retail'] }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('admin.market_imports.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="crop_name" class="form-control form-control-sm" placeholder="ফসলের নাম (যেমন: ব্রি ধান-২৮, আলু)..." value="{{ request('crop_name') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="district" class="form-control form-control-sm" placeholder="জেলা (যেমন: বগুড়া)..." value="{{ request('district') }}">
            </div>
            <div class="col-md-2">
                <select name="price_type" class="form-select form-select-sm">
                    <option value="">সকল মূল্য ধরণ</option>
                    <option value="grower" {{ request('price_type') === 'grower' ? 'selected' : '' }}>কৃষক পর্যায় (Grower)</option>
                    <option value="wholesale" {{ request('price_type') === 'wholesale' ? 'selected' : '' }}>পাইকারি (Wholesale)</option>
                    <option value="retail" {{ request('price_type') === 'retail' ? 'selected' : '' }}>খুচরা (Retail)</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="is_verified" class="form-select form-select-sm">
                    <option value="">সকল ভেরিফিকেশন</option>
                    <option value="1" {{ request('is_verified') === '1' ? 'selected' : '' }}>অফিসিয়াল যাচাইকৃত (Verified)</option>
                    <option value="0" {{ request('is_verified') === '0' ? 'selected' : '' }}>অযাচাইকৃত / ডেমো (Unverified)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100">ফিল্টার</button>
                <a href="{{ route('admin.market_imports.index') }}" class="btn btn-outline-secondary btn-sm">রিসেট</a>
            </div>
        </form>
    </div>

    {{-- Benchmarks Table --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ফসল ও জাত (Crop / Variety)</th>
                        <th>ক্যাটাগরি</th>
                        <th>বাজার ও জেলা</th>
                        <th>মূল্যের ধরণ</th>
                        <th>বেঞ্চমার্ক মূল্য</th>
                        <th>পর্যবেক্ষণ তারিখ</th>
                        <th>উৎস ও স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($benchmarks as $bm)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $bm->crop_name }}</div>
                                <small class="text-muted">{{ $bm->variety ?: $bm->crop_name_en }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $bm->category }}</span></td>
                            <td>
                                <div>{{ $bm->market }}</div>
                                <small class="text-secondary">{{ $bm->district }}</small>
                            </td>
                            <td>
                                @if($bm->price_type === 'grower')
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">কৃষক পর্যায়</span>
                                @elseif($bm->price_type === 'wholesale')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">পাইকারি</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">খুচরা</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">৳{{ number_format((float)$bm->price_modal, 2) }}</strong>
                                <small class="text-muted d-block">পরিসীমা: ৳{{ number_format((float)$bm->price_min, 1) }} - ৳{{ number_format((float)$bm->price_max, 1) }} / {{ $bm->unit }}</small>
                            </td>
                            <td>
                                <span class="text-nowrap">{{ $bm->observation_date ? $bm->observation_date->format('d M Y') : '—' }}</span>
                                <small class="text-muted d-block">সংগ্রহ: {{ $bm->date_accessed ? $bm->date_accessed->format('d/m/Y') : '—' }}</small>
                            </td>
                            <td>
                                @if($bm->is_verified)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle mb-1">✓ Verified</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mb-1">⚠ Unverified Demo</span>
                                @endif
                                <small class="d-block text-truncate" style="max-width: 150px;">
                                    <a href="{{ $bm->source_url }}" target="_blank" rel="noopener" class="text-secondary" title="{{ $bm->source_url }}">DAM Portal ↗</a>
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">কোনো বাজার তথ্য পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($benchmarks->hasPages())
            <div class="p-3 border-top">
                {{ $benchmarks->links() }}
            </div>
        @endif
    </div>

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.market_imports.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">DAM বাজার দর ডাটা রিপোর্ট আমদানি</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small mb-3">
                            <strong>সতর্কতা:</strong> সিস্টেম স্বয়ংক্রিয়ভাবে ডুপ্লিকেট এন্ট্রি শনাক্ত করে বাদ দেবে এবং তারিখ/ইউনিট/মূল্য যাচাই করবে। কোনো অনুমানকৃত তথ্য সরাসরি Verified হিসেবে গ্রহণ করা হবে না।
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">CSV অথবা JSON ফাইল আপলোড করুন</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv,.json,.txt">
                            <small class="text-muted">কলামসমূহ: crop_name, crop_name_en, variety, category, market, district, price_type, price_min, price_max, price_modal, unit, observation_date, is_verified</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">অথবা সরাসরি JSON কোড পেস্ট করুন</label>
                            <textarea name="json_content" class="form-control font-monospace small" rows="5" placeholder='[{"crop_name": "আলু (ডায়মন্ড)", "category": "কন্দাল ফসল", "market": "কারওয়ান বাজার", "district": "ঢাকা", "price_type": "wholesale", "price_modal": 28.00, "unit": "কেজি", "observation_date": "2026-10-20"}]'></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">অফিসিয়াল সোর্স URL</label>
                            <input type="url" name="default_source_url" class="form-control" value="https://market.dam.gov.bd/">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                        <button type="submit" class="btn btn-primary">ডাটা যাচাই ও সংরক্ষণ করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
