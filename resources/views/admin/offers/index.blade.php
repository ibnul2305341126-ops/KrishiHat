@extends('layouts.app')
@section('title', 'Price Negotiation & Offers | KrishiHat Admin')

@section('content')
<main class="container py-4 py-lg-5">
    @include('admin._nav')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold">🤝 দরদাম ও নেগোসিয়েশন পর্যালোচনা (Bargaining & Offers)</h2>
            <p class="text-secondary small mb-0">ক্রেতা ও কৃষকদের মধ্যবর্তী দর কষাকষি, প্রস্তাবিত মূল্য, কাউন্টার অফার এবং অর্ডারে রূপান্তর ট্র্যাকিং।</p>
        </div>
        <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
            মোট অফার: {{ $offers->total() }}
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('admin.offers.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="ফসলের নাম দিয়ে খুঁজুন…" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">সকল স্ট্যাটাস (All Statuses)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>অপেক্ষমাণ (Pending)</option>
                    <option value="countered" {{ request('status') === 'countered' ? 'selected' : '' }}>কাউন্টার অফার (Countered)</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>গৃহীত (Accepted)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>প্রত্যাখ্যাত (Rejected)</option>
                    <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>প্রত্যাহারকৃত (Withdrawn)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success flex-grow-1">ফিল্টার</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">রিসেট</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Offers Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">আইডি</th>
                        <th scope="col">ফসল / পণ্য</th>
                        <th scope="col">ক্রেতা ও কৃষক</th>
                        <th scope="col">পরিমাণ</th>
                        <th scope="col">প্রস্তাবিত ও বর্তমান দর</th>
                        <th scope="col">স্ট্যাটাস</th>
                        <th scope="col">অর্ডারে রূপান্তর</th>
                        <th scope="col" class="text-end">ইভেন্ট ইতিহাস</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td>#{{ $offer->id }}</td>
                            <td>
                                <strong>{{ $offer->product?->name ?? '—' }}</strong>
                                <div class="small text-muted">মূল লিস্টিং মূল্য: ৳{{ number_format((float)($offer->product?->price ?? 0), 2) }}</div>
                            </td>
                            <td>
                                <div>ক্রেতা: <strong>{{ $offer->buyer?->name ?? 'Buyer' }}</strong></div>
                                <div class="small text-secondary">খামারি: <strong>{{ $offer->farmer?->name ?? 'Farmer' }}</strong></div>
                            </td>
                            <td>
                                <strong>{{ number_format($offer->quantity) }}</strong> {{ $offer->product?->unit ?: 'unit' }}
                            </td>
                            <td>
                                <div class="small text-muted">প্রথম অফার: ৳{{ number_format((float)$offer->initial_price, 2) }}</div>
                                <div class="fw-bold text-success fs-6">বর্তমান: ৳{{ number_format((float)$offer->current_price, 2) }}</div>
                            </td>
                            <td>
                                @if($offer->status === 'accepted')
                                    <span class="badge bg-success">গৃহীত (Accepted)</span>
                                @elseif($offer->status === 'countered')
                                    <span class="badge bg-info text-dark">কাউন্টার অফার</span>
                                @elseif($offer->status === 'rejected')
                                    <span class="badge bg-danger">প্রত্যাখ্যাত</span>
                                @elseif($offer->status === 'withdrawn')
                                    <span class="badge bg-secondary">প্রত্যাহারকৃত</span>
                                @else
                                    <span class="badge bg-warning text-dark">অপেক্ষমাণ (Pending)</span>
                                @endif
                            </td>
                            <td>
                                @if($offer->order)
                                    <a href="{{ route('admin.orders.show', $offer->order) }}" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none">
                                        অর্ডারকৃত: {{ $offer->order->tracking_code ?: '#'.$offer->order->id }} ↗
                                    </a>
                                @elseif($offer->status === 'accepted')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">অর্ডারের অপেক্ষায়</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="badge bg-light text-dark border">{{ $offer->events->count() }} টি পদক্ষেপ</span>
                                <small class="d-block text-muted mt-1">{{ $offer->updated_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">কোনো দরদাম রেকর্ড পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($offers->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $offers->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
