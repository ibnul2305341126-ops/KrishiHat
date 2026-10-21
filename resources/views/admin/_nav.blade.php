<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
    <div class="d-flex align-items-center gap-2">
        <span class="fs-4">🛡️</span>
        <div>
            <h1 class="h3 mb-0 fw-bold">KrishiHat কন্ট্রোল প্যানেল</h1>
            <small class="text-muted">Smart Agriculture Marketplace Administration</small>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm {{ request()->routeIs('admin.dashboard') ? 'btn-success' : 'btn-outline-secondary' }}">
            📊 ড্যাশবোর্ড
        </a>
        <a href="{{ route('admin.catalogue.index') }}" class="btn btn-sm {{ request()->routeIs('admin.catalogue.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            🔍 ক্যাটালগ রিভিউ
        </a>
        <a href="{{ route('admin.market_imports.index') }}" class="btn btn-sm {{ request()->routeIs('admin.market_imports.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            🌾 সরকারি বাজার দর
        </a>
        <a href="{{ route('admin.plans.index') }}" class="btn btn-sm {{ request()->routeIs('admin.plans.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            💎 প্ল্যান কনফিগ
        </a>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm {{ request()->routeIs('admin.subscriptions.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            🎫 সাবস্ক্রিপশন
        </a>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm {{ request()->routeIs('admin.payments.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            💳 পেমেন্ট ও গেটওয়ে
        </a>
        @php
            $pendingVerificationsCount = \App\Models\User::where('role', 'farmer')->where('verification_status', \App\Models\User::VERIFY_STATUS_PENDING)->count();
        @endphp
        <a href="{{ route('admin.verifications.index') }}" class="btn btn-sm {{ request()->routeIs('admin.verifications.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            🪪 কৃষক ভেরিফিকেশন (NID/Trade)
            @if($pendingVerificationsCount > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $pendingVerificationsCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm {{ request()->routeIs('admin.users.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            👥 ব্যবহারকারী
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm {{ request()->routeIs('admin.products.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            📦 পণ্য তালিকা
        </a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-sm {{ request()->routeIs('admin.categories.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            📂 ক্যাটাগরি
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm {{ request()->routeIs('admin.orders.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            🚚 অর্ডার
        </a>
        <a href="{{ route('admin.offers.index') }}" class="btn btn-sm {{ request()->routeIs('admin.offers.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            💬 অফার
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm {{ request()->routeIs('admin.reports.*') ? 'btn-success' : 'btn-outline-secondary' }}">
            📈 রিপোর্ট
        </a>
    </div>
</div>
