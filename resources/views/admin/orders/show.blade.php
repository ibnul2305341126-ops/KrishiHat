@extends('layouts.app')
@section('title', 'Order ' . ($order->tracking_code ?: $order->id) . ' | KrishiHat Admin')

@section('content')
<main class="container py-4 py-lg-5">
    @include('admin._nav')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">← অর্ডারের তালিকায় ফিরুন</a>
                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'shipped' ? 'primary' : 'warning text-dark') }} fs-6">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <h2 class="h3 fw-bold mt-2 mb-0">অর্ডার ট্র্যাকিং কোড: <code>{{ $order->tracking_code ?: 'KH-ORD-'.$order->id }}</code></h2>
            <small class="text-secondary">অর্ডার সম্পন্ন হয়েছে: {{ $order->created_at->format('d M, Y h:i A') }}</small>
        </div>
        <div class="text-end">
            <span class="text-muted small d-block">সর্বমোট পরিশোধযোগ্য মূল্য</span>
            <strong class="fs-4 text-success">৳{{ number_format((float)$order->total_amount, 2) }}</strong>
            <span class="badge bg-light text-secondary border d-block mt-1">পেমেন্ট মেথড: ক্যাশ অন ডেলিভারি</span>
        </div>
    </div>

    <div class="row g-4">
        {{-- Order Items & Buyer Info --}}
        <div class="col-lg-7">
            {{-- Delivery & Buyer Box --}}
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h3 class="h5 fw-bold mb-3 border-bottom pb-2">📦 ক্রেতা ও ডেলিভারি বিবরণ</h3>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">ক্রেতার নাম:</span>
                        <strong>{{ $order->buyer?->name ?? 'Guest/Deleted User' }}</strong>
                        <div class="small text-muted">{{ $order->buyer?->email }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">যোগাযোগের ফোন নম্বর:</span>
                        <strong>📞 {{ $order->phone }}</strong>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small d-block">গন্তব্য / ডেলিভারি ঠিকানা:</span>
                        <div class="p-2 bg-light rounded mt-1 border">
                            📍 {{ $order->delivery_address }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Products Table --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h3 class="h5 fw-bold mb-0">🌾 চালানের ফসল ও পণ্যের তালিকা</h3>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>পণ্য / ফসল</th>
                                <th>খামারি</th>
                                <th>পরিমাণ</th>
                                <th>একক দর</th>
                                <th class="text-end">মোট</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product?->name ?? 'পণ্য' }}</strong>
                                        <div class="small text-muted">আইডি: #{{ $item->product_id }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $item->farmer?->name ?? $item->product?->farmer?->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $item->farmer?->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $item->quantity }}</strong> {{ $item->product?->unit ?: 'unit' }}
                                    </td>
                                    <td>৳{{ number_format((float)$item->unit_price, 2) }}</td>
                                    <td class="text-end fw-bold">৳{{ number_format((float)$item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">মোট বিল:</td>
                                <td class="text-end fw-bold text-success fs-6">৳{{ number_format((float)$order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Logistics Dispatch Details --}}
            @if($order->driver_name || $order->vehicle_number || $order->current_location_text)
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="h5 fw-bold mb-3 border-bottom pb-2">🚚 পরিবহন ও চালকের তথ্য</h3>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">চালক:</span>
                            <strong>{{ $order->driver_name ?: 'নির্ধারিত হয়নি' }}</strong>
                            @if($order->driver_phone)
                                <div class="small text-muted">ফোন: {{ $order->driver_phone }}</div>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">গাড়ির নম্বর:</span>
                            <code>{{ $order->vehicle_number ?: 'নির্ধারিত হয়নি' }}</code>
                        </div>
                        @if($order->current_location_text)
                            <div class="col-12">
                                <span class="text-muted small d-block">সর্বশেষ আপডেটকৃত অবস্থান:</span>
                                <div>📍 {{ $order->current_location_text }}</div>
                                @if($order->current_location_google_maps_url)
                                    <a href="{{ $order->current_location_google_maps_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-1">
                                        <span>📍</span> Google Maps-এ দেখুন ↗
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Tracking Timeline --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4">
                <h3 class="h5 fw-bold mb-3 border-bottom pb-2">⏱️ ট্র্যাকিং টাইমলাইন (Audit Timeline)</h3>

                @if($order->trackingEvents->isNotEmpty())
                    <div class="timeline ps-2 border-start border-2 border-success position-relative" style="margin-left: 10px;">
                        @foreach($order->trackingEvents as $event)
                            <div class="mb-4 position-relative ps-3">
                                <div class="position-absolute bg-success rounded-circle" style="width: 12px; height: 12px; left: -7px; top: 5px;"></div>
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <strong class="text-dark">{{ $event->title }}</strong>
                                    <small class="text-muted">{{ $event->created_at->format('d M, h:i A') }}</small>
                                </div>
                                <p class="small text-secondary mb-1">{{ $event->description }}</p>
                                @if($event->location)
                                    <div class="small text-muted">📍 {{ $event->location }}</div>
                                @endif
                                @if($event->actor)
                                    <small class="text-muted d-block mt-1">নথিবদ্ধকারী: {{ $event->actor->name }} ({{ ucfirst($event->actor->role) }})</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted small text-center py-4">কোনো ট্র্যাকিং ইভেন্ট এখনো রেকর্ড করা হয়নি।</div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
