
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KrishiHat - Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2>🛒 আমার Cart</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php $total = 0; @endphp

    @forelse ($products as $product)
        @php
            $quantity = $cart[$product->id] ?? 0;
            $subtotal = $product->price * $quantity;
            $total += $subtotal;
        @endphp

        <div class="card mb-3">
            <div class="card-body">
                <h5>{{ $product->name }}</h5>
                <p>পরিমাণ: {{ $quantity }} {{ $product->unit }}</p>
                <p>দাম: ৳{{ number_format($product->price, 2) }}</p>
                <strong>মোট: ৳{{ number_format($subtotal, 2) }}</strong>
            </div>
        </div>
    @empty
        <p>তোমার Cart এখন খালি।</p>
    @endforelse

    <h4>সর্বমোট: ৳{{ number_format($total, 2) }}</h4>

    <a href="{{ url('/') }}" class="btn btn-success mt-3">
@if (count($products) > 0)
    @auth
        @if (auth()->user()->role === 'buyer')
            <a href="{{ route('checkout.index') }}"
               class="btn btn-primary mt-3">
                Checkout করুন
            </a>
        @endif
    @else
        <a href="{{ route('login') }}" class="btn btn-primary mt-3">
            লগইন করে Checkout করুন
        </a>
    @endauth
@endif
        বাজারে ফিরে যাও
    </a>
</div>
</body>
</html>
