@extends('layouts.app')
@section('title', 'Reports & Analytics | KrishiHat Admin')

@section('content')
<main class="container py-4 py-lg-5">
    @include('admin._nav')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold">📈 মার্কেটপ্লেস রিপোর্ট ও অ্যানালিটিক্স (Analytics & Reports)</h2>
            <p class="text-secondary small mb-0">প্রকৃত ডাটাবেজ তথ্য ও লেনদেনের ভিত্তিতে প্রস্তুতকৃত সার্বিক বাণিজ্যিক পরিসংখ্যান।</p>
        </div>
    </div>

    {{-- System Disclosure Alert --}}
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex gap-3 align-items-center">
            <span class="fs-3">💡</span>
            <div>
                <strong>ইন্টিগ্রেশন ও পেমেন্ট স্ট্যাটাস নোট:</strong>
                মার্কেটপ্লেসের সমস্ত অর্ডার ও বিক্রয় বর্তমানে <strong>ক্যাশ-অন-ডেলিভারি (Cash on Delivery)</strong> এবং <strong>অ্যাকাডেমিক ডেমো চেকআউট</strong> মডেলে পরিচালিত। অনলাইন কার্ড/এমএফএস গেটওয়ে বা প্রদেয় সাবস্ক্রিপশন মডিউল এখনো ব্যাংক API-এর সাথে সংযুক্ত নয়; তাই কোনো কাল্পনিক অনলাইন ট্রানজেকশন সফল দাবি করা হয় না।
            </div>
        </div>
    </div>

    {{-- Financial Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <span class="text-muted small text-uppercase fw-semibold">সর্বমোট বিক্রয় মূল্য (Gross Revenue)</span>
                <h3 class="display-6 fw-bold text-success mt-2 mb-0">৳{{ number_format($totalRevenue, 2) }}</h3>
                <small class="text-secondary mt-2">সকল অর্ডারের সমষ্টিগত মূল্য</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <span class="text-muted small text-uppercase fw-semibold">মোট সফল অর্ডার সংখ্যা</span>
                <h3 class="display-6 fw-bold text-primary mt-2 mb-0">{{ number_format($ordersCount) }} টি</h3>
                <small class="text-secondary mt-2">ডাটাবেজে সংরক্ষিত রেকর্ড</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <span class="text-muted small text-uppercase fw-semibold">গড় অর্ডার মূল্য (Avg Order Value)</span>
                <h3 class="display-6 fw-bold text-dark mt-2 mb-0">৳{{ number_format($avgOrderValue, 2) }}</h3>
                <small class="text-secondary mt-2">প্রতি অর্ডারের গড় চালান মূল্য</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Top Selling Crops --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h3 class="h5 fw-bold mb-0">🏆 শীর্ষ বিক্রিত ফসলসমূহ (Top Selling Crops)</h3>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ফসলের নাম</th>
                                <th>মোট বিক্রিত পরিমাণ</th>
                                <th class="text-end">মোট বিক্রয় মূল্য</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingProducts as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product?->name ?? 'পণ্য #'.$item->product_id }}</strong>
                                        <div class="small text-muted">{{ $item->product?->district ?? 'বাংলাদেশ' }}</div>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($item->total_qty) }}</strong> {{ $item->product?->unit ?: 'unit' }}
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ৳{{ number_format((float)$item->total_sales, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">কোনো অর্ডারের তথ্য নেই।</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Order Status Distribution --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h3 class="h5 fw-bold mb-3 border-bottom pb-2">📊 অর্ডার স্ট্যাটাস বন্টন</h3>
                <ul class="list-group list-group-flush mb-0">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>ডেলিভারি সম্পন্ন (Delivered)</span>
                        <span class="badge bg-success rounded-pill px-3">{{ $ordersByStatus['delivered'] ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>ট্রানজিটে আছে (Shipped)</span>
                        <span class="badge bg-primary rounded-pill px-3">{{ $ordersByStatus['shipped'] ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>প্যাকেজিং ও প্রস্তুতি (Processing)</span>
                        <span class="badge bg-info text-dark rounded-pill px-3">{{ $ordersByStatus['processing'] ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>খামারি কর্তৃক নিশ্চিতকৃত (Confirmed)</span>
                        <span class="badge bg-info text-dark rounded-pill px-3">{{ $ordersByStatus['confirmed'] ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>অপেক্ষমাণ নতুন অর্ডার (Pending)</span>
                        <span class="badge bg-warning text-dark rounded-pill px-3">{{ $ordersByStatus['pending'] ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>বাতিলকৃত অর্ডার (Cancelled)</span>
                        <span class="badge bg-danger rounded-pill px-3">{{ $ordersByStatus['cancelled'] ?? 0 }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Agricultural Market Reference Data (DAM) --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h3 class="h5 fw-bold mb-0 d-flex align-items-center gap-2">
                    <span>🏛️</span> কৃষি বিপণন অধিদপ্তর (DAM) কাঠামোভিত্তিক ডেমো রেফারেন্স রেট
                </h3>
                <small class="text-secondary">বাংলাদেশ কৃষি বিপণন অধিদপ্তর (https://market.dam.gov.bd) কাঠামোভিত্তিক সংরক্ষিত ডেমো রেফারেন্স দর (লাইভ প্রত্যয়ন অনুপস্থিত)</small>
            </div>
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1">
                Unverified Demo Reference
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ফসল ও জাত (Crop)</th>
                        <th>বাজার / মোকাম (Market Location)</th>
                        <th>পাইকারি দর (Wholesale)</th>
                        <th>খুচরা দর (Retail)</th>
                        <th>পর্যবেক্ষণ তারিখ</th>
                        <th>উৎস ও স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($damRecords ?? [] as $record)
                        <tr>
                            <td>
                                <strong class="d-block text-dark">{{ $record['crop_name_bn'] }}</strong>
                                <small class="text-muted font-monospace">{{ $record['crop_name_en'] }}</small>
                            </td>
                            <td>
                                <span class="d-block text-dark">{{ $record['market_name'] }}</span>
                                <small class="text-secondary">জেলা: {{ $record['district'] }}</small>
                            </td>
                            <td>
                                <strong class="text-success fs-6">৳{{ number_format($record['price_wholesale'], 2) }}</strong>
                                <small class="text-muted">/ {{ $record['unit'] }}</small>
                            </td>
                            <td>
                                <strong class="text-primary fs-6">৳{{ number_format($record['price_retail'], 2) }}</strong>
                                <small class="text-muted">/ {{ $record['unit'] }}</small>
                            </td>
                            <td>
                                <span class="font-monospace small text-dark d-block">{{ $record['observation_date'] }}</span>
                                <small class="text-muted">ইমপোর্ট: {{ $record['retrieval_date'] }}</small>
                            </td>
                            <td>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle d-block mb-1" style="font-size: 0.72rem;">
                                    {{ $record['verification_status'] }}
                                </span>
                                <a href="{{ $record['source_url'] }}" target="_blank" rel="noopener noreferrer" class="small text-decoration-none">
                                    DAM Portal ↗
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
