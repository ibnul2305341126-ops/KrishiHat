@extends('layouts.app')
@section('title', 'Profile | KrishiHat')

@section('content')
<main class="dashboard-main py-4 py-lg-5">
    <div class="container" style="max-width:960px">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profile</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <span class="eyebrow text-uppercase text-success fw-bold">ACCOUNT SETTINGS & PROFILE</span>
                <h1 class="dashboard-title mb-1">আপনার প্রোফাইল (Profile)</h1>
                <p class="text-secondary mb-0">আপনার ব্যক্তিগত তথ্য, অ্যাকাউন্ট রোল ও নিরাপত্তা ব্যবস্থাপনা</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('addresses.index') }}" class="btn btn-outline-success d-inline-flex align-items-center gap-1 shadow-xs">
                    <span>📍</span>
                    <span>ডেলিভারি ঠিকানা</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger d-inline-flex align-items-center gap-2 shadow-xs">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>লগ আউট (Log Out)</span>
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="status">
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
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Profile Information Card --}}
        <section class="dashboard-card p-4 p-md-5 mb-4 shadow-sm bg-white rounded-3 border">
            <h2 class="h5 fw-bold mb-4 d-flex align-items-center gap-2">
                <span>👤</span> ব্যক্তিগত তথ্য সম্পাদনা
            </h2>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    @if($user->profile_photo_path)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Profile photo" class="rounded-circle border" width="96" height="96" style="object-fit:cover">
                        </div>
                    @endif
                    @if($user->role === 'farmer')
                        <label class="form-label fw-semibold" for="profile_photo">খামারির প্রোফাইল ছবি (Profile Photo)</label>
                        <input id="profile_photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="form-control">
                        <small class="text-muted">JPG, PNG বা WebP ফরম্যাট (সর্বোচ্চ ৫ মেগাবাইট)</small>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="name">পূর্ণ নাম (Name)</label>
                        <input class="form-control form-control-lg" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="email">ইমেইল ঠিকানা (Email)</label>
                        <input class="form-control form-control-lg" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="255" required>
                    </div>
                </div>

                <div class="mt-3 mb-4 pt-3 border-top d-flex align-items-center gap-2">
                    <span class="text-secondary">অ্যাকাউন্টের ধরন (Account Role):</span>
                    @php
                        $roleBadgeClasses = [
                            'farmer' => 'bg-success text-white',
                            'buyer' => 'bg-primary text-white',
                            'admin' => 'bg-danger text-white',
                        ];
                        $roleNames = [
                            'farmer' => '👨‍🌾 খামারি / বিক্রেতা (Farmer)',
                            'buyer' => '🛒 পাইকারি ক্রেতা (Wholesale Buyer)',
                            'admin' => '🛡️ সিস্টেম অ্যাডমিন (Administrator)',
                        ];
                    @endphp
                    <span class="badge {{ $roleBadgeClasses[$user->role] ?? 'bg-secondary text-white' }} px-3 py-2 fs-6">
                        {{ $roleNames[$user->role] ?? ucfirst($user->role) }}
                    </span>
                </div>

                <button class="btn btn-brand px-4 py-2 fw-semibold" type="submit">
                    তথ্য সংরক্ষণ করুন (Save Profile)
                </button>
            </form>
        </section>

        {{-- Farmer Verification Card (NID & Trade License) --}}
        @if($user->role === 'farmer')
            <section class="dashboard-card p-4 p-md-5 mb-4 shadow-sm bg-white rounded-3 border" id="farmer-verification">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <span class="eyebrow text-uppercase text-success fw-bold">KYC & COMPLIANCE</span>
                        <h2 class="h5 fw-bold mb-1 d-flex align-items-center gap-2">
                            <span>🪪</span> কৃষক প্রোফাইল ভেরিফিকেশন (NID ও ট্রেড লাইসেন্স)
                        </h2>
                        <p class="text-secondary small mb-0">সরকারি নিয়ম ও প্ল্যাটফর্মের নিরাপত্তার স্বার্থে NID এবং ট্রেড লাইসেন্স যাচাই ছাড়া পণ্য বেচাকেনা করা যায় না।</p>
                    </div>
                    <div>
                        @if($user->verification_status === 'verified')
                            <span class="badge bg-success px-3 py-2 fs-6">✅ ভেরিফাইড খামারি</span>
                        @elseif($user->verification_status === 'pending')
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6">⏳ যাচাই অপেক্ষমাণ</span>
                        @elseif($user->verification_status === 'rejected')
                            <span class="badge bg-danger px-3 py-2 fs-6">❌ আবেদন বাতিল</span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 fs-6">⚠️ অযাচাইকৃত</span>
                        @endif
                    </div>
                </div>

                @if($user->verification_status === 'verified')
                    <div class="alert alert-success d-flex align-items-center gap-3 mb-4">
                        <span class="fs-2">🎉</span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">আপনার প্রোফাইল সফলভাবে ভেরিফাই করা হয়েছে!</h3>
                            <p class="mb-0 small">আপনার জাতীয় পরিচয়পত্র ও ট্রেড লাইসেন্স অ্যাডমিন প্যানেল কর্তৃক অনুমোদিত। আপনি এখন নিয়মিত পণ্য আপলোড, অফার গ্রহণ ও বিক্রি করতে পারবেন।</p>
                        </div>
                    </div>

                    <div class="row g-3 bg-light p-3 rounded-3 border mb-3">
                        <div class="col-md-4">
                            <span class="small text-muted d-block">খামার / প্রতিষ্ঠান:</span>
                            <strong>{{ $user->farm_name ?: 'ব্যক্তিগত খামার' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="small text-muted d-block">NID নম্বর:</span>
                            <code class="text-primary fw-bold">{{ $user->nid_number }}</code>
                        </div>
                        <div class="col-md-4">
                            <span class="small text-muted d-block">ট্রেড লাইসেন্স / কার্ড:</span>
                            <code class="text-success fw-bold">{{ $user->trade_license_number }}</code>
                        </div>
                    </div>
                @elseif($user->verification_status === 'pending')
                    <div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
                        <span class="fs-2">⏳</span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">আপনার আবেদনটি অ্যাডমিন প্যানেলে যাচাই-বাছাইয়ের জন্য অপেক্ষমাণ রয়েছে।</h3>
                            <p class="mb-0 small">অ্যাডমিন কর্তৃক NID ও ট্রেড লাইসেন্স যাচাই শেষ হলেই পণ্য বেচাকেনার সকল ফিচার সক্রিয় হয়ে যাবে।</p>
                            @if($user->verification_submitted_at)
                                <small class="text-muted d-block mt-1">জমা দেওয়ার সময়: {{ $user->verification_submitted_at->format('d M, Y (h:i A)') }}</small>
                            @endif
                        </div>
                    </div>
                @elseif($user->verification_status === 'rejected')
                    <div class="alert alert-danger d-flex align-items-start gap-3 mb-4">
                        <span class="fs-2">❌</span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">আপনার ভেরিফিকেশন আবেদনটি বাতিল করা হয়েছে!</h3>
                            <p class="mb-1 small"><strong>কারণ:</strong> {{ $user->verification_rejection_reason ?: 'ডকুমেন্টস অস্পষ্ট বা ত্রুটিপূর্ণ।' }}</p>
                            <small class="text-muted">অনুগ্রহ করে নিচের ফর্মের মাধ্যমে সঠিক ও স্পষ্ট ডকুমেন্টস দিয়ে পুনরায় আবেদন করুন।</small>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
                        <span class="fs-2">ℹ️</span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">পণ্য আপলোড ও বিক্রয় করতে প্রোফাইল ভেরিফাই করুন</h3>
                            <p class="mb-0 small">KrishiHat-এ অনিবন্ধিত বা ভূয়া পণ্য বিক্রি রোধে জাতীয় পরিচয়পত্র (NID) ও ট্রেড লাইসেন্স বা কৃষি কার্ডের তথ্য প্রদান বাধ্যতামূলক।</p>
                        </div>
                    </div>
                @endif

                @if($user->verification_status !== 'verified')
                    <form action="{{ route('profile.farmer-verification') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="farm_name" class="form-label fw-semibold">খামার বা প্রতিষ্ঠানের নাম (Farm / Business Name)</label>
                                <input type="text" id="farm_name" name="farm_name" class="form-control" value="{{ old('farm_name', $user->farm_name) }}" placeholder="যেমন: মা এগ্রো ফার্ম, বগুড়া" maxlength="255">
                            </div>

                            <div class="col-md-6">
                                <label for="nid_number" class="form-label fw-semibold">জাতীয় পরিচয়পত্র (NID) নম্বর <span class="text-danger">*</span></label>
                                <input type="text" id="nid_number" name="nid_number" class="form-control" value="{{ old('nid_number', $user->nid_number) }}" placeholder="১০, ১৩ অথবা ১৭ ডিজিটের NID নম্বর" required maxlength="50">
                            </div>

                            <div class="col-md-6">
                                <label for="trade_license_number" class="form-label fw-semibold">ট্রেড লাইসেন্স বা কৃষি কার্ড নম্বর <span class="text-danger">*</span></label>
                                <input type="text" id="trade_license_number" name="trade_license_number" class="form-control" value="{{ old('trade_license_number', $user->trade_license_number) }}" placeholder="যেমন: TRAD/BOG/2026/0412" required maxlength="100">
                            </div>

                            <div class="col-md-6">
                                <label for="nid_front_photo" class="form-label fw-semibold">NID কার্ডের সামনের অংশ (Front Photo)</label>
                                <input type="file" id="nid_front_photo" name="nid_front_photo" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf">
                                <small class="text-muted">JPG, PNG, WebP বা PDF (সর্বোচ্চ ৫ MB)</small>
                                @if($user->nid_front_path)
                                    <div class="mt-1"><span class="badge bg-light text-dark border">সংযুক্ত আছে: {{ basename($user->nid_front_path) }}</span></div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="nid_back_photo" class="form-label fw-semibold">NID কার্ডের পেছনের অংশ (Back Photo)</label>
                                <input type="file" id="nid_back_photo" name="nid_back_photo" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf">
                                <small class="text-muted">JPG, PNG, WebP বা PDF (সর্বোচ্চ ৫ MB)</small>
                                @if($user->nid_back_path)
                                    <div class="mt-1"><span class="badge bg-light text-dark border">সংযুক্ত আছে: {{ basename($user->nid_back_path) }}</span></div>
                                @endif
                            </div>

                            <div class="col-12">
                                <label for="trade_license_doc" class="form-label fw-semibold">ট্রেড লাইসেন্স বা কৃষি সনদপত্রের ছবি/ডকুমেন্ট</label>
                                <input type="file" id="trade_license_doc" name="trade_license_doc" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf">
                                <small class="text-muted">ট্রেড লাইসেন্স, ডিএই কৃষক কার্ড বা ইউনিয়ন পরিষদের ছাড়পত্র (সর্বোচ্চ ৫ MB)</small>
                                @if($user->trade_license_path)
                                    <div class="mt-1"><span class="badge bg-light text-dark border">সংযুক্ত আছে: {{ basename($user->trade_license_path) }}</span></div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-brand px-4 py-2 fw-semibold">
                                🪪 ভেরিফিকেশন আবেদন জমা দিন
                            </button>
                        </div>
                    </form>
                @endif
            </section>
        @endif

        {{-- Fast Demo Account Switcher --}}
        <section class="dashboard-card p-4 p-md-5 mb-4 shadow-sm bg-white rounded-3 border">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1 d-flex align-items-center gap-2">
                        <span>🔄</span> দ্রুত অ্যাকাউন্ট পরিবর্তন (Switch Account)
                    </h2>
                    <p class="text-secondary small mb-0">বিশ্ববিদ্যালয় ডেমো উপস্থাপনার জন্য এক ক্লিকে যেকোনো টেস্ট অ্যাকাউন্টে সুইচ করুন</p>
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1">
                    Demo Mode Active
                </span>
            </div>

            <div class="row g-3">
                {{-- 1. Farmer Mokbul --}}
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100 {{ $user->email === 'farmer.mokbul@demo.krishihat.test' ? 'border-success bg-success-subtle bg-opacity-25' : 'bg-light' }}">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fs-4">👨‍🌾</span>
                            <div>
                                <strong class="d-block text-dark">মোঃ মোকবুল হোসেন</strong>
                                <span class="badge bg-success text-white small">খামারি (Farmer)</span>
                            </div>
                        </div>
                        <p class="small text-muted mb-2 font-monospace" style="font-size: 0.8rem;">farmer.mokbul@demo.krishihat.test</p>
                        <p class="small text-secondary mb-3">বগুড়ার আলু ও ধান উৎপাদক খামারি। ফসল আপলোড ও অর্ডার প্রসেসিং করতে পারেন।</p>
                        @if($user->email === 'farmer.mokbul@demo.krishihat.test')
                            <button class="btn btn-sm btn-outline-success w-100 disabled" disabled>
                                ✓ বর্তমান অ্যাকাউন্ট (Active)
                            </button>
                        @else
                            <form action="{{ route('switch-account') }}" method="POST">
                                @csrf
                                <input type="hidden" name="target_role" value="farmer">
                                <button type="submit" class="btn btn-sm btn-success w-100 fw-semibold">
                                    খামারি অ্যাকাউন্টে সুইচ করুন ➔
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- 2. Buyer Karim --}}
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100 {{ $user->email === 'buyer.karim@demo.krishihat.test' ? 'border-primary bg-primary-subtle bg-opacity-25' : 'bg-light' }}">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fs-4">🛒</span>
                            <div>
                                <strong class="d-block text-dark">করিম ট্রেডার্স</strong>
                                <span class="badge bg-primary text-white small">ক্রেতা (Buyer)</span>
                            </div>
                        </div>
                        <p class="small text-muted mb-2 font-monospace" style="font-size: 0.8rem;">buyer.karim@demo.krishihat.test</p>
                        <p class="small text-secondary mb-3">কারওয়ান বাজারের পাইকারি আড়তদার। কার্ট, চেকআউট, দরদাম ও ট্র্যাকিং দেখতে পারেন।</p>
                        @if($user->email === 'buyer.karim@demo.krishihat.test')
                            <button class="btn btn-sm btn-outline-primary w-100 disabled" disabled>
                                ✓ বর্তমান অ্যাকাউন্ট (Active)
                            </button>
                        @else
                            <form action="{{ route('switch-account') }}" method="POST">
                                @csrf
                                <input type="hidden" name="target_role" value="buyer">
                                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                                    ক্রেতা অ্যাকাউন্টে সুইচ করুন ➔
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- 3. Super Admin --}}
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100 {{ $user->email === 'admin@demo.krishihat.test' ? 'border-danger bg-danger-subtle bg-opacity-25' : 'bg-light' }}">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fs-4">🛡️</span>
                            <div>
                                <strong class="d-block text-dark">সুপার অ্যাডমিন</strong>
                                <span class="badge bg-danger text-white small">অ্যাডমিন (Admin)</span>
                            </div>
                        </div>
                        <p class="small text-muted mb-2 font-monospace" style="font-size: 0.8rem;">admin@demo.krishihat.test</p>
                        <p class="small text-secondary mb-3">প্ল্যাটফর্ম প্রশাসক। ব্যবহারকারী, ক্যাটাগরি, প্রোডাক্ট, অর্ডার ও অফার নিয়ন্ত্রণ করতে পারেন।</p>
                        @if($user->email === 'admin@demo.krishihat.test')
                            <button class="btn btn-sm btn-outline-danger w-100 disabled" disabled>
                                ✓ বর্তমান অ্যাকাউন্ট (Active)
                            </button>
                        @elseif($user->role === 'admin')
                            <form action="{{ route('switch-account') }}" method="POST">
                                @csrf
                                <input type="hidden" name="target_role" value="admin">
                                <button type="submit" class="btn btn-sm btn-danger w-100 fw-semibold">
                                    অ্যাডমিন অ্যাকাউন্টে সুইচ করুন ➔
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger w-100 fw-semibold" title="অ্যাডমিন অ্যাক্সেসের জন্য পাসওয়ার্ড দিয়ে লগইন করুন">
                                🔒 অ্যাডমিন অ্যাকাউন্টে লগইন
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Contact or Delivery Snapshot Details --}}
        @if($user->role === 'farmer')
            <section class="dashboard-card p-4 mb-4 shadow-sm bg-white rounded-3 border">
                <h2 class="h5 fw-bold mb-2">খামারির বিক্রয় ও যোগাযোগ বিবরণ</h2>
                <p class="text-secondary mb-3">মার্কেটপ্লেসে ক্রেতারা সরাসরি আপনার দেওয়া যোগাযোগের মাধ্যমে যোগাযোগ করবেন।</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('farmer.dashboard') }}" class="btn btn-outline-success">
                        🌾 খামারি ড্যাশবোর্ড ও ফসল তালিকা
                    </a>
                    <a href="{{ route('products.create') }}" class="btn btn-success">
                        + নতুন ফসল আপলোড করুন
                    </a>
                </div>
            </section>
        @elseif($latestOrder)
            <section class="dashboard-card p-4 mb-4 shadow-sm bg-white rounded-3 border">
                <h2 class="h5 fw-bold mb-2">সাম্প্রতিক ডেলিভারি ঠিকানা (Last Delivery Address Snapshot)</h2>
                <p class="mb-1 text-dark">📍 <strong>{{ $latestOrder->delivery_address }}</strong></p>
                <p class="mb-0 text-muted">ফোন: <strong>{{ $latestOrder->phone }}</strong> (অর্ডার #{{ $latestOrder->id }} থেকে সংরক্ষিত)</p>
                <p class="small text-secondary mt-2 mb-0">প্রতিটি অর্ডারের ডেলিভারি তথ্য আলাদা স্ন্যাপশট হিসেবে ডাটাবেজে স্থায়ীভাবে সংরক্ষিত থাকে।</p>
            </section>
        @else
            <section class="dashboard-card p-4 mb-4 shadow-sm bg-white rounded-3 border">
                <h2 class="h5 fw-bold mb-2">ডেলিভারির তথ্য</h2>
                <p class="text-secondary mb-0">আপনি যখন চেকআউটের মাধ্যমে কোনো অর্ডার প্লেস করবেন, সেই ডেলিভারি ঠিকানাটি স্থায়ীভাবে সংরক্ষিত থাকবে।</p>
            </section>
        @endif

        {{-- Security and Logout Zone --}}
        <section class="dashboard-card p-4 shadow-sm bg-white rounded-3 border border-danger-subtle">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h6 fw-bold text-danger mb-1">অ্যাকাউন্ট সিকিউরিটি ও প্রস্থান</h2>
                    <p class="small text-secondary mb-0">আপনার সেশন সমাপ্ত করতে অথবা অন্য ডিভাইসে লগইন করতে লগআউট করুন।</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                        অন্য অ্যাকাউন্টে লগইন
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1">
                            <span>🚪</span> লগ আউট করুন
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</main>
@endsection