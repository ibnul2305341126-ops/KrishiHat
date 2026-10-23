@extends('layouts.app')
@section('title', 'Manage categories | KrishiHat')
@section('content')
<main class="container py-4 py-lg-5">
    @include('admin._nav')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><span class="text-success fw-semibold">ADMINISTRATION</span><h2 class="h3 mb-0 fw-bold">Manage categories</h2></div>
        <a class="btn btn-success" href="{{ route('admin.categories.create') }}">Add category</a>
    </div>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th scope="col">Category</th><th scope="col">Slug</th><th scope="col">Products</th><th scope="col" class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($categories as $category)
            <tr><td><span class="me-2" aria-hidden="true">{{ $category->icon ?: '•' }}</span><strong>{{ $category->name }}</strong>@if($category->description)<small class="d-block text-secondary">{{ $category->description }}</small>@endif</td><td><code>{{ $category->slug }}</code></td><td>{{ $category->products_count }}</td><td class="text-end"><a class="btn btn-sm btn-outline-success" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                <form class="d-inline" method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Categories with assigned products cannot be deleted.');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit" @disabled($category->products_count > 0)>Delete</button></form>
            </td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-secondary py-5">No categories yet. Add the first marketplace category.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
</main>
@endsection
