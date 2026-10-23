@extends('layouts.app')
@section('title', $category->name.' | KrishiHat')
@section('content')
<main>
    <section class="container py-5">
        <nav aria-label="Breadcrumb" class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Marketplace</a></li><li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li></ol></nav>
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
            <div><span class="display-6 me-2" aria-hidden="true">{{ $category->icon ?: '🌾' }}</span><h1 class="d-inline-block align-middle">{{ $category->name }}</h1>@if($category->description)<p class="text-secondary mt-2 mb-0">{{ $category->description }}</p>@endif</div>
            <form action="{{ route('categories.show', $category->slug) }}" method="GET" class="d-flex gap-2" role="search"><label for="category-search" class="visually-hidden">Search {{ $category->name }}</label><input id="category-search" class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search in {{ $category->name }}"><button class="btn btn-success" type="submit">Search</button></form>
        </div>
        <div class="row g-4">
            @forelse($products as $product)
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">@include('components.product-card', ['product' => $product])</div>
            @empty
                <div class="col-12"><div class="text-center bg-light rounded-4 p-5"><h2 class="h4">No products in this category yet</h2><p class="text-secondary">Try another search or browse all current marketplace listings.</p><a class="btn btn-success" href="{{ route('home') }}#products">Browse all products</a></div></div>
            @endforelse
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </section>
</main>
@endsection
