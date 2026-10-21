@extends('layouts.app')
@section('title', 'ইউজার সাবস্ক্রিপশন ও ম্যানুয়াল গ্রান্ট অডিট | KrishiHat Admin')

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

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">🎫 ব্যবহারকারী সাবস্ক্রিপশন ব্যবস্থাপনা ও অডিট ট্রেইল</h2>
            <p class="text-secondary small mb-0">
                সক্রিয় মেম্বারশিপ, মেয়াদের তারিখ, লিস্টিং এনটাইটেলমেন্ট এবং বাধ্যতামূলক অডিট রেকর্ডসহ ম্যানুয়াল গ্রান্ট পরিচালনা করুন।
            </p>
        </div>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#manualGrantModal">
            ✨ ম্যানুয়াল সাবস্ক্রিপশন প্রদান (Audit Grant)
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">মোট সাবস্ক্রিপশন</span>
                <h3 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">সক্রিয় (Active)</span>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['active'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">অনুমোদনের অপেক্ষায় (Pending)</span>
                <h3 class="fw-bold mb-0 text-warning">{{ $stats['pending'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">মেয়াদোত্তীর্ণ / বাতিল</span>
                <h3 class="fw-bold mb-0 text-secondary">{{ $stats['expired'] + $stats['cancelled'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="ব্যবহারকারীর নাম, মোবাইল বা ইমেইল..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="plan_id" class="form-select form-select-sm">
                    <option value="">সকল প্ল্যান</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" {{ request('plan_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (সক্রিয়)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (অপেক্ষমান)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired (মেয়াদোত্তীর্ণ)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled (বাতিল)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100">ফিল্টার</button>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary btn-sm">রিসেট</a>
            </div>
        </form>
    </div>

    {{-- Subscriptions Table --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>ব্যবহারকারী</th>
                        <th>প্ল্যান ও লিমিট</th>
                        <th>স্ট্যাটাস</th>
                        <th>মেয়াদকাল</th>
                        <th>পেমেন্ট পদ্ধতি</th>
                        <th>অডিট ট্রেইল ও নোট</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td><strong>#{{ $sub->id }}</strong></td>
                            <td>
                                <div class="fw-bold">{{ $sub->user?->name ?? 'Deleted User' }}</div>
                                <small class="text-muted">{{ $sub->user?->phone }} | {{ $sub->user?->role }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-primary">{{ $sub->plan?->name ?? 'Custom Plan' }}</div>
                                <small class="text-muted">লিস্টিং সীমা: {{ $sub->plan?->active_listing_limit ?? 5 }}টি</small>
                            </td>
                            <td>
                                @if($sub->status === 'active')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">✓ Active</span>
                                @elseif($sub->status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Pending</span>
                                @elseif($sub->status === 'expired')
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Expired</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">শুরু: {{ $sub->starts_at ? $sub->starts_at->format('d/m/Y') : '—' }}</div>
                                <div class="small text-muted">মেয়াদ: {{ $sub->expires_at ? $sub->expires_at->format('d/m/Y') : 'আজীবন' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">{{ $sub->payment_method ?: 'Free' }}</span>
                            </td>
                            <td>
                                <small class="text-muted d-block" style="max-width: 250px;">{{ $sub->notes ?: 'কোনো বিশেষ নোট নেই' }}</small>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editSubModal{{ $sub->id }}">
                                    স্ট্যাটাস
                                </button>
                            </td>
                        </tr>

                        {{-- Edit Sub Modal --}}
                        <div class="modal fade" id="editSubModal{{ $sub->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.subscriptions.update-status', $sub) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">সাবস্ক্রিপশন স্ট্যাটাস পরিবর্তন: #{{ $sub->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">স্ট্যাটাস</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="active" {{ $sub->status === 'active' ? 'selected' : '' }}>Active (সক্রিয়)</option>
                                                    <option value="pending" {{ $sub->status === 'pending' ? 'selected' : '' }}>Pending (অপেক্ষমান)</option>
                                                    <option value="expired" {{ $sub->status === 'expired' ? 'selected' : '' }}>Expired (মেয়াদোত্তীর্ণ)</option>
                                                    <option value="cancelled" {{ $sub->status === 'cancelled' ? 'selected' : '' }}>Cancelled (বাতিল)</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">অ্যাডমিন অডিট মন্তব্য</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="পরিবর্তনের কারণ বা যাচাইকরণ তথ্য..."></textarea>
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
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">কোনো সাবস্ক্রিপশন রেকর্ড পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="p-3 border-top">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

    {{-- Manual Grant Modal --}}
    <div class="modal fade" id="manualGrantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.subscriptions.manual-grant') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">ম্যানুয়াল সাবস্ক্রিপশন প্রদান (Audit Trail Required)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning small mb-3">
                            <strong>সতর্কতা:</strong> সিস্টেম অডিট ট্রেইলের অংশ হিসেবে এই অ্যাকশনটি স্থায়ীভাবে লগ করা হবে এবং সংশ্লিষ্ট কর্মকর্তার নাম সংযুক্ত থাকবে।
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ব্যবহারকারী নির্বাচন করুন *</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">নির্বাচন করুন...</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->phone }} | {{ $u->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">প্ল্যান নির্বাচন করুন *</label>
                            <select name="plan_id" class="form-select" required>
                                <option value="">নির্বাচন করুন...</option>
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (৳{{ $p->price }} / {{ $p->duration_days }} দিন)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">মেয়াদ (দিন) *</label>
                            <input type="number" name="duration_days" class="form-control" value="30" min="1" max="365" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">বাধ্যতামূলক অডিট কারণ (Audit Reason) *</label>
                            <textarea name="audit_reason" class="form-control" rows="3" placeholder="যেমন: বিশেষ বিশ্ববিদ্যালয় ডেমোনস্ট্রেশন / অফলাইন ক্যাশ পেমেন্ট রিসিট নং #7890" required></textarea>
                            <small class="text-muted">অন্তত ৫ অক্ষরের যুক্তিসঙ্গত ব্যাখ্যা প্রদান আবশ্যক।</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                        <button type="submit" class="btn btn-success">গ্রান্ট নিশ্চিত করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
