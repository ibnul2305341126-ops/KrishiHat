@extends('layouts.app')
@section('title', 'সংরক্ষিত ডেলিভারি ঠিকানা | KrishiHat')

@section('content')
<main class="dashboard-main py-4 py-lg-5">
    <div class="container" style="max-width:960px">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
                <li class="breadcrumb-item active" aria-current="page">ডেলিভারি ঠিকানা</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <span class="eyebrow text-uppercase text-success fw-bold">DELIVERY & SHIPPING ADDRESSES</span>
                <h1 class="dashboard-title mb-1">সংরক্ষিত ডেলিভারি ঠিকানা</h1>
                <p class="text-secondary mb-0">আপনার নিয়মিত কেনাকাটা ও দ্রুত চেকআউটের জন্য ডেলিভারি ঠিকানা ব্যবস্থাপনা</p>
            </div>
            <div>
                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                    ← প্রোফাইলে ফিরুন
                </a>
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

        <div class="row g-4">
            <!-- Saved Addresses List -->
            <div class="col-lg-6">
                <div class="dashboard-card p-4 h-100">
                    <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                        <span>📍 আপনার সংরক্ষিত ঠিকানা ({{ $addresses->count() }})</span>
                    </h2>

                    @forelse($addresses as $address)
                        <div class="card mb-3 border {{ $address->is_default ? 'border-success shadow-xs' : 'border-secondary-subtle' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <h3 class="h6 fw-bold mb-0">
                                        {{ $address->title ?: 'ঠিকানা' }}
                                        @if($address->is_default)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">ডিফল্ট</span>
                                        @endif
                                    </h3>
                                    <div class="d-flex gap-1">
                                        @if(!$address->is_default)
                                            <form action="{{ route('addresses.set-default', $address) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 small" title="ডিফল্ট করুন">ডিফল্ট</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('addresses.destroy', $address) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি এই ঠিকানাটি মুছে ফেলতে নিশ্চিত?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 small" title="মুছুন">✕</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="text-dark small mb-1"><strong>প্রাপক:</strong> {{ $address->recipient_name }} | <strong>ফোন:</strong> {{ $address->phone }}</p>
                                <p class="text-secondary small mb-0">{{ $address->formatted_address }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <p class="mb-2">এখনও কোনো ডেলিভারি ঠিকানা সংরক্ষণ করা হয়নি।</p>
                            <small class="text-secondary">ডানপাশের ফর্মটি পূরণ করে একটি ঠিকানা যোগ করুন।</small>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Add New Address Form -->
            <div class="col-lg-6">
                <div class="dashboard-card p-4">
                    <h2 class="h5 fw-bold mb-3">➕ নতুন ঠিকানা যোগ করুন</h2>
                    <form action="{{ route('addresses.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="addr_title">ঠিকানার নাম (Title)</label>
                            <input id="addr_title" type="text" name="title" class="form-control" placeholder="যেমন: বাসা, অফিস, আড়ত…" value="{{ old('title', 'Home') }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="addr_recipient">প্রাপকের নাম (Recipient)</label>
                                <input id="addr_recipient" type="text" name="recipient_name" class="form-control" placeholder="নাম লিখুন" value="{{ old('recipient_name', auth()->user()->name) }}" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="addr_phone">মোবাইল নম্বর (Phone)</label>
                                <input id="addr_phone" type="tel" name="phone" class="form-control" placeholder="01700-000000" value="{{ old('phone') }}" required>
                            </div>
                        </div>

                        <!-- Division & District -->
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="division_id">বিভাগ (Division)</label>
                                <select id="division_id" name="division_id" class="form-select" required>
                                    <option value="">-- বিভাগ নির্বাচন করুন --</option>
                                    @foreach(\App\Models\Division::orderBy('name_bn')->get() as $div)
                                        <option value="{{ $div->id }}" {{ old('division_id') == $div->id ? 'selected' : '' }}>
                                            {{ $div->name_bn }} ({{ $div->name_en }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="district_id">জেলা (District)</label>
                                <select id="district_id" name="district_id" class="form-select" data-initial-value="{{ old('district_id') }}" required disabled>
                                    <option value="">-- আগে বিভাগ নির্বাচন করুন --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Upazila & Union -->
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="upazila_id">উপজেলা / থানা (Upazila)</label>
                                <select id="upazila_id" name="upazila_id" class="form-select" data-initial-value="{{ old('upazila_id') }}" required disabled>
                                    <option value="">-- আগে জেলা নির্বাচন করুন --</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold" for="union_id">ইউনিয়ন (ঐচ্ছিক / গ্রামীণ)</label>
                                <select id="union_id" name="union_id" class="form-select" data-initial-value="{{ old('union_id') }}" disabled>
                                    <option value="">-- আগে উপজেলা নির্বাচন করুন --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Street Address -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="street_address">রাস্তা, বাড়ি বা বিস্তারিত ঠিকানা (Street address)</label>
                            <textarea id="street_address" name="street_address" class="form-control" rows="2" placeholder="বাড়ি নং, রোড নং, এলাকা বা বাজারের নাম…" required>{{ old('street_address') }}</textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" {{ old('is_default', $addresses->isEmpty() ? '1' : '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label small" for="is_default">
                                এটি আমার ডিফল্ট ডেলিভারি ঠিকানা হিসেবে সেট করুন
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-semibold">
                            ঠিকানা সংরক্ষণ করুন
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
