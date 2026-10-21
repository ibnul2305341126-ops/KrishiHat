@extends('layouts.app')
@section('title', 'সকল নোটিফিকেশন (Notifications) | KrishiHat')

@section('content')
<main class="dashboard-main py-4 py-lg-5">
    <div class="container" style="max-width: 860px;">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">হোম</a></li>
                <li class="breadcrumb-item active" aria-current="page">নোটিফিকেশন</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <span class="eyebrow text-success fw-semibold">UPDATES & ALERTS</span>
                <h1 class="dashboard-title h2 mb-0">🔔 আপনার সকল নোটিফিকেশন</h1>
            </div>
            @if(auth()->user()->unreadNotifications->isNotEmpty())
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1">
                        <span>✓✓</span> সব পঠিত চিহ্নিত করুন
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="list-group list-group-flush">
                @forelse($notifications as $notification)
                    @php($isUnread = $notification->unread())
                    <a href="{{ route('notifications.open', $notification->id) }}" class="list-group-item list-group-item-action p-3 p-md-4 d-flex align-items-start gap-3 {{ $isUnread ? 'bg-light border-start border-success border-4' : '' }}">
                        <span class="fs-4">{{ $isUnread ? '🔔' : '🔕' }}</span>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                <h2 class="h6 mb-0 fw-semibold {{ $isUnread ? 'text-dark fw-bold' : 'text-secondary' }}">
                                    {{ $notification->data['message'] ?? 'নতুন আপডেট রয়েছে' }}
                                </h2>
                                <span class="badge {{ $isUnread ? 'bg-success text-white' : 'bg-light text-muted border' }}">
                                    {{ $isUnread ? 'নতুন (Unread)' : 'পঠিত' }}
                                </span>
                            </div>
                            <small class="text-muted d-block">
                                {{ $notification->created_at->format('M j, Y g:i A') }} ({{ $notification->created_at->diffForHumans() }})
                            </small>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5">
                        <span class="fs-1 d-block mb-3">📭</span>
                        <h2 class="h5 text-dark">কোনো নোটিফিকেশন পাওয়া যায়নি</h2>
                        <p class="text-secondary small">আপনার পণ্য, অফার বা চ্যাটের নতুন কোনো তথ্য এলে এখানে প্রদর্শিত হবে।</p>
                        <a href="{{ route('home') }}#products" class="btn btn-brand btn-sm">বাজারে যান</a>
                    </div>
                @endforelse
            </div>
        </div>

        @if($notifications->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
