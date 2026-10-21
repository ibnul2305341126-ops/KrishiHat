@extends('layouts.app')
@section('title', 'Order #' . $order->id . ' Tracking | KrishiHat')

@section('content')
<main class="dashboard-main order-detail-page py-4 py-lg-5">
    <div class="container">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Marketplace</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">My orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Order #{{ $order->id }}</li>
            </ol>
        </nav>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="status">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Order Header & Tracking Code --}}
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="eyebrow mb-0">LIVE SHIPMENT & ORDER TRACKING</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace" data-tracking-code>
                        {{ $order->tracking_code }}
                    </span>
                </div>
                <h1 class="dashboard-title mb-1">অর্ডার #{{ $order->id }}</h1>
                <p class="text-secondary mb-0">অর্ডার সময়: {{ $order->created_at->format('M j, Y · g:i A') }}</p>
            </div>
            <div class="text-end">
                <span class="badge text-bg-light border fs-6 px-3 py-2 fw-semibold" id="order-status-badge">
                    {{ ucfirst($order->status) }}
                </span>
                <div class="small text-muted mt-1" id="live-indicator">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
                    লাইভ ট্র্যাকিং সক্রিয়
                </div>
            </div>
        </div>

        {{-- Visual Tracking Step Indicator --}}
        @php
            $steps = [
                'pending' => ['name' => 'অপেক্ষমান', 'sub' => 'Order Placed', 'icon' => '📝'],
                'confirmed' => ['name' => 'নিশ্চিত', 'sub' => 'Confirmed', 'icon' => '✓'],
                'processing' => ['name' => 'প্রক্রিয়াধীন', 'sub' => 'Packaging', 'icon' => '📦'],
                'shipped' => ['name' => 'পরিবহনে', 'sub' => 'In Transit', 'icon' => '🚚'],
                'delivered' => ['name' => 'সম্পন্ন', 'sub' => 'Delivered', 'icon' => '🎉'],
            ];
            $stepKeys = array_keys($steps);
            $currentStatus = $order->status;
            $currentIndex = array_search($currentStatus, $stepKeys);
            if ($currentIndex === false && $currentStatus === 'cancelled') {
                $currentIndex = -1;
            }
        @endphp

        <section class="card border rounded-4 p-4 mb-4 bg-white shadow-xs" aria-label="Shipment progress">
            @if($currentStatus === 'cancelled')
                <div class="alert alert-danger mb-0 d-flex align-items-center gap-2">
                    <span class="fs-4">✕</span>
                    <div>
                        <strong>এই অর্ডারটি বাতিল করা হয়েছে (Order Cancelled)</strong>
                        <p class="small mb-0">এই অর্ডারের জন্য আর কোনো সরবরাহ আপডেট কার্যকর নেই।</p>
                    </div>
                </div>
            @else
                <div class="row g-2 text-center" id="tracking-steps-container">
                    @foreach($steps as $key => $step)
                        @php
                            $stepIndex = array_search($key, $stepKeys);
                            $isPassed = $currentIndex !== false && $stepIndex <= $currentIndex;
                            $isCurrent = $currentIndex !== false && $stepIndex === $currentIndex;
                        @endphp
                        <div class="col">
                            <div class="p-3 rounded-3 border h-100 {{ $isCurrent ? 'bg-success text-white border-success shadow-sm' : ($isPassed ? 'bg-success-subtle text-success-emphasis border-success-subtle' : 'bg-light text-muted') }}" data-step="{{ $key }}">
                                <div class="fs-4 mb-1">{{ $step['icon'] }}</div>
                                <strong class="d-block small">{{ $step['name'] }}</strong>
                                <small class="d-block" style="font-size: 0.72rem; opacity: 0.85;">{{ $step['sub'] }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Transparent notice regarding tracking --}}
            <div class="mt-3 pt-3 border-top small text-muted d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span>
                    <strong>তথ্যসূত্র:</strong> খামারি ও সরবরাহ ব্যবস্থাপনা দ্বারা প্রত্যয়িত টাইমলাইন।
                </span>
                <span class="badge bg-light text-secondary border">
                    স্ট্যাটাস ভিত্তিক ট্র্যাকিং
                </span>
            </div>
        </section>

        <div class="row g-4">
            {{-- Tracking Event Timeline --}}
            <div class="col-lg-7">
                <section class="dashboard-card mb-4">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h5 mb-1">সরবরাহ টাইমলাইন (Shipment History)</h2>
                            <p class="text-secondary small mb-0">প্রতিটি আপডেটের সময় ও অবস্থান রেকর্ড করা হয়েছে</p>
                        </div>
                        <span class="badge bg-light text-dark border font-monospace" id="event-count-badge">
                            {{ $order->trackingEvents->count() }} টি আপডেট
                        </span>
                    </div>

                    <div class="p-4">
                        <div class="tracking-timeline" id="timeline-container">
                            @forelse($order->trackingEvents as $event)
                                <div class="timeline-item mb-4 pb-3 border-bottom position-relative" data-event-id="{{ $event->id }}">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <strong class="text-dark">{{ $event->title }}</strong>
                                        <span class="small text-muted font-monospace">{{ $event->created_at->format('M j, Y · g:i A') }}</span>
                                    </div>
                                    @if($event->description)
                                        <p class="small text-secondary mb-1">{{ $event->description }}</p>
                                    @endif
                                    @if($event->location)
                                        <div class="small text-success d-flex align-items-center gap-1">
                                            <span>📍</span> <span>{{ $event->location }}</span>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small" id="no-events-placeholder">
                                    কোনো ট্র্যাকিং আপডেট এখনও যুক্ত হয়নি।
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                {{-- Order Items Section --}}
                <section class="dashboard-card">
                    <div class="p-4 border-bottom">
                        <h2 class="h5 mb-0">অর্ডারের আইটেমসমূহ (Items)</h2>
                    </div>
                    <div class="p-4">
                        @foreach($order->items as $item)
                            <div class="d-flex justify-content-between gap-3 py-3 border-bottom">
                                <div>
                                    <strong class="d-block">{{ $item->product?->name ?? 'Product unavailable' }}</strong>
                                    <div class="small text-secondary">
                                        Quantity: {{ $item->quantity }} × ৳{{ number_format((float) $item->unit_price, 2) }}
                                        @if($item->farmer)
                                            · খামারি: {{ $item->farmer->name }}
                                        @endif
                                    </div>
                                </div>
                                <strong class="text-dark">৳{{ number_format((float) $item->subtotal, 2) }}</strong>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                            <span class="fw-bold">সর্বমোট প্রদেয় (Order total)</span>
                            <strong class="fs-4 text-success">৳{{ number_format((float) $order->total_amount, 2) }}</strong>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Delivery and Driver Details --}}
            <div class="col-lg-5">
                {{-- Driver and Dispatch Info (if assigned) --}}
                @if($order->driver_name || $order->vehicle_number || $order->driver_phone)
                    <section class="dashboard-card p-4 mb-4 border-success-subtle">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="fs-4 text-success">🚚</span>
                            <h2 class="h5 mb-0">পরিবহন ও চালকের তথ্য</h2>
                        </div>
                        @if($order->driver_name)
                            <p class="mb-2"><span class="summary-label d-block text-muted small">চালকের নাম:</span> <strong>{{ $order->driver_name }}</strong></p>
                        @endif
                        @if($order->driver_phone)
                            <p class="mb-2"><span class="summary-label d-block text-muted small">যোগাযোগ নম্বর:</span> <a href="tel:{{ $order->driver_phone }}" class="text-success fw-bold">{{ $order->driver_phone }}</a></p>
                        @endif
                        @if($order->vehicle_number)
                            <p class="mb-2"><span class="summary-label d-block text-muted small">গাড়ির নম্বর:</span> <span class="badge bg-light text-dark border">{{ $order->vehicle_number }}</span></p>
                        @endif
                        @if($order->current_location_text)
                            <p class="mb-2"><span class="summary-label d-block text-muted small">বর্তমান অবস্থান:</span> 📍 {{ $order->current_location_text }}</p>
                            @if($order->current_location_google_maps_url)
                                <a href="{{ $order->current_location_google_maps_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 mb-2">
                                    <span>📍</span> Google Maps-এ অবস্থান ↗
                                </a>
                            @endif
                        @endif
                    </section>
                @endif

                {{-- Payment Details Card --}}
                <section class="dashboard-card p-4 mb-4">
                    <h2 class="h5 mb-3">পেমেন্ট সংক্রান্ত তথ্য (Payment details)</h2>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">পেমেন্ট মেথড:</span>
                        <strong>{{ $order->payment_method_label }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">পরিশোধের অবস্থা:</span>
                        @if($order->isPaid())
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">✓ {{ $order->payment_status_label }}</span>
                        @elseif($order->payment_status === 'pending')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">{{ $order->payment_status_label }}</span>
                        @elseif($order->payment_status === 'failed')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">{{ $order->payment_status_label }}</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">{{ $order->payment_status_label }}</span>
                        @endif
                    </div>
                    @php
                        $latestPayment = $order->payments->first();
                    @endphp
                    @if($latestPayment)
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="text-muted small">ট্রানজেকশন ID:</span>
                            <code class="small text-dark">{{ $latestPayment->transaction_id }}</code>
                        </div>
                    @endif
                </section>

                {{-- Delivery Destination --}}
                <section class="dashboard-card p-4">
                    <h2 class="h5 mb-3">ডেলিভারির ঠিকানা (Delivery details)</h2>
                    <p class="mb-3">
                        <span class="summary-label d-block text-muted small">গন্তব্য ঠিকানা:</span>
                        <strong class="d-block">{{ $order->delivery_address }}</strong>
                    </p>
                    <p class="mb-3">
                        <span class="summary-label d-block text-muted small">গ্রাহকের ফোন:</span>
                        <strong>{{ $order->phone }}</strong>
                    </p>
                    @if($order->estimated_delivery_at)
                        <p class="mb-3">
                            <span class="summary-label d-block text-muted small">আনুমানিক ডেলিভারি:</span>
                            <span class="text-success fw-bold">{{ $order->estimated_delivery_at->format('M j, Y · g:i A') }}</span>
                        </p>
                    @endif
                    @if($order->delivery_google_maps_url)
                        <div class="pt-2 border-top d-flex flex-wrap gap-2">
                            <a href="{{ $order->delivery_google_maps_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                <span>📍</span> Google Maps-এ দেখুন ↗
                            </a>
                            @if($order->delivery_google_maps_directions_url)
                                <a href="{{ $order->delivery_google_maps_directions_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                                    <span>🧭</span> দিকনির্দেশনা (Directions) ↗
                                </a>
                            @endif
                        </div>
                    @endif
                </section>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                ← আমার সকল অর্ডারে ফিরে যান
            </a>
        </div>
    </div>
</main>

{{-- Auto-refresh Live Tracking Poller via Vanilla JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trackingUrl = @json(route('orders.tracking-data', $order));
    const statusBadge = document.getElementById('order-status-badge');
    const timelineContainer = document.getElementById('timeline-container');
    const eventCountBadge = document.getElementById('event-count-badge');
    const noEventsPlaceholder = document.getElementById('no-events-placeholder');
    let knownEventIds = new Set([
        @foreach($order->trackingEvents as $ev)
            {{ $ev->id }},
        @endforeach
    ]);

    const stepsOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    function updateStepIndicator(status) {
        const currentIndex = stepsOrder.indexOf(status);
        stepsOrder.forEach((stepKey, idx) => {
            const el = document.querySelector(`[data-step="${stepKey}"]`);
            if (!el) return;
            if (currentIndex !== -1 && idx === currentIndex) {
                el.className = 'p-3 rounded-3 border h-100 bg-success text-white border-success shadow-sm';
            } else if (currentIndex !== -1 && idx < currentIndex) {
                el.className = 'p-3 rounded-3 border h-100 bg-success-subtle text-success-emphasis border-success-subtle';
            } else {
                el.className = 'p-3 rounded-3 border h-100 bg-light text-muted';
            }
        });
    }

    let pollInterval = null;

    async function pollTrackingUpdates() {
        if (document.hidden) return;
        try {
            const res = await fetch(trackingUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            if (res.status === 401 || res.status === 403) {
                const liveEl = document.getElementById('live-indicator');
                if (liveEl) {
                    liveEl.innerHTML = '<span class="text-danger small">⚠️ ট্র্যাকিং অনুমোদনের অভাব।</span>';
                }
                if (pollInterval) clearInterval(pollInterval);
                return;
            }
            if (!res.ok) return;
            const data = await res.json();

            // Update status badge
            if (statusBadge && data.status_label) {
                statusBadge.textContent = data.status_label;
            }

            // Update step indicator
            if (data.status) {
                updateStepIndicator(data.status);
            }

            // Append newly arrived timeline events
            if (Array.isArray(data.events)) {
                let addedAny = false;
                data.events.forEach(ev => {
                    if (!knownEventIds.has(ev.id)) {
                        knownEventIds.add(ev.id);
                        addedAny = true;

                        const item = document.createElement('div');
                        item.className = 'timeline-item mb-4 pb-3 border-bottom position-relative bg-success-subtle p-2 rounded';
                        item.setAttribute('data-event-id', ev.id);
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong class="text-dark">${ev.title || 'Status update'}</strong>
                                <span class="small text-muted font-monospace">${ev.time}</span>
                            </div>
                            ${ev.description ? `<p class="small text-secondary mb-1">${ev.description}</p>` : ''}
                            ${ev.location ? `<div class="small text-success d-flex align-items-center gap-1"><span>📍</span> <span>${ev.location}</span></div>` : ''}
                        `;
                        if (noEventsPlaceholder) noEventsPlaceholder.remove();
                        timelineContainer.appendChild(item);
                    }
                });

                if (addedAny && eventCountBadge) {
                    eventCountBadge.textContent = `${knownEventIds.size} টি আপডেট`;
                }
            }
        } catch (e) {
            // Poller silently retries
        }
    }

    // Poll every 4 seconds
    pollInterval = setInterval(pollTrackingUpdates, 4000);
});
</script>
@endsection
