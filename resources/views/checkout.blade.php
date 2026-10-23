@extends('layouts.app')
@section('title', 'Checkout | KrishiHat')

@section('content')
<main class="container py-4 py-lg-5" style="max-width: 1000px">
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Your cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <h1 class="dashboard-title h2 mb-4">Complete your order</h1>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="dashboard-card p-4 mb-4">
                <h2 class="h4 mb-3 fw-bold">ডেলিভারি ও যোগাযোগের ঠিকানা</h2>
                <form action="{{ route('checkout.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="delivery_address">ডেলিভারির সম্পূর্ণ ঠিকানা (Delivery address)</label>
                        <textarea id="delivery_address" name="delivery_address" class="form-control" rows="3" maxlength="2000" placeholder="যেমন: দোকান নং ১২, আড়তপট্টি, কারওয়ান বাজার, ঢাকা…" required>{{ old('delivery_address') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="phone">যোগাযোগের মোবাইল নম্বর (Phone number)</label>
                        <input id="phone" type="tel" name="phone" class="form-control" placeholder="01700-000000" value="{{ old('phone') }}" maxlength="30" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2 d-block">পেমেন্ট মেথড নির্বাচন করুন (Choose Payment Method)</label>
                        
                        <div class="list-group mb-3">
                            <!-- COD -->
                            <label class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3">
                                <input class="form-check-input flex-shrink-0 mt-1 payment-method-radio" type="radio" name="payment_method" id="cod" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark">💵 ক্যাশ অন ডেলিভারি (Cash on Delivery)</strong>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">জিরো ফি</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">পণ্য হাতে পেয়ে ওজন ও গুণমান নিশ্চিত করে নগদ অর্থ প্রদান করুন। ডেলিভারির আগ পর্যন্ত অর্ডার Unpaid থাকবে।</small>
                                </div>
                            </label>

                            <!-- bKash -->
                            <label class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3">
                                <input class="form-check-input flex-shrink-0 mt-1 payment-method-radio" type="radio" name="payment_method" id="method_bkash" value="bkash" {{ old('payment_method') === 'bkash' ? 'checked' : '' }}>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark">📱 বিকাশ পেমেন্ট (bKash)</strong>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">SSLCOMMERZ / Sandbox</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">SSLCOMMERZ গেটওয়ের মাধ্যমে তাৎক্ষণিক ও নিরাপদ bKash পেমেন্ট।</small>
                                </div>
                            </label>

                            <!-- Nagad -->
                            <label class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3">
                                <input class="form-check-input flex-shrink-0 mt-1 payment-method-radio" type="radio" name="payment_method" id="method_nagad" value="nagad" {{ old('payment_method') === 'nagad' ? 'checked' : '' }}>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark">📲 নগদ পেমেন্ট (Nagad)</strong>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">SSLCOMMERZ / Sandbox</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">SSLCOMMERZ গেটওয়ে ব্যবহার করে নগদ ওয়ালেট থেকে সরাসরি পেমেন্ট।</small>
                                </div>
                            </label>

                            <!-- Debit / Credit Card -->
                            <label class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3">
                                <input class="form-check-input flex-shrink-0 mt-1 payment-method-radio" type="radio" name="payment_method" id="method_card" value="card" {{ old('payment_method') === 'card' ? 'checked' : '' }}>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark">💳 ডেবিট / ক্রেডিট কার্ড (Visa / Mastercard)</strong>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">SSLCOMMERZ Secure</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">যেকোনো দেশীয় বা আন্তর্জাতিক ভিসা ও মাস্টারকার্ড দিয়ে সুরক্ষিত লেনদেন।</small>
                                </div>
                            </label>

                            <!-- Bank Transfer -->
                            <label class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3">
                                <input class="form-check-input flex-shrink-0 mt-1 payment-method-radio" type="radio" name="payment_method" id="method_bank" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark">🏦 ব্যাংক ট্রান্সফার (Manual Bank Deposit)</strong>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">অ্যাডমিন ভেরিফিকেশন</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">অগ্রণী বা সোনালী ব্যাংক এস্ক্রো একাউন্টে অর্থ জমা দিয়ে ট্রানজেকশন স্লিপ সাবমিট করুন।</small>
                                </div>
                            </label>
                        </div>

                        <!-- Bank Transfer Details Form (Toggled dynamically) -->
                        <div id="bank_details_section" class="p-3 bg-light rounded border mb-3" style="display: {{ old('payment_method') === 'bank_transfer' ? 'block' : 'none' }};">
                            <h6 class="fw-bold text-primary mb-2">📋 KrishiHat অফিসিয়াল এস্ক্রো ব্যাংক অ্যাকাউন্ট তথ্য</h6>
                            <div class="small bg-white p-2 rounded border mb-3 text-secondary">
                                <div><strong>ব্যাংকের নাম:</strong> অগ্রণী ব্যাংক পিএলসি (Agrani Bank PLC)</div>
                                <div><strong>হিসাবের নাম:</strong> KrishiHat Agri Marketplace Escrow</div>
                                <div><strong>অ্যাকাউন্ট নম্বর:</strong> 0200018472910</div>
                                <div><strong>শাখা:</strong> কারওয়ান বাজার কর্পোরেট শাখা, ঢাকা</div>
                                <div><strong>রাউটিং নম্বর:</strong> 010260451</div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold" for="bank_name">প্রেরক ব্যাংকের নাম (Your Bank)</label>
                                    <input type="text" id="bank_name" name="bank_name" class="form-control form-control-sm" placeholder="যেমন: ডাচ-বাংলা ব্যাংক / ব্র্যাক ব্যাংক" value="{{ old('bank_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold" for="bank_account_no">হিসাব নম্বর (Your Account No)</label>
                                    <input type="text" id="bank_account_no" name="bank_account_no" class="form-control form-control-sm" placeholder="যেমন: 1234567890" value="{{ old('bank_account_no') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold" for="bank_tran_ref">ট্রানজেকশন আইডি / রেফারেন্স নম্বর (Transaction Ref)</label>
                                    <input type="text" id="bank_tran_ref" name="bank_tran_ref" class="form-control form-control-sm" placeholder="যেমন: FT261021987654" value="{{ old('bank_tran_ref') }}">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">অ্যাডমিন আপনার ব্যাংক স্টেটমেন্ট ও ট্রানজেকশন আইডি মিলিয়ে অর্ডার অনুমোদিত করবেন।</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-brand btn-lg">Place order</button>
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary btn-lg">Back to cart</a>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const radios = document.querySelectorAll('.payment-method-radio');
                        const bankSection = document.getElementById('bank_details_section');
                        const bankNameInput = document.getElementById('bank_name');
                        const bankAccInput = document.getElementById('bank_account_no');
                        const bankRefInput = document.getElementById('bank_tran_ref');

                        function updateBankSection() {
                            const selected = document.querySelector('.payment-method-radio:checked');
                            if (selected && selected.value === 'bank_transfer') {
                                bankSection.style.display = 'block';
                                bankNameInput.setAttribute('required', 'required');
                                bankAccInput.setAttribute('required', 'required');
                                bankRefInput.setAttribute('required', 'required');
                            } else {
                                bankSection.style.display = 'none';
                                bankNameInput.removeAttribute('required');
                                bankAccInput.removeAttribute('required');
                                bankRefInput.removeAttribute('required');
                            }
                        }

                        radios.forEach(radio => radio.addEventListener('change', updateBankSection));
                        updateBankSection();
                    });
                </script>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="dashboard-card p-4">
                <h2 class="h4 mb-3 fw-bold">Order summary</h2>
                <div class="border-bottom pb-2 mb-3">
                    @foreach($products as $product)
                        <div class="d-flex justify-content-between gap-3 py-2">
                            <div>
                                <span class="fw-medium text-dark">{{ $product->name }}</span>
                                <small class="text-muted d-block">{{ $cart[$product->id] }} {{ $product->unit ?: 'unit' }} × ৳{{ number_format((float)$product->price, 2) }}</small>
                            </div>
                            <strong class="text-nowrap">৳{{ number_format($product->price * $cart[$product->id], 2) }}</strong>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between align-items-baseline mt-3">
                    <span class="fs-5 fw-bold">Subtotal</span>
                    <strong class="fs-4 text-success">৳{{ number_format($total, 2) }}</strong>
                </div>
                <div class="small text-secondary mt-2">
                    ✓ কোনো অতিরিক্ত প্ল্যাটফর্ম ফি নেই<br>
                    ✓ নিরাপদ ও স্বচ্ছ সরাসরি কৃষক-টু-পাইকার বাণিজ্য
                </div>
            </section>
        </div>
    </div>
</main>
@endsection
