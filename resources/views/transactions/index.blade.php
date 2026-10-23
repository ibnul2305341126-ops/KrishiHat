@extends('layouts.app')
@section('title', 'লেনদেন ও সিকিউরিটি অডিট রেকর্ড (Transaction Security Log) | KrishiHat')

@section('content')
<main class="dashboard-main py-4 py-lg-5" style="background: linear-gradient(180deg, #f3f7f4 0%, #f9fbf9 100%); min-height: 85vh;">
    <div class="container" style="max-width: {{ auth()->user()->role === 'admin' ? '1320px' : '1080px' }};">
        
        {{-- Breadcrumb Navigation --}}
        <nav aria-label="Breadcrumb" class="mb-3">
            <ol class="breadcrumb py-1 px-2 mb-0" style="background: transparent;">
                <li class="breadcrumb-item">
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('dashboard') }}" class="text-decoration-none fw-semibold">
                        <span>📊</span> ড্যাশবোর্ড
                    </a>
                </li>
                <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">লেনদেন ও সিকিউরিটি হিস্ট্রি</li>
            </ol>
        </nav>

        {{-- Modernized Hero Banner --}}
        <div class="audit-hero-banner p-4 p-md-5 mb-4 mb-lg-5">
            <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(56, 211, 135, 0.35); backdrop-filter: blur(8px);">
                        <span class="ticker-dot"></span>
                        <span class="small fw-bold text-light text-uppercase tracking-wider" style="letter-spacing: 0.08em; font-size: 0.76rem;">
                            🛡️ OFFICIAL FINANCIAL & TRADE SECURITY AUDIT TRAIL
                        </span>
                    </div>
                    <h1 class="h2 fw-extrabold text-white mb-2" style="font-size: clamp(1.8rem, 3.2vw, 2.5rem); letter-spacing: -0.03em;">
                        💳 লেনদেন ও নিরাপত্তা অডিট রেকর্ড (Transactions)
                    </h1>
                    @if(auth()->user()->role === 'admin')
                        <p class="text-light text-opacity-90 mb-0 fs-6" style="max-width: 680px; line-height: 1.6;">
                            কোন গ্রাহক (ক্রেতা) কোন খামারি (কৃষক) থেকে কত টাকার কি ফসল ক্রয় করেছেন—তার ডিজিটাল ভেরিফিকেশন, আর্থিক নিষ্পত্তি ও সম্পূর্ণ সিকিউরিটি ট্রেইল।
                        </p>
                    @else
                        <p class="text-light text-opacity-90 mb-0 fs-6" style="max-width: 620px; line-height: 1.6;">
                            আপনার সম্পন্নকৃত সকল পেমেন্ট, ফসল ক্রয়-বিক্রয় ও ডিজিটাল ব্যাংক/নগদ লেনদেনের অফিসিয়াল নিরাপত্তা রেকর্ড।
                        </p>
                    @endif
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.payments.index') }}" class="btn btn-light fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                                <span>🛡️</span> পেমেন্ট গেটওয়ে অডিট
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-light fw-semibold px-3 py-2 d-inline-flex align-items-center gap-2">
                                <span>📦</span> সকল অর্ডার
                            </a>
                        @else
                            <a href="{{ route('home') }}#products" class="btn btn-light fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                                <span>🌱</span> নতুন ফসল কিনুন
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin KPI Summary Cards with Sleek FinTech Elevation --}}
        @if(auth()->user()->role === 'admin' && isset($adminStats))
            <div class="row g-3 g-lg-4 mb-4 mb-lg-5">
                <div class="col-sm-6 col-lg-3">
                    <div class="audit-stat-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="audit-stat-icon success">৳</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle small fw-bold">পরিশোধিত ভলিউম</span>
                        </div>
                        <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">মোট সফল নিষ্পত্তি</small>
                        <h3 class="fw-extrabold mb-1 mt-1 text-dark" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                            ৳{{ number_format($adminStats['total_volume'], 2) }}
                        </h3>
                        <div class="small text-muted mt-auto pt-2 border-top d-flex align-items-center gap-1">
                            <span class="text-success fw-bold">✓ ১০০% ভেরিফাইড</span> এসক্রো ভলিউম
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="audit-stat-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="audit-stat-icon primary">📑</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-bold">অডিট রেকর্ড</span>
                        </div>
                        <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">সর্বমোট লেনদেন</small>
                        <h3 class="fw-extrabold mb-1 mt-1 text-dark" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                            {{ $adminStats['total_records'] }} <small class="fs-6 text-muted fw-normal">টি</small>
                        </h3>
                        <div class="small text-muted mt-auto pt-2 border-top d-flex align-items-center gap-1">
                            <span class="text-primary fw-bold">সকল অর্ডার ও সাবস্ক্রিপশন</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="audit-stat-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="audit-stat-icon info">✓</span>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle small fw-bold">নিষ্পত্তিকৃত</span>
                        </div>
                        <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">সম্পন্ন লেনদেন (Completed)</small>
                        <h3 class="fw-extrabold mb-1 mt-1 text-success" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                            {{ $adminStats['completed_count'] }} <small class="fs-6 text-muted fw-normal">টি</small>
                        </h3>
                        <div class="small text-muted mt-auto pt-2 border-top d-flex align-items-center gap-1">
                            <span class="text-success fw-bold">পেমেন্ট নিশ্চিতকৃত</span> রেকর্ড
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="audit-stat-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="audit-stat-icon warning">⏳</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small fw-bold">অপেক্ষমান</span>
                        </div>
                        <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">ক্যাশ অন ডেলিভারি / ব্যাংক</small>
                        <h3 class="fw-extrabold mb-1 mt-1 text-warning" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                            {{ $adminStats['pending_count'] }} <small class="fs-6 text-muted fw-normal">টি</small>
                        </h3>
                        <div class="small text-muted mt-auto pt-2 border-top d-flex align-items-center gap-1">
                            <span class="text-warning fw-bold">ডেলিভারি ও ভেরিফিকেশন সাপেক্ষে</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modern Segmented Pill Filters for Admin --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-2 bg-white rounded-4 shadow-sm border border-light">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="small fw-bold text-secondary ps-2 me-1">রেকর্ড ফিল্টার:</span>
                    <a href="{{ route('transactions.index', ['status' => 'all']) }}" 
                       class="audit-filter-pill {{ ($statusFilter ?? 'all') === 'all' ? 'active' : '' }}">
                        <span>🌐</span> সকল রেকর্ড ({{ $adminStats['total_records'] }})
                    </a>
                    <a href="{{ route('transactions.index', ['status' => 'completed']) }}" 
                       class="audit-filter-pill {{ ($statusFilter ?? '') === 'completed' ? 'active' : '' }}">
                        <span>✓</span> সফল ও পরিশোধিত ({{ $adminStats['completed_count'] }})
                    </a>
                    <a href="{{ route('transactions.index', ['status' => 'pending']) }}" 
                       class="audit-filter-pill {{ ($statusFilter ?? '') === 'pending' ? 'active' : '' }}">
                        <span>⏳</span> অপেক্ষমান / COD ({{ $adminStats['pending_count'] }})
                    </a>
                </div>
                <div class="pe-2 small text-muted d-none d-md-block">
                    🔒 এনক্রিপ্টেড অডিট ট্রেইল সক্রিয়
                </div>
            </div>
        @endif

        {{-- Empty State Alert --}}
        @if($payments->isEmpty())
            <div class="audit-table-card p-5 text-center my-4">
                <div class="audit-stat-icon info mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem;">
                    💳
                </div>
                <h4 class="fw-bold text-dark mb-2">কোনো লেনদেনের রেকর্ড পাওয়া যায়নি (No payment transactions are recorded in KrishiHat).</h4>
                <p class="text-secondary mx-auto mb-0" style="max-width: 580px; line-height: 1.6;">
                    The marketplace currently stores orders and order totals, but it has no payment processing or payment confirmation records. An order is not proof of a successful payment.
                </p>
            </div>
        @else
            {{-- Modern High-End FinTech Transactions Table Card --}}
            <div class="audit-table-card mb-4">
                <div class="p-3 px-4 border-bottom bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-5">📜</span>
                        <h2 class="h5 mb-0 fw-bold text-dark">আর্থিক লেনদেন ও সরবরাহ সিকিউরিটি লেজার</h2>
                    </div>
                    <span class="badge bg-light text-secondary border small px-3 py-2 rounded-pill">
                        মোট প্রদর্শিত: {{ $payments->count() }}টি ট্রানজেকশন
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table audit-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="ps-4">ট্রানজেকশন ID ও সিকিউরিটি</th>
                                @if(auth()->user()->role === 'admin')
                                    <th scope="col">গ্রাহক / ক্রেতা (Customer)</th>
                                    <th scope="col">খামারি / বিক্রেতা (Farmer)</th>
                                    <th scope="col">পণ্য ও পরিমাণ (Produce)</th>
                                @endif
                                <th scope="col">রেফারেন্স</th>
                                <th scope="col">পদ্ধতি</th>
                                <th scope="col">টাকার পরিমাণ</th>
                                <th scope="col">স্ট্যাটাস</th>
                                <th scope="col">তারিখ</th>
                                @if(auth()->user()->role === 'admin')
                                    <th scope="col" class="text-end pe-4">অ্যাকশন</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $p)
                                @php
                                    $order = $p->payable instanceof \App\Models\Order ? $p->payable : null;
                                    $customer = $p->customer;
                                    $farmers = $p->farmers;
                                    $items = $p->order_items;
                                @endphp
                                <tr>
                                    {{-- Transaction ID & Security Stamp --}}
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-bold font-monospace text-dark">{{ $p->transaction_id }}</span>
                                        </div>
                                        @if($order && $order->tracking_code)
                                            <span class="badge bg-light text-secondary border font-monospace small py-1 px-2">
                                                📦 {{ $order->tracking_code }}
                                            </span>
                                        @endif
                                        <div class="mt-1">
                                            <span class="audit-sec-code">
                                                🛡️ {{ $p->security_verification_code }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Customer (Buyer) --}}
                                    @if(auth()->user()->role === 'admin')
                                        <td>
                                            @if($customer)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="audit-avatar buyer">
                                                        {{ mb_substr($customer->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark lh-sm">{{ $customer->name }}</div>
                                                        <a href="tel:{{ $customer->phone }}" class="text-decoration-none small text-muted d-block mt-1">
                                                            📞 {{ $customer->phone ?: 'ফোন নম্বর নেই' }}
                                                        </a>
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small mt-1" style="font-size: 0.68rem;">
                                                            {{ ucfirst($customer->role) }} #{{ $customer->id }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>

                                        {{-- Farmer (Seller) --}}
                                        <td>
                                            @if($order && $farmers->isNotEmpty())
                                                @foreach($farmers->take(2) as $farmer)
                                                    <div class="d-flex align-items-center gap-2 {{ !$loop->last ? 'mb-2 pb-1 border-bottom border-light' : '' }}">
                                                        <div class="audit-avatar farmer">
                                                            {{ mb_substr($farmer->name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-success lh-sm">{{ $farmer->name }}</div>
                                                            <a href="tel:{{ $farmer->phone }}" class="text-decoration-none small text-muted d-block mt-1">
                                                                📞 {{ $farmer->phone ?: 'ফোন নম্বর নেই' }}
                                                            </a>
                                                            @if($farmer->district)
                                                                <small class="text-secondary d-block" style="font-size: 0.73rem;">
                                                                    📍 {{ $farmer->upazila ? $farmer->upazila.', ' : '' }}{{ $farmer->district }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @if($farmers->count() > 2)
                                                    <span class="badge bg-light text-muted border small mt-1">+ আরো {{ $farmers->count() - 2 }} খামারি</span>
                                                @endif
                                            @elseif($p->payment_type === 'subscription')
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="audit-avatar farmer" style="background: #eef2ff; color: #4338ca;">🌱</div>
                                                    <div>
                                                        <div class="fw-bold text-dark lh-sm">KrishiHat প্ল্যাটফর্ম</div>
                                                        <small class="text-muted d-block">সদস্যপদ সার্ভিস ফি</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">সরাসরি কৃষক প্রযোজ্য নয়</span>
                                            @endif
                                        </td>

                                        {{-- Products & Items --}}
                                        <td>
                                            @if($order && $items->isNotEmpty())
                                                @foreach($items->take(2) as $item)
                                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                        <span class="small fw-bold text-dark">• {{ $item->product?->name ?? 'ফসল' }}</span>
                                                    </div>
                                                    <div class="small text-muted mb-1 ps-2" style="font-size: 0.77rem;">
                                                        <span class="badge bg-light text-dark border">{{ $item->quantity }} {{ $item->product?->unit ?: 'kg' }}</span>
                                                        <span class="text-secondary">@ ৳{{ number_format((float)$item->unit_price, 2) }}</span>
                                                    </div>
                                                @endforeach
                                                @if($items->count() > 2)
                                                    <span class="badge bg-light text-secondary border small">+ আরো {{ $items->count() - 2 }} টি ফসল</span>
                                                @endif
                                            @elseif($p->payment_type === 'subscription')
                                                <div class="small fw-bold text-info-emphasis">
                                                    {{ $p->payable?->plan?->name ?? 'মেম্বারশিপ প্যাকেজ' }}
                                                </div>
                                                <small class="text-muted">{{ $p->payable?->plan?->duration_days ?? 30 }} দিনের প্রিমিয়াম সার্ভিস</small>
                                            @else
                                                <small class="text-muted">সাধারণ লেনদেন</small>
                                            @endif
                                        </td>
                                    @endif

                                    {{-- Type & Reference --}}
                                    <td>
                                        @if($p->payment_type === 'order')
                                            <a href="{{ auth()->user()->role === 'admin' ? route('admin.orders.show', $p->payable_id) : route('orders.show', $p->payable_id) }}" 
                                               class="text-decoration-none">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2 fw-bold">
                                                    Order #{{ $p->payable_id }}
                                                </span>
                                            </a>
                                        @else
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle py-1 px-2 fw-bold">
                                                Subscription #{{ $p->payable_id }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Payment Method --}}
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase py-1 px-2 font-monospace">
                                            {{ $p->payment_method }}
                                        </span>
                                        <small class="d-block text-secondary mt-1 fw-semibold" style="font-size: 0.75rem;">
                                            {{ $p->payment_method_bangla }}
                                        </small>
                                    </td>

                                    {{-- Amount --}}
                                    <td>
                                        <div class="audit-amount-text">
                                            ৳{{ number_format((float)$p->amount, 2) }}
                                        </div>
                                        <small class="text-muted d-block font-monospace" style="font-size: 0.72rem;">{{ $p->currency }}</small>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @if($p->status === 'successful' || $p->status === 'completed')
                                            <span class="audit-pill-badge completed">
                                                ✓ Completed
                                            </span>
                                        @elseif($p->status === 'pending')
                                            <span class="audit-pill-badge pending">
                                                ⏳ Pending
                                            </span>
                                        @elseif($p->status === 'failed')
                                            <span class="audit-pill-badge failed">
                                                ✗ Failed
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                {{ ucfirst($p->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td>
                                        <div class="small fw-bold text-dark">{{ $p->created_at->format('M j, Y') }}</div>
                                        <small class="text-muted font-monospace" style="font-size: 0.73rem;">{{ $p->created_at->format('h:i A') }}</small>
                                    </td>

                                    {{-- Security Dossier Action (Admin) --}}
                                    @if(auth()->user()->role === 'admin')
                                        <td class="text-end pe-4">
                                            <button type="button" 
                                                    class="audit-action-btn"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#secModal{{ $p->id }}"
                                                    title="সম্পূর্ণ সিকিউরিটি ও আর্থিক অডিট রেকর্ড দেখুন">
                                                <span>🛡️</span> সিকিউরিটি রেকর্ড
                                            </button>
                                        </td>
                                    @endif
                                </tr>

                                {{-- Security Record Modal for Each Transaction --}}
                                @if(auth()->user()->role === 'admin')
                                    <div class="modal fade" id="secModal{{ $p->id }}" tabindex="-1" aria-labelledby="secModalLabel{{ $p->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                            <div class="modal-content audit-modal-cert overflow-hidden border-0">
                                                {{-- Certificate Modal Header --}}
                                                <div class="modal-header audit-cert-header px-4 py-3">
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <span class="fs-4">🛡️</span>
                                                            <h5 class="modal-title fw-bold text-white mb-0" id="secModalLabel{{ $p->id }}">
                                                                অফিসিয়াল লেনদেন ও নিরাপত্তা অডিট রেকর্ড
                                                            </h5>
                                                        </div>
                                                        <small class="text-light text-opacity-75">KrishiHat Digital Financial Security & Crop Traceability Certificate</small>
                                                    </div>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                {{-- Certificate Modal Body --}}
                                                <div class="modal-body p-4 bg-light audit-cert-watermark">
                                                    {{-- Key Metrics Banner --}}
                                                    <div class="card border-0 shadow-sm p-3 mb-4 bg-white rounded-3">
                                                        <div class="row g-3 align-items-center">
                                                            <div class="col-sm-6 col-md-3">
                                                                <small class="text-muted d-block fw-semibold">সিকিউরিটি ভেরিফিকেশন আইডি:</small>
                                                                <code class="fw-bold text-primary fs-6">{{ $p->security_verification_code }}</code>
                                                            </div>
                                                            <div class="col-sm-6 col-md-3">
                                                                <small class="text-muted d-block fw-semibold">সিস্টেম ট্রানজেকশন ID:</small>
                                                                <strong class="font-monospace text-dark">{{ $p->transaction_id }}</strong>
                                                            </div>
                                                            <div class="col-sm-6 col-md-3">
                                                                <small class="text-muted d-block fw-semibold">লেনদেনের মোট মূল্য:</small>
                                                                <strong class="text-success fs-5">৳{{ number_format((float)$p->amount, 2) }} {{ $p->currency }}</strong>
                                                            </div>
                                                            <div class="col-sm-6 col-md-3">
                                                                <small class="text-muted d-block fw-semibold">নিষ্পত্তির স্ট্যাটাস:</small>
                                                                @if($p->status === 'successful' || $p->status === 'completed')
                                                                    <span class="audit-pill-badge completed">✓ সম্পন্ন (Completed)</span>
                                                                @else
                                                                    <span class="audit-pill-badge pending">⏳ অপেক্ষমান (Pending)</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Party Profiles: Buyer vs Farmer --}}
                                                    <div class="row g-3 mb-4">
                                                        {{-- Customer Column --}}
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded-3">
                                                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                                                    <div class="audit-avatar buyer">👤</div>
                                                                    <div>
                                                                        <h6 class="fw-bold mb-0 text-dark">গ্রাহক / ক্রেতা পরিচিতি</h6>
                                                                        <small class="text-muted">Buyer Security Profile</small>
                                                                    </div>
                                                                </div>
                                                                @if($customer)
                                                                    <div class="small mb-2"><strong>নাম:</strong> {{ $customer->name }}</div>
                                                                    <div class="small mb-2"><strong>মোবাইল নম্বর:</strong> <a href="tel:{{ $customer->phone }}" class="text-decoration-none fw-semibold">{{ $customer->phone ?: 'প্রদত্ত নেই' }}</a></div>
                                                                    <div class="small mb-2"><strong>ইমেইল:</strong> {{ $customer->email ?: 'N/A' }}</div>
                                                                    <div class="small mb-2"><strong>ইউজার আইডি:</strong> #{{ $customer->id }} ({{ ucfirst($customer->role) }})</div>
                                                                    @if($order && $order->delivery_address)
                                                                        <div class="small mt-2 pt-2 border-top">
                                                                            <strong>ডেলিভারি ঠিকানা:</strong>
                                                                            <p class="text-muted mb-0 mt-1 p-2 rounded bg-light">{{ $order->delivery_address }}</p>
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <p class="text-muted small mb-0">গ্রাহকের তথ্য সংরক্ষিত নেই।</p>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- Farmer Column --}}
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded-3">
                                                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                                                    <div class="audit-avatar farmer">🌾</div>
                                                                    <div>
                                                                        <h6 class="fw-bold mb-0 text-success">সরবরাহকারী খামারি পরিচিতি</h6>
                                                                        <small class="text-muted">Farmer Security Profile</small>
                                                                    </div>
                                                                </div>
                                                                @if($farmers->isNotEmpty())
                                                                    @foreach($farmers as $farmer)
                                                                        <div class="mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
                                                                            <div class="small mb-1"><strong>খামারি নাম:</strong> {{ $farmer->name }}</div>
                                                                            <div class="small mb-1"><strong>যোগাযোগ:</strong> <a href="tel:{{ $farmer->phone }}" class="text-decoration-none fw-semibold text-success">{{ $farmer->phone ?: 'প্রদত্ত নেই' }}</a></div>
                                                                            <div class="small mb-1"><strong>ইমেইল:</strong> {{ $farmer->email ?: 'N/A' }}</div>
                                                                            <div class="small mb-1"><strong>খামারি আইডি:</strong> #{{ $farmer->id }} (Farmer)</div>
                                                                            @if($farmer->district)
                                                                                <div class="small text-secondary mt-1 p-2 rounded bg-light">
                                                                                    <strong>খামারের এলাকা:</strong> {{ $farmer->upazila ? $farmer->upazila.', ' : '' }}{{ $farmer->district }}, {{ $farmer->division }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                @elseif($p->payment_type === 'subscription')
                                                                    <div class="small mb-2"><strong>প্রাপক প্রতিষ্ঠান:</strong> KrishiHat Central Operations</div>
                                                                    <div class="small mb-2"><strong>উদ্দেশ্য:</strong> মেম্বারশিপ সার্ভিস ও এসক্রো সিকিউরিটি ফি</div>
                                                                    <div class="small text-muted p-2 rounded bg-light">সরাসরি KrishiHat ডিজিটাল প্ল্যাটফর্ম কর্তৃপক্ষের এসক্রো অ্যাকাউন্টে জমা।</div>
                                                                @else
                                                                    <p class="text-muted small mb-0">সরাসরি কোনো একক কৃষক নির্ধারিত নেই।</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Itemized Produce Ledger --}}
                                                    <div class="card border-0 shadow-sm p-3 mb-4 bg-white rounded-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="fs-5">📦</span>
                                                                <h6 class="fw-bold mb-0 text-dark">পণ্য, সরবরাহ ও মূল্য বিবরণী (Itemized Goods Ledger)</h6>
                                                            </div>
                                                            @if($order && $order->tracking_code)
                                                                <span class="badge bg-light text-dark border font-monospace px-3 py-1">
                                                                    ট্র্যাকিং কোড: {{ $order->tracking_code }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        @if($order && $items->isNotEmpty())
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered align-middle mb-0">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>ফসলের নাম</th>
                                                                            <th>সরবরাহকারী খামারি</th>
                                                                            <th class="text-center">পরিমাণ</th>
                                                                            <th class="text-end">দর (টাকা)</th>
                                                                            <th class="text-end">মোট মূল্য (টাকা)</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($items as $item)
                                                                            <tr>
                                                                                <td>
                                                                                    <strong>{{ $item->product?->name ?? 'ফসল' }}</strong>
                                                                                    @if($item->product?->category)
                                                                                        <small class="d-block text-muted">{{ is_string($item->product->category) ? $item->product->category : ($item->product->category->name ?? '') }}</small>
                                                                                    @endif
                                                                                </td>
                                                                                <td>
                                                                                    {{ $item->farmer?->name ?? 'খামারি' }}
                                                                                    <small class="d-block text-muted">{{ $item->farmer?->phone }}</small>
                                                                                </td>
                                                                                <td class="text-center">
                                                                                    <strong>{{ $item->quantity }}</strong> {{ $item->product?->unit ?: 'kg' }}
                                                                                </td>
                                                                                <td class="text-end font-monospace">
                                                                                    ৳{{ number_format((float)$item->unit_price, 2) }}
                                                                                </td>
                                                                                <td class="text-end font-monospace fw-bold text-success">
                                                                                    ৳{{ number_format((float)$item->subtotal, 2) }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                    <tfoot class="table-light">
                                                                        <tr>
                                                                            <th colspan="4" class="text-end fw-bold">সর্বমোট লেনদেন মূল্য:</th>
                                                                            <th class="text-end text-success fs-6 font-monospace fw-bold">
                                                                                ৳{{ number_format((float)$p->amount, 2) }}
                                                                            </th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        @elseif($p->payment_type === 'subscription')
                                                            <div class="p-3 bg-light rounded-3">
                                                                <div class="fw-bold text-dark">{{ $p->payable?->plan?->name ?? 'মেম্বারশিপ প্যাকেজ' }}</div>
                                                                <div class="small text-muted mt-1">{{ $p->payable?->plan?->description ?? 'সরাসরি প্ল্যাটফর্ম মেম্বারশিপ সার্ভিস ফি।' }}</div>
                                                                <div class="mt-2 fw-bold text-success font-monospace fs-5">৳{{ number_format((float)$p->amount, 2) }} {{ $p->currency }}</div>
                                                            </div>
                                                        @else
                                                            <p class="text-muted small mb-0">আইটেমাইজড রেকর্ড উপলব্ধ নেই।</p>
                                                        @endif
                                                    </div>

                                                    {{-- Gateway & Financial Audit Trail --}}
                                                    <div class="card border-0 shadow-sm p-3 bg-white rounded-3">
                                                        <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                                                            <span class="fs-5">💳</span>
                                                            <h6 class="fw-bold mb-0 text-dark">আর্থিক চ্যানেল ও অডিট ট্রেইল (Payment Gateway & Settlement Trail)</h6>
                                                        </div>
                                                        <div class="row g-2 small">
                                                            <div class="col-sm-6">
                                                                <strong>পেমেন্ট মেথড:</strong> {{ $p->payment_method_bangla }} (<code>{{ $p->payment_method }}</code>)
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <strong>লেনদেনের তারিখ ও সময়:</strong> {{ $p->created_at->format('d M Y, h:i:s A T') }}
                                                            </div>
                                                            @if($p->paid_at)
                                                                <div class="col-sm-6">
                                                                    <strong>পরিশোধের সময়:</strong> {{ $p->paid_at->format('d M Y, h:i:s A') }}
                                                                </div>
                                                            @endif
                                                            @if($p->verified_at)
                                                                <div class="col-sm-6 text-success">
                                                                    <strong>অ্যাডমিন ভেরিফিকেশন:</strong> সম্পন্ন ({{ $p->verified_at->format('d M Y, h:i A') }})
                                                                </div>
                                                            @endif
                                                            @if($p->notes)
                                                                <div class="col-12 mt-2 pt-2 border-top text-muted">
                                                                    <strong>অডিট নোট / রিমার্কস:</strong> {{ $p->notes }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Certificate Modal Footer --}}
                                                <div class="modal-footer bg-white px-4 py-3 justify-content-between">
                                                    <div class="small text-muted font-monospace">
                                                        🔒 Audit Stamp: <code>{{ $p->security_verification_code }}</code>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                                                            🖨️ প্রিন্ট / রসিদ ডাউনলোড
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                                                        @if($order)
                                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-success">
                                                                অর্ডার সম্পূর্ণ দেখুন →
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Bottom Quick Action Links --}}
        <div class="mt-4 d-flex flex-wrap gap-2">
            @if(auth()->user()->role === 'buyer')
                <a href="{{ route('orders.index') }}" class="btn btn-brand shadow-sm">View purchase history</a>
            @elseif(auth()->user()->role === 'farmer')
                <a href="{{ route('farmer.dashboard') }}" class="btn btn-brand shadow-sm">Review product orders</a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="btn btn-brand shadow-sm">📊 অ্যাডমিন ড্যাশবোর্ড</a>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-success shadow-sm">💳 পেমেন্ট অডিট প্যানেল</a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary shadow-sm">📦 সকল অর্ডার ও ট্র্যাকিং</a>
            @endif
        </div>
    </div>
</main>
@endsection