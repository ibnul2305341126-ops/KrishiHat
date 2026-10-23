@extends('layouts.app')
@section('title', 'Your cart | KrishiHat')

@section('content')
<main class="container py-4 py-lg-5" style="max-width: 1000px">
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Marketplace</a></li>
            <li class="breadcrumb-item active" aria-current="page">Your cart</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <span class="eyebrow text-success fw-semibold">YOUR MARKET BASKET</span>
            <h1 class="dashboard-title h2 mb-0">Shopping cart</h1>
        </div>
        <a href="{{ route('home') }}#products" class="btn btn-outline-success">Continue browsing</a>
    </div>

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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php $total = 0; @endphp

    @forelse($products as $product)
        @php
            $quantity = (int) ($cart[$product->id] ?? 0);
            $subtotal = (float) $product->price * $quantity;
            $total += $subtotal;

            $coverPhoto = $product->photos?->first();
            $thumbnail = null;
            if ($coverPhoto) {
                $thumbnail = asset('storage/' . $coverPhoto->path);
            } elseif ($product->image) {
                if (\Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://'])) {
                    $thumbnail = $product->image;
                } elseif (\Illuminate\Support\Str::startsWith($product->image, ['images/', 'products/'])) {
                    $thumbnail = asset($product->image);
                } else {
                    $thumbnail = asset('storage/' . $product->image);
                }
            }
        @endphp

        <article class="dashboard-card mb-3 p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center">
                <div class="d-flex align-items-center gap-3">
                    @if($thumbnail)
                        <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="rounded shadow-sm flex-shrink-0" width="76" height="60" style="object-fit: cover;">
                    @else
                        <div class="bg-light rounded shadow-sm flex-shrink-0 d-flex align-items-center justify-content-center text-muted fs-4" style="width: 76px; height: 60px;">🌱</div>
                    @endif
                    <div>
                        <a href="{{ route('products.show', $product) }}" class="h5 d-block mb-1 text-decoration-none text-dark fw-bold">
                            {{ $product->name }}
                        </a>
                        <span class="text-secondary">৳{{ number_format((float) $product->price, 2) }} / {{ $product->unit ?: 'unit' }}</span>
                        @php($pMin = (int) ($product->min_order_quantity ?? 1))
                        <div class="small text-secondary mt-1">
                            {{ $product->stock }} {{ $product->unit ?: 'units' }} available
                            @if($pMin > 1)
                                <span class="badge bg-warning-subtle text-dark border ms-1">সর্বনিম্ন: {{ $pMin }} {{ $product->unit ?: 'units' }}</span>
                            @endif
                            @if(!empty($product->district))
                                <span class="ms-2 text-muted">📍 {{ $product->district }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between justify-content-md-end">
                    <form action="{{ route('cart.update', $product) }}" method="POST" class="d-flex gap-1 align-items-center">
                        @csrf
                        @method('PUT')
                        <label for="quantity-{{ $product->id }}" class="visually-hidden">Quantity for {{ $product->name }}</label>
                        <div class="input-group" style="width: 140px;">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="let inp = document.getElementById('quantity-{{ $product->id }}'); if (parseInt(inp.value) > parseInt(inp.min)) { inp.value = parseInt(inp.value) - 1; inp.form.submit(); }">-</button>
                            <input id="quantity-{{ $product->id }}" class="form-control text-center px-1" type="number" name="quantity" min="{{ $pMin }}" max="{{ $product->stock }}" value="{{ $quantity }}" required>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="let inp = document.getElementById('quantity-{{ $product->id }}'); if (parseInt(inp.value) < parseInt(inp.max)) { inp.value = parseInt(inp.value) + 1; inp.form.submit(); }">+</button>
                        </div>
                        <button class="btn btn-sm btn-outline-success" type="submit">Update</button>
                    </form>

                    <strong class="fs-5 text-success text-nowrap">৳{{ number_format($subtotal, 2) }}</strong>

                    <form action="{{ route('cart.remove', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('পণ্যটি কার্ট থেকে সরিয়ে ফেলতে চান?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm" type="submit">Remove</button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="empty-state text-center py-5">
            <div class="empty-icon fs-1 mb-3">🧺</div>
            <h2 class="h4">Your cart is empty</h2>
            <p class="text-secondary">Browse current listings and add something to your basket.</p>
            <a href="{{ route('home') }}#products" class="btn btn-brand">Browse products</a>
        </div>
    @endforelse

    @if($products->isNotEmpty())
        <section class="dashboard-card mt-4 p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <span class="summary-label text-muted small text-uppercase fw-semibold">Cart subtotal</span>
                    <h2 class="h3 mb-0 text-success fw-bold">৳{{ number_format($total, 2) }}</h2>
                    <small class="text-muted">ক্যাশ-অন-ডেলিভারি / ডেমো চেকআউট</small>
                </div>
                @auth
                    @if(auth()->user()->role === 'buyer')
                        <a href="{{ route('checkout.index') }}" class="btn btn-brand btn-lg">Continue to checkout →</a>
                    @else
                        <div class="text-end">
                            <span class="text-secondary d-block">Sign in with a buyer account to place an order.</span>
                            <small class="text-muted">(খামারি অ্যাকাউন্ট থেকে কেনাকাটা করা যায় না)</small>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-brand btn-lg">Log in to checkout</a>
                @endauth
            </div>
        </section>
    @endif
</main>
@endsection
