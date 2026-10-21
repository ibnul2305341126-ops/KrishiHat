
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KrishiHat | কৃষকের বাজার</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f7faf7;
            color: #24352a;
            font-family: Arial, sans-serif;
        }

        .navbar { background: #146c43; }

        .brand {
            font-size: 25px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .hero {
            background: linear-gradient(120deg, #14532d, #22a06b);
            color: white;
            padding: 65px 20px;
            border-radius: 0 0 28px 28px;
        }

        .hero h1 {
            font-size: clamp(30px, 5vw, 48px);
            font-weight: bold;
        }

        .search-box {
            max-width: 650px;
            margin: 25px auto 0;
        }

        .category-card {
            background: white;
            border: 1px solid #e1ebe3;
            border-radius: 15px;
            padding: 22px 10px;
            text-align: center;
            height: 100%;
            transition: 0.2s;
        }

        .category-card:hover {
            border-color: #198754;
            transform: translateY(-3px);
        }

        .category-link {
            color: #24352a;
            text-decoration: none;
        }

        .product-card {
            background: white;
            border: 1px solid #e5ebe5;
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            transition: 0.2s;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 22px #00000012;
        }

        .product-image,
        .product-placeholder {
            display: flex;
            width: 100%;
            height: 190px;
            object-fit: cover;
            background: #e9f5e9;
            justify-content: center;
            align-items: center;
        }

        .product-placeholder { font-size: 65px; }

        .price {
            font-size: 23px;
            font-weight: bold;
            color: #198754;
        }

        .section-title { font-weight: bold; }

        .flash-message { margin-bottom: 20px; }

        footer {
            background: #123d29;
            color: white;
            padding: 28px 0;
            margin-top: 55px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="brand" href="{{ route('home') }}">🌾 KrishiHat</a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarMenu"
                    aria-controls="navbarMenu"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('home') }}">হোম</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#categories">ক্যাটাগরি</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#products">পণ্যসমূহ</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cart.index') }}">
                            🛒 কার্ট ({{ array_sum(session('cart', [])) }})
                        </a>
                    </li>

                    @guest
                        <li class="nav-item ms-lg-2">
                            <a href="{{ route('login') }}"
                               class="btn btn-outline-light btn-sm my-1">
                                Login
                            </a>
                        </li>

                        <li class="nav-item ms-lg-2">
                            <a href="{{ route('register') }}"
                               class="btn btn-warning btn-sm my-1">
                                Register
                            </a>
                        </li>
                    @endguest

                    @auth
                        <li class="nav-item ms-lg-2">
                            <span class="nav-link text-white">
                                👤 {{ Auth::user()->name }}
                            </span>
                        </li>

                        {{-- Buyer Order Tracking --}}
                        @if (Auth::user()->role === 'buyer')
                            <li class="nav-item ms-lg-2">
                                <a href="{{ route('orders.index') }}"
                                   class="btn btn-warning btn-sm my-1">
                                    📦 আমার অর্ডার / ট্র্যাকিং
                                </a>
                            </li>
                        @endif

                        {{-- Farmer Dashboard --}}
                        @if (Auth::user()->role === 'farmer')
                            <li class="nav-item ms-lg-2">
                                <a href="{{ route('farmer.dashboard') }}"
                                   class="btn btn-light btn-sm my-1">
                                    🌾 Farmer Dashboard
                                </a>
                            </li>
                        @endif

                        {{-- Logout --}}
                        <li class="nav-item ms-lg-2">
                            <form action="{{ route('logout') }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit"
                                        class="btn btn-outline-light btn-sm my-1">
                                    Logout
                                </button>
                            </form>
                        </li>
                    @endauth

                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero text-center">
        <div class="container">
            <h1>কৃষকের পণ্য, সবার বাজার</h1>

            <p class="lead mt-3">
                কৃষকের কাছ থেকে সরাসরি তাজা কৃষিপণ্য খুঁজে নিন।
            </p>

            <form action="{{ route('home') }}"
                  method="GET" class="search-box">
                <div class="input-group input-group-lg">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="পণ্যের নাম লিখুন..."
                           value="{{ request('search') }}"
                           aria-label="পণ্য খুঁজুন">

                    <button type="submit"
                            class="btn btn-warning fw-bold">
                        খুঁজুন
                    </button>
                </div>
            </form>
        </div>
    </section>

    <main class="container py-5">

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show flash-message"
                 role="alert">
                {{ session('success') }}

                <button type="button" class="btn-close"
                        data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Error Message -->
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show flash-message"
                 role="alert">
                {{ session('error') }}

                <button type="button" class="btn-close"
                        data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Categories -->
        <section id="categories" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">পণ্যের ক্যাটাগরি</h2>

                <a href="{{ route('home') }}" class="text-success">
                    সব দেখুন
                </a>
            </div>

            <div class="row g-3">
                @forelse ($categories as $category)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="category-link"
                           href="{{ route('home') }}?category={{ urlencode($category) }}#products">
                            <div class="category-card">
                                <div class="fs-1 mb-2">🌱</div>
                                <h6 class="mb-0">{{ $category }}</h6>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted">
                            এখনো কোনো ক্যাটাগরি যোগ করা হয়নি।
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Products -->
        <section id="products">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h2 class="section-title mb-1">আমাদের পণ্যসমূহ</h2>
                    <p class="text-muted mb-0">
                        কৃষকের কাছ থেকে পছন্দের পণ্য বেছে নিন।
                    </p>
                </div>

                @if (request()->filled('search') || request()->filled('category'))
                    <a href="{{ route('home') }}" class="btn btn-outline-success">
                        সব পণ্য দেখুন
                    </a>
                @endif
            </div>

            @if (request()->filled('search'))
                <p>
                    <strong>খোঁজার ফলাফল:</strong>
                    {{ request('search') }}
                </p>
            @endif

            @if (request()->filled('category'))
                <p>
                    <strong>ক্যাটাগরি:</strong>
                    {{ request('category') }}
                </p>
            @endif

            <div class="row g-4">
                @forelse ($products as $product)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="product-card">

                            <!-- Product Image -->
                            @if (!empty($product->image))
                                <img src="{{ asset($product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="product-image"
                                     loading="lazy">
                            @else
                                <div class="product-placeholder">🌾</div>
                            @endif

                            <!-- Product Information -->
                            <div class="p-3">
                                <span class="badge text-bg-success mb-2">
                                    {{ $product->category ?: 'অন্যান্য' }}
                                </span>

                                <h5 class="fw-bold">{{ $product->name }}</h5>

                                <p class="text-muted small">
                                    {{ $product->description ?: 'কোনো বিবরণ দেওয়া হয়নি।' }}
                                </p>

                                <div class="price">
                                    ৳{{ number_format((float) $product->price, 2) }}
                                    <small class="text-muted fs-6">
                                        / {{ $product->unit ?: 'একক' }}
                                    </small>
                                </div>

                                <!-- Stock Status -->
                                <p class="small mt-2 mb-3">
                                    @if ($product->stock > 0)
                                        <span class="text-success">
                                            স্টকে আছে: {{ $product->stock }}
                                            {{ $product->unit ?: 'একক' }}
                                        </span>
                                    @else
                                        <span class="text-danger">স্টক শেষ</span>
                                    @endif
                                </p>

                                <!-- Product Details -->
                                <a href="{{ route('products.show', $product->id) }}"
                                   class="btn btn-success w-100">
                                    বিস্তারিত দেখুন
                                </a>

                                <!-- Add to Cart -->
                                @if ($product->stock > 0)
                                    <form action="{{ route('cart.add', $product->id) }}"
                                          method="POST" class="mt-2">
                                        @csrf

                                        <button type="submit"
                                                class="btn btn-outline-success w-100">
                                            🛒 Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <button type="button"
                                            class="btn btn-secondary w-100 mt-2"
                                            disabled>
                                        স্টক শেষ
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center bg-white rounded-4 p-5">
                            <div class="display-3">🔎</div>

                            <h4 class="mt-3">কোনো পণ্য পাওয়া যায়নি</h4>

                            <p class="text-muted">
                                অন্য নামে খুঁজে দেখুন অথবা সব পণ্য দেখুন।
                            </p>

                            <a href="{{ route('home') }}" class="btn btn-success">
                                সব পণ্য দেখুন
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <h4 class="fw-bold">🌾 KrishiHat</h4>

            <p class="mb-1">
                বাংলাদেশের কৃষক ও ক্রেতার ডিজিটাল বাজার
            </p>

            <small>
                © {{ date('Y') }} KrishiHat. সর্বস্বত্ব সংরক্ষিত।
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
