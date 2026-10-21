@extends('layouts.app')
@section('title', 'User Management | KrishiHat Admin')

@section('content')
<main class="container py-4 py-lg-5">
    @include('admin._nav')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold">👥 ব্যবহারকারী ব্যবস্থাপনা (User Management)</h2>
            <p class="text-secondary small mb-0">সিস্টেমের সমস্ত কৃষক, পাইকারি ক্রেতা ও অ্যাডমিনদের তালিকা ও ভূমিকা নিয়ন্ত্রণ।</p>
        </div>
        <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
            মোট ব্যবহারকারী: {{ $users->total() }}
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

    {{-- Filter & Search Form --}}
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="নাম অথবা ইমেইল দিয়ে খুঁজুন…" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="role" class="form-select">
                    <option value="">সকল ভূমিকা (All Roles)</option>
                    <option value="farmer" {{ request('role') === 'farmer' ? 'selected' : '' }}>খামারি (Farmer)</option>
                    <option value="buyer" {{ request('role') === 'buyer' ? 'selected' : '' }}>পাইকারি ক্রেতা (Buyer)</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>অ্যাডমিন (Admin)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success flex-grow-1">ফিল্টার করুন</button>
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">রিসেট</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">আইডি</th>
                        <th scope="col">ব্যবহারকারীর নাম</th>
                        <th scope="col">ইমেইল</th>
                        <th scope="col">ভূমিকা (Role)</th>
                        <th scope="col">পণ্য / অর্ডার</th>
                        <th scope="col">নিবন্ধন তারিখ</th>
                        <th scope="col" class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>#{{ $user->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $user->name }}</div>
                                @if(Str::contains($user->email, 'demo'))
                                    <span class="badge bg-secondary-subtle text-secondary small">Demo Account</span>
                                @endif
                            </td>
                            <td><code>{{ $user->email }}</code></td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">অ্যাডমিন (Admin)</span>
                                @elseif($user->role === 'farmer')
                                    <span class="badge bg-success">খামারি (Farmer)</span>
                                @else
                                    <span class="badge bg-primary">ক্রেতা (Buyer)</span>
                                @endif
                            </td>
                            <td>
                                @if($user->role === 'farmer')
                                    <span class="badge bg-light text-dark border">{{ $user->products_count }} পণ্য</span>
                                @elseif($user->role === 'buyer')
                                    <span class="badge bg-light text-dark border">{{ $user->orders_count }} অর্ডার</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-secondary">{{ $user->created_at?->format('d M, Y') ?? '—' }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-success">
                                    সম্পাদনা
                                </a>
                                @if($user->id !== auth()->id())
                                    <form class="d-inline" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে {{ $user->name }}-এর অ্যাকাউন্ট মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" @disabled($user->orders_count > 0 || $user->products_count > 0)>
                                            মুছুন
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                কোনো ব্যবহারকারী খুঁজে পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
