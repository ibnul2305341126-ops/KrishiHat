<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>আমার অর্ডার - KrishiHat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">আমার অর্ডার</h2>

    @forelse ($orders as $order)
        <div class="card mb-3">
            <div class="card-body">
                <h5>Order #{{ $order->id }}</h5>
                <p>তারিখ: {{ $order->created_at->format('d-m-Y h:i A') }}</p>
                <p>মোট মূল্য: ৳{{ number_format($order->total_amount, 2) }}</p>
                <p>স্ট্যাটাস: <strong>{{ $order->status }}</strong></p>

                <a href="{{ route('orders.show', $order) }}"
                   class="btn btn-primary">
                    অর্ডার ট্র্যাক করুন
                </a>
            </div>
        </div>
    @empty
        <p>এখনো কোনো অর্ডার নেই।</p>
    @endforelse

    <a href="{{ route('home') }}" class="btn btn-secondary">
        বাজারে ফিরে যান
    </a>
</div>
</body>
</html>