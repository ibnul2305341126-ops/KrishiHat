@extends('layouts.app')
@section('title', 'কৃষক ভেরিফিকেশন বিস্তারিত | KrishiHat Admin')

@section('content')
<main class="dashboard-main py-4 py-lg-5">
    <div class="container" style="max-width: 1000px">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন ড্যাশবোর্ড</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.verifications.index') }}">কৃষক ভেরিফিকেশন</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
            </ol>
        </nav>

        @include('admin._nav')

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="status">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <span class="eyebrow text-uppercase text-success fw-bold">KYC DOCUMENT INSPECTION</span>
                <h1 class="dashboard-title h3 mb-1">{{ $user->name }} - ভেরিফিকেশন ফাইলসমূহ</h1>
                <p class="text-secondary mb-0">জাতীয় পরিচয়পত্র ও ট্রেড লাইসেন্স তথ্যাবলী নিবিড়ভাবে নিরীক্ষা করুন।</p>
            </div>
            <a href="{{ route('admin.verifications.index') }}" class="btn btn-outline-secondary">
                ← তালিকায় ফিরে যান
            </a>
        </div>

        <div class="row g-4 mb-4">
            {{-- Farmer Summary Card --}}
            <div class="col-md-5">
                <section class="dashboard-card p-4 shadow-sm bg-white rounded-3 border h-100">
                    <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                        <span>👤</span> খামারির পরিচয়
                    </h2>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><strong>নাম:</strong> {{ $user->name }}</li>
                        <li class="mb-2"><strong>ইমেইল:</strong> {{ $user->email }}</li>
                        <li class="mb-2"><strong>খামারের নাম:</strong> {{ $user->farm_name ?: 'উল্লেখ নেই' }}</li>
                        <li class="mb-2"><strong>যোগদানের তারিখ:</strong> {{ $user->created_at->format('d M, Y (h:i A)') }}</li>
                        <li class="mb-2"><strong>মোট পণ্য লিস্টিং:</strong> {{ $user->products->count() }} টি</li>
                    </ul>

                    <h3 class="h6 fw-bold mb-2">বর্তমান ভেরিফিকেশন স্ট্যাটাস:</h3>
                    @if($user->verification_status === 'verified')
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                            <span class="fs-4">✅</span>
                            <div>
                                <strong class="d-block">ভেরিফাইড কৃষক (অনুমোদিত)</strong>
                                <small>অনুমোদন তারিখ: {{ $user->farmer_verified_at?->format('d M, Y') }}</small>
                            </div>
                        </div>
                    @elseif($user->verification_status === 'pending')
                        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                            <span class="fs-4">⏳</span>
                            <div>
                                <strong class="d-block">যাচাইয়ের জন্য অপেক্ষমাণ</strong>
                                <small>জমা দেওয়ার সময়: {{ $user->verification_submitted_at?->diffForHumans() }}</small>
                            </div>
                        </div>
                    @elseif($user->verification_status === 'rejected')
                        <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
                            <span class="fs-4">❌</span>
                            <div>
                                <strong class="d-block">আবেদন প্রত্যাখ্যাত হয়েছে</strong>
                                <small class="d-block text-dark mt-1">কারণ: {{ $user->verification_rejection_reason }}</small>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-secondary d-flex align-items-center gap-2 mb-3">
                            <span class="fs-4">⚠️</span>
                            <div>
                                <strong class="d-block">ডকুমেন্টস জমা দেওয়া হয়নি</strong>
                            </div>
                        </div>
                    @endif

                    <div class="d-grid gap-2 mt-4 pt-3 border-top">
                        @if($user->verification_status !== 'verified')
                            <form action="{{ route('admin.verifications.approve', $user) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে ডকুমেন্টস যাচাই করে কৃষককে অনুমোদন করতে চান?');">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-xs">
                                    ✓ অনুমোদন ও ভেরিফাই করুন
                                </button>
                            </form>
                        @endif

                        <button type="button" class="btn btn-outline-danger w-100 py-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            ✕ আবেদন বাতিল করুন (Reject)
                        </button>
                    </div>
                </section>
            </div>

            {{-- Documents Verification Card --}}
            <div class="col-md-7">
                <section class="dashboard-card p-4 shadow-sm bg-white rounded-3 border h-100">
                    <h2 class="h5 fw-bold mb-4 d-flex align-items-center gap-2">
                        <span>🪪</span> জাতীয় পরিচয়পত্র (NID) ও ট্রেড লাইসেন্স
                    </h2>

                    {{-- NID Section --}}
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">১. জাতীয় পরিচয়পত্র (NID) নম্বর:</span>
                            <code class="fs-5 fw-bold text-primary">{{ $user->nid_number ?: 'প্রদান করা হয়নি' }}</code>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-sm-6">
                                <span class="small text-muted d-block mb-1">NID সামনের অংশ:</span>
                                @if($user->nid_front_path)
                                    <div class="border rounded p-2 text-center bg-light">
                                        <a href="{{ asset('storage/' . $user->nid_front_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $user->nid_front_path) }}" alt="NID Front" class="img-fluid rounded" style="max-height: 180px; object-fit: contain;">
                                        </a>
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $user->nid_front_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                🔍 বড় করে দেখুন
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="border rounded p-4 text-center text-muted bg-light">
                                        ছবি সংযুক্ত নেই
                                    </div>
                                @endif
                            </div>

                            <div class="col-sm-6">
                                <span class="small text-muted d-block mb-1">NID পেছনের অংশ:</span>
                                @if($user->nid_back_path)
                                    <div class="border rounded p-2 text-center bg-light">
                                        <a href="{{ asset('storage/' . $user->nid_back_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $user->nid_back_path) }}" alt="NID Back" class="img-fluid rounded" style="max-height: 180px; object-fit: contain;">
                                        </a>
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $user->nid_back_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                🔍 বড় করে দেখুন
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="border rounded p-4 text-center text-muted bg-light">
                                        ছবি সংযুক্ত নেই
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Trade License Section --}}
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">২. ট্রেড লাইসেন্স / কৃষি কার্ড নম্বর:</span>
                            <code class="fs-5 fw-bold text-success">{{ $user->trade_license_number ?: 'প্রদান করা হয়নি' }}</code>
                        </div>

                        <div class="mt-2">
                            <span class="small text-muted d-block mb-1">ট্রেড লাইসেন্স কপি:</span>
                            @if($user->trade_license_path)
                                <div class="border rounded p-3 text-center bg-light">
                                    <a href="{{ asset('storage/' . $user->trade_license_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $user->trade_license_path) }}" alt="Trade License" class="img-fluid rounded" style="max-height: 220px; object-fit: contain;">
                                    </a>
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/' . $user->trade_license_path) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                            🔍 সম্পূর্ণ ডকুমেন্ট দেখুন
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="border rounded p-4 text-center text-muted bg-light">
                                    ডকুমেন্ট সংযুক্ত নেই
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.verifications.reject', $user) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">ভেরিফিকেশন আবেদন বাতিল ({{ $user->name }})</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label fw-bold">বাতিল করার স্পষ্ট কারণ লিখুন <span class="text-danger">*</span></label>
                            <textarea name="reason" id="rejectReason" class="form-control" rows="4" placeholder="যেমন: NID কার্ডের ছবি অস্পষ্ট, ট্রেড লাইসেন্স মেয়াদোত্তীর্ণ ইত্যাদি..." required></textarea>
                            <small class="text-muted">এই কারণটি সরাসরি কৃষকের প্রোফাইল ও নোটিফিকেশনে প্রদর্শিত হবে।</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                        <button type="submit" class="btn btn-danger">বাতিল নিশ্চিত করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
