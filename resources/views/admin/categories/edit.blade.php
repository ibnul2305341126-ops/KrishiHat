@extends('layouts.app')
@section('title', 'Edit category | KrishiHat')
@section('content')
<main class="container py-5"><div class="mx-auto" style="max-width:720px"><a href="{{ route('admin.categories.index') }}" class="text-success">← Categories</a><h1 class="h2 mt-3 mb-4">Edit {{ $category->name }}</h1>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="card border-0 shadow-sm"><div class="card-body p-4"><form method="POST" action="{{ route('admin.categories.update', $category) }}">@method('PUT')@include('admin.categories._form', ['submitLabel' => 'Save changes'])</form></div></div>
</div></main>
@endsection
