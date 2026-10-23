<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['admin', 'buyer', 'farmer'], true), 403);

        if ($user->role === 'admin') {
            $statusFilter = $request->input('status', 'all');

            $baseQuery = Payment::with([
                'user',
                'payable' => function ($morphTo) {
                    $morphTo->morphWith([
                        \App\Models\Order::class => ['buyer', 'items.farmer', 'items.product'],
                        \App\Models\UserSubscription::class => ['plan', 'user'],
                    ]);
                },
            ]);

            $allPayments = (clone $baseQuery)->latest('id')->get();

            if ($statusFilter === 'completed') {
                $payments = (clone $baseQuery)->where('status', Payment::STATUS_COMPLETED)->latest('id')->get();
            } elseif ($statusFilter === 'pending') {
                $payments = (clone $baseQuery)->where('status', Payment::STATUS_PENDING)->latest('id')->get();
            } else {
                $payments = $allPayments;
            }

            $adminStats = [
                'total_volume' => (float) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount'),
                'completed_count' => Payment::where('status', Payment::STATUS_COMPLETED)->count(),
                'pending_count' => Payment::where('status', Payment::STATUS_PENDING)->count(),
                'total_records' => Payment::count(),
            ];

            return view('transactions.index', compact('payments', 'allPayments', 'adminStats', 'statusFilter'));
        }

        $payments = $user->payments()
            ->with(['payable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Order::class => ['buyer', 'items.farmer', 'items.product'],
                    \App\Models\UserSubscription::class => ['plan', 'user'],
                ]);
            }])
            ->where('status', Payment::STATUS_COMPLETED)
            ->latest('id')
            ->get();

        $allPayments = $user->payments()
            ->with(['payable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Order::class => ['buyer', 'items.farmer', 'items.product'],
                    \App\Models\UserSubscription::class => ['plan', 'user'],
                ]);
            }])
            ->latest('id')
            ->get();

        return view('transactions.index', compact('payments', 'allPayments'));
    }
}