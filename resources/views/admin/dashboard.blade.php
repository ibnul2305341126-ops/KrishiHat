@extends('layouts.app')
@section('title', 'Admin Dashboard | KrishiHat')

@section('content')
<main class="container py-4 py-lg-5" style="max-width: 1320px;">
    @include('admin._nav')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Modern Hero Banner --}}
    <div class="audit-hero-banner p-4 p-md-5 mb-4 mb-lg-5">
        <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(56, 211, 135, 0.35); backdrop-filter: blur(8px);">
                    <span class="ticker-dot"></span>
                    <span class="small fw-bold text-light text-uppercase tracking-wider" style="letter-spacing: 0.08em; font-size: 0.76rem;">
                        CENTRAL COMMAND & INTELLIGENCE DASHBOARD
                    </span>
                </div>
                <h1 class="h2 fw-extrabold text-white mb-2" style="font-size: clamp(1.8rem, 3.2vw, 2.5rem); letter-spacing: -0.03em;">
                    📊 কৃষি হাট কেন্দ্রীয় অ্যাডমিন কন্ট্রোল সেন্টার
                </h1>
                <p class="text-light text-opacity-90 mb-0 fs-6" style="max-width: 680px; line-height: 1.6;">
                    সম্পূর্ণ লাইভ ডাটাবেজ ডেরাইভড মেট্রিক্স, রিয়েল-টাইম অর্ডার ফ্লো, দরদাম ব্যবস্থাপনা এবং গ্রাহক ও খামারি আর্থিক লেনদেন অডিট রেকর্ড।
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                    <a href="{{ route('transactions.index') }}" class="btn btn-light fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                        <span>🛡️</span> সিকিউরিটি অডিট লেজার
                    </a>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-light fw-semibold px-3 py-2 d-inline-flex align-items-center gap-2">
                        <span>💳</span> পেমেন্ট গেটওয়ে
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Real Metric Cards with FinTech Elevation --}}
    <div class="row g-3 mb-4 mb-lg-5">
        <div class="col-sm-6 col-lg-3">
            <div class="audit-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="audit-stat-icon success">👥</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle small fw-bold">লাইভ অ্যাকাউন্ট</span>
                </div>
                <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">মোট ব্যবহারকারী</small>
                <h3 class="fw-extrabold mb-1 mt-1 text-dark" style="font-size: 1.75rem; letter-spacing: -0.02em;">
                    {{ number_format($metrics['total_users']) }}
                </h3>
                <div class="mt-auto pt-2 border-top small text-secondary d-flex justify-content-between">
                    <span>খামারি: <strong class="text-success">{{ $metrics['total_farmers'] }}</strong></span>
                    <span>ক্রেতা: <strong class="text-primary">{{ $metrics['total_buyers'] }}</strong></span>
                    <span>অ্যাডমিন: <strong>{{ $metrics['total_admins'] }}</strong></span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="audit-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="audit-stat-icon primary">🌾</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-bold">মার্কেটপ্লেস লিস্টিং</span>
                </div>
                <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">পণ্য ও ফসল সংখ্যা</small>
                <h3 class="fw-extrabold mb-1 mt-1 text-primary" style="font-size: 1.75rem; letter-spacing: -0.02em;">
                    {{ number_format($metrics['total_products']) }}
                </h3>
                <div class="mt-auto pt-2 border-top small text-secondary d-flex justify-content-between">
                    <span>মজুদ: <strong class="text-success">{{ $metrics['in_stock_products'] }}</strong></span>
                    <span>স্টক শেষ: <strong class="text-danger">{{ $metrics['sold_out_products'] }}</strong></span>
                    <span>ক্যাটাগরি: <strong>{{ $metrics['total_categories'] }}</strong></span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="audit-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="audit-stat-icon info">📦</span>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle small fw-bold">মোট বিক্রয় ভলিউম</span>
                </div>
                <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">মোট অর্ডার ও বিক্রয়</small>
                <h3 class="fw-extrabold mb-1 mt-1 text-success" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                    ৳{{ number_format($metrics['total_revenue'], 2) }}
                </h3>
                <div class="mt-auto pt-2 border-top small text-secondary d-flex justify-content-between">
                    <span>অর্ডার: <strong>{{ $metrics['total_orders'] }}</strong></span>
                    <span>ডেলিভার্ড: <strong class="text-success">{{ $metrics['delivered_orders'] }}</strong></span>
                    <span>চলমান: <strong class="text-warning">{{ $metrics['in_transit_orders'] }}</strong></span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="audit-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="audit-stat-icon warning">🤝</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small fw-bold">দরদাম ফ্লো</span>
                </div>
                <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">অফার ও দর কষাকষি</small>
                <h3 class="fw-extrabold mb-1 mt-1 text-warning" style="font-size: 1.75rem; letter-spacing: -0.02em;">
                    {{ number_format($metrics['total_offers']) }}
                </h3>
                <div class="mt-auto pt-2 border-top small text-secondary d-flex justify-content-between">
                    <span>গৃহীত: <strong class="text-success">{{ $metrics['accepted_offers'] }}</strong></span>
                    <span>কাউন্টার: <strong class="text-info">{{ $metrics['countered_offers'] }}</strong></span>
                    <span>অপেক্ষমান: <strong>{{ $metrics['pending_offers'] }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- System Status Banner --}}
    <div class="card border-0 shadow-sm p-3 mb-4 rounded-4 bg-white border d-flex flex-row align-items-center gap-3">
        <span class="fs-3 p-2 rounded-3 bg-light">ℹ️</span>
        <div class="small">
            <strong class="text-dark d-block mb-1">সিস্টেম ও ডেটা স্বচ্ছতা নোট:</strong>
            <span class="text-muted">ড্যাশবোর্ডের সমস্ত পরিসংখ্যান সরাসরি লাইভ ডাটাবেজ কোয়েরি (`User`, `Product`, `Order`, `Offer`) থেকে সংগৃহীত। কোনো ভুয়া বা হার্ডকোডেড কাউন্টার ব্যবহার করা হয়নি। ডেমো ও পরীক্ষামূলক রেকর্ড স্পষ্ট ট্যাগসহ সংরক্ষিত।</span>
        </div>
    </div>

    <div class="row g-4">
        {{-- Recent Orders --}}
        <div class="col-lg-7">
            <div class="audit-table-card h-100">
                <div class="p-3 px-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 fw-bold text-dark">📦 সাম্প্রতিক অর্ডারসমূহ (Recent Orders)</h2>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">সকল অর্ডার →</a>
                </div>
                <div class="table-responsive">
                    <table class="table audit-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="ps-4">ট্র্যাকিং কোড</th>
                                <th scope="col">ক্রেতা (Customer)</th>
                                <th scope="col">খামারি (Farmer)</th>
                                <th scope="col">পরিমাণ ও ফসল</th>
                                <th scope="col">স্ট্যাটাস</th>
                                <th scope="col" class="text-end pe-4">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                @php
                                    $farmer = $order->items->first()?->farmer;
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <code class="fw-bold">{{ $order->tracking_code ?: 'KH-ORD-'.$order->id }}</code>
                                        <div class="small text-muted">{{ $order->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <strong class="text-dark d-block">{{ $order->buyer?->name ?? 'Guest/Deleted' }}</strong>
                                        <small class="text-muted">📞 {{ $order->phone }}</small>
                                    </td>
                                    <td>
                                        @if($farmer)
                                            <strong class="text-success d-block">{{ $farmer->name }}</strong>
                                            <small class="text-muted">📞 {{ $farmer->phone ?: ($farmer->district ?? '') }}</small>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-dark">৳{{ number_format((float)$order->total_amount, 2) }}</strong>
                                        <div class="small text-muted">{{ $order->items->first()?->product?->name ?? 'ফসল' }} ({{ $order->items->count() }} আইটেম)</div>
                                    </td>
                                    <td>
                                        @if($order->status === 'delivered')
                                            <span class="audit-pill-badge completed">Delivered</span>
                                        @elseif($order->status === 'shipped')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2">In Transit</span>
                                        @elseif($order->status === 'confirmed')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle py-1 px-2">Confirmed</span>
                                        @else
                                            <span class="audit-pill-badge pending">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">বিস্তারিত</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">কোনো অর্ডার এখনো পাওয়া যায়নি।</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Offers & Users --}}
        <div class="col-lg-5">
            <div class="audit-table-card mb-4">
                <div class="p-3 px-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 fw-bold text-dark">🤝 সাম্প্রতিক দরদাম (Recent Offers)</h2>
                    <a href="{{ route('admin.offers.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">সকল দরদাম →</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentOffers as $offer)
                        <div class="list-group-item p-3 border-light">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <strong class="text-dark d-block">{{ $offer->product?->name ?? 'Crop' }}</strong>
                                    <small class="text-muted">
                                        ক্রেতা: {{ $offer->buyer?->name ?? 'Buyer' }} ➔ কৃষক: {{ $offer->farmer?->name ?? 'Farmer' }}
                                    </small>
                                </div>
                                <span class="badge {{ $offer->status === 'accepted' ? 'bg-success' : ($offer->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill">
                                    {{ ucfirst($offer->status) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 small">
                                <span class="text-muted">{{ $offer->quantity }} {{ $offer->product?->unit ?: 'unit' }} @ ৳{{ number_format((float)$offer->current_price, 2) }}</span>
                                <strong class="text-success">মোট: ৳{{ number_format((float)($offer->current_price * $offer->quantity), 2) }}</strong>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">কোনো অফার রেকর্ড পাওয়া যায়নি।</div>
                    @endforelse
                </div>
            </div>

            <div class="audit-table-card">
                <div class="p-3 px-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 fw-bold text-dark">👥 নতুন নিবন্ধিত ব্যবহারকারী</h2>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">সকল ব্যবহারকারী →</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentUsers as $user)
                        <div class="list-group-item p-3 border-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="audit-avatar {{ $user->role === 'farmer' ? 'farmer' : 'buyer' }}">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <strong class="text-dark d-block">{{ $user->name }}</strong>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                            <span class="badge {{ $user->role === 'farmer' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }} rounded-pill px-3 py-1">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">কোনো ব্যবহারকারী পাওয়া যায়নি।</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
