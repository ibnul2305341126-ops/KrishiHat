
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KrishiHat - Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4">অর্ডার সম্পন্ন করুন</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5>আপনার পণ্য</h5>

            @foreach ($products as $product)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>
                        {{ $product->name }} × {{ $cart[$product->id] }}
                    </span>
                    <span>
                        ৳{{ number_format($product->price * $cart[$product->id], 2) }}
                    </span>
                </div>
            @endforeach

            <h5 class="mt-3">
                সর্বমোট: ৳{{ number_format($total, 2) }}
            </h5>
        </div>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">ডেলিভারির ঠিকানা</label>
            <textarea name="delivery_address"
                      class="form-control"
                      rows="3"
                      required>{{ old('delivery_address') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">ফোন নম্বর</label>
            <input type="text"
                   name="phone"
                   class="form-control"
                   value="{{ old('phone') }}"
                   maxlength="30"
                   required>
        </div>

        <button type="submit" class="btn btn-success">
            অর্ডার নিশ্চিত করুন
        </button>

        <a href="{{ route('cart.index') }}" class="btn btn-secondary">
            Cart-এ ফিরে যান
        </a>
    </form>
</div>

</body>
</html>