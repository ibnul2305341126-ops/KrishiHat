@extends('layouts.app')
@section('title', $product->name . ' | KrishiHat')
@section('content')
<main class="container py-4 py-lg-5">
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-white p-3 rounded-3 shadow-xs border">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none fw-semibold">🏪 Marketplace</a></li>
            <li class="breadcrumb-item">
                @if($product->categoryRecord)
                    <a href="{{ route('categories.show', $product->categoryRecord->slug) }}" class="text-success text-decoration-none">{{ $product->categoryRecord->name }}</a>
                @else
                    <span class="text-secondary">{{ $product->category ?: 'Products' }}</span>
                @endif
            </li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="status">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-5">✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-5">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm border-0 mb-4" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <article class="detail-card bg-white rounded-4 border shadow-sm overflow-hidden" style="border-color: rgba(11, 106, 64, 0.12) !important;">
        <div class="row g-0">
            <div class="col-lg-6 product-detail-media position-relative bg-light">
                @if($product->photos->isNotEmpty())
                    <div id="productPhotos" class="carousel slide h-100" data-bs-ride="false">
                        <div class="carousel-inner h-100">
                            @foreach($product->photos as $photo)
                                <div class="carousel-item @if($loop->first)active @endif h-100">
                                    <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $product->name }} photo" class="product-detail-image w-100 h-100" loading="lazy" style="object-fit: cover; min-height: 440px;">
                                </div>
                            @endforeach
                        </div>
                        @if($product->photos->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#productPhotos" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productPhotos" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        @endif
                    </div>
                @elseif($product->image)
                    <img src="{{ \Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://']) ? $product->image : (\Illuminate\Support\Str::startsWith($product->image, ['images/', 'products/']) ? asset($product->image) : asset('storage/' . $product->image)) }}" alt="{{ $product->name }}" class="product-detail-image w-100 h-100" data-fallback-image style="object-fit: cover; min-height: 440px;">
                    <div class="product-detail-placeholder" data-image-placeholder hidden aria-hidden="true">✿</div>
                @else
                    <div class="product-detail-placeholder d-grid place-items-center h-100" style="min-height: 440px;" aria-hidden="true">✿</div>
                @endif
            </div>

            <div class="col-lg-6 p-4 p-lg-5 d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="category-label align-self-start">{{ $product->categoryRecord?->name ?: ($product->category ?: 'Farm produce') }}</span>
                    @if(\Illuminate\Support\Str::contains($product->name, 'Demo'))
                        <span class="badge bg-secondary-subtle text-secondary align-self-start">ডেমো ক্যাটালগ লিস্টিং (Demo Catalogue)</span>
                    @endif
                </div>

                <h1 class="display-6 fw-bold text-dark mt-1 mb-2">{{ $product->name }}</h1>
                <p class="text-secondary mt-1 mb-3" style="line-height: 1.6;">{{ $product->description ?: 'No additional product description has been provided.' }}</p>

                <div class="detail-price p-3 rounded-3 bg-light border d-inline-flex align-items-baseline gap-2 mb-3">
                    <span class="h2 mb-0 fw-bold text-success">৳{{ number_format((float) $product->price, 2) }}</span>
                    <span class="text-muted fw-semibold">/ {{ $product->unit ?: 'unit' }}</span>
                </div>

                <div class="stock-panel p-3 rounded-3 bg-light border d-flex align-items-center gap-2 mb-3">
                    <span class="stock-indicator {{ $product->stock > 0 ? 'available' : 'unavailable' }}"></span>
                    <span>
                        @if($product->stock > 0)
                            Available stock: {{ $product->stock }} {{ $product->unit ?: 'units' }}
                        @else
                            <strong class="stock-empty text-danger">Sold Out</strong>
                        @endif
                    </span>
                </div>

                @if($product->farmer)
                    <div class="seller-detail p-3 rounded-3 bg-white border shadow-xs d-flex align-items-center gap-3 mb-3">
                        @if($product->farmer->profile_photo_path)
                            <img src="{{ asset('storage/' . $product->farmer->profile_photo_path) }}" alt="{{ $product->farmer->name }}" class="rounded-circle border" width="44" height="44" style="object-fit:cover">
                        @else
                            <div class="rounded-circle bg-success text-white d-grid place-items-center fw-bold" style="width: 44px; height: 44px;">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->farmer->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div class="small text-muted">লিস্টিংকারী খামারি:</div>
                            <strong class="text-dark">{{ $product->farmer->name }}</strong>
                            @if($product->farmer->isFarmerVerified())
                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-2 small">🛡️ সরকারি NID ও লাইসেন্স যাচাইকৃত</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-2 small">⚠️ ভেরিফিকেশন প্রক্রিয়াধীন</span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="product-location-box p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-6 text-success">📍</span>
                            @if($product->formatted_address && $product->formatted_address !== 'অবস্থান নির্ধারিত নয়')
                                <span>খামারের অবস্থান: <strong>{{ $product->formatted_address }}</strong></span>
                                @if($product->hasExactCoordinates())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">যাচাইকৃত জিপিএস</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary small">আনুমানিক এলাকা</span>
                                @endif
                            @elseif($product->district || $product->upazila)
                                <span>খামারের অবস্থান: <strong>{{ $product->district }}{{ $product->upazila ? ' (' . $product->upazila . ')' : '' }}</strong></span>
                                @if($product->hasExactCoordinates())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">যাচাইকৃত জিপিএস</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary small">আনুমানিক এলাকা</span>
                                @endif
                            @else
                                <span class="text-muted">খামারের অবস্থান: অবস্থান দেওয়া হয়নি</span>
                            @endif
                        </div>
                    </div>

                    @if($product->google_maps_search_url)
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <a href="{!! $product->google_maps_search_url !!}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 rounded-2">
                                <span>📍</span> Google Maps-এ দেখুন {{ $product->hasExactCoordinates() ? '' : '(আনুমানিক)' }} ↗
                            </a>
                            @if($product->google_maps_directions_url)
                                <a href="{!! $product->google_maps_directions_url !!}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 rounded-2">
                                    <span>🧭</span> দিকনির্দেশনা (Directions) ↗
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                @if($product->reference_image)
                    <div class="product-reference-box p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ asset($product->reference_image) }}" alt="রেফারেন্স ফটো" class="rounded border" style="width: 55px; height: 55px; object-fit: cover;">
                            <div>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mb-1">📚 শস্য রেফারেন্স তথ্য ও আদর্শ নমুনা</span>
                                <div class="small fw-semibold text-dark">{{ $product->suggested_correction ?: $product->name }}</div>
                                <small class="text-muted d-block">
                                    উৎস: {{ $product->reference_image_source ?: 'সরকারি কৃষি বিপণন ও উইকিমিডিয়া' }} 
                                    @if($product->reference_image_license)({{ $product->reference_image_license }})@endif
                                </small>
                            </div>
                        </div>
                        <small class="text-secondary d-block mt-2" style="font-size: 0.75rem;">
                            * দ্রষ্টব্য: উপরের মূল ফটোটি খামারির নিজস্ব আপলোডকৃত। এই ছবিটি শিক্ষামূলক রেফারেন্স ও সঠিক জাত শনাক্তকরণের জন্য সংযুক্ত।
                        </small>
                    </div>
                @endif

                @php($minQty = (int) ($product->min_order_quantity ?? 1))
                @if($minQty > 1)
                    <div class="alert alert-warning py-2 px-3 mt-1 mb-3 small d-inline-flex align-items-center gap-2 border-warning rounded-3 shadow-xs">
                        <span class="fs-6">⚖️</span>
                        <span>এই পণ্যের জন্য <strong>সর্বনিম্ন অর্ডারের পরিমাণ (MOQ): {{ $minQty }} {{ $product->unit ?: 'units' }}</strong></span>
                    </div>
                @endif

                @if($product->stock > 0)
                    <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-2">
                        @csrf
                        <div class="row g-2">
                            <div class="col-5">
                                <label for="quantity" class="form-label small fw-semibold">Quantity @if($minQty > 1)<span class="text-danger fw-normal">(Min: {{ $minQty }})</span>@endif</label>
                                <input id="quantity" class="form-control form-control-lg rounded-3" type="number" name="quantity" min="{{ $minQty }}" max="{{ $product->stock }}" value="{{ max(1, $minQty) }}" required>
                            </div>
                            <div class="col-7 d-flex align-items-end">
                                <button type="submit" class="btn btn-brand btn-lg w-100 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" style="background: linear-gradient(135deg, #0B6A40 0%, #13814C 100%); border: 0;">
                                    <span>🛒</span> Add to cart
                                </button>
                            </div>
                        </div>
                        @if($minQty > 1)
                            <div class="form-text small text-muted mt-1">⚠️ পাইকারি ক্রয়ের জন্য কমপক্ষে {{ $minQty }} {{ $product->unit ?: 'units' }} নির্বাচন করুন।</div>
                        @endif
                    </form>
                @else
                    <div class="row g-2 mt-2">
                        <div class="col-4">
                            <label for="quantity" class="form-label small fw-semibold">Quantity</label>
                            <input id="quantity" class="form-control form-control-lg rounded-3" type="number" value="0" disabled>
                        </div>
                        <div class="col-8 d-flex align-items-end">
                            <button type="button" class="btn btn-secondary btn-lg w-100 rounded-3" disabled>Sold Out</button>
                        </div>
                    </div>
                @endif

                <a href="{{ route('home') }}#products" class="text-link mt-4 d-inline-flex align-items-center gap-1">← Back to products</a>
            </div>
        </div>
    </article>

    @auth
        @if(auth()->user()->role === 'buyer' && auth()->id() !== $product->farmer_id)
            <div class="mt-3">
                <form action="{{ route('conversations.start-product', $product) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-success px-4 py-2 fw-semibold rounded-3 d-inline-flex align-items-center gap-2 shadow-xs" type="submit">
                        <span>💬</span> Message Farmer / Chat with Farmer
                    </button>
                </form>
            </div>
        @endif

        @if(auth()->user()->role === 'buyer' && auth()->id() !== $product->farmer_id && $product->stock > 0)
            <section class="dashboard-card p-4 p-md-5 mt-4 rounded-4 border shadow-sm bg-white" aria-labelledby="offer-heading">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="fs-4">🤝</span>
                    <h2 id="offer-heading" class="h4 fw-bold mb-0 text-dark">Make Offer</h2>
                </div>
                <p class="text-secondary small mb-4">Offer a price per unit and choose a quantity up to the available stock. An accepted offer does not place an order.</p>
                <form action="{{ route('offers.store', $product) }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small" for="offer-price">Your price per {{ $product->unit ?: 'unit' }} (৳)</label>
                        <input id="offer-price" class="form-control rounded-3 py-2" type="number" name="price" min="0.01" max="99999999.99" step="0.01" value="{{ old('price') }}" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small" for="offer-quantity">Quantity @if($minQty > 1)<span class="text-danger fw-normal">(Min: {{ $minQty }})</span>@endif</label>
                        <input id="offer-quantity" class="form-control rounded-3 py-2" type="number" name="quantity" min="{{ $minQty }}" max="{{ $product->stock }}" value="{{ old('quantity', max(1, $minQty)) }}" required>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-brand w-100 rounded-3 py-2 fw-bold shadow-sm" style="background: linear-gradient(135deg, #0B6A40 0%, #13814C 100%); border: 0;">Send offer</button>
                    </div>
                </form>
            </section>
        @endif
    @endauth

    @guest
        <section class="dashboard-card p-4 mt-4 rounded-4 border shadow-sm bg-white" aria-label="Buyer actions">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fs-5">🔐</span>
                <p class="mb-0 fw-semibold text-dark">Log in as a buyer to make an offer or chat privately with this farmer.</p>
            </div>
            <div class="mt-3">
                <a class="btn btn-brand me-2 px-4 rounded-3" href="{{ route('login') }}">Log in</a>
                <a class="btn btn-outline-success px-4 rounded-3" href="{{ route('register') }}">Create an account</a>
            </div>
        </section>
    @endguest
</main>
@endsection

