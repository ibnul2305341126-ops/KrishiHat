@extends('layouts.app')
@section('title', 'Orders & Tracking | KrishiHat Admin')

@section('content')
<main class="container py-4 py-lg-5">
    @include('admin._nav')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold">📦 অর্ডার ও ডেলিভারি ট্র্যাকিং (Orders & Logistics)</h2>
            <p class="text-secondary small mb-0">সিস্টেমের সমস্ত পাইকারি ক্রয়ের বিস্তারিত বিবরণ, চালানের অবস্থা ও ট্র্যাকিং ইভেন্ট পর্যবেক্ষণ।</p>
        </div>
        <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
            মোট অর্ডার: {{ $orders->total() }}
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="ট্র্যাকিং কোড, ক্রেতার নাম অথবা ফোন নম্বর…" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">সকল স্ট্যাটাস (All Statuses)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>গৃহীত / অপেক্ষমাণ (Pending)</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>নিশ্চিতকৃত (Confirmed)</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>প্যাকেজিং ও প্রস্তুতি (Processing)</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>ট্রানজিটে আছে (In Transit / Shipped)</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>ডেলিভারি সম্পন্ন (Delivered)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>বাতিলকৃত (Cancelled)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success flex-grow-1">ফিল্টার</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">রিসেট</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">অর্ডার / ট্র্যাকিং</th>
                        <th scope="col">তারিখ</th>
                        <th scope="col">ক্রেতা (Customer)</th>
                        <th scope="col">খামারি (Farmer)</th>
                        <th scope="col">ফসল ও আইটেম</th>
                        <th scope="col">মোট মূল্য</th>
                        <th scope="col">বর্তমান অবস্থা</th>
                        <th scope="col" class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $farmer = $order->items->first()?->farmer;
                        @endphp
                        <tr>
                            <td>
                                <code class="fw-bold">{{ $order->tracking_code ?: 'KH-ORD-'.$order->id }}</code>
                                @if($order->accepted_offer_id)
                                    <span class="badge bg-warning-subtle text-dark border d-block mt-1" style="width: fit-content;">দরদামের অর্ডার</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-secondary">{{ $order->created_at->format('d M, Y h:i A') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->buyer?->name ?? 'Guest/Deleted' }}</div>
                                <small class="text-muted">📞 {{ $order->phone }}</small>
                            </td>
                            <td>
                                @if($farmer)
                                    <strong class="text-success">{{ $farmer->name }}</strong>
                                    <small class="text-muted d-block">📞 {{ $farmer->phone ?: ($farmer->district ?? '') }}</small>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @foreach($order->items->take(2) as $item)
                                    <div class="small">
                                        • {{ $item->product?->name ?? 'Crop' }} ({{ $item->quantity }} {{ $item->product?->unit ?: 'unit' }})
                                    </div>
                                @endforeach
                                @if($order->items->count() > 2)
                                    <small class="text-muted">+ আরো {{ $order->items->count() - 2 }} টি ফসল</small>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success fs-6">৳{{ number_format((float)$order->total_amount, 2) }}</strong>
                                <small class="d-block text-muted">ক্যাশ অন ডেলিভারি</small>
                            </td>
                            <td>
                                @if($order->status === 'delivered')
                                    <span class="badge bg-success">ডেলিভার্ড (Delivered)</span>
                                @elseif($order->status === 'shipped')
                                    <span class="badge bg-primary">ট্রানজিটে আছে (Shipped)</span>
                                @elseif($order->status === 'processing')
                                    <span class="badge bg-info text-dark">প্রস্তুতি চলছে (Processing)</span>
                                @elseif($order->status === 'confirmed')
                                    <span class="badge bg-info text-dark">খামারি নিশ্চিত করেছেন</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-danger">বাতিল (Cancelled)</span>
                                @else
                                    <span class="badge bg-warning text-dark">নতুন অর্ডার (Pending)</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                    🔍 ট্র্যাক ও বিবরণ
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                কোনো অর্ডার রেকর্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
