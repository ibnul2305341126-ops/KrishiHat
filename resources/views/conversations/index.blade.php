@extends('layouts.app')
@section('title', 'দরদাম চ্যাট ও বার্তা (Messages & Chat) | KrishiHat')

@section('content')
<main class="dashboard-main py-4 py-lg-5">
    <div class="container" style="max-width: 960px;">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">হোম</a></li>
                <li class="breadcrumb-item active" aria-current="page">দরদাম চ্যাট</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <span class="eyebrow text-success fw-semibold">LIVE BARGAINING & CONVERSATIONS</span>
                <h1 class="dashboard-title h2 mb-0">💬 আপনার বার্তা ও দরদাম চ্যাট</h1>
                <p class="text-secondary small mb-0 mt-1">খামারি ও পাইকারি ক্রেতাদের মধ্যে সরাসরি পণ্য দরদাম এবং লেনদেন সংক্রান্ত কথোপকথন।</p>
            </div>
            <a href="{{ route('home') }}#products" class="btn btn-outline-success btn-sm">
                🌾 নতুন পণ্য খুঁজুন
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-3">
            @forelse($conversations as $conversation)
                @php
                    $isBuyer = auth()->id() === $conversation->buyer_id;
                    $partner = $isBuyer ? $conversation->farmer : $conversation->buyer;
                    $partnerRole = $isBuyer ? '👨‍🌾 খামারি' : '🛒 ক্রেতা';
                    $lastMessage = $conversation->messages->first();
                @endphp
                <div class="col-12">
                    <a class="card border-0 shadow-sm rounded-4 p-3 p-md-4 text-decoration-none text-dark transition-all hover-shadow d-block" href="{{ route('conversations.show', $conversation) }}">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px; font-size: 1.4rem;">
                                    💬
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <h2 class="h6 fw-bold mb-0 text-success">{{ $conversation->product->name }}</h2>
                                        <span class="badge bg-light text-dark border small">{{ $partnerRole }}: {{ $partner->name }}</span>
                                        @if($conversation->offer)
                                            @php($status = $conversation->offer->status)
                                            <span class="badge {{ $status === 'accepted' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }} small">
                                                অফার: {{ strtoupper($status) }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-secondary small mb-1 text-truncate" style="max-width: 600px;">
                                        @if($lastMessage)
                                            <strong>{{ $lastMessage->sender_id === auth()->id() ? 'আপনি: ' : ($lastMessage->sender->name . ': ') }}</strong>
                                            {{ $lastMessage->body ?: ($lastMessage->attachment_path ? '📷 [ছবি সংযুক্ত করা হয়েছে]' : 'বার্তা পাঠানো হয়েছে') }}
                                        @else
                                            <span class="text-muted fst-italic">এখনো কোনো বার্তা বিনিময় হয়নি। চ্যাট শুরু করতে ক্লিক করুন।</span>
                                        @endif
                                    </p>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        🕒 সর্বশেষ সক্রিয়: {{ $conversation->updated_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-md-end flex-shrink-0">
                                <span class="btn btn-outline-success btn-sm px-3 fw-semibold">
                                    চ্যাট খুলুন →
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5 px-3">
                        <span class="fs-1 d-block mb-3">💬</span>
                        <h2 class="h5 text-dark fw-bold">কোনো চলমান কথোপকথন নেই</h2>
                        <p class="text-secondary small mb-4">বাজারে কোনো পণ্যের বিস্তারিত পৃষ্ঠায় গিয়ে "Message Farmer / Chat with Farmer" অথবা দরপ্রস্তাব (Make Offer) পাঠালে এখানে চ্যাট তৈরি হবে।</p>
                        <div>
                            <a href="{{ route('home') }}#products" class="btn btn-brand px-4">
                                বাজার থেকে পণ্য দেখুন
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</main>
@endsection
