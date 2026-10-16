<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Tracking - KrishiHat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2>Order #{{ $order->id }} ট্র্যাকিং</h2>

    <div class="card mt-4">
        <div class="card-body">
            <h5>বর্তমান স্ট্যাটাস: {{ $order->status }}</h5>
            <p>মোট মূল্য: ৳{{ number_format($order->total_amount, 2) }}</p>
            <p>ডেলিভারির ঠিকানা: {{ $order->delivery_address }}</p>
            <p>ফোন: {{ $order->phone }}</p>

            <h5 class="mt-4">পণ্যের তালিকা</h5>

            @foreach ($order->items as $item)
                <div class="border-bottom py-2">
                    {{ $item->product?->name ?? 'পণ্য পাওয়া যাচ্ছে না' }}
                    × {{ $item->quantity }}
                    — ৳{{ number_format($item->subtotal, 2) }}
                </div>
            @endforeach
        </div>
    </div>

    <a href="{{ route('orders.index') }}"
       class="btn btn-secondary mt-3">
        আমার অর্ডারে ফিরে যান
    </a>
</div>
</body>
</html>