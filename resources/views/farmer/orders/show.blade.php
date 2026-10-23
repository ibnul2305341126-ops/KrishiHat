@extends('layouts.app')
@section('title', 'Farmer order #' . $order->id . ' | KrishiHat')

@section('content')
<main class="dashboard-main py-4 py-lg-5">
    <div class="container">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('farmer.dashboard') }}">Farmer dashboard</a></li>
                <li class="breadcrumb-item active">Order #{{ $order->id }}</li>
            </ol>
        </nav>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="status">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="eyebrow mb-0">FARMER ORDER FULFILLMENT</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">
                        {{ $order->tracking_code }}
                    </span>
                </div>
                <h1 class="dashboard-title mb-1">Order #{{ $order->id }}</h1>
                <p class="text-secondary mb-0">Placed {{ $order->created_at->format('M j, Y · g:i A') }}</p>
            </div>
            <span class="badge text-bg-light border fs-6 px-3 py-2 fw-semibold">
                {{ $order->status_label }}
            </span>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                {{-- Products in this order --}}
                <section class="dashboard-card p-4 mb-4">
                    <h2 class="h5 mb-3">Your products in this order</h2>
                    @foreach($order->items as $item)
                        <div class="d-flex justify-content-between gap-3 py-3 border-bottom">
                            <div>
                                <strong>{{ $item->product?->name ?? 'Product unavailable' }}</strong>
                                <div class="small text-secondary">
                                    {{ $item->quantity }} {{ $item->product?->unit ?? 'units' }} × ৳{{ number_format((float) $item->unit_price, 2) }}
                                </div>
                            </div>
                            <strong class="text-dark">৳{{ number_format((float) $item->subtotal, 2) }}</strong>
                        </div>
                    @endforeach
                </section>

                {{-- Tracking Timeline History --}}
                <section class="dashboard-card p-4">
                    <h2 class="h5 mb-3">Recorded tracking events</h2>
                    <div class="tracking-timeline">
                        @forelse($order->trackingEvents as $event)
                            <div class="timeline-item mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong class="text-dark">{{ $event->title }}</strong>
                                    <span class="small text-muted font-monospace">{{ $event->created_at->format('M j, Y · g:i A') }}</span>
                                </div>
                                @if($event->description)
                                    <p class="small text-secondary mb-1">{{ $event->description }}</p>
                                @endif
                                @if($event->location)
                                    <small class="text-success d-block">📍 {{ $event->location }}</small>
                                @endif
                            </div>
                        @empty
                            <p class="small text-muted mb-0">No tracking events recorded yet.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                {{-- Buyer and Delivery details --}}
                <section class="dashboard-card p-4 mb-4">
                    <h2 class="h5 mb-3">Buyer and delivery</h2>
                    <p class="mb-2"><strong>{{ $order->buyer?->name ?? 'Account unavailable' }}</strong></p>
                    <p class="small text-secondary mb-2">📍 {{ $order->delivery_address }}</p>
                    <p class="small text-secondary mb-2">Phone: <strong>{{ $order->phone }}</strong></p>
                    <div class="p-2 bg-light rounded small mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">পেমেন্ট মেথড:</span>
                            <strong>{{ $order->payment_method_label }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted">পরিশোধের অবস্থা:</span>
                            @if($order->isPaid())
                                <span class="badge bg-success-subtle text-success border border-success-subtle">✓ {{ $order->payment_status_label }}</span>
                            @elseif($order->payment_status === 'pending')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ $order->payment_status_label }}</span>
                            @elseif($order->payment_status === 'failed')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $order->payment_status_label }}</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $order->payment_status_label }}</span>
                            @endif
                        </div>
                    </div>

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

                {{-- Status Update Form --}}
                <section class="dashboard-card p-4">
                    <h2 class="h5 mb-3">Update Order Status</h2>
                    @if($canUpdateStatus)
                        <form action="{{ route('farmer.orders.update', $order) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="status" class="form-label">Order status</label>
                                <select id="status" name="status" class="form-select" required>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" @selected($order->status === $status)>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label small text-secondary">Current Location (ঐচ্ছিক / Optional)</label>
                                <input id="location" type="text" name="location" class="form-control form-control-sm" value="{{ old('location', $order->current_location_text) }}" placeholder="e.g. Bogura Hub, Mohakhali Depot">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label for="driver_name" class="form-label small text-secondary">Driver Name (Optional)</label>
                                    <input id="driver_name" type="text" name="driver_name" class="form-control form-control-sm" value="{{ old('driver_name', $order->driver_name) }}" placeholder="Driver's name">
                                </div>
                                <div class="col-sm-6">
                                    <label for="vehicle_number" class="form-label small text-secondary">Vehicle No. (Optional)</label>
                                    <input id="vehicle_number" type="text" name="vehicle_number" class="form-control form-control-sm" value="{{ old('vehicle_number', $order->vehicle_number) }}" placeholder="DHAKA-METRO-11">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label small text-secondary">Status note / update message</label>
                                <textarea id="description" name="description" class="form-control form-control-sm" rows="2" placeholder="Brief note explaining current shipment progress..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-brand w-100">Update status</button>
                            <p class="small text-secondary mt-3 mb-0">Choices use statuses already recorded in KrishiHat. No payment status is implied.</p>
                        </form>
                    @else
                        <p class="small text-secondary mb-0">
                            This order contains products from multiple farmers, so its shared status cannot be changed from this seller view.
                        </p>
                    @endif
                </section>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('farmer.dashboard') }}#farmer-orders" class="btn btn-outline-secondary">
                ← Back to farmer dashboard
            </a>
        </div>
    </div>
</main>
@endsection