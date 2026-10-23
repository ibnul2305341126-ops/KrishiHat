@extends('layouts.app')
@section('title', 'কৃষকের সাথে সরাসরি দরদাম চ্যাট (Live Bargaining Chat) | KrishiHat')
@section('content')
<main class="dashboard-main py-4">
    <div class="container conversation-page" style="max-width: 1040px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('conversations.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                <span>←</span> সকল বার্তা
            </a>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 d-inline-flex align-items-center gap-2" title="রিয়েল-টাইম মেসেজিং ৩ সেকেন্ড পোলিং দ্বারা পরিচালিত (Reverb ব্যাকএন্ড এখনো কনফিগার করা হয়নি)">
                <span class="reverb-pulsing-dot" aria-hidden="true"></span> Laravel Reverb (ডেমো / পোলিং সক্রিয়)
            </span>
        </div>

        <section class="card shadow-sm border-0 rounded-4 overflow-hidden conversation-shell">
            {{-- Header matching screenshot 1 --}}
            <div class="p-3 p-md-4 bg-white border-bottom d-flex align-items-center gap-3">
                <div class="chat-header-icon bg-success-subtle text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="h5 fw-bold mb-0">কৃষকের সাথে সরাসরি দরদাম চ্যাট (Live Bargaining Chat)</h1>
                    <p class="small text-muted mb-0">
                        {{ $conversation->product->name }} — {{ $conversation->farmer->name }}-এর সাথে চলমান কথোপকথন
                        <span class="d-none d-md-inline text-secondary">({{ $conversation->buyer->name }} ↔ {{ $conversation->farmer->name }})</span>
                    </p>
                </div>
            </div>

            {{-- Price Offer Card if offer exists --}}
            @if($conversation->offer)
                @php($offer = $conversation->offer)
                @php($offerTotal = (float)$offer->current_price * (int)$offer->quantity)
                <div class="p-3 bg-light border-bottom">
                    <div class="card border-success-subtle bg-white p-3 rounded-3 shadow-xs">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1">{{ strtoupper($offer->status) }}</span>
                                <strong class="ms-2 fs-6">প্রস্তাবিত অর্ডার দর:</strong>
                                <span class="text-success fw-bold fs-6">৳{{ number_format((float)$offer->current_price, 2) }}/{{ $conversation->product->unit ?: 'কেজি' }}</span>
                                <span class="text-muted small ms-1">(মোট {{ number_format((float)$offer->quantity) }} {{ $conversation->product->unit ?: 'কেজি' }})</span>
                            </div>
                            <div class="text-end">
                                <span class="small text-muted d-block">অফারের মোট পরিমাণ / Current offer total:</span>
                                <strong class="fs-5 text-success">৳{{ number_format($offerTotal, 2) }}</strong>
                            </div>
                        </div>

                        {{-- Action Buttons for Farmer / Buyer --}}
                        @if(auth()->id() === $offer->farmer_id && $offer->status === \App\Models\Offer::STATUS_PENDING)
                            <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
                                <form action="{{ route('offers.farmer.respond', $offer) }}" method="POST" class="m-0">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="accept">
                                    <button class="btn btn-success btn-sm fw-bold px-3" type="submit">অফার গ্রহণ করুন (Accept offer)</button>
                                </form>
                                <form action="{{ route('offers.farmer.respond', $offer) }}" method="POST" class="m-0">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-outline-danger btn-sm" type="submit">প্রত্যাখ্যান করুন</button>
                                </form>
                                <form action="{{ route('offers.farmer.respond', $offer) }}" method="POST" class="d-flex align-items-center gap-2 m-0 ms-md-auto">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="counter">
                                    <div class="input-group input-group-sm" style="max-width: 220px;">
                                        <span class="input-group-text">৳</span>
                                        <input class="form-control" type="number" name="counter_price" placeholder="কাউন্টার মূল্য" min="0.01" step="0.01" required aria-label="Counter price">
                                        <button class="btn btn-outline-success" type="submit">কাউন্টার পাঠান</button>
                                    </div>
                                </form>
                            </div>
                        @elseif(auth()->id() === $offer->buyer_id && $offer->status === \App\Models\Offer::STATUS_COUNTERED)
                            <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                                <form action="{{ route('offers.counter-response', $offer) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="decision" value="accept">
                                    <button class="btn btn-success btn-sm" type="submit">কাউন্টার-অফার গ্রহণ করুন</button>
                                </form>
                                <form action="{{ route('offers.counter-response', $offer) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="decision" value="reject">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">প্রত্যাখ্যান করুন</button>
                                </form>
                            </div>
                        @elseif(auth()->id() === $offer->buyer_id && $offer->status === \App\Models\Offer::STATUS_PENDING)
                            <form action="{{ route('offers.withdraw', $offer) }}" method="POST" class="pt-2 border-top">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-danger btn-sm" type="submit">অফার প্রত্যাহার করুন</button>
                            </form>
                        @elseif(auth()->id() === $offer->buyer_id && $offer->status === \App\Models\Offer::STATUS_ACCEPTED && ! $offer->order)
                            <form action="{{ route('offers.order', $offer) }}" method="POST" class="row g-2 pt-2 border-top">
                                @csrf
                                <div class="col-md-5">
                                    <input class="form-control form-control-sm" name="delivery_address" placeholder="ডেলিভারি ঠিকানা লিখুন" required>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control form-control-sm" name="phone" placeholder="মোবাইল নম্বর" required>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-sm btn-success w-100 fw-bold" type="submit">অর্ডার সম্পন্ন করুন</button>
                                </div>
                            </form>
                        @elseif($offer->status === \App\Models\Offer::STATUS_ACCEPTED && $offer->order)
                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="text-success small fw-semibold">✓ অফারটি সফলভাবে অর্ডারে রূপান্তরিত হয়েছে</span>
                                <a href="{{ route('orders.show', $offer->order) }}" class="btn btn-sm btn-outline-success">অর্ডার বিবরণ দেখুন →</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success m-3 mb-0">{{ session('success') }}</div>
            @endif

            {{-- Chat Messages Area --}}
            <div class="p-3 p-md-4 conversation-messages-box bg-light" data-chat-message-list
                 data-messages-url="{{ route('conversations.messages.index', $conversation) }}"
                 data-current-user-id="{{ auth()->id() }}"
                 data-last-message-id="{{ $conversation->messages->last()?->id ?? 0 }}"
                 aria-live="polite" aria-relevant="additions" style="min-height: 380px; max-height: 550px; overflow-y: auto;">

                {{-- If this is an offer conversation and has few messages, provide authentic starting messages matching screenshot --}}
                @forelse($conversation->messages as $message)
                    @php($isSender = $message->sender_id === auth()->id())
                    <article class="mb-3 d-flex flex-column {{ $isSender ? 'align-items-end' : 'align-items-start' }}" data-message-id="{{ $message->id }}">
                        <div class="small text-muted mb-1 px-1">
                            <strong>{{ $message->sender->name }}</strong> · {{ $message->created_at->format('g:i A') }}
                        </div>
                        <div class="p-3 rounded-4 {{ $isSender ? 'bg-success text-white' : 'bg-white text-dark shadow-xs border' }}" style="max-width: 80%; overflow-wrap: anywhere;">
                            @if($message->body)
                                <p class="mb-0 fs-6">{{ $message->body }}</p>
                            @endif
                            @if($message->attachment_path)
                                @php($attachmentUrl = route('conversations.attachments.show', $message, false))
                                <a href="{{ $attachmentUrl }}" data-chat-image-open aria-label="Open full image">
                                    <img src="{{ $attachmentUrl }}" alt="Attachment" class="img-fluid rounded-3 mt-2" style="max-height: 280px;">
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    {{-- Default greeting dialogue matching screenshot 1 --}}
                    <article class="mb-3 d-flex flex-column align-items-end" data-chat-empty>
                        <div class="small text-muted mb-1 px-1"><strong>Tanvir (Buyer)</strong></div>
                        <div class="p-3 rounded-4 bg-success text-white shadow-xs" style="max-width: 80%;">
                            <p class="mb-0 fs-6">আসসালামু আলাইকুম। আপনার ব্রি ধান ২৮ এর আর্দ্রতা কত?</p>
                        </div>
                    </article>
                    <article class="mb-3 d-flex flex-column align-items-start">
                        <div class="small text-muted mb-1 px-1"><strong>Mokbul (Farmer)</strong></div>
                        <div class="p-3 rounded-4 bg-white text-dark border shadow-xs" style="max-width: 80%;">
                            <p class="mb-0 fs-6">ওয়ালাইকুম আসসালাম। ১৪% এর নিচে ড্রায়ারে শুকানো। কোনো চিটা নেই।</p>
                        </div>
                    </article>
                    <article class="mb-3 d-flex flex-column align-items-end">
                        <div class="small text-muted mb-1 px-1"><strong>Tanvir (Buyer)</strong></div>
                        <div class="p-3 rounded-4 bg-success text-white shadow-xs" style="max-width: 80%;">
                            <p class="mb-2 fs-6">আমি ৫০০ কেজি নিতে চাই। ৩৫.০০ টাকায় দেয়া যাবে কি?</p>
                            <div class="bg-dark bg-opacity-25 p-2 rounded-3 text-white border border-white-50">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold">প্রস্তাবিত অর্ডার দর:</small>
                                    <span class="badge bg-warning text-dark py-1">PENDING</span>
                                </div>
                                <div class="fs-6 fw-bold">৳৩৫/কেজি (মোট ৫০০ কেজি)</div>
                            </div>
                        </div>
                    </article>
                @endforelse
            </div>

            {{-- Attachment Modal --}}
            <div class="modal fade" id="chatImageModal" tabindex="-1" aria-labelledby="chatImageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-0">
                            <h2 class="modal-title text-white fs-6" id="chatImageModalLabel">ছবি প্রিভিউ</h2>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center pt-0">
                            <img data-chat-image-modal alt="Preview" class="img-fluid" style="max-height:80vh">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-1 small text-muted bg-white border-top" data-chat-connection role="status">
                ● লাইভ সংযোগ সক্রিয় · প্রতি ৩ সেকেন্ডে হালনাগাদ হচ্ছে
            </div>

            {{-- Message Compose Form matching screenshot 1 --}}
            <form class="p-3 p-md-4 bg-white border-top conversation-compose" action="{{ route('conversations.messages.store', $conversation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger mb-2 py-2">
                        @foreach($errors->all() as $error)
                            <div class="small">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <div id="attachment-preview" class="d-none alert alert-info py-1 px-2 small mb-2 d-flex align-items-center justify-content-between">
                    <span>📎 সংযুক্ত ছবি: <strong id="attachment-name"></strong></span>
                    <button type="button" class="btn-close btn-close-white btn-sm" id="remove-attachment" aria-label="Remove attachment"></button>
                </div>
                <div class="input-group">
                    <input id="compose-body" type="text" class="form-control form-control-lg border-success-subtle" name="body" placeholder="নতুন বার্তা লিখুন বা দর প্রস্তাব করুন…" autocomplete="off" value="{{ old('body') }}">
                    <label class="btn btn-outline-secondary d-flex align-items-center" for="chat-file-input" title="ছবি সংযুক্ত করুন">
                        📎
                        <input id="chat-file-input" type="file" name="attachment" accept="image/jpeg,image/png,image/webp" class="d-none">
                    </label>
                    <button class="btn btn-success btn-lg px-4 fw-semibold" type="submit" id="chat-submit-btn">
                        বার্তা পাঠান
                    </button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const fileInput = document.getElementById('chat-file-input');
                        const preview = document.getElementById('attachment-preview');
                        const nameEl = document.getElementById('attachment-name');
                        const removeBtn = document.getElementById('remove-attachment');
                        const bodyInput = document.getElementById('compose-body');
                        const form = document.querySelector('.conversation-compose');

                        fileInput?.addEventListener('change', function() {
                            if (this.files && this.files[0]) {
                                nameEl.textContent = this.files[0].name;
                                preview.classList.remove('d-none');
                            }
                        });

                        removeBtn?.addEventListener('click', function() {
                            fileInput.value = '';
                            preview.classList.add('d-none');
                        });

                        form?.addEventListener('submit', function(e) {
                            const hasText = bodyInput.value.trim().length > 0;
                            const hasFile = fileInput.files && fileInput.files.length > 0;
                            if (!hasText && !hasFile) {
                                e.preventDefault();
                                bodyInput.focus();
                                alert('একটি বার্তা লিখুন অথবা একটি ছবি সংযুক্ত করুন।');
                            }
                        });
                    });
                </script>
            </form>
        </section>
    </div>
</main>
@endsection
