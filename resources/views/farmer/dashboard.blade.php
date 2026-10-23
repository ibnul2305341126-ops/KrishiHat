@extends('layouts.app')

@section('title', 'Farmer dashboard | KrishiHat')

@section('content')
@php
    $activeListingCount = $products->filter(fn ($product) => $product->stock > 0)->count();
    $pendingOrderCount = $orders->where('status', \App\Models\Order::INITIAL_STATUS)->count();
@endphp
<main class="dashboard-main farmer-dashboard">
    <div class="container py-4 py-lg-5">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Marketplace</a></li>
                <li class="breadcrumb-item active" aria-current="page">Farmer dashboard</li>
            </ol>
        </nav>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="status">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert"><strong>Please review the following:</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @if(auth()->user()->verification_status === 'unverified')
            <div class="alert alert-warning border border-warning-subtle shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 rounded-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="fs-2">⚠️</span>
                    <div>
                        <strong class="d-block text-dark">আপনার খামারি প্রোফাইল এখনো ভেরিফাইড নয়!</strong>
                        <small class="text-secondary">নিরাপদ বেচাকেনা ও নতুন পণ্য আপলোড করতে জাতীয় পরিচয়পত্র (NID) ও ট্রেড লাইসেন্স জমা দিন।</small>
                    </div>
                </div>
                <a href="{{ route('profile.show') }}#farmer-verification" class="btn btn-sm btn-warning text-dark fw-bold">
                    🪪 ভেরিফিকেশন জমা দিন →
                </a>
            </div>
        @elseif(auth()->user()->verification_status === 'pending')
            <div class="alert alert-info border border-info-subtle shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 rounded-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="fs-2">⏳</span>
                    <div>
                        <strong class="d-block text-dark">আপনার ভেরিফিকেশন আবেদনটি অ্যাডমিন প্যানেলে অপেক্ষমাণ রয়েছে।</strong>
                        <small class="text-secondary">অ্যাডমিন যাচাই-বাছাই শেষে অনুমোদন দিলে আপনার কেনাবেচা পুরোপুরি সক্রিয় হবে।</small>
                    </div>
                </div>
                <a href="{{ route('profile.show') }}#farmer-verification" class="btn btn-sm btn-outline-info text-dark">
                    স্ট্যাটাস দেখুন
                </a>
            </div>
        @elseif(auth()->user()->verification_status === 'rejected')
            <div class="alert alert-danger border border-danger-subtle shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 rounded-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="fs-2">❌</span>
                    <div>
                        <strong class="d-block text-dark">আপনার ভেরিফিকেশন আবেদন বাতিল করা হয়েছে!</strong>
                        <small class="text-danger">কারণ: {{ auth()->user()->verification_rejection_reason }}</small>
                    </div>
                </div>
                <a href="{{ route('profile.show') }}#farmer-verification" class="btn btn-sm btn-danger fw-bold">
                    সংশোধন করে পুনরায় জমা দিন →
                </a>
            </div>
        @endif

        <header id="overview" class="farmer-dashboard-heading">
            <div>
                <span class="eyebrow">FARMER WORKSPACE</span>
                <h1 class="dashboard-title">Welcome, {{ auth()->user()->name }}</h1>
                <p class="text-secondary mb-0">Keep your listings up to date and follow orders for your products.</p>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-brand btn-lg"><span aria-hidden="true">＋</span> Add product</a>
        </header>

        <div class="row g-4 align-items-start farmer-dashboard-grid">
            <aside class="col-lg-3" aria-label="Farmer dashboard navigation">
                <nav class="farmer-sidebar dashboard-card" aria-label="Dashboard sections">
                    <span class="farmer-sidebar-label">WORKSPACE</span>
                    <a class="farmer-side-link active" href="#overview"><span aria-hidden="true">⌂</span> Overview</a>
                    <a class="farmer-side-link" href="#farmer-products"><span aria-hidden="true">▣</span> My products</a>
                    <a class="farmer-side-link farmer-side-link-add" href="{{ route('products.create') }}"><span aria-hidden="true">＋</span> Add product</a>
                    <a class="farmer-side-link" href="#farmer-orders"><span aria-hidden="true">▤</span> Orders <span class="farmer-nav-count">{{ $orders->count() }}</span></a>
                    <a class="farmer-side-link" href="{{ route('profile.show') }}#farmer-verification"><span aria-hidden="true">🪪</span> Verification</a>
                    <a class="farmer-side-link" href="{{ route('conversations.index') }}"><span aria-hidden="true">✉</span> Messages</a>
                    <a class="farmer-side-link" href="{{ route('profile.show') }}"><span aria-hidden="true">○</span> Profile</a>
                    <div class="farmer-sidebar-foot"><span class="farmer-status-dot"></span> Signed in as farmer</div>
                </nav>
            </aside>

            <div class="col-lg-9">
                <section aria-label="Farm summary" class="mb-4">
                    <div class="row g-3">
                        <div class="col-sm-6 col-xl-3"><article class="farmer-stat-card"><span class="farmer-stat-icon">▣</span><span class="summary-label">Total products</span><strong>{{ $products->count() }}</strong><small>Across your listings</small></article></div>
                        <div class="col-sm-6 col-xl-3"><article class="farmer-stat-card"><span class="farmer-stat-icon farmer-stat-icon-green">●</span><span class="summary-label">Active listings</span><strong>{{ $activeListingCount }}</strong><small>Products with stock available</small></article></div>
                        <div class="col-sm-6 col-xl-3"><article class="farmer-stat-card"><span class="farmer-stat-icon farmer-stat-icon-amber">▤</span><span class="summary-label">Pending orders</span><strong>{{ $pendingOrderCount }}</strong><small>Waiting for order updates</small></article></div>
                        <div class="col-sm-6 col-xl-3"><article class="farmer-stat-card farmer-stock-stat"><span class="farmer-stat-icon farmer-stat-icon-blue">↗</span><span class="summary-label">Available stock</span><strong class="farmer-stock-values">@forelse ($stockByUnit as $unit => $quantity)<span>{{ number_format($quantity) }} <small>{{ $unit }}</small></span>@empty<span>0 <small>units</small></span>@endforelse</strong><small>Grouped by unit</small></article></div>
                    </div>
                    <p class="farmer-metric-note mt-2 mb-0">Sales totals are not shown because KrishiHat does not record payment transactions.</p>
                </section>

                <section id="farmer-products" class="dashboard-card farmer-dashboard-section mb-4" aria-labelledby="farmer-products-heading">
                    <div class="farmer-section-heading">
                        <div><span class="eyebrow">YOUR MARKETPLACE</span><h2 id="farmer-products-heading">My products</h2><p>Manage the products and stock buyers see on the marketplace.</p></div>
                        <a href="{{ route('products.create') }}" class="btn btn-outline-success">Add listing</a>
                    </div>
                    @if ($products->isEmpty())
                        <div class="farmer-empty-state"><span class="farmer-empty-icon" aria-hidden="true">✿</span><h3>Your marketplace starts here</h3><p>You haven’t published a product yet. Add your first listing when you’re ready.</p><a href="{{ route('products.create') }}" class="btn btn-brand">Create a listing</a></div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 farmer-products-table">
                                <thead><tr><th scope="col">Product</th><th scope="col">Price / unit</th><th scope="col">Available stock</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td><div class="d-flex align-items-center gap-3"><span class="farmer-product-initial" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}</span><span><a class="farmer-product-name" href="{{ route('products.show', $product) }}">{{ $product->name }}</a><small class="d-block text-secondary">{{ $product->category ?: 'Farm produce' }}</small></span></div></td>
                                            <td><strong>৳{{ number_format((float) $product->price, 2) }}</strong><span class="text-secondary"> / {{ $product->unit ?: 'unit' }}</span></td>
                                            <td>{{ number_format((int) $product->stock) }} {{ $product->unit ?: 'units' }}</td>
                                            <td><span class="badge rounded-pill {{ $product->stock > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->stock > 0 ? 'Active' : 'Sold out' }}</span></td>
                                            <td class="text-end text-nowrap"><a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-success">Edit</a> <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this listing? Products with order history cannot be deleted.')">Delete</button></form></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <section id="farmer-orders" class="dashboard-card farmer-dashboard-section mb-4" aria-labelledby="farmer-orders-heading">
                    <div class="farmer-section-heading"><div><span class="eyebrow">FULFILLMENT</span><h2 id="farmer-orders-heading">Orders for my products</h2><p>Only orders containing your products are listed here. An order does not confirm payment.</p></div><span class="farmer-section-total">{{ $orders->count() }} {{ \Illuminate\Support\Str::plural('order', $orders->count()) }}</span></div>
                    @forelse ($orders as $order)
                        <article class="farmer-order-row">
                            <div class="farmer-order-main"><div class="d-flex align-items-center flex-wrap gap-2"><strong>Order #{{ $order->id }}</strong><span class="badge text-bg-light border">{{ ucfirst($order->status) }}</span></div><p class="mb-1">{{ $order->buyer?->name ?? 'Account unavailable' }} <span aria-hidden="true">·</span> {{ $order->created_at->format('M j, Y') }}</p><small>@foreach ($order->items as $item){{ $item->product?->name ?? 'Product unavailable' }} × {{ $item->quantity }}@if (! $loop->last), @endif @endforeach</small></div>
                            <a href="{{ route('farmer.orders.show', $order) }}" class="btn btn-sm btn-outline-success">Review order</a>
                        </article>
                    @empty
                        <div class="farmer-empty-state farmer-empty-compact"><span class="farmer-empty-icon" aria-hidden="true">▤</span><h3>No orders yet</h3><p>Orders containing your products will appear here.</p></div>
                    @endforelse
                </section>

                <section id="farmer-offers" class="dashboard-card farmer-dashboard-section" aria-labelledby="farmer-offers-heading">
                    <div class="farmer-section-heading"><div><span class="eyebrow">BUYER NEGOTIATIONS</span><h2 id="farmer-offers-heading">Buyer price offers</h2><p>Offers for your listings. Accepting an offer does not place an order.</p></div><span class="farmer-section-total">{{ $offers->count() }} {{ \Illuminate\Support\Str::plural('offer', $offers->count()) }}</span></div>
                    @forelse ($offers as $offer)
                        <article class="farmer-offer-row">
                            <div class="d-flex flex-wrap justify-content-between gap-3">
                                <div><span class="summary-label">Offer #{{ $offer->id }}</span><h3 class="h5 mb-1">{{ $offer->product->name }}</h3><div class="small text-secondary">Buyer: {{ $offer->buyer->name }} · Quantity: {{ $offer->quantity }} {{ $offer->product->unit ?: 'units' }} · {{ $offer->created_at->format('M j, Y g:i A') }}</div></div>
                                <div class="d-flex align-items-start gap-2"><span class="badge text-bg-light border">{{ ucfirst($offer->status) }}</span><form action="{{ route('conversations.start-offer', $offer) }}" method="POST">@csrf<button type="submit" class="btn btn-sm btn-outline-success">Message buyer</button></form></div>
                            </div>
                            <div class="row g-3 mt-1"><div class="col-sm-6"><span class="summary-label">Buyer offer</span><strong class="d-block">৳{{ number_format((float) $offer->initial_price, 2) }} / {{ $offer->product->unit ?: 'unit' }}</strong></div><div class="col-sm-6"><span class="summary-label">Current proposed price</span><strong class="d-block">৳{{ number_format((float) $offer->current_price, 2) }} / {{ $offer->product->unit ?: 'unit' }}</strong></div></div>
                            <details class="mt-3"><summary class="fw-semibold">Negotiation history</summary><ol class="small text-secondary mt-2 mb-0">@foreach ($offer->events as $event)<li>{{ ucfirst(str_replace('_', ' ', $event->action)) }} by {{ $event->actor?->name ?? ucfirst($event->actor_role) }}@if ($event->price) — ৳{{ number_format((float) $event->price, 2) }}@endif · {{ $event->created_at->format('M j, Y g:i A') }}</li>@endforeach</ol></details>
                            @if ($offer->status === \App\Models\Offer::STATUS_PENDING)
                                <div class="farmer-offer-actions">
                                    <form action="{{ route('offers.farmer.respond', $offer) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="action" value="accept"><button class="btn btn-sm btn-success" type="submit">Accept offer</button></form>
                                    <form action="{{ route('offers.farmer.respond', $offer) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="action" value="reject"><button class="btn btn-sm btn-outline-danger" type="submit">Reject offer</button></form>
                                    <form action="{{ route('offers.farmer.respond', $offer) }}" method="POST" class="d-flex flex-wrap align-items-end gap-2">@csrf @method('PATCH')<input type="hidden" name="action" value="counter"><div><label class="form-label small mb-1" for="counter-price-{{ $offer->id }}">Counter price (৳ / {{ $offer->product->unit ?: 'unit' }})</label><input id="counter-price-{{ $offer->id }}" class="form-control form-control-sm" type="number" name="counter_price" min="0.01" max="99999999.99" step="0.01" required></div><button class="btn btn-sm btn-outline-success" type="submit">Send counter-offer</button></form>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="farmer-empty-state farmer-empty-compact"><span class="farmer-empty-icon" aria-hidden="true">↔</span><h3>No buyer offers yet</h3><p>Offers for your listings will appear here.</p></div>
                    @endforelse
                </section>
            </div>
        </div>
    </div>
</main>
@endsection
