@extends('layouts.app')
@section('title', 'Product Management | KrishiHat Admin')

@section('content')
<main class="container py-4 py-lg-5">
    @include('admin._nav')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold">🌾 পণ্য ক্যাটালগ ব্যবস্থাপনা (Product Catalogue Management)</h2>
            <p class="text-secondary small mb-0">সকল ফসলের বিবরণ, মূল্য, মজুদ, ছবি ও অবস্থান পর্যালোচনা ও নিয়ন্ত্রণ।</p>
        </div>
        <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
            মোট লিস্টিং: {{ $products->total() }}
        </div>
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

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="ফসলের নাম দিয়ে খুঁজুন…" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">সকল ক্যাটাগরি</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_status" class="form-select">
                    <option value="">সকল মজুদ স্ট্যাটাস</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>মজুদ আছে (In Stock)</option>
                    <option value="sold_out" {{ request('stock_status') === 'sold_out' ? 'selected' : '' }}>স্টক শেষ (Sold Out)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-success flex-grow-1">ফিল্টার</button>
                @if(request()->hasAny(['search', 'category_id', 'stock_status']))
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">রিসেট</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Products Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 70px;">ছবি</th>
                        <th scope="col">ফসলের নাম</th>
                        <th scope="col">ক্যাটাগরি</th>
                        <th scope="col">মূল্য ও মজুদ</th>
                        <th scope="col">খামারি ও অবস্থান</th>
                        <th scope="col">স্ট্যাটাস</th>
                        <th scope="col" class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $coverPhoto = $product->photos?->first();
                            $img = null;
                            if ($coverPhoto) {
                                $img = asset('storage/' . $coverPhoto->path);
                            } elseif ($product->image) {
                                if (\Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://'])) {
                                    $img = $product->image;
                                } elseif (\Illuminate\Support\Str::startsWith($product->image, ['images/', 'products/'])) {
                                    $img = asset($product->image);
                                } else {
                                    $img = asset('storage/' . $product->image);
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $product->name }}" class="rounded" width="54" height="42" style="object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 54px; height: 42px;">🌱</div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('products.show', $product) }}" class="fw-bold text-dark text-decoration-none d-block" target="_blank">
                                    {{ $product->name }} ↗
                                </a>
                                @if(Str::contains($product->name, 'Demo'))
                                    <span class="badge bg-secondary-subtle text-secondary small">Demo Item</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $product->categoryRecord?->name ?? $product->category }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-success">৳{{ number_format((float)$product->price, 2) }} / {{ $product->unit ?: 'unit' }}</div>
                                <div class="small text-secondary">
                                    মজুদ: {{ number_format((float)$product->stock) }} {{ $product->unit ?: 'unit' }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $product->farmer?->name ?? '—' }}</div>
                                <small class="text-muted">
                                    📍 {{ $product->district ?: 'অবস্থান দেওয়া হয়নি' }}{{ $product->upazila ? ', ' . $product->upazila : '' }}
                                </small>
                            </td>
                            <td>
                                @if($product->stock > 0)
                                    <span class="badge bg-success">In Stock</span>
                                @else
                                    <span class="badge bg-danger">Sold Out</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $product->stock > 0 ? 'btn-outline-warning' : 'btn-outline-success' }}" title="Toggle availability">
                                            {{ $product->stock > 0 ? 'স্টক ০ করুন' : 'স্টক রিস্টোর' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('পণ্যটি স্থায়ীভাবে তালিকা থেকে মুছতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">মুছুন</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">কোনো পণ্য তালিকাভুক্ত নেই।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
