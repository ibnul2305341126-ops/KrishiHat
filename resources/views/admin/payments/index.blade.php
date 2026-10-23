@extends('layouts.app')
@section('title', 'পেমেন্ট ট্রানজেকশন ও গেটওয়ে মনিটরিং | KrishiHat Admin')

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

    {{-- Modernized Hero Header --}}
    <div class="audit-hero-banner p-4 p-md-5 mb-4 mb-lg-5">
        <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(56, 211, 135, 0.35); backdrop-filter: blur(8px);">
                    <span class="ticker-dot"></span>
                    <span class="small fw-bold text-light text-uppercase tracking-wider" style="letter-spacing: 0.08em; font-size: 0.76rem;">
                        GATEWAY MONITORING & PAYMENT ESCROW
                    </span>
                </div>
                <h1 class="h2 fw-extrabold text-white mb-2" style="font-size: clamp(1.8rem, 3.2vw, 2.5rem); letter-spacing: -0.03em;">
                    💳 পেমেন্ট গেটওয়ে ও অডিট মনিটরিং
                </h1>
                <p class="text-light text-opacity-90 mb-0 fs-6" style="max-width: 680px; line-height: 1.6;">
                    মার্কেটপ্লেস অর্ডার ও সাবস্ক্রিপশন পেমেন্টের সম্পূর্ণ অডিট ট্রেইল, গেটওয়ে কনফিগারেশন এবং সেটেলমেন্ট অবস্থা পর্যবেক্ষণ করুন।
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle p-2 px-3 rounded-pill fw-bold bg-white">
                        ✓ SSLCOMMERZ V4 Ready
                    </span>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle p-2 px-3 rounded-pill fw-bold bg-white">
                        ✓ Bank Escrow Active
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Gateway Status Card --}}
    <div class="card border-0 shadow-sm p-4 mb-4 bg-white rounded-4 border">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">গেটওয়ে প্রোভাইডার:</span>
                <strong class="d-block text-dark fs-6">SSLCOMMERZ Hosted & Embedded</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">পরিবেশ মোড:</span>
                <div>
                    @if(config('services.sslcommerz.is_sandbox'))
                        <span class="badge bg-warning text-dark py-1 px-2 rounded-pill fw-bold">SANDBOX / TEST MODE</span>
                    @else
                        <span class="badge bg-success py-1 px-2 rounded-pill fw-bold">LIVE PRODUCTION</span>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">সাপোর্টেড ডিজিটাল চ্যানেল:</span>
                <div class="small fw-bold text-dark">bKash, Nagad, Visa, Mastercard, Internet Banking</div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">স্টোর আইডি:</span>
                <code class="px-2 py-1 rounded bg-light border">{{ config('services.sslcommerz.store_id') ?: 'krishihatapplive (Demo)' }}</code>
            </div>
        </div>
    </div>

    {{-- Modern KPI Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="audit-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="audit-stat-icon success">৳</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle small fw-bold">সফল ভলিউম</span>
                </div>
                <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">মোট সফল নিষ্পত্তি</small>
                <h3 class="fw-extrabold mb-1 mt-1 text-dark" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                    ৳{{ number_format($stats['total_volume'], 2) }}
                </h3>
                <small class="text-muted mt-auto pt-2 border-top d-block">{{ $stats['completed'] }}টি সফল লেনদেন</small>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="audit-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="audit-stat-icon warning">⏳</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small fw-bold">অপেক্ষমান</span>
                </div>
                <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">যাচাইয়ের অপেক্ষায়</small>
                <h3 class="fw-extrabold mb-1 mt-1 text-warning" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                    {{ $stats['pending'] }}
                </h3>
                <small class="text-warning mt-auto pt-2 border-top d-block">ব্যাংক ও COD যাচাইকরণ বাকি</small>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="audit-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="audit-stat-icon primary">📦</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-bold">অর্ডার</span>
                </div>
                <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">মার্কেটপ্লেস অর্ডার</small>
                <h3 class="fw-extrabold mb-1 mt-1 text-primary" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                    {{ $stats['order_payments'] }}
                </h3>
                <small class="text-muted mt-auto pt-2 border-top d-block">ফসলের কেনাবেচা পেমেন্ট</small>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="audit-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="audit-stat-icon info">💎</span>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle small fw-bold">মেম্বারশিপ</span>
                </div>
                <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.05em;">সাবস্ক্রিপশন ফি</small>
                <h3 class="fw-extrabold mb-1 mt-1 text-info" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                    {{ $stats['subscription_payments'] }}
                </h3>
                <small class="text-muted mt-auto pt-2 border-top d-block">সদস্যপদ প্যাকেজ সংশ্লিষ্ট</small>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm p-3 mb-4 bg-white rounded-4 border">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm rounded-pill px-3" placeholder="ট্রানজেকশন ID বা ক্রেতার নাম..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="payment_type" class="form-select form-select-sm rounded-pill px-3">
                    <option value="">সকল পেমেন্ট ধরণ</option>
                    <option value="order" {{ request('payment_type') === 'order' ? 'selected' : '' }}>মার্কেটপ্লেস অর্ডার</option>
                    <option value="subscription" {{ request('payment_type') === 'subscription' ? 'selected' : '' }}>মেম্বারশিপ সাবস্ক্রিপশন</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_method" class="form-select form-select-sm rounded-pill px-3">
                    <option value="">সকল মেথড</option>
                    <option value="cod" {{ request('payment_method') === 'cod' ? 'selected' : '' }}>ক্যাশ অন ডেলিভারি (COD)</option>
                    <option value="bkash" {{ request('payment_method') === 'bkash' ? 'selected' : '' }}>বিকাশ (bKash)</option>
                    <option value="nagad" {{ request('payment_method') === 'nagad' ? 'selected' : '' }}>নগদ (Nagad)</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>কার্ড (Card)</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>ব্যাংক ট্রান্সফার</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm rounded-pill px-3">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>পরিশোধিত (Completed)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>অপেক্ষমান (Pending)</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>ব্যর্থ/বাতিল (Failed)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill fw-bold">ফিল্টার</button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">রিসেট</a>
            </div>
        </form>
    </div>

    {{-- Transactions Table Card --}}
    <div class="audit-table-card mb-4">
        <div class="p-3 px-4 border-bottom bg-white d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0 fw-bold text-dark">💳 গেটওয়ে ও ব্যাংক ট্রানজেকশন অডিট লেজার</h2>
            <span class="badge bg-light text-secondary border small px-3 py-1 rounded-pill">
                মোট রেকর্ড: {{ $payments->total() }}টি
            </span>
        </div>

        <div class="table-responsive">
            <table class="table audit-table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-4">ট্রানজেকশন ID ও সিকিউরিটি</th>
                        <th scope="col">গ্রাহক / ক্রেতা (Customer)</th>
                        <th scope="col">খামারি / বিক্রেতা (Farmer)</th>
                        <th scope="col">পণ্য ও উদ্দেশ্য (Produce)</th>
                        <th scope="col">পদ্ধতি</th>
                        <th scope="col">পরিমাণ</th>
                        <th scope="col">স্ট্যাটাস</th>
                        <th scope="col">তারিখ ও অডিট তথ্য</th>
                        <th scope="col" class="text-end pe-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        @php
                            $order = $payment->payable instanceof \App\Models\Order ? $payment->payable : null;
                            $customer = $payment->customer;
                            $farmers = $payment->farmers;
                            $items = $payment->order_items;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <strong class="font-monospace text-dark d-block">{{ $payment->transaction_id }}</strong>
                                <small class="audit-sec-code d-inline-block mt-1">🛡️ {{ $payment->security_verification_code }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="audit-avatar buyer">
                                        {{ mb_substr($customer?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark lh-sm">{{ $customer?->name ?? 'Deleted User' }}</div>
                                        <small class="text-muted d-block mt-1">📞 {{ $customer?->phone ?: 'N/A' }}</small>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small mt-1" style="font-size: 0.68rem;">
                                            {{ ucfirst($customer?->role ?? 'user') }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($order && $farmers->isNotEmpty())
                                    @foreach($farmers->take(2) as $farmer)
                                        <div class="d-flex align-items-center gap-2 {{ !$loop->last ? 'mb-2 pb-1 border-bottom border-light' : '' }}">
                                            <div class="audit-avatar farmer">
                                                {{ mb_substr($farmer->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-success lh-sm">{{ $farmer->name }}</div>
                                                <small class="text-muted d-block">📞 {{ $farmer->phone ?: 'N/A' }}</small>
                                                @if($farmer->district)
                                                    <small class="text-secondary d-block" style="font-size: 0.72rem;">📍 {{ $farmer->district }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($farmers->count() > 2)
                                        <small class="text-muted d-block mt-1">+ আরো {{ $farmers->count() - 2 }} খামারি</small>
                                    @endif
                                @elseif($payment->payment_type === 'subscription')
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="audit-avatar farmer" style="background: #eef2ff; color: #4338ca;">🌱</div>
                                        <div>
                                            <div class="fw-semibold text-dark lh-sm">KrishiHat প্ল্যাটফর্ম</div>
                                            <small class="text-muted">সদস্যপদ ফি</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($order && $items->isNotEmpty())
                                    @foreach($items->take(2) as $item)
                                        <div class="small fw-semibold text-dark">• {{ $item->product?->name ?? 'ফসল' }}</div>
                                        <small class="text-muted d-block">{{ $item->quantity }} {{ $item->product?->unit ?: 'kg' }} @ ৳{{ number_format((float)$item->unit_price, 2) }}</small>
                                    @endforeach
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1 py-1 px-2 fw-bold">Order #{{ $payment->payable_id }}</span>
                                @elseif($payment->payment_type === 'subscription')
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle py-1 px-2 fw-bold">Sub #{{ $payment->payable_id }}</span>
                                    <small class="d-block text-secondary mt-1">{{ $payment->payable?->plan?->name ?? 'মেম্বারশিপ' }}</small>
                                @else
                                    <small class="text-muted">সাধারণ পেমেন্ট</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase font-monospace py-1 px-2">{{ $payment->payment_method }}</span>
                                @if(!empty($payment->payment_details['bank_name']))
                                    <small class="d-block text-muted mt-1">{{ $payment->payment_details['bank_name'] }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="audit-amount-text">৳{{ number_format((float)$payment->amount, 2) }}</div>
                                <small class="text-muted d-block font-monospace" style="font-size: 0.72rem;">{{ $payment->currency }}</small>
                            </td>
                            <td>
                                @if($payment->status === 'completed' || $payment->status === 'successful')
                                    <span class="audit-pill-badge completed">✓ Completed</span>
                                @elseif($payment->status === 'pending')
                                    <span class="audit-pill-badge pending">⏳ Pending</span>
                                @elseif($payment->status === 'failed')
                                    <span class="audit-pill-badge failed">✗ Failed</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-bold text-dark">{{ $payment->created_at->format('d M Y, h:i A') }}</div>
                                @if($payment->verified_at)
                                    <small class="text-success d-block fw-semibold">ভেরিফাইড: {{ $payment->verified_at->format('d/m/Y') }}</small>
                                @endif
                                @if($payment->notes)
                                    <small class="text-muted d-block text-truncate mt-1" style="max-width: 170px;" title="{{ $payment->notes }}">{{ $payment->notes }}</small>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#adminSecModal{{ $payment->id }}" title="সিকিউরিটি রেকর্ড ও সম্পূর্ণ বিবরণ দেখুন">
                                        🛡️
                                    </button>

                                    @if($payment->status === 'pending')
                                        <form action="{{ route('admin.payments.verify', $payment) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success shadow-sm" title="পেমেন্ট প্রাপ্তি নিশ্চিত করুন">✓</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $payment->id }}" title="প্রত্যাখ্যান করুন">
                                            ✗
                                        </button>
                                    @else
                                        <span class="text-muted small ms-1">নিষ্পন্ন</span>
                                    @endif
                                </div>

                                {{-- Security Record Modal --}}
                                <div class="modal fade text-start" id="adminSecModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content audit-modal-cert overflow-hidden border-0">
                                            <div class="modal-header audit-cert-header px-4 py-3">
                                                <div>
                                                    <h5 class="modal-title fw-bold text-white mb-0">🛡️ অফিসিয়াল সিকিউরিটি অডিট ও ট্রেসেবিলিটি রেকর্ড</h5>
                                                    <small class="text-light text-opacity-75">লেনদেন সিকিউরিটি ট্রেইল ও ভেরিফিকেশন সার্টিফিকেট</small>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 bg-light audit-cert-watermark">
                                                <div class="card border-0 shadow-sm p-3 mb-3 bg-white rounded-3">
                                                    <div class="row g-2 small">
                                                        <div class="col-sm-6"><strong>ট্রানজেকশন ID:</strong> <code class="fw-bold fs-6">{{ $payment->transaction_id }}</code></div>
                                                        <div class="col-sm-6"><strong>সিকিউরিটি কোড:</strong> <code class="text-primary fs-6">{{ $payment->security_verification_code }}</code></div>
                                                        <div class="col-sm-6"><strong>মোট লেনদেন:</strong> <strong class="text-success fs-5">৳{{ number_format((float)$payment->amount, 2) }} {{ $payment->currency }}</strong></div>
                                                        <div class="col-sm-6"><strong>পেমেন্ট মাধ্যম:</strong> {{ $payment->payment_method_bangla }}</div>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded-3">
                                                            <h6 class="fw-bold text-dark border-bottom pb-2">👤 গ্রাহক / ক্রেতা পরিচিতি</h6>
                                                            <div class="small mb-1"><strong>নাম:</strong> {{ $customer?->name }}</div>
                                                            <div class="small mb-1"><strong>ফোন:</strong> <a href="tel:{{ $customer?->phone }}" class="text-decoration-none fw-semibold">{{ $customer?->phone ?: 'N/A' }}</a></div>
                                                            <div class="small mb-1"><strong>ইমেইল:</strong> {{ $customer?->email ?: 'N/A' }}</div>
                                                            @if($order && $order->delivery_address)
                                                                <div class="small mt-2 pt-2 border-top"><strong>ডেলিভারি ঠিকানা:</strong> <span class="text-muted d-block mt-1 p-2 rounded bg-light">{{ $order->delivery_address }}</span></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm p-3 h-100 bg-white rounded-3">
                                                            <h6 class="fw-bold text-success border-bottom pb-2">🌾 সরবরাহকারী খামারি পরিচিতি</h6>
                                                            @if($farmers->isNotEmpty())
                                                                @foreach($farmers as $farmer)
                                                                    <div class="small mb-2">
                                                                        <div><strong>নাম:</strong> {{ $farmer->name }}</div>
                                                                        <div><strong>ফোন:</strong> <a href="tel:{{ $farmer->phone }}" class="text-decoration-none fw-semibold text-success">{{ $farmer->phone ?: 'N/A' }}</a></div>
                                                                        <div class="text-muted"><strong>ঠিকানা:</strong> {{ $farmer->upazila ? $farmer->upazila.', ' : '' }}{{ $farmer->district }}</div>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <p class="text-muted small mb-0">সরাসরি কৃষক প্রযোজ্য নয় (প্ল্যাটফর্ম সাবস্ক্রিপশন)।</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($order && $items->isNotEmpty())
                                                    <div class="card border-0 shadow-sm p-3 bg-white rounded-3">
                                                        <h6 class="fw-bold text-dark border-bottom pb-2">📦 পণ্য ও মূল্য বিবরণী</h6>
                                                        <table class="table table-sm table-bordered align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>ফসল</th>
                                                                    <th>খামারি</th>
                                                                    <th class="text-center">পরিমাণ</th>
                                                                    <th class="text-end">দর</th>
                                                                    <th class="text-end">মোট</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($items as $item)
                                                                    <tr>
                                                                        <td><strong>{{ $item->product?->name }}</strong></td>
                                                                        <td>{{ $item->farmer?->name }}</td>
                                                                        <td class="text-center fw-bold">{{ $item->quantity }} {{ $item->product?->unit ?: 'kg' }}</td>
                                                                        <td class="text-end font-monospace">৳{{ number_format((float)$item->unit_price, 2) }}</td>
                                                                        <td class="text-end font-monospace fw-bold text-success">৳{{ number_format((float)$item->subtotal, 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-white">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();">🖨️ প্রিন্ট</button>
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                                                @if($order)
                                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary">অর্ডার বিবরণী →</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reject Modal --}}
                                <div class="modal fade text-start" id="rejectModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.payments.reject', $payment) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">পেমেন্ট প্রত্যাখ্যান: {{ $payment->transaction_id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">প্রত্যাখ্যানের সুনির্দিষ্ট কারণ *</label>
                                                        <textarea name="reason" class="form-control" rows="3" placeholder="যেমন: ভুয়া ব্যাংক ট্রানজেকশন রেফারেন্স বা একাউন্টে অর্থ জমা হয়নি।" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                                                    <button type="submit" class="btn btn-danger">প্রত্যাখ্যান নিশ্চিত করুন</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">কোনো পেমেন্ট রেকর্ড পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="p-3 border-top bg-white">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
