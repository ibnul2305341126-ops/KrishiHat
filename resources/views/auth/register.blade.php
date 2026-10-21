
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account | KrishiHat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --green: #16834a;
            --green-dark: #0d6035;
            --green-light: #eaf7ee;
            --ink: #183528;
            --muted: #748379;
            --border: #e1eae3;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, "Noto Sans Bengali", Arial, sans-serif;
            color: var(--ink);
            background: #f4f8f4;
        }

        .page-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
        }

        .brand-panel {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px clamp(25px, 5vw, 76px);
            color: #fff;
            background:
                radial-gradient(circle at 10% 85%, #ffffff20 0 90px, transparent 91px),
                radial-gradient(circle at 100% 20%, #ffffff18 0 150px, transparent 151px),
                linear-gradient(145deg, #0d6035, #16834a 55%, #42a765);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-size: 25px;
            font-weight: 800;
            text-decoration: none;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            display: grid;
            place-items: center;
            border: 1px solid #ffffff50;
            background: #ffffff20;
            border-radius: 15px;
            font-size: 25px;
        }

        .brand-copy {
            position: relative;
            z-index: 1;
            max-width: 490px;
            padding: 55px 0;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            margin-bottom: 23px;
            border: 1px solid #ffffff40;
            border-radius: 30px;
            background: #ffffff14;
            font-size: 13px;
        }

        .brand-copy h1 {
            font-size: clamp(35px, 4vw, 55px);
            line-height: 1.25;
            font-weight: 800;
            letter-spacing: -1.3px;
            margin-bottom: 20px;
        }

        .brand-copy p {
            max-width: 410px;
            color: #e3f4e8;
            font-size: 16px;
            line-height: 1.9;
        }

        .benefits {
            display: grid;
            gap: 13px;
            margin-top: 34px;
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #f1fff4;
        }

        .benefit-icon {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #ffffff1e;
        }

        .brand-footer {
            position: relative;
            z-index: 1;
            color: #d7efdf;
            font-size: 12px;
        }

        .form-panel {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 36px 22px;
        }

        .register-card {
            width: 100%;
            max-width: 490px;
            padding: clamp(24px, 3.2vw, 43px);
            border: 1px solid #e8eee8;
            border-radius: 25px;
            background: #fff;
            box-shadow: 0 18px 60px #163d2010;
        }

        .mobile-logo {
            display: none;
            color: var(--green);
            text-decoration: none;
            font-weight: 800;
            font-size: 22px;
            margin-bottom: 25px;
        }

        .form-heading {
            margin-bottom: 27px;
        }

        .form-heading h2 {
            font-size: 29px;
            font-weight: 800;
            letter-spacing: -.7px;
            margin-bottom: 9px;
        }

        .form-heading p {
            color: var(--muted);
            font-size: 14px;
            margin: 0;
        }

        .form-label {
            color: #2d4536;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .form-control,
        .form-select {
            min-height: 49px;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
            color: var(--ink);
            background-color: #fff;
        }

        .form-control::placeholder { color: #a0aaa2; }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 4px #16834a15;
        }

        .field-wrap { margin-bottom: 19px; }

        .password-input { border-right: 0; }

        .password-toggle {
            min-width: 65px;
            border: 1px solid var(--border);
            border-left: 0;
            border-radius: 0 12px 12px 0;
            background: white;
            color: var(--green);
            font-size: 12px;
            font-weight: 700;
        }

        .password-toggle:hover {
            color: var(--green-dark);
            background: var(--green-light);
        }

        .role-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .role-option {
            position: relative;
            display: block;
            cursor: pointer;
        }

        .role-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-card {
            min-height: 112px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 7px;
            padding: 15px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: #fff;
            transition: .18s ease;
        }

        .role-card:hover {
            border-color: #94c8a5;
            background: #f8fcf8;
        }

        .role-option input:checked + .role-card {
            border: 1.5px solid var(--green);
            background: #eff9f1;
            box-shadow: 0 0 0 3px #16834a10;
        }

        .role-option input:focus-visible + .role-card {
            outline: 3px solid #16834a45;
            outline-offset: 3px;
        }

        .role-emoji { font-size: 24px; }

        .role-name {
            color: #213c2b;
            font-size: 14px;
            font-weight: 800;
        }

        .role-desc {
            color: var(--muted);
            font-size: 11px;
        }

        .role-check {
            position: absolute;
            top: 10px;
            right: 11px;
            color: var(--green);
            opacity: 0;
            font-weight: 800;
        }

        .role-option input:checked + .role-card .role-check {
            opacity: 1;
        }

        .role-help {
            margin-top: 10px;
            color: var(--muted);
            font-size: 12px;
        }

        .btn-register {
            width: 100%;
            min-height: 51px;
            border: 0;
            border-radius: 13px;
            background: var(--green);
            color: white;
            font-size: 14px;
            font-weight: 800;
            transition: .2s;
        }

        .btn-register:hover {
            background: var(--green-dark);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px #16834a25;
        }

        .secure-note {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            margin-top: 13px;
            color: var(--muted);
            font-size: 11px;
        }

        .bottom-link {
            margin-top: 23px;
            text-align: center;
            color: #758078;
            font-size: 13px;
        }

        .bottom-link a {
            color: var(--green);
            font-weight: 800;
            text-decoration: none;
        }

        .bottom-link a:hover { text-decoration: underline; }

        .home-link {
            display: block;
            text-align: center;
            margin-top: 17px;
            color: #65756a;
            text-decoration: none;
            font-size: 12px;
        }

        .home-link:hover { color: var(--green); }

        .alert {
            border-radius: 12px;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .page-shell { grid-template-columns: 1fr; }

            .brand-panel { display: none; }

            .form-panel {
                min-height: 100vh;
                padding: 25px 16px;
                background:
                    radial-gradient(circle at top right, #dcefe0 0, transparent 300px),
                    #f4f8f4;
            }

            .register-card {
                max-width: 500px;
                padding: clamp(23px, 6vw, 40px);
            }

            .mobile-logo { display: inline-block; }
        }

        @media (max-width: 400px) {
            .role-options { gap: 8px; }
            .role-card { padding: 12px 10px; }
            .form-heading h2 { font-size: 25px; }
        }
       
/* Premium KrishiHat UI upgrade */
:root {
    --green: #117a43;
    --green-dark: #075b31;
    --green-light: #eaf8ef;
    --ink: #183528;
    --muted: #748379;
    --border: #e1ebe3;
}

body {
    background:
        radial-gradient(ellipse at 8% 8%, #d9f4df 0, transparent 34%),
        radial-gradient(ellipse at 95% 90%, #e0f1d8 0, transparent 32%),
        #f5f8f4;
    background-attachment: fixed;
}

.form-panel {
    position: relative;
    isolation: isolate;
}

.register-card {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, .9);
    border-radius: 28px;
    background: rgba(255, 255, 255, .91);
    box-shadow:
        0 28px 80px rgba(24, 68, 39, .09),
        0 4px 16px rgba(24, 68, 39, .04);
    animation: cardEnter .55s ease both;
}

.register-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #0c6838, #49b56e, #b2e7bb);
}

.form-heading h2 {
    color: #163b27;
    font-size: clamp(27px, 3vw, 33px);
    letter-spacing: -1px;
}

.form-heading p {
    line-height: 1.8;
}

.form-label {
    letter-spacing: .1px;
}

.form-control,
.form-select {
    min-height: 52px;
    border-radius: 14px;
    background-color: #fcfefc;
    transition: border-color .2s, box-shadow .2s, background .2s;
}

.form-control:hover,
.form-select:hover {
    border-color: #b8d7c0;
}

.form-control:focus,
.form-select:focus {
    background: #fff;
    border-color: #218c50;
    box-shadow: 0 0 0 4px rgba(22, 131, 74, .11);
}

.role-options {
    gap: 14px;
}

.role-card {
    min-height: 132px;
    padding: 18px;
    border: 1.5px solid #e3ece5;
    border-radius: 18px;
    background: linear-gradient(145deg, #fff, #fbfdfb);
    transition:
        transform .2s ease,
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;
}

.role-card:hover {
    transform: translateY(-3px);
    border-color: #9bceaa;
    box-shadow: 0 10px 24px rgba(25, 92, 46, .07);
}

.role-option input:checked + .role-card {
    border-color: #188448;
    background: linear-gradient(145deg, #f1fbf3, #e6f7eb);
    box-shadow: 0 0 0 3px rgba(22, 131, 74, .09);
}

.role-emoji {
    display: inline-grid;
    place-items: center;
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: #edf7ef;
    font-size: 23px;
}

.role-name {
    font-size: 14px;
}

.role-desc {
    line-height: 1.5;
}

.role-check {
    display: grid;
    place-items: center;
    width: 23px;
    height: 23px;
    border-radius: 50%;
    background: #16834a;
    color: white;
    font-size: 12px;
}

.btn-register {
    position: relative;
    overflow: hidden;
    min-height: 54px;
    border-radius: 14px;
    background: linear-gradient(110deg, #0c6b38, #16894b);
    box-shadow: 0 8px 20px rgba(17, 122, 67, .16);
    letter-spacing: .2px;
}

.btn-register:hover {
    background: linear-gradient(110deg, #07572e, #117a43);
    box-shadow: 0 12px 25px rgba(17, 122, 67, .24);
}

.password-toggle {
    border-radius: 0 14px 14px 0;
}

.secure-note {
    padding: 10px;
    border: 1px solid #edf3ee;
    border-radius: 11px;
    background: #f8fbf8;
}

.bottom-link {
    line-height: 1.9;
}

.brand-panel {
    background:
        radial-gradient(circle at 15% 75%, #ffffff18 0 130px, transparent 131px),
        radial-gradient(circle at 90% 25%, #ffffff18 0 190px, transparent 191px),
        linear-gradient(145deg, #084d2b 0%, #10743e 50%, #28a05a 100%);
}

.logo-icon {
    border-radius: 16px;
    box-shadow: inset 0 1px 0 #ffffff35;
    backdrop-filter: blur(10px);
}

.benefit-icon {
    border: 1px solid #ffffff24;
    backdrop-filter: blur(8px);
}

@keyframes cardEnter {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 900px) {
    .register-card {
        border-radius: 25px;
        box-shadow: 0 20px 55px rgba(24, 68, 39, .10);
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}
    </style>
</head>

<body>
<div class="page-shell">

    <aside class="brand-panel">
        <a href="{{ route('home') }}" class="brand-logo">
            <span class="logo-icon">🌱</span>
            <span>KrishiHat</span>
        </a>

        <div class="brand-copy">
            <div class="eyebrow">
                <span>🌾</span>
                বাংলাদেশের কৃষির ডিজিটাল বাজার
            </div>

            <h1>কৃষকের পরিশ্রম,<br>আপনার আস্থা।</h1>

            <p>
                কৃষক ও ক্রেতাকে এক জায়গায় যুক্ত করে সহজ,
                স্বচ্ছ এবং সুবিধাজনক কৃষিপণ্যের বাজার গড়ে তুলি।
            </p>

            <div class="benefits">
                <div class="benefit">
                    <span class="benefit-icon">✓</span>
                    কৃষিপণ্যের সহজ কেনাবেচা
                </div>
                <div class="benefit">
                    <span class="benefit-icon">✓</span>
                    কৃষক ও ক্রেতার সরাসরি সংযোগ
                </div>
                <div class="benefit">
                    <span class="benefit-icon">✓</span>
                    নিজের অ্যাকাউন্টে পণ্যের তথ্য পরিচালনা
                </div>
            </div>
        </div>

        <div class="brand-footer">
            © {{ date('Y') }} KrishiHat · কৃষকের পাশে, সবসময়
        </div>
    </aside>

    <main class="form-panel">
        <section class="register-card">

            <a href="{{ route('home') }}" class="mobile-logo">🌱 KrishiHat</a>

            <div class="form-heading">
                <h2>Create your account</h2>
                <p>শুরু করতে নিচের তথ্যগুলো পূরণ করুন।</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>অ্যাকাউন্ট তৈরি করা যায়নি।</strong>
                    <ul class="mb-0 mt-2 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.store', [], false) }}" method="POST">
                @csrf

                <div class="field-wrap">
                    <label for="name" class="form-label">পূর্ণ নাম / Full name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="আপনার সম্পূর্ণ নাম"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        maxlength="255"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field-wrap">
                    <label for="email" class="form-label">ইমেইল ঠিকানা / Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="name@example.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field-wrap">
                    <label class="form-label d-block">আপনি কীভাবে ব্যবহার করবেন?</label>

                    <div class="role-options">
                        <label class="role-option">
                            <input
                                type="radio"
                                name="role"
                                value="buyer"
                                {{ old('role', 'buyer') === 'buyer' ? 'checked' : '' }}
                                required
                            >
                            <span class="role-card">
                                <span class="role-check">✓</span>
                                <span class="role-emoji">🛒</span>
                                <span class="role-name">Buyer · ক্রেতা</span>
                                <span class="role-desc">কৃষিপণ্য কিনতে চাই</span>
                            </span>
                        </label>

                        <label class="role-option">
                            <input
                                type="radio"
                                name="role"
                                value="farmer"
                                {{ old('role') === 'farmer' ? 'checked' : '' }}
                                required
                            >
                            <span class="role-card">
                                <span class="role-check">✓</span>
                                <span class="role-emoji">🌾</span>
                                <span class="role-name">Farmer · কৃষক</span>
                                <span class="role-desc">কৃষিপণ্য বিক্রি করতে চাই</span>
                            </span>
                        </label>
                    </div>

                    @error('role')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror

                    <div class="role-help">
                        পরে অনুমোদিত সুবিধা অনুযায়ী আপনার অ্যাকাউন্ট ব্যবহার করতে পারবেন।
                    </div>
                </div>

                <div class="field-wrap">
                    <label for="password" class="form-label">পাসওয়ার্ড / Password</label>

                    <div class="input-group">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control password-input @error('password') is-invalid @enderror"
                            placeholder="কমপক্ষে ৮ অক্ষর"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                        <button
                            class="btn password-toggle"
                            type="button"
                            data-password-toggle="password"
                            aria-controls="password"
                            aria-pressed="false"
                        >Show</button>
                    </div>

                    <div class="form-text small">কমপক্ষে ৮ অক্ষর ব্যবহার করুন।</div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field-wrap">
                    <label for="password_confirmation" class="form-label">
                        পাসওয়ার্ড নিশ্চিত করুন / Confirm password
                    </label>

                    <div class="input-group">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control password-input"
                            placeholder="একই পাসওয়ার্ড আবার লিখুন"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                        <button
                            class="btn password-toggle"
                            type="button"
                            data-password-toggle="password_confirmation"
                            aria-controls="password_confirmation"
                            aria-pressed="false"
                        >Show</button>
                    </div>

                    <div id="password-match-message"
                         class="small mt-2"
                         aria-live="polite"></div>
                </div>

                <button type="submit" class="btn btn-register">
                    Create Account <span class="ms-1">→</span>
                </button>

                <div class="secure-note">
                    <span>🔒</span>
                    আপনার পাসওয়ার্ড নিরাপদভাবে সংরক্ষণ করা হয়
                </div>
            </form>

            <div class="bottom-link">
                ইতোমধ্যে অ্যাকাউন্ট আছে?
                <a href="{{ route('login') }}">Login করুন</a>
            </div>

            <a href="{{ route('home') }}" class="home-link">
                ← হোম পেজে ফিরে যান
            </a>
        </section>
    </main>
</div>

<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(
                this.getAttribute('data-password-toggle')
            );

            const showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            this.textContent = showing ? 'Hide' : 'Show';
            this.setAttribute('aria-pressed', String(showing));
        });
    });

    const password = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
    const matchMessage = document.getElementById('password-match-message');

    function checkPasswordMatch() {
        if (!confirmation.value) {
            matchMessage.textContent = '';
            confirmation.setCustomValidity('');
            return;
        }

        if (password.value === confirmation.value) {
            matchMessage.textContent = '✓ পাসওয়ার্ড দুটি মিলেছে';
            matchMessage.className = 'small mt-2 text-success';
            confirmation.setCustomValidity('');
        } else {
            matchMessage.textContent = 'পাসওয়ার্ড দুটি মিলছে না';
            matchMessage.className = 'small mt-2 text-danger';
            confirmation.setCustomValidity('পাসওয়ার্ড দুটি মিলছে না।');
        }
    }

    password.addEventListener('input', checkPasswordMatch);
    confirmation.addEventListener('input', checkPasswordMatch);
</script>
</body>
</html>