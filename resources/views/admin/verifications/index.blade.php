@extends('layouts.app')
@section('title', 'কৃষক ভেরিফিকেশন ব্যবস্থাপনা | KrishiHat Admin')

@section('content')
<main class="dashboard-main py-4 py-lg-5">
    <div class="container" style="max-width: 1200px">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন ড্যাশবোর্ড</a></li>
                <li class="breadcrumb-item active" aria-current="page">কৃষক ভেরিফিকেশন (NID & Trade License)</li>
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
                <span class="eyebrow text-uppercase text-success fw-bold">KYC & IDENTITY VERIFICATION</span>
                <h1 class="dashboard-title h3 mb-1">কৃষক প্রোফাইল ও ডকুমেন্টস ভেরিফিকেশন</h1>
                <p class="text-secondary mb-0">জাতীয় পরিচয়পত্র (NID) ও ট্রেড লাইসেন্স / কৃষি কার্ড যাচাই করে বেচাকেনার অনুমতি প্রদান করুন।</p>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('admin.verifications.index', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                সব খামারি ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.verifications.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' }}">
                ⏳ অপেক্ষমাণ যাচাই ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('admin.verifications.index', ['status' => 'verified']) }}" class="btn btn-sm {{ $status === 'verified' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                ✅ ভেরিফাইড খামারি ({{ $counts['verified'] }})
            </a>
            <a href="{{ route('admin.verifications.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">
                ❌ বাতিলকৃত ({{ $counts['rejected'] }})
            </a>
            <a href="{{ route('admin.verifications.index', ['status' => 'unverified']) }}" class="btn btn-sm {{ $status === 'unverified' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                ⚠️ আবেদন করেনি ({{ $counts['unverified'] }})
            </a>
        </div>

        {{-- Search Card --}}
        <section class="dashboard-card p-3 mb-4 shadow-sm bg-white rounded-3 border">
            <form action="{{ route('admin.verifications.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="col-md-9">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="নাম, ইমেইল, NID নম্বর, ট্রেড লাইসেন্স বা ফার্মের নাম দিয়ে খুঁজুন...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1">অনুসন্ধান</button>
                    @if(request('search'))
                        <a href="{{ route('admin.verifications.index', ['status' => $status]) }}" class="btn btn-outline-secondary">রিসেট</a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Farmers Verification Table --}}
        <section class="dashboard-card shadow-sm bg-white rounded-3 border overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">কৃষক / খামার</th>
                            <th scope="col">NID নম্বর</th>
                            <th scope="col">ট্রেড লাইসেন্স / কৃষি কার্ড</th>
                            <th scope="col">সংযুক্ত ডকুমেন্টস</th>
                            <th scope="col">স্ট্যাটাস</th>
                            <th scope="col" class="text-end">পদক্ষেপ (Actions)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($farmers as $farmer)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $farmer->name }}</div>
                                    <small class="text-muted d-block">{{ $farmer->email }}</small>
                                    @if($farmer->farm_name)
                                        <span class="badge bg-light text-dark border mt-1">🏡 {{ $farmer->farm_name }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($farmer->nid_number)
                                        <code class="fw-bold text-primary">{{ $farmer->nid_number }}</code>
                                    @else
                                        <span class="text-muted small">জমা দেওয়া হয়নি</span>
                                    @endif
                                </td>
                                <td>
                                    @if($farmer->trade_license_number)
                                        <code class="fw-bold text-success">{{ $farmer->trade_license_number }}</code>
                                    @else
                                        <span class="text-muted small">জমা দেওয়া হয়নি</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($farmer->nid_front_path)
                                            <a href="{{ asset('storage/' . $farmer->nid_front_path) }}" target="_blank" class="badge bg-primary-subtle text-primary border text-decoration-none">
                                                🪪 NID সামনে
                                            </a>
                                        @endif
                                        @if($farmer->nid_back_path)
                                            <a href="{{ asset('storage/' . $farmer->nid_back_path) }}" target="_blank" class="badge bg-primary-subtle text-primary border text-decoration-none">
                                                🪪 NID পেছনে
                                            </a>
                                        @endif
                                        @if($farmer->trade_license_path)
                                            <a href="{{ asset('storage/' . $farmer->trade_license_path) }}" target="_blank" class="badge bg-success-subtle text-success border text-decoration-none">
                                                📜 ট্রেড লাইসেন্স
                                            </a>
                                        @endif
                                        @if(!$farmer->nid_front_path && !$farmer->trade_license_path)
                                            <span class="text-muted small">কোনো ফাইল নেই</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($farmer->verification_status === 'verified')
                                        <span class="badge bg-success">✅ ভেরিফাইড</span>
                                        @if($farmer->farmer_verified_at)
                                            <div class="small text-muted mt-1">{{ $farmer->farmer_verified_at->format('d M, Y') }}</div>
                                        @endif
                                    @elseif($farmer->verification_status === 'pending')
                                        <span class="badge bg-warning text-dark">⏳ অপেক্ষমাণ</span>
                                        @if($farmer->verification_submitted_at)
                                            <div class="small text-muted mt-1">{{ $farmer->verification_submitted_at->diffForHumans() }}</div>
                                        @endif
                                    @elseif($farmer->verification_status === 'rejected')
                                        <span class="badge bg-danger">❌ বাতিলকৃত</span>
                                    @else
                                        <span class="badge bg-secondary">⚠️ অযাচাইকৃত</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.verifications.show', $farmer) }}" class="btn btn-sm btn-outline-primary" title="বিস্তারিত দেখুন">
                                            বিস্তারিত
                                        </a>

                                        @if($farmer->verification_status !== 'verified')
                                            <form action="{{ route('admin.verifications.approve', $farmer) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই কৃষকের NID ও ট্রেড লাইসেন্স সঠিক এবং প্রোফাইল ভেরিফাই করতে চান?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    অনুমোদন
                                                </button>
                                            </form>
                                        @endif

                                        @if($farmer->verification_status === 'pending' || $farmer->verification_status === 'verified')
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $farmer->id }}">
                                                বাতিল
                                            </button>

                                            {{-- Reject Modal --}}
                                            <div class="modal fade text-start" id="rejectModal{{ $farmer->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $farmer->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('admin.verifications.reject', $farmer) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="rejectModalLabel{{ $farmer->id }}">ভেরিফিকেশন আবেদন বাতিল ({{ $farmer->name }})</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="reason{{ $farmer->id }}" class="form-label fw-bold">বাতিল করার স্পষ্ট কারণ লিখুন <span class="text-danger">*</span></label>
                                                                    <textarea name="reason" id="reason{{ $farmer->id }}" class="form-control" rows="3" placeholder="যেমন: NID কার্ডের ছবি অস্পষ্ট, ট্রেড লাইসেন্স মেয়াদোত্তীর্ণ ইত্যাদি..." required></textarea>
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
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="fs-1 mb-2">🪪</div>
                                    <h5 class="fw-normal">কোনো খামারির ভেরিফিকেশন আবেদন পাওয়া যায়নি</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($farmers->hasPages())
                <div class="p-3 border-top">
                    {{ $farmers->links() }}
                </div>
            @endif
        </section>
    </div>
</main>
@endsection
