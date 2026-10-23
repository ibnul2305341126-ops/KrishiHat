@extends('layouts.app')
@section('title', 'Sandbox Payment Gateway | KrishiHat')

@section('content')
<main class="container py-5" style="max-width: 720px;">
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-dark text-white p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">SSLCOMMERZ SANDBOX SIMULATOR</span>
                    <h1 class="h4 mb-0 fw-bold">KrishiHat সুরক্ষিত পেমেন্ট গেটওয়ে</h1>
                </div>
                <div class="text-end">
                    <small class="text-white-50 d-block">প্রদেয় অর্থ</small>
                    <strong class="fs-4 text-warning">৳{{ number_format($payment->amount, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="alert alert-info py-2 small mb-4">
                <strong>🏛️ একাডেমিক ও বিশ্ববিদ্যালয় প্রজেক্ট ডেমো:</strong> এটি SSLCOMMERZ V4 ইন্টিগ্রেশন প্রটোকল ভিত্তিক সুরক্ষিত টেস্টবক্স। লাইভ মার্চেন্ট অ্যাকাউন্ট কানেক্ট হলে এটি সরাসরি বিকাশ, নগদ ও ব্যাংকের রিয়েল গেটওয়ে স্ক্রিনে রিডাইরেক্ট করবে।
            </div>

            <div class="bg-light p-3 rounded-3 mb-4 border">
                <div class="row g-2 small">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">ট্রানজেকশন রেফারেন্স:</span>
                        <strong class="font-monospace text-dark">{{ $payment->transaction_id }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">পেমেন্টের উদ্দেশ্য:</span>
                        <strong class="text-dark">
                            @if($payable instanceof \App\Models\Order)
                                KrishiHat Order #{{ $payable->id }} ({{ $payable->items->count() }} টি পণ্য)
                            @elseif($payable instanceof \App\Models\UserSubscription)
                                সদস্যপদ প্ল্যান: {{ $payable->plan->name }}
                            @else
                                KrishiHat সার্ভিস
                            @endif
                        </strong>
                    </div>
                </div>
            </div>

            <h2 class="h6 fw-bold mb-3 text-secondary text-uppercase">পেমেন্ট চ্যানেল বেছে নিন</h2>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <label class="card h-100 p-3 text-center border rounded-3 cursor-pointer bg-white channel-select active-channel" style="cursor: pointer;">
                        <input type="radio" name="channel_pick" value="bkash" class="d-none" checked>
                        <span class="fs-3 d-block mb-1">📱</span>
                        <strong class="d-block small text-dark">bKash</strong>
                        <small class="text-muted" style="font-size: 0.7rem;">বিকাশ ওয়ালেট</small>
                    </label>
                </div>
                <div class="col-6 col-md-3">
                    <label class="card h-100 p-3 text-center border rounded-3 cursor-pointer bg-white channel-select" style="cursor: pointer;">
                        <input type="radio" name="channel_pick" value="nagad" class="d-none">
                        <span class="fs-3 d-block mb-1">📲</span>
                        <strong class="d-block small text-dark">Nagad</strong>
                        <small class="text-muted" style="font-size: 0.7rem;">নগদ ওয়ালেট</small>
                    </label>
                </div>
                <div class="col-6 col-md-3">
                    <label class="card h-100 p-3 text-center border rounded-3 cursor-pointer bg-white channel-select" style="cursor: pointer;">
                        <input type="radio" name="channel_pick" value="card" class="d-none">
                        <span class="fs-3 d-block mb-1">💳</span>
                        <strong class="d-block small text-dark">Card</strong>
                        <small class="text-muted" style="font-size: 0.7rem;">ভিসা / মাস্টারকার্ড</small>
                    </label>
                </div>
                <div class="col-6 col-md-3">
                    <label class="card h-100 p-3 text-center border rounded-3 cursor-pointer bg-white channel-select" style="cursor: pointer;">
                        <input type="radio" name="channel_pick" value="internet_bank" class="d-none">
                        <span class="fs-3 d-block mb-1">🏦</span>
                        <strong class="d-block small text-dark">Bank</strong>
                        <small class="text-muted" style="font-size: 0.7rem;">ইন্টারনেট ব্যাংকিং</small>
                    </label>
                </div>
            </div>

            <form action="{{ route('payment.sandbox.action') }}" method="POST" id="sandbox-action-form">
                @csrf
                <input type="hidden" name="tran_id" value="{{ $payment->transaction_id }}">
                <input type="hidden" name="channel" id="selected-channel" value="bkash">
                <input type="hidden" name="action" id="action-input" value="success">

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-lg fw-bold py-3" onclick="submitSimulatorAction('success')">
                        ✓ পেমেন্ট সম্পন্ন করুন (Simulate Success)
                    </button>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-danger w-100 py-2" onclick="submitSimulatorAction('fail')">
                                ✕ পেমেন্ট ব্যর্থতা (Simulate Fail)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary w-100 py-2" onclick="submitSimulatorAction('cancel')">
                                ↺ পেমেন্ট বাতিল (Simulate Cancel)
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.querySelectorAll('input[name="channel_pick"]').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.channel-select').forEach(card => card.classList.remove('border-success', 'bg-light'));
        this.closest('.channel-select').classList.add('border-success', 'bg-light');
        document.getElementById('selected-channel').value = this.value;
    });
});

function submitSimulatorAction(action) {
    document.getElementById('action-input').value = action;
    document.getElementById('sandbox-action-form').submit();
}
</script>
@endsection
