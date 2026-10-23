@extends('layouts.app')
@section('title', 'Proposed plans & Membership Criteria | KrishiHat')

@section('content')
<main class="dashboard-main feature-preview-page">
    <div class="container py-4 py-lg-5">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Marketplace</a></li>
                <li class="breadcrumb-item active" aria-current="page">Plans</li>
            </ol>
        </nav>

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

        {{-- Active Subscription Banner for Authenticated Users --}}
        @auth
            @php
                $user = auth()->user();
                $curPlan = $user->currentPlan();
                $activeSub = $user->activeSubscription();
            @endphp
            <div class="card border-0 shadow-sm p-4 mb-5 bg-white border-start border-4 border-success">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle mb-1">আপনার বর্তমান স্ট্যাটাস</span>
                        <h3 class="h4 fw-bold mb-1">{{ $curPlan->name }}</h3>
                        <p class="text-secondary small mb-0">
                            সক্রিয় লিস্টিং অনুমতি: <strong>{{ $user->products()->count() }} / {{ $user->activeListingLimit() }}টি</strong> | 
                            ভূমিকা: <strong>{{ $user->role === 'farmer' ? 'খামারি (Farmer)' : 'ক্রেতা (Buyer)' }}</strong> |
                            মেয়াদ: <strong>{{ $activeSub && $activeSub->expires_at ? $activeSub->expires_at->format('d M Y') : 'আজীবন' }}</strong>
                        </p>
                    </div>
                    <div>
                        <a href="#active-plans-section" class="btn btn-outline-success">
                            🔄 প্ল্যান পরিবর্তন / আপগ্রেড
                        </a>
                    </div>
                </div>
            </div>
        @endauth

        {{-- Required Preview Screen Header (Preserved for tests & clarity) --}}
        <div class="text-center feature-preview-heading">
            <span class="eyebrow">PROPOSED PLAN PRICING</span>
            <h1 class="dashboard-title mt-2">Subscription plans</h1>
            <p class="text-secondary">These are proposed display prices only. Plans, billing and payment verification are not configured.</p>
        </div>

        <div class="row g-4 justify-content-center mt-2">
            <div class="col-md-6 col-xl-4">
                <article class="proposed-plan-card">
                    <span class="badge text-bg-light border">Proposed</span>
                    <h2>Free Farmer</h2>
                    <p class="proposed-plan-price">৳0</p>
                    <p class="text-secondary">A proposed no-cost plan. No plan entitlements are currently configured.</p>
                    <button class="btn btn-outline-secondary w-100" type="button" disabled>Not available</button>
                </article>
            </div>
            <div class="col-md-6 col-xl-4">
                <article class="proposed-plan-card proposed-plan-featured">
                    <span class="badge text-bg-success">Proposed</span>
                    <h2>Growth Agro</h2>
                    <p class="proposed-plan-price">৳499 <small>/ month</small></p>
                    <p class="text-secondary">A proposed monthly price. No subscription or billing workflow is connected.</p>
                    <button class="btn btn-outline-secondary w-100" type="button" disabled>Not available</button>
                </article>
            </div>
            <div class="col-md-6 col-xl-4">
                <article class="proposed-plan-card">
                    <span class="badge text-bg-light border">Proposed</span>
                    <h2>AgriEnterprise</h2>
                    <p class="proposed-plan-price">৳1,299 <small>/ month</small></p>
                    <p class="text-secondary">A proposed monthly price. No subscription or billing workflow is connected.</p>
                    <button class="btn btn-outline-secondary w-100" type="button" disabled>Not available</button>
                </article>
            </div>
        </div>

        <p class="preview-note mt-4">
            <span aria-hidden="true">ⓘ</span> No payment will be collected from this preview. KrishiHat has no configured plan records, billing integration, or payment confirmation workflow.
        </p>

        {{-- Phase 2 Live Configurable Membership & Entitlements Section --}}
        <hr class="my-5">

        <div id="active-plans-section" class="pt-2">
            <div class="text-center mb-4">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 mb-2">Phase 2 Membership Architecture</span>
                <h2 class="h3 fw-bold">সক্রিয় মেম্বারশিপ প্ল্যান ও সাবস্ক্রিপশন সুবিধা</h2>
                <p class="text-secondary small mx-auto" style="max-width: 650px;">
                    প্রশাসক কর্তৃক নির্ধারিত প্রকৃত ডেটাবেস প্ল্যান। আপনি প্রয়োজন অনুযায়ী সাবস্ক্রিপশন সক্রিয় করতে পারেন।
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                @if(isset($plans) && $plans->isNotEmpty())
                    @foreach($plans as $plan)
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm p-4 {{ $plan->is_featured ? 'border-top border-4 border-success' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="fw-bold mb-0">{{ $plan->name }}</h4>
                                    @if($plan->is_demo)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Demo</span>
                                    @endif
                                </div>
                                <div class="my-2">
                                    <span class="display-6 fw-bold text-success">৳{{ number_format((float)$plan->price, 0) }}</span>
                                    <span class="text-muted small">/ {{ $plan->duration_days }} দিন</span>
                                </div>
                                <p class="text-secondary small">{{ $plan->description }}</p>

                                <ul class="list-unstyled small mb-4 text-muted">
                                    <li class="py-1 border-bottom">✓ <strong>{{ $plan->active_listing_limit }}টি</strong> সক্রিয় শস্য লিস্টিং সীমা</li>
                                    <li class="py-1 border-bottom">✓ ভূমিকা শর্ত: <strong>{{ $plan->role_eligibility === 'farmer' ? 'খামারি' : ($plan->role_eligibility === 'buyer' ? 'ক্রেতা' : 'সকলের জন্য') }}</strong></li>
                                    <li class="py-1 border-bottom">✓ বিক্রয় অ্যানালিটিক্স: <strong>{{ $plan->can_view_analytics ? 'অন্তর্ভুক্ত' : 'সীমিত' }}</strong></li>
                                    <li class="py-1">✓ বাল্ক পারচেজ ফিচার: <strong>{{ $plan->can_bulk_purchase ? 'অন্তর্ভুক্ত' : 'না' }}</strong></li>
                                </ul>

                                @auth
                                    @if((float)$plan->price <= 0)
                                        <form action="{{ route('plans.subscribe', $plan) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success w-100">
                                                বিনামূল্যে সক্রিয় করুন
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-brand w-100" data-bs-toggle="modal" data-bs-target="#subModal{{ $plan->id }}">
                                            সাবস্ক্রাইব করুন
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
                                        সাবস্ক্রাইব করতে লগইন করুন
                                    </a>
                                @endauth
                            </div>
                        </div>

                        {{-- Modal for paid plan --}}
                        @auth
                            <div class="modal fade" id="subModal{{ $plan->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('plans.subscribe', $plan) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">সাবস্ক্রিপশন নিশ্চিতকরণ: {{ $plan->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="p-3 bg-light rounded mb-3">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>প্ল্যানের নাম:</span>
                                                        <strong>{{ $plan->name }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>মূল্য:</span>
                                                        <strong class="text-success">৳{{ number_format((float)$plan->price, 2) }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>মেয়াদ:</span>
                                                        <strong>{{ $plan->duration_days }} দিন</strong>
                                                    </div>
                                                </div>

                                                <label class="form-label fw-bold mb-2">পেমেন্ট মেথড নির্বাচন করুন</label>
                                                <div class="list-group mb-3">
                                                    <label class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                                        <input class="form-check-input mt-0" type="radio" name="payment_method" value="bkash" checked>
                                                        <div>
                                                            <strong>📱 বিকাশ (bKash)</strong>
                                                            <small class="text-muted d-block">SSLCOMMERZ / Sandbox সিমুলেটর</small>
                                                        </div>
                                                    </label>
                                                    <label class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                                        <input class="form-check-input mt-0" type="radio" name="payment_method" value="nagad">
                                                        <div>
                                                            <strong>📲 নগদ (Nagad)</strong>
                                                            <small class="text-muted d-block">SSLCOMMERZ / Sandbox সিমুলেটর</small>
                                                        </div>
                                                    </label>
                                                    <label class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                                        <input class="form-check-input mt-0" type="radio" name="payment_method" value="card">
                                                        <div>
                                                            <strong>💳 ভিসা / মাস্টারকার্ড (Card)</strong>
                                                            <small class="text-muted d-block">SSLCOMMERZ Secure Gateway</small>
                                                        </div>
                                                    </label>
                                                    <label class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                                        <input class="form-check-input mt-0" type="radio" name="payment_method" value="bank_transfer">
                                                        <div>
                                                            <strong>🏦 ব্যাংক ট্রান্সফার (Manual Escrow)</strong>
                                                            <small class="text-muted d-block">অগ্রণী ব্যাংক একাউন্টে ডিপোজিট স্লিপ জমা দিন</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                                                <button type="submit" class="btn btn-success">পেমেন্ট ও সাবস্ক্রিপশন এগিয়ে নিন</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endauth
                    @endforeach
                @endif
            </div>

            {{-- Documented Rules Breakdown --}}
            <div class="card border-0 shadow-sm p-4 mt-5 bg-light">
                <h4 class="h5 fw-bold mb-3">📋 মেম্বারশিপ নীতিমালা ও সাবস্ক্রিপশন শর্তাবলী (Eligibility & Rules)</h4>
                <div class="row g-3 small text-secondary">
                    <div class="col-md-6">
                        <strong>১. যোগ্যতা ও ভূমিকা:</strong>
                        <p class="mb-2">ফ্রি প্ল্যান সকল সাধারণ ব্যবহারকারীর জন্য উন্মুক্ত। Farmer Pro প্ল্যান শুধুমাত্র কৃষক/খামারি অ্যাকাউন্টের জন্য এবং Business/Wholesale প্ল্যান পাইকারি ক্রেতা ও বৃহৎ এগ্রি-এন্টারপ্রাইজের জন্য প্রযোজ্য।</p>
                        <strong>২. লিস্টিং ও সীমা প্রয়োগ:</strong>
                        <p class="mb-0">সার্ভার-সাইডে সক্রিয় লিস্টিং সীমা প্রয়োগ করা হয়। অনুমোদিত সীমার অতিরিক্ত নতুন পণ্য যোগ করতে প্ল্যান আপগ্রেড আবশ্যক।</p>
                    </div>
                    <div class="col-md-6">
                        <strong>৩. সাবস্ক্রিপশন স্ট্যাটাস সাইকেল:</strong>
                        <p class="mb-2">সাবস্ক্রিপশন <em>Active</em>, <em>Pending</em> (পেমেন্ট ভেরিফিকেশন সাপেক্ষে), <em>Expired</em> এবং <em>Cancelled</em> অবস্থায় থাকতে পারে। মেয়াদ উত্তীর্ণ হলে অ্যাকাউন্ট স্বয়ংক্রিয়ভাবে বেসিক ফ্রি ফিচারে পরিবর্তিত হয়।</p>
                        <strong>৪. রিফান্ড ও অডিট ট্রেইল:</strong>
                        <p class="mb-0">সকল আর্থিক লেনদেনের জন্য পৃথক ইউনিক ট্রানজেকশন আইডি এবং অ্যাডমিন অডিট ট্রেইল ডেটাবেসে সংরক্ষিত হয়।</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
