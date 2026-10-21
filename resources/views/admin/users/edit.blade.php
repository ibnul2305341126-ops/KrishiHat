@extends('layouts.app')
@section('title', 'Edit User | KrishiHat Admin')

@section('content')
<main class="container py-4 py-lg-5" style="max-width: 700px;">
    @include('admin._nav')

    <div class="card border-0 shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="text-success fw-semibold small text-uppercase">USER EDIT</span>
                <h2 class="h4 mb-0 fw-bold">ব্যবহারকারী তথ্য ও ভূমিকা পরিবর্তন</h2>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">← তালিকায় ফিরে যান</a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">পুরো নাম (Full Name)</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">ইমেইল ঠিকানা (Email)</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="mb-4">
                <label for="role" class="form-label fw-semibold">ভূমিকা / পদমর্যাদা (Role)</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="buyer" {{ old('role', $user->role) === 'buyer' ? 'selected' : '' }}>পাইকারি ক্রেতা (Buyer)</option>
                    <option value="farmer" {{ old('role', $user->role) === 'farmer' ? 'selected' : '' }}>খামারি / বিক্রেতা (Farmer)</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>সিস্টেম অ্যাডমিন (Administrator)</option>
                </select>
                <div class="form-text text-muted">
                    সতর্কতা: শুধুমাত্র বিশ্বস্ত প্রশাসনিক ব্যক্তিদের জন্য অ্যাডমিন ভূমিকা নির্বাচন করুন।
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success px-4">সংরক্ষণ করুন</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">বাতিল</a>
            </div>
        </form>
    </div>
</main>
@endsection
