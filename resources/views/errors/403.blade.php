<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - অননুমোদিত অ্যাক্সেস (Unauthorized) | KrishiHat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f5e9, #f7fbf5, #dcedc8);
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.1);
            max-width: 540px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
        }
        .error-icon {
            font-size: 56px;
            margin-bottom: 16px;
        }
        .btn-brand {
            background: #198754;
            color: #fff;
            font-weight: 600;
            border-radius: 12px;
            padding: 12px 24px;
        }
        .btn-brand:hover {
            background: #146c43;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">🛡️</div>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 mb-2">HTTP 403 · অননুমোদিত অ্যাক্সেস</span>
        <h1 class="h3 fw-bold text-dark mt-2 mb-2">অ্যাডমিন প্যানেলে প্রবেশাধিকার সংরক্ষিত</h1>
        <p class="text-secondary small mb-4">
            {{ $exception->getMessage() ?: 'অ্যাডমিন প্যানেলে প্রবেশ করার অনুমতি আপনার বর্তমান অ্যাকাউন্টে নেই। আপনি বর্তমানে সাধারণ খামারি বা ক্রেতা হিসেবে লগইন আছেন।' }}
        </p>

        <div class="p-3 bg-light rounded-3 text-start mb-4 border">
            <div class="fw-bold text-success small mb-1">🔑 ডেমো অ্যাডমিন লগইন তথ্য:</div>
            <div class="small text-dark mb-1">ইমেইল: <strong class="user-select-all font-monospace">admin@demo.krishihat.test</strong></div>
            <div class="small text-dark">পাসওয়ার্ড: <strong class="user-select-all font-monospace">Password123!</strong></div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
            <a href="{{ route('login') }}?as=admin" class="btn btn-brand">
                🛡️ অ্যাডমিন হিসেবে লগইন করুন
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                ← হোমপেজে ফিরে যান
            </a>
        </div>
    </div>
</body>
</html>
