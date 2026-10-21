
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | KrishiHat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f5e9, #f7fbf5, #dcedc8);
            font-family: Arial, sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            padding: 38px;
            background: #fff;
            border-radius: 24px;
            border: 1px solid rgba(34, 139, 34, .10);
            box-shadow: 0 15px 45px rgba(31, 91, 46, .12);
        }

        .brand-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            background: #e8f5e9;
            font-size: 34px;
        }

        .brand-title {
            color: #198754;
            font-weight: 800;
        }

        .subtitle {
            color: #6c757d;
            font-size: 14px;
        }

        .form-label {
            color: #344437;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            border-radius: 11px;
            padding: 12px 14px;
            border-color: #dce5dc;
        }

        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .12);
        }

        .password-input {
            border-right: 0;
        }

        .password-toggle {
            border: 1px solid #dce5dc;
            border-left: 0;
            border-radius: 0 11px 11px 0;
            background: #fff;
            color: #198754;
            font-size: 13px;
            font-weight: 600;
        }

        .password-toggle:hover {
            background: #f0f8f1;
        }

        .btn-login {
            padding: 13px;
            background: #198754;
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            transition: .2s;
        }

        .btn-login:hover {
            background: #146c43;
            color: #fff;
            transform: translateY(-1px);
        }

        .bottom-link a {
            color: #198754;
            font-weight: 700;
            text-decoration: none;
        }

        .bottom-link a:hover {
            text-decoration: underline;
        }

        .home-link {
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
        }

        .home-link:hover {
            color: #198754;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 27px 21px;
                border-radius: 19px;
            }
        }
    </style>
</head>

<body>
<div class="login-wrapper">
    <div class="login-card">

        <div class="text-center mb-4">
            <div class="brand-icon">🌱</div>
            <h2 class="brand-title mb-1">KrishiHat</h2>
            <h4 class="fw-bold mt-3">Welcome Back!</h4>
            <p class="subtitle mb-0">আপনার অ্যাকাউন্টে লগইন করুন</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger rounded-3">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success rounded-3">
                {{ session('success') }}
            </div>
        @endif

        @php
            $prefillEmail = old('email');
            $prefillPassword = '';
            if (request('as') === 'admin') {
                $prefillEmail = 'admin@demo.krishihat.test';
                $prefillPassword = 'Password123!';
            } elseif (request('as') === 'farmer') {
                $prefillEmail = 'farmer.mokbul@demo.krishihat.test';
                $prefillPassword = 'Password123!';
            } elseif (request('as') === 'buyer') {
                $prefillEmail = 'buyer.karim@demo.krishihat.test';
                $prefillPassword = 'Password123!';
            }
        @endphp

        <div class="card bg-light border-0 rounded-4 p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-success">⚡ ১-ক্লিকে ডেমো লগইন বাটন:</span>
                <span class="badge bg-success-subtle text-success small">Quick Fill</span>
            </div>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-sm btn-outline-danger d-flex justify-content-between align-items-center text-start" onclick="fillCredentials('admin@demo.krishihat.test', 'Password123!')">
                    <span>🛡️ <strong>সুপার অ্যাডমিন (Admin)</strong></span>
                    <span class="small font-monospace text-muted">Password123!</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success d-flex justify-content-between align-items-center text-start" onclick="fillCredentials('farmer.mokbul@demo.krishihat.test', 'Password123!')">
                    <span>👨‍🌾 <strong>মোকবুল (খামারি)</strong></span>
                    <span class="small font-monospace text-muted">Password123!</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary d-flex justify-content-between align-items-center text-start" onclick="fillCredentials('buyer.karim@demo.krishihat.test', 'Password123!')">
                    <span>🛒 <strong>করিম (পাইকারি ক্রেতা)</strong></span>
                    <span class="small font-monospace text-muted">Password123!</span>
                </button>
            </div>
        </div>

        <form action="{{ route('login.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="name@example.com"
                    value="{{ $prefillEmail }}"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>

                <div class="input-group">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control password-input"
                        placeholder="আপনার পাসওয়ার্ড লিখুন"
                        value="{{ $prefillPassword }}"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        class="btn password-toggle"
                        type="button"
                        data-password-toggle
                        aria-controls="password"
                        aria-label="Show password"
                        aria-pressed="false"
                    >Show</button>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">
                Login to KrishiHat →
            </button>
        </form>

        <div class="text-center mt-4 bottom-link">
            নতুন ব্যবহারকারী?
            <a href="{{ route('register') }}">Create Account</a>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('home') }}" class="home-link">
                ← হোম পেজে ফিরে যান
            </a>
        </div>

        <div class="text-center mt-4">
            <small class="text-secondary">
                © {{ date('Y') }} KrishiHat · কৃষকের পাশে, সবসময়
            </small>
        </div>

    </div>
</div>

<script>
    function fillCredentials(email, password) {
        const emailInput = document.getElementById('email');
        const passInput = document.getElementById('password');
        if (emailInput && passInput) {
            emailInput.value = email;
            passInput.value = password;
            passInput.type = 'text'; // momentarily show password so user sees it filled
            setTimeout(() => { passInput.type = 'password'; }, 1500);
        }
    }

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(
                this.getAttribute('aria-controls')
            );

            const showing = input.type === 'password';

            input.type = showing ? 'text' : 'password';
            this.textContent = showing ? 'Hide' : 'Show';
            this.setAttribute('aria-pressed', String(showing));
            this.setAttribute(
                'aria-label',
                showing ? 'Hide password' : 'Show password'
            );
        });
    });
</script>
</body>
</html>