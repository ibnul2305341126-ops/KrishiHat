<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPaymentController extends Controller
{
    /**
     * List payment transactions with filters.
     */
    public function index(Request $request)
    {
        $query = Payment::with([
            'user',
            'payable' => function ($morphTo) {
                $morphTo->morphWith([
                    Order::class => ['buyer', 'items.farmer', 'items.product'],
                    UserSubscription::class => ['plan', 'user'],
                ]);
            },
        ]);

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total_volume' => (float) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'total_count' => Payment::count(),
            'completed' => Payment::where('status', Payment::STATUS_COMPLETED)->count(),
            'pending' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'failed' => Payment::where('status', Payment::STATUS_FAILED)->count(),
            'order_payments' => Payment::where('payment_type', Payment::TYPE_ORDER)->count(),
            'subscription_payments' => Payment::where('payment_type', Payment::TYPE_SUBSCRIPTION)->count(),
        ];

        return view('admin.payments.index', compact('payments', 'stats'));
    }

    /**
     * Verify and confirm payment settlement.
     */
    public function verify(Request $request, Payment $payment)
    {
        $admin = auth()->user();
        $notes = $request->input('notes', 'অ্যাডমিন কর্তৃক ব্যাংকিং ডিপোজিট/নগদ রসিদ যাচাই ও নিশ্চিত করা হয়েছে।');

        DB::transaction(function () use ($payment, $admin, $notes) {
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'verified_at' => now(),
                'notes' => ($payment->notes ? $payment->notes . "\n" : '') . "ভেরিফাইড বাই অ্যাডমিন: {$admin->name} (#{$admin->id}) | {$notes}",
            ]);

            // If this payment was for an Order
            if ($payment->payable instanceof Order) {
                $order = $payment->payable;
                $order->update([
                    'payment_status' => Order::PAYMENT_STATUS_PAID,
                ]);

                $order->recordTrackingEvent(
                    $order->status,
                    'পেমেন্ট নিশ্চিতকরণ (Payment Verified)',
                    "পেমেন্ট সফলভাবে নিশ্চিত করা হয়েছে (ট্রানজেকশন ID: {$payment->transaction_id})।",
                    $order->delivery_address,
                    actorId: $admin->id
                );
            }

            // If this payment was for a Subscription
            if ($payment->payable instanceof UserSubscription) {
                $subscription = $payment->payable;
                $durationDays = $subscription->plan ? $subscription->plan->duration_days : 30;

                $subscription->update([
                    'status' => UserSubscription::STATUS_ACTIVE,
                    'starts_at' => now(),
                    'expires_at' => now()->addDays($durationDays),
                    'notes' => ($subscription->notes ? $subscription->notes . ' | ' : '') . 'পেমেন্ট অনুমোদিত।',
                ]);
            }
        });

        return back()->with('success', "পেমেন্ট ট্রানজেকশন #{$payment->transaction_id} সফলভাবে ভেরিফাই ও পরিশোধিত হিসেবে চিহ্নিত করা হয়েছে।");
    }

    /**
     * Mark payment as failed or rejected.
     */
    public function reject(Request $request, Payment $payment)
    {
        $reason = $request->validate([
            'reason' => 'required|string|max:500',
        ])['reason'];

        $admin = auth()->user();

        DB::transaction(function () use ($payment, $admin, $reason) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'notes' => ($payment->notes ? $payment->notes . "\n" : '') . "অ্যাডমিন প্রত্যাখ্যান: {$reason} (বাই {$admin->name})",
            ]);

            if ($payment->payable instanceof Order) {
                $payment->payable->update([
                    'payment_status' => Order::PAYMENT_STATUS_FAILED,
                ]);
            }

            if ($payment->payable instanceof UserSubscription) {
                $payment->payable->update([
                    'status' => UserSubscription::STATUS_CANCELLED,
                ]);
            }
        });

        return back()->with('success', "পেমেন্ট #{$payment->transaction_id} প্রত্যাখ্যান করা হয়েছে।");
    }
}
