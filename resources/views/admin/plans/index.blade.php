@extends('layouts.app')
@section('title', 'মেম্বারশিপ প্ল্যান ও ক্রাইটেরিয়া কনফিগারেশন | KrishiHat Admin')

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

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">💎 মেম্বারশিপ প্ল্যান ও ক্রাইটেরিয়া কনফিগারেশন</h2>
            <p class="text-secondary small mb-0">
                খামারি ও পাইকারি ক্রেতাদের জন্য প্ল্যানের নাম, মূল্য, মেয়াদ, লিস্টিং লিমিট এবং এনটাইটেলমেন্ট নির্ধারণ করুন।
            </p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPlanModal">
            ➕ নতুন প্ল্যান তৈরি করুন
        </button>
    </div>

    {{-- Plans Grid --}}
    <div class="row g-4 mb-4">
        @forelse($plans as $plan)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm {{ $plan->is_featured ? 'border-top border-4 border-success' : '' }}">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h4 class="card-title fw-bold mb-1">{{ $plan->name }}</h4>
                                <small class="text-muted">Slug: <code>{{ $plan->slug }}</code></small>
                            </div>
                            <div>
                                @if($plan->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">সক্রিয়</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">নিষ্ক্রিয়</span>
                                @endif
                                @if($plan->is_demo)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Demo</span>
                                @endif
                            </div>
                        </div>

                        <div class="my-3">
                            <span class="display-6 fw-bold text-success">৳{{ number_format((float)$plan->price, 0) }}</span>
                            <span class="text-muted small">/ {{ $plan->duration_days }} দিন ({{ $plan->billing_interval }})</span>
                        </div>

                        <p class="card-text text-secondary small flex-grow-1">{{ $plan->description }}</p>

                        <div class="bg-light p-3 rounded mb-3 small">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>সক্রিয় লিস্টিং সীমা:</span>
                                <strong>{{ $plan->active_listing_limit }}টি</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>যোগ্যতার শর্ত:</span>
                                <strong>
                                    {{ $plan->role_eligibility === 'farmer' ? 'শুধু খামারি' : ($plan->role_eligibility === 'buyer' ? 'শুধু ক্রেতা' : 'উভয়ই') }}
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>অ্যানালিটিক্স অ্যাক্সেস:</span>
                                <strong>{{ $plan->can_view_analytics ? '✓ হ্যাঁ' : '✗ না' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span>পাইকারি ভলিউম সুবিধা:</span>
                                <strong>{{ $plan->can_bulk_purchase ? '✓ হ্যাঁ' : '✗ না' }}</strong>
                            </div>
                        </div>

                        @if(!empty($plan->features))
                            <div class="mb-3 small">
                                <span class="fw-semibold d-block mb-1">সুবিধাসমূহ:</span>
                                <ul class="list-unstyled mb-0 text-muted ps-1">
                                    @foreach($plan->features as $feature)
                                        <li>✓ {{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-auto pt-3 border-top d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $plan->id }}">
                                ✏️ সম্পাদনা
                            </button>
                            <form action="{{ route('admin.plans.toggle', $plan) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $plan->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ $plan->is_active ? 'বন্ধ করুন' : 'চালু করুন' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit Modal --}}
            <div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">প্ল্যান সম্পাদনা: {{ $plan->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">প্ল্যানের নাম</label>
                                        <input type="text" name="name" class="form-control" value="{{ $plan->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">মূল্য (টাকা)</label>
                                        <input type="number" step="0.01" name="price" class="form-control" value="{{ $plan->price }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">বিলিং ইন্টারভাল</label>
                                        <select name="billing_interval" class="form-select" required>
                                            <option value="monthly" {{ $plan->billing_interval === 'monthly' ? 'selected' : '' }}>মাসিক (Monthly)</option>
                                            <option value="yearly" {{ $plan->billing_interval === 'yearly' ? 'selected' : '' }}>বাৎসরিক (Yearly)</option>
                                            <option value="lifetime" {{ $plan->billing_interval === 'lifetime' ? 'selected' : '' }}>লাইফটাইম (Lifetime)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">মেয়াদ (দিন)</label>
                                        <input type="number" name="duration_days" class="form-control" value="{{ $plan->duration_days }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">সক্রিয় লিস্টিং সীমা</label>
                                        <input type="number" name="active_listing_limit" class="form-control" value="{{ $plan->active_listing_limit }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">যোগ্যতার ভূমিকা (Role Eligibility)</label>
                                        <select name="role_eligibility" class="form-select" required>
                                            <option value="all" {{ $plan->role_eligibility === 'all' ? 'selected' : '' }}>সকল ব্যবহারকারী (All)</option>
                                            <option value="farmer" {{ $plan->role_eligibility === 'farmer' ? 'selected' : '' }}>শুধু খামারি (Farmer only)</option>
                                            <option value="buyer" {{ $plan->role_eligibility === 'buyer' ? 'selected' : '' }}>শুধু ক্রেতা (Buyer only)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">বর্ণনা</label>
                                        <input type="text" name="description" class="form-control" value="{{ $plan->description }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">ফিচার তালিকা (প্রতি লাইনে ১টি)</label>
                                        <textarea name="features_text" class="form-control" rows="3">{{ is_array($plan->features) ? implode("\n", $plan->features) : '' }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap gap-4 pt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="can_view_analytics" value="1" id="an{{ $plan->id }}" {{ $plan->can_view_analytics ? 'checked' : '' }}>
                                                <label class="form-check-label" for="an{{ $plan->id }}">অ্যানালিটিক্স রিপোর্ট অ্যাক্সেস</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="can_bulk_purchase" value="1" id="bp{{ $plan->id }}" {{ $plan->can_bulk_purchase ? 'checked' : '' }}>
                                                <label class="form-check-label" for="bp{{ $plan->id }}">পাইকারি সুবিধা</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="priority_support" value="1" id="ps{{ $plan->id }}" {{ $plan->priority_support ? 'checked' : '' }}>
                                                <label class="form-check-label" for="ps{{ $plan->id }}">অগ্রাধিকার সাপোর্ট</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="ft{{ $plan->id }}" {{ $plan->is_featured ? 'checked' : '' }}>
                                                <label class="form-check-label" for="ft{{ $plan->id }}">ফিচার্ড হাইলাইট</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_demo" value="1" id="dm{{ $plan->id }}" {{ $plan->is_demo ? 'checked' : '' }}>
                                                <label class="form-check-label" for="dm{{ $plan->id }}">ডেমো প্ল্যান লেবেল</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                                <button type="submit" class="btn btn-primary">আপডেট করুন</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">কোনো মেম্বারশিপ প্ল্যান পাওয়া যায়নি।</div>
            </div>
        @endforelse
    </div>

    {{-- Create Plan Modal --}}
    <div class="modal fade" id="createPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.plans.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">নতুন মেম্বারশিপ প্ল্যান তৈরি করুন</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">প্ল্যানের নাম *</label>
                                <input type="text" name="name" class="form-control" placeholder="যেমন: Agro Super Pro" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">মূল্য (টাকা) *</label>
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="499" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">বিলিং ইন্টারভাল *</label>
                                <select name="billing_interval" class="form-select" required>
                                    <option value="monthly">মাসিক (Monthly)</option>
                                    <option value="yearly">বাৎসরিক (Yearly)</option>
                                    <option value="lifetime">লাইফটাইম (Lifetime)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">মেয়াদ (দিন) *</label>
                                <input type="number" name="duration_days" class="form-control" value="30" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">সক্রিয় লিস্টিং সীমা *</label>
                                <input type="number" name="active_listing_limit" class="form-control" value="50" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">যোগ্যতার ভূমিকা (Role Eligibility) *</label>
                                <select name="role_eligibility" class="form-select" required>
                                    <option value="all">সকল ব্যবহারকারী (All)</option>
                                    <option value="farmer">শুধু খামারি (Farmer only)</option>
                                    <option value="buyer">শুধু ক্রেতা (Buyer only)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">বর্ণনা</label>
                                <input type="text" name="description" class="form-control" placeholder="উন্নত খামারিদের জন্য বর্ধিত সুবিধা">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">ফিচারসমূহ (প্রতি লাইনে ১টি)</label>
                                <textarea name="features_text" class="form-control" rows="3" placeholder="৫০টি পর্যন্ত সক্রিয় শস্য লিস্টিং&#10;বিস্তারিত বিক্রয় ও চাহিদা অ্যানালিটিক্স&#10;২৪/৭ ভিআইপি সাপোর্ট"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-4 pt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="can_view_analytics" value="1" id="new_an" checked>
                                        <label class="form-check-label" for="new_an">অ্যানালিটিক্স অ্যাক্সেস</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="can_bulk_purchase" value="1" id="new_bp">
                                        <label class="form-check-label" for="new_bp">পাইকারি সুবিধা</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="new_act" checked>
                                        <label class="form-check-label" for="new_act">সক্রিয় রাখুন</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                        <button type="submit" class="btn btn-primary">তৈরি করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
