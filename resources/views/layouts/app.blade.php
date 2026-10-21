<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KrishiHat কৃষি হাট | Smart Agriculture Marketplace')</title>
    <meta name="description" content="কৃষকের সরাসরি ফসল, মধ্যস্বত্বভোগীহীন পাইকারি কেনাবেচা — KrishiHat">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
{{-- Top Live Market Running Marquee Ticker Bar (60 FPS Infinite Scroll) --}}
<div class="market-ticker km-ticker-bar">
    <div class="km-ticker-wrap">
        <div class="km-ticker-move">
            <span class="km-ticker-item">
                <span class="ticker-badge km-ticker-tag">
                    <span class="ticker-dot"></span> লাইভ বাজার দর
                </span>
                <span class="ticker-text">বগুড়া ব্রি ধান: ৳৩৫.৫০/কেজি (▲ ১.২%) • রাজশাহী তাহেরপুরী পেঁয়াজ: ৳৬৭.০০/কেজি (▼ ০.৭%) • দিনাজপুর ডায়মন্ড আলু: ৳২৮.০০/কেজি • নাটোর আখ: ৳১২.০০/কেজি • কুষ্টিয়া সরিষা: ৳৯৫.০০/কেজি • যশোর বেগুন: ৳৪০.০০/কেজি</span>
            </span>
            <span class="km-ticker-item">
                <span class="km-ticker-tag">🌾 সরকারি মান</span>
                <span>কৃষি বিপণন অধিদপ্তর (DAM) ও বিএডিসি মানসম্মত পাইকারি বাজার ও অফিশিয়াল ন্যূনতম অর্ডারের পরিমাণ (MOQ)</span>
            </span>
            <span class="km-ticker-item">
                <span class="km-ticker-tag">🛡️ এসক্রো অডিট</span>
                <span>১০০% ডিজিটাল সিকিউরিটি ট্র্যাকিং ও মধ্যস্বত্বভোগীহীন সরাসরি কৃষক-ক্রেতা প্ল্যাটফর্ম</span>
            </span>
            {{-- Duplicate block for seamless 60fps infinite marquee loop --}}
            <span class="km-ticker-item">
                <span class="ticker-badge km-ticker-tag">
                    <span class="ticker-dot"></span> লাইভ বাজার দর
                </span>
                <span class="ticker-text">বগুড়া ব্রি ধান: ৳৩৫.৫০/কেজি (▲ ১.২%) • রাজশাহী তাহেরপুরী পেঁয়াজ: ৳৬৭.০০/কেজি (▼ ০.৭%) • দিনাজপুর ডায়মন্ড আলু: ৳২৮.০০/কেজি • নাটোর আখ: ৳১২.০০/কেজি • কুষ্টিয়া সরিষা: ৳৯৫.০০/কেজি • যশোর বেগুন: ৳৪০.০০/কেজি</span>
            </span>
            <span class="km-ticker-item">
                <span class="km-ticker-tag">🌾 সরকারি মান</span>
                <span>কৃষি বিপণন অধিদপ্তর (DAM) ও বিএডিসি মানসম্মত পাইকারি বাজার ও অফিশিয়াল ন্যূনতম অর্ডারের পরিমাণ (MOQ)</span>
            </span>
        </div>
    </div>
</div>

{{-- Top Utility Bar (KrishiMarket Inspired) --}}
<div class="km-top-utility d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted">🇧🇩 বাংলাদেশ জাতীয় কৃষি ই-মার্কেটপ্লেস</span>
            <span class="text-secondary">•</span>
            <span class="km-helpline-badge">
                <span>📞 হেল্পলাইন (WhatsApp):</span>
                <strong>+880 1800-KRISHI</strong>
            </span>
        </div>
        <div class="d-flex align-items-center gap-3">
            @guest
                <a href="{{ route('register') }}?as=farmer" class="fw-semibold">👨‍🌾 বিক্রেতা / খামারি হোন</a>
                <span class="text-muted">|</span>
                <a href="{{ route('login') }}" class="fw-semibold">🔐 খামারি লগইন</a>
            @else
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    ✓ লগইন একাউন্ট: {{ ucfirst(auth()->user()->role) }} ({{ auth()->user()->name }})
                </span>
            @endguest
            <span class="text-muted">|</span>
            <span class="ticker-lang-btn small cursor-pointer" title="ভাষা নির্বাচন">🌐 বাংলা (BD) | Taka (৳)</span>
        </div>
    </div>
</div>

{{-- Main Navbar --}}
<header class="site-header km-header">
    <nav class="navbar navbar-expand-lg py-2">
        <div class="container">
            <a class="navbar-brand km-logo-link" href="{{ route('home') }}">
                <div class="brand-icon-box km-logo-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                        <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                    </svg>
                </div>
                <div>
                    <span class="brand-text km-logo-title">
                        Krishi<span class="brand-accent km-logo-accent">Hat</span>
                    </span>
                    <small class="brand-bengali km-logo-subtitle">কৃষি হাট • নিরাপদ ও বিশ্বস্ত</small>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-controls="siteNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="siteNav">
                <ul class="navbar-nav marketplace-nav mx-auto my-2 my-lg-0">
                    <li class="nav-item">
                        <a class="nav-link km-nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}#products">
                            <span class="nav-icon" aria-hidden="true">🏪</span> বাজার (Marketplace)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link km-nav-link {{ request()->routeIs('forecast.*') ? 'active' : '' }}" href="{{ route('forecast.index') }}">
                            <span class="nav-icon" aria-hidden="true">📈</span> এআই দর পূর্বাভাস
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link km-nav-link {{ request()->routeIs('conversations.*') ? 'active' : '' }}" href="{{ route('conversations.index') }}">
                            <span class="nav-icon" aria-hidden="true">💬</span> দরদাম চ্যাট
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link km-nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                            <span class="nav-icon" aria-hidden="true">📦</span> অর্ডার ও লাইভ ট্র্যাকিং
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link km-nav-link {{ request()->routeIs('plans.*') ? 'active' : '' }}" href="{{ route('plans.index') }}">
                            <span class="nav-icon" aria-hidden="true">✨</span> সদস্যপদ প্ল্যান
                        </a>
                    </li>

                    @auth
                        @if (auth()->user()->role === 'farmer')
                            <li class="nav-item"><a class="nav-link km-nav-link" href="{{ route('farmer.dashboard') }}#farmer-orders">History</a></li>
                            <li class="nav-item"><a class="nav-link km-nav-link fw-bold text-success" href="{{ route('farmer.dashboard') }}">ফার্মার ড্যাশবোর্ড</a></li>
                        @elseif (auth()->user()->role === 'buyer')
                            <li class="nav-item"><a class="nav-link km-nav-link" href="{{ route('orders.index') }}">History</a></li>
                            <li class="nav-item"><a class="nav-link km-nav-link fw-bold text-primary" href="{{ route('dashboard') }}">ড্যাশবোর্ড</a></li>
                        @else
                            <li class="nav-item"><a class="nav-link km-nav-link" href="{{ route('dashboard') }}">ড্যাশবোর্ড</a></li>
                        @endif
                        @if(auth()->user()->role === 'admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link km-nav-link dropdown-toggle text-danger fw-semibold d-inline-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span>🛡️</span> অ্যাডমিন প্যানেল
                                </a>
                                <ul class="dropdown-menu shadow-sm border-0 rounded-3">
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}">📊 ড্যাশবোর্ড (Dashboard)</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.users.index') }}">👥 ব্যবহারকারী (Users)</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.products.index') }}">🌾 পণ্য ক্যাটালগ (Products)</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.categories.index') }}">📂 ক্যাটাগরি (Categories)</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.orders.index') }}">📦 অর্ডার ও ট্র্যাকিং (Orders)</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.offers.index') }}">💬 দরদাম ও অফার (Offers)</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.reports.index') }}">📈 রিপোর্ট ও অ্যানালিটিক্স</a></li>
                                </ul>
                            </li>
                        @endif
                        @php
                            $unreadNotifs = auth()->user()->unreadNotifications;
                            $unreadCount = $unreadNotifs->count();
                            $recentNotifications = auth()->user()->notifications()->take(6)->get();
                        @endphp
                        <li class="nav-item dropdown">
                            <a class="nav-link km-nav-link dropdown-toggle position-relative d-inline-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="নোটিফিকেশন">
                                <span>🔔</span>
                                <span class="d-none d-lg-inline">নোটিফিকেশন</span>
                                @if($unreadCount > 0)
                                    <span class="badge rounded-pill bg-danger" style="font-size: 0.72rem; padding: 0.25em 0.55em;">
                                        {{ $unreadCount }}
                                        <span class="visually-hidden">unread notifications</span>
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0" style="min-width: 330px; max-width: 380px;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light rounded-top-4">
                                    <span class="fw-bold text-dark">🔔 নোটিফিকেশন</span>
                                    @if($unreadCount > 0)
                                        <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0 text-success fw-semibold" style="font-size: 0.8rem;">
                                                সব পঠিত করুন
                                            </button>
                                        </form>
                                    @endif
                                </li>
                                <div style="max-height: 320px; overflow-y: auto;">
                                    @forelse($recentNotifications as $notification)
                                        @php($isUnread = $notification->unread())
                                        <li>
                                            <a class="dropdown-item px-3 py-2 border-bottom text-wrap d-flex align-items-start gap-2 {{ $isUnread ? 'bg-light fw-semibold' : 'text-secondary' }}" href="{{ route('notifications.open', $notification->id) }}">
                                                <span class="mt-1" style="font-size: 0.65rem;">{!! $isUnread ? '🔵' : '⚪' !!}</span>
                                                <div class="flex-grow-1">
                                                    <div class="small {{ $isUnread ? 'text-dark' : 'text-muted' }}" style="line-height: 1.35;">
                                                        {{ $notification->data['message'] ?? 'নতুন আপডেট রয়েছে' }}
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.72rem;">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="px-3 py-4 text-center text-muted small">
                                            <span class="fs-4 d-block mb-1">📭</span>
                                            নতুন কোনো নোটিফিকেশন নেই
                                        </li>
                                    @endforelse
                                </div>
                                <li class="p-2 text-center border-top bg-light rounded-bottom-4">
                                    <a class="small text-decoration-none text-success fw-semibold" href="{{ route('notifications.index') }}">
                                        সকল নোটিফিকেশন দেখুন →
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link km-nav-link" href="{{ route('transactions.index') }}">লেনদেন</a></li>
                        <li class="nav-item">
                            <a class="nav-link km-nav-link profile-nav-link d-inline-flex align-items-center gap-2" href="{{ route('profile.show') }}" aria-label="View your profile">
                                @if(auth()->user()->profile_photo_path)
                                    <img class="profile-avatar" src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="">
                                @else
                                    <span class="profile-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                                <span>Profile</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link km-nav-link dropdown-toggle text-success fw-semibold d-inline-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Demo Switch">
                                <span>🔄</span> Switch ({{ ucfirst(auth()->user()->role) }})
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4">
                                <li class="dropdown-header small text-muted">🔄 দ্রুত একাউন্ট পরিবর্তন:</li>
                                <li>
                                    <form action="{{ route('switch-account') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="target_role" value="farmer">
                                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2">
                                            <span>👨‍🌾 মোকবুল (খামারি)</span>
                                            @if(auth()->user()->role === 'farmer') <span class="badge bg-success small">Active</span> @endif
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('switch-account') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="target_role" value="buyer">
                                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2">
                                            <span>🛒 করিম (ক্রেতা)</span>
                                            @if(auth()->user()->role === 'buyer') <span class="badge bg-primary small">Active</span> @endif
                                        </button>
                                    </form>
                                </li>
                                @if(auth()->user()->role === 'admin')
                                    <li>
                                        <form action="{{ route('switch-account') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="target_role" value="admin">
                                            <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2">
                                                <span>🛡️ সুপার অ্যাডমিন</span>
                                                <span class="badge bg-danger small">Active</span>
                                            </button>
                                        </form>
                                    </li>
                                @else
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a href="{{ route('login') }}?as=admin" class="dropdown-item text-danger d-flex align-items-center justify-content-between py-2">
                                            <span>🛡️ সুপার অ্যাডমিন লগইন</span>
                                            <span class="badge bg-danger-subtle text-danger small">Admin</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endauth
                </ul>

                <div class="nav-actions d-flex flex-wrap align-items-center gap-2">
                    <a class="btn btn-cart km-cart-btn" href="{{ route('cart.index') }}" aria-label="Shopping cart, {{ array_sum(session('cart', [])) }} items">
                        <span>🛒 কার্ট</span>
                        <span class="cart-count km-cart-badge">{{ array_sum(session('cart', [])) }}</span>
                    </a>
                    @guest
                        <a class="btn btn-outline-success px-3" href="{{ route('login') }}">লগ ইন</a>
                        <a class="btn btn-brand px-3" href="{{ route('register') }}">নিবন্ধন</a>
                    @else
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm" type="submit">Log out</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>
</header>

@yield('content')

{{-- Modernized Footer with Trust Seals & Official Database Links --}}
<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-5 col-md-6">
                <a class="footer-brand d-inline-flex align-items-center gap-2" href="{{ route('home') }}">
                    <span class="brand-icon-box-sm">🌱</span> KrishiHat কৃষি হাট
                </a>
                <p class="mt-3 mb-3 text-white-50" style="line-height: 1.7;">
                    কৃষক থেকে পাইকারি ক্রেতার সরাসরি ডিজিটাল কৃষি প্ল্যাটফর্ম। মধ্যস্বত্বভোগীহীন ন্যায্য বাজার, সরকারি মানসম্মত পাইকারি MOQ এবং ডিজিটাল সিকিউরিটি অডিট নিশ্চিত করাই আমাদের লক্ষ্য।
                </p>
                <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 small">
                    <div class="d-flex align-items-center gap-2 text-warning fw-semibold mb-1">
                        <span>🏛️</span> বাংলাদেশ সরকারের অফিসিয়াল ডাটাবেজ
                    </div>
                    <div class="text-white-50" style="font-size: 0.78rem;">
                        গণপ্রজাতন্ত্রী বাংলাদেশ সরকারের জাতীয় তথ্য বাতায়ন (<a href="https://bangladesh.gov.bd" target="_blank" class="text-info text-decoration-none">bangladesh.gov.bd</a>) ও বাংলাদেশ ডাক বিভাগ থেকে বিভাগ, জেলা, উপজেলা, ইউনিয়ন ও পোস্ট কোড ডাটাবেজ সমন্বিত।
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-3">
                <h2 class="footer-heading text-uppercase text-white" style="letter-spacing: 0.05em; font-size: 0.85rem;">মার্কেটপ্লেস ও সেবা</h2>
                <a href="{{ route('home') }}#products">🌾 সকল ফসল ও লিস্টিং</a>
                <a href="{{ route('forecast.index') }}">📈 এআই বাজার দর পূর্বাভাস</a>
                <a href="{{ route('orders.index') }}">📦 অর্ডার ও লাইভ ট্র্যাকিং</a>
                <a href="{{ route('conversations.index') }}">💬 সরাসরি দরদাম চ্যাট</a>
                <a href="{{ route('transactions.index') }}">🛡️ ডিজিটাল লেনদেন ও অডিট</a>
                <a href="{{ route('cart.index') }}">🛒 শপিং কার্ট</a>
            </div>
            <div class="col-6 col-lg-4 col-md-3">
                <h2 class="footer-heading text-uppercase text-white" style="letter-spacing: 0.05em; font-size: 0.85rem;">কৃষক ও ক্রেতা হেল্পলাইন</h2>
                <div class="mb-3 text-white-50 small">
                    <div>📞 হটলাইন: <strong class="text-white">+880 1800-KRISHI</strong></div>
                    <div>🟢 হোয়াটসঅ্যাপ সাপোর্ট: <strong class="text-white">+880 1870-178888</strong></div>
                    <div>✉️ ইমেইল: support@krishihat.gov.bd</div>
                    <div>🕒 সাপোর্ট সময়: প্রতিদিন সকাল ৮টা - রাত ১০টা</div>
                </div>
                @guest
                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light px-3">লগ ইন</a>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-success px-3">নতুন অ্যাকাউন্ট</a>
                    </div>
                @else
                    <div class="small text-white-50">
                        লগইন আছেন: <strong class="text-white">{{ auth()->user()->name }}</strong> ({{ ucfirst(auth()->user()->role) }})
                    </div>
                @endguest
            </div>
        </div>
        <div class="footer-bottom mt-5 pt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>© {{ date('Y') }} KrishiHat কৃষি হাট — স্মার্ট এগ্রিকালচার মার্কেটপ্লেস SaaS. সর্বস্বত্ব সংরক্ষিত।</div>
            <div class="d-flex align-items-center gap-3 small text-white-50">
                <span>✓ DAM পাইকারি মান</span>
                <span>✓ BADC বীজ মান</span>
                <span>✓ ডিজিটাল এসক্রো সিকিউরিটি</span>
            </div>
        </div>
    </div>
</footer>

{{-- Floating Help Chat Panel --}}
<section id="kh-chat-panel" class="kh-chat-panel" data-kh-chat-panel hidden role="dialog" aria-modal="false" aria-label="KrishiHat help chat">
    <header class="kh-chat-header">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-5">🌱</span>
            <div>
                <h2 class="h6 mb-0 text-white">KrishiHat AI ও লাইভ সহায়তা</h2>
                <small class="text-white-50" style="font-size: 0.72rem;">কৃষক ও ক্রেতাদের জন্য সার্বক্ষণিক সাপোর্ট</small>
            </div>
        </div>
        <button class="btn btn-sm btn-outline-light rounded-circle p-1" style="width: 28px; height: 28px; line-height: 1;" type="button" data-kh-chat-close aria-label="Close chat">×</button>
    </header>
    <div class="kh-chat-messages" data-kh-chat-messages role="log" aria-live="polite" aria-relevant="additions">
        <div class="kh-chat-message kh-chat-message-assistant">স্বাগতম! KrishiHat সম্পর্কে যেকোনো প্রশ্ন, ফসলের দর, সরকারি মানসম্মত পাইকারি MOQ বা অর্ডার সম্পর্কে জানতে পারেন।</div>
    </div>
    <div class="px-3 pt-2" data-kh-chat-loading hidden role="status">
        <span class="spinner-border spinner-border-sm text-success" aria-hidden="true"></span>
        <span class="small text-secondary">উত্তর তৈরি হচ্ছে…</span>
    </div>
    <form class="kh-chat-compose" data-kh-chat-form>
        <label class="visually-hidden" for="kh-chat-input">আপনার বার্তা</label>
        <textarea id="kh-chat-input" class="form-control" data-kh-chat-input rows="1" maxlength="2000" placeholder="আপনার প্রশ্নটি লিখুন…" required></textarea>
        <button class="btn btn-success align-self-end" data-kh-chat-submit type="submit" disabled>পাঠান</button>
    </form>
</section>

{{-- KrishiMarket-Style Floating 60fps WhatsApp & AI Help Launcher --}}
<div class="km-whatsapp-float">
    <span class="km-whatsapp-tooltip d-none d-lg-inline-block">🟢 হেল্পলাইন ও লাইভ সহায়তা</span>
    <button class="km-whatsapp-btn border-0 kh-chat-launcher" type="button" data-kh-chat-toggle aria-expanded="false" aria-controls="kh-chat-panel" title="লাইভ সহায়তা ও চ্যাট">
        <span class="km-whatsapp-pulse"></span>
        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.78 2.71 4.3 3.8.6.26 1.07.42 1.44.53.61.2 1.16.17 1.6.1.49-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.3"/>
        </svg>
        <span class="visually-hidden">Open KrishiHat help chat</span>
    </button>
</div>
</body>
</html>

