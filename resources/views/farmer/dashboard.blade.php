
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - KrishiHat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f3f7f1;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: #176b38;
        }

        .dashboard-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
        }

        .page-title {
            color: #176b38;
            font-weight: 700;
        }

        .table thead th {
            background: #e8f3e9;
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">🌱 KrishiHat</a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white">
                {{ auth()->user()->name }}
            </span>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-light btn-sm" type="submit">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container py-5">

    <h2 class="page-title mb-2">Farmer Dashboard</h2>

{{-- Navigation Buttons --}}
<div class="d-flex flex-wrap gap-2 my-3">
    <a href="{{ route('home') }}" class="btn btn-outline-success">
        🏠 Home Page
    </a>

    <a href="{{ route('products.create') }}" class="btn btn-success">
        + নতুন পণ্য যোগ করুন
    </a>
</div>

<p class="text-secondary mb-4">
    স্বাগতম, {{ auth()->user()->name }}!
    KrishiHat-এ আপনার কৃষিপণ্যের তালিকা দেখুন।
</p>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>অনুগ্রহ করে নিচের ভুলগুলো ঠিক করুন:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Dashboard Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card dashboard-card p-4 h-100">
                <h6 class="text-secondary">মোট পণ্য</h6>
                <h2 class="page-title">{{ $products->count() }}</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card p-4 h-100">
                <h6 class="text-secondary">অ্যাকাউন্ট</h6>
                <h5 class="text-success mb-0">Farmer</h5>
            </div>
        </div>
    </div>

    {{-- Product List --}}
    <div class="card dashboard-card p-4">

        <div class="d-flex flex-wrap justify-content-between
                    align-items-center gap-3 mb-4">

            <h4 class="mb-0">কৃষিপণ্যের তালিকা</h4>

            {{-- Add Product Button --}}
            <a href="{{ route('products.create') }}"
               class="btn btn-success">
                + Add Product
            </a>
        </div>

        @if($products->isEmpty())

            <div class="alert alert-info mb-0">
                এখনো কোনো পণ্য যোগ করা হয়নি।
                নতুন পণ্য যোগ করতে <strong>Add Product</strong> বাটনে ক্লিক করুন।
            </div>

        @else

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th>পণ্যের নাম</th>
                            <th>ক্যাটাগরি</th>
                            <th>দাম</th>
                            <th>স্টক</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $product->name }}
                                </td>

                                <td>
                                    {{ $product->category ?: 'N/A' }}
                                </td>

                                <td>
                                    ৳{{ number_format((float) $product->price, 2) }}
                                    / {{ $product->unit ?: 'কেজি' }}
                                </td>

                                <td>
                                    @if((int) $product->stock > 0)
                                        <span class="badge bg-success">
                                            {{ $product->stock }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            স্টক শেষ
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        {{-- Edit Product Button --}}
                                        <a href="{{ route('products.edit', $product) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        @endif

        <div class="mt-4">
            <a href="{{ route('home') }}"
               class="btn btn-outline-success">
                ← বাজারে ফিরে যান
            </a>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>