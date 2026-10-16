
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $product->name }} | KrishiHat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f7faf7;
            color: #24352a;
        }

        .navbar {
            background: #146c43;
        }

        .detail-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #e5ebe5;
        }

        .product-image {
            width: 100%;
            height: 380px;
            object-fit: cover;
            background: #e9f5e9;
        }

        .placeholder {
            height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 100px;
            background: #e9f5e9;
        }

        .price {
            color: #198754;
            font-size: 30px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            🌾 KrishiHat
        </a>

        <a href="{{ url('/') }}" class="btn btn-outline-light">
            হোমে ফিরে যান
        </a>
    </div>
</nav>

<div class="container py-5">

    <a href="{{ url('/') }}" class="text-success text-decoration-none">
        ← সব পণ্যে ফিরে যান
    </a>

    <div class="detail-card mt-4">
        <div class="row g-0">

            <div class="col-md-6">
                @if ($product->image)
                    <img
                        src="{{ asset('storage/' . $product->image) }}"
                        alt="{{ $product->name }}"
                        class="product-image"
                    >
                @else
                    <div class="placeholder">🌾</div>
                @endif
            </div>

            <div class="col-md-6 p-4 p-lg-5">
                <span class="badge text-bg-success mb-3">
                    {{ $product->category ?: 'অন্যান্য' }}
                </span>

                <h1 class="fw-bold">{{ $product->name }}</h1>

                <p class="text-muted mt-3">
                    {{ $product->description ?: 'এই পণ্যের কোনো বিবরণ দেওয়া হয়নি।' }}
                </p>

                <div class="price mt-4">
                    ৳{{ number_format((float) $product->price, 2) }}
                    <small class="fs-6 text-muted">
                        / {{ $product->unit ?: 'একক' }}
                    </small>
                </div>

                <hr>

                <p>
                    <strong>ক্যাটাগরি:</strong>
                    {{ $product->category ?: 'অন্যান্য' }}
                </p>

                <p>
                    <strong>স্টক:</strong>
                    @if ($product->stock > 0)
                        <span class="text-success">
                            {{ $product->stock }}
                            {{ $product->unit ?: 'একক' }} উপলব্ধ
                        </span>
                    @else
                        <span class="text-danger">স্টক শেষ</span>
                    @endif
                </p>

                @if ($product->stock > 0)
                    <button type="button"
                            class="btn btn-success btn-lg w-100 mt-3"
                            onclick="alert('অর্ডার করার সুবিধা পরবর্তী ধাপে যুক্ত করা হবে।')">
                        🛒 অর্ডার করুন
                    </button>
                @else
                    <button class="btn btn-secondary btn-lg w-100 mt-3" disabled>
                        স্টক শেষ
                    </button>
                @endif

                <p class="small text-muted mt-3 mb-0">
                    নোট: অর্ডার বাটনটি বর্তমানে Demo; প্রকৃত অর্ডার ও পেমেন্ট
                    সিস্টেম এখনো যুক্ত করা হয়নি।
                </p>
            </div>

        </div>
    </div>
</div>

</body>
</html>
