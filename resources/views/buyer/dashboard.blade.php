@extends('layouts.app')
@section('title', 'Buyer dashboard | KrishiHat')
@section('content')
<main class="dashboard-main"><div class="container py-4 py-lg-5">
    <nav aria-label="Breadcrumb" class="mb-4"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Marketplace</a></li><li class="breadcrumb-item active" aria-current="page">Buyer dashboard</li></ol></nav>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><span class="eyebrow">YOUR BUYER ACCOUNT</span><h1 class="dashboard-title">Welcome, {{ auth()->user()->name }}</h1><p class="text-secondary mb-0">Browse products and review your recorded orders.</p></div><div class="d-flex gap-2"><a href="{{ route('profile.show') }}" class="btn btn-outline-success">Profile</a><a href="{{ route('home') }}#products" class="btn btn-brand">Browse products</a></div></div>
    <div class="row g-3 mb-4"><div class="col-sm-6 col-lg-4"><div class="summary-card"><span class="summary-icon">▣</span><div><span class="summary-label">Recorded orders</span><strong>{{ $orderCount }}</strong></div></div></div><div class="col-sm-6 col-lg-4"><div class="summary-card"><span class="summary-icon">৳</span><div><span class="summary-label">Order value recorded</span><strong>৳{{ number_format((float) $totalOrdered, 2) }}</strong></div></div></div><div class="col-sm-6 col-lg-4"><a href="{{ route('transactions.index') }}" class="summary-card summary-link"><span class="summary-icon">↗</span><div><span class="summary-label">Payment transactions</span><strong class="summary-action">View history</strong></div></a></div></div>
    <div class="alert alert-info">Order totals represent placed orders. KrishiHat does not currently record payment transactions or payment confirmations.</div>
    <section class="dashboard-card"><div class="d-flex justify-content-between align-items-center gap-3 p-4 border-bottom"><div><h2 class="h4 mb-1">Recent orders</h2><p class="text-secondary mb-0">Track the current status of your purchases.</p></div><a href="{{ route('orders.index') }}" class="text-link">Full purchase history →</a></div>
        @forelse($orders->take(5) as $order)<div class="p-4 border-bottom d-flex flex-column flex-sm-row justify-content-between gap-3"><div><strong>Order #{{ $order->id }}</strong><span class="badge text-bg-light border ms-2">{{ ucfirst($order->status) }}</span><div class="small text-secondary mt-1">Placed {{ $order->created_at->format('M j, Y') }} · {{ $order->items->count() }} line item(s)</div></div><div class="d-flex align-items-center gap-3"><strong>৳{{ number_format((float) $order->total_amount, 2) }}</strong><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-success">Details</a></div></div>@empty<div class="empty-state py-5"><div class="empty-icon">▣</div><h3>No orders yet</h3><p>Your purchases will appear here after you place an order.</p><a href="{{ route('home') }}#products" class="btn btn-brand">Find fresh products</a></div>@endforelse
    </section>
    <section id="my-offers" class="dashboard-card mt-4">
        <div class="p-4 border-bottom"><h2 class="h4 mb-1">My price offers</h2><p class="text-secondary mb-0">Review farmer responses and negotiation history. Accepted offers do not create orders.</p></div>
        @forelse($offers as $offer)
            <article class="p-4 border-bottom">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div><span class="summary-label">Offer #{{ $offer->id }}</span><h3 class="h5 mb-1"><a href="{{ route('products.show', $offer->product) }}">{{ $offer->product->name }}</a></h3><div class="small text-secondary">Farmer: {{ $offer->farmer->name }} · Quantity: {{ $offer->quantity }} {{ $offer->product->unit ?: 'units' }}</div></div>
                    <span class="badge text-bg-light border align-self-start">{{ ucfirst($offer->status) }}</span>
                </div>
                <div class="row g-3 mt-1"><div class="col-sm-6"><span class="summary-label">Your original price</span><strong class="d-block">৳{{ number_format((float) $offer->initial_price, 2) }} / {{ $offer->product->unit ?: 'unit' }}</strong></div><div class="col-sm-6"><span class="summary-label">Current proposed price</span><strong class="d-block">৳{{ number_format((float) $offer->current_price, 2) }} / {{ $offer->product->unit ?: 'unit' }}</strong></div></div>
                <details class="mt-3"><summary class="fw-semibold">Negotiation history</summary><ol class="small text-secondary mt-2 mb-0">@foreach($offer->events as $event)<li>{{ ucfirst(str_replace('_', ' ', $event->action)) }} by {{ $event->actor?->name ?? ucfirst($event->actor_role) }}@if($event->price) — ৳{{ number_format((float) $event->price, 2) }}@endif · {{ $event->created_at->format('M j, Y g:i A') }}</li>@endforeach</ol></details><div class="d-flex flex-wrap gap-2 mt-3"><form action="{{ route('conversations.start-offer', $offer) }}" method="POST">@csrf<button type="submit" class="btn btn-sm btn-outline-success">Message farmer</button></form>@if($offer->status === \App\Models\Offer::STATUS_ACCEPTED)<form action="{{ route('offers.order', $offer) }}" method="POST" class="row g-2 mt-2">@csrf<div class="col-md-5"><input class="form-control form-control-sm" name="delivery_address" maxlength="2000" placeholder="Delivery address" required></div><div class="col-md-4"><input class="form-control form-control-sm" name="phone" maxlength="30" placeholder="Phone" required></div><div class="col-md-3"><button class="btn btn-sm btn-success" type="submit">Place agreed order</button></div></form>@endif</div>
                @if($offer->status === \App\Models\Offer::STATUS_COUNTERED)
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <form action="{{ route('offers.counter-response', $offer) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="decision" value="accept"><button class="btn btn-sm btn-success" type="submit">Accept counter-offer</button></form>
                        <form action="{{ route('offers.counter-response', $offer) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="decision" value="reject"><button class="btn btn-sm btn-outline-secondary" type="submit">Reject counter-offer</button></form>
                    </div>
                @elseif($offer->status === \App\Models\Offer::STATUS_PENDING)
                    <form action="{{ route('offers.withdraw', $offer) }}" method="POST" class="mt-3">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger" type="submit">Withdraw offer</button></form>
                @endif
            </article>
        @empty
            <div class="empty-state py-5"><h3 class="h5">No offers yet</h3><p>Offers you send on product pages will appear here.</p><a href="{{ route('home') }}#products" class="btn btn-outline-success">Browse products</a></div>
        @endforelse
    </section>
    @if($latestOrder)<section class="dashboard-card p-4 mt-4"><h2 class="h5">Latest delivery details</h2><p class="mb-1">{{ $latestOrder->delivery_address }}</p><p class="text-secondary mb-0">{{ $latestOrder->phone }} · saved with order #{{ $latestOrder->id }}</p></section>@endif
</div></main>
@endsection
