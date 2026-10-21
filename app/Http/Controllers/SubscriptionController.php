<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\UserSubscription;
use App\Services\SslCommerzPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * Show membership plans and eligibility criteria.
     */
    public function index()
    {
        $plans = \Illuminate\Support\Facades\Schema::hasTable('membership_plans')
            ? MembershipPlan::where('is_active', true)->orderBy('price')->get()
            : collect();
        $user = auth()->user();
        $activeSubscription = ($user && \Illuminate\Support\Facades\Schema::hasTable('user_subscriptions')) ? $user->activeSubscription() : null;
        $allSubscriptions = ($user && \Illuminate\Support\Facades\Schema::hasTable('user_subscriptions')) ? $user->subscriptions()->with('plan', 'payments')->latest()->get() : collect();

        return view('plans.index', compact('plans', 'activeSubscription', 'allSubscriptions'));
    }

    /**
     * Subscribe to a membership plan.
     */
    public function subscribe(Request $request, MembershipPlan $plan)
    {
        $user = $request->user();

        // Check plan status
        if (! $plan->is_active) {
            return back()->with('error', 'এই সাবস্ক্রিপশন প্ল্যানটি বর্তমানে বন্ধ রয়েছে।');
        }

        // Role eligibility check
        if ($plan->role_eligibility !== 'all' && $plan->role_eligibility !== $user->role) {
            $requiredRole = $plan->role_eligibility === 'farmer' ? 'খামারি (Farmer)' : 'পাইকারি ক্রেতা (Buyer)';
            return back()->with('error', "এই প্ল্যানটি শুধুমাত্র {$requiredRole} ব্যবহারকারীদের জন্য প্রযোজ্য।");
        }

        // Free plan activation
        if ((float) $plan->price <= 0) {
            DB::transaction(function () use ($user, $plan) {
                // Cancel existing active subscriptions
                $user->subscriptions()
                    ->where('status', UserSubscription::STATUS_ACTIVE)
                    ->update(['status' => UserSubscription::STATUS_CANCELLED]);

                $user->subscriptions()->create([
                    'plan_id' => $plan->id,
                    'status' => UserSubscription::STATUS_ACTIVE,
                    'starts_at' => now(),
                    'expires_at' => now()->addDays($plan->duration_days ?: 365),
                    'payment_method' => Payment::METHOD_FREE,
                    'notes' => 'বিনামূল্যে ফ্রি খামারি অ্যাকাউন্ট সক্রিয় করা হয়েছে।',
                ]);
            });

            return back()->with('success', "আপনি সফলভাবে {$plan->name} প্ল্যান সক্রিয় করেছেন।");
        }

        // Paid plan flow
        $validated = $request->validate([
            'payment_method' => 'required|string|in:sslcommerz,bkash,nagad,card,bank_transfer',
            'bank_name' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
            'bank_account_no' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
            'bank_tran_ref' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
        ]);

        $paymentMethod = $validated['payment_method'];

        $subscription = DB::transaction(function () use ($user, $plan, $paymentMethod, $validated) {
            $sub = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => UserSubscription::STATUS_PENDING,
                'payment_method' => $paymentMethod,
                'notes' => "সাবস্ক্রিপশন অনুরোধ: {$plan->name} (" . strtoupper($paymentMethod) . ")",
            ]);

            if ($paymentMethod === 'bank_transfer') {
                Payment::create([
                    'payable_type' => UserSubscription::class,
                    'payable_id' => $sub->id,
                    'user_id' => $user->id,
                    'payment_type' => Payment::TYPE_SUBSCRIPTION,
                    'payment_method' => Payment::METHOD_BANK_TRANSFER,
                    'amount' => $plan->price,
                    'currency' => 'BDT',
                    'transaction_id' => $validated['bank_tran_ref'] ?? Payment::generateTransactionId('KH-SUB-BNK'),
                    'status' => Payment::STATUS_PENDING,
                    'payment_details' => [
                        'plan_name' => $plan->name,
                        'bank_name' => $validated['bank_name'] ?? null,
                        'bank_account_no' => $validated['bank_account_no'] ?? null,
                        'bank_tran_ref' => $validated['bank_tran_ref'] ?? null,
                    ],
                    'notes' => 'ব্যাংক ট্রান্সফারের মাধ্যমে সাবস্ক্রিপশন ফি জমা — অ্যাডমিন যাচাইকরণ সাপেক্ষে কার্যকর।',
                ]);
            }

            return $sub;
        });

        // Online gateway redirect
        if (in_array($paymentMethod, ['sslcommerz', 'bkash', 'nagad', 'card'], true)) {
            $gateway = new SslCommerzPaymentService();
            $session = $gateway->initiateSubscriptionPayment($subscription, $paymentMethod);
            if (!empty($session['redirect_url'])) {
                return redirect($session['redirect_url']);
            }
        }

        return redirect()->route('plans.index')
            ->with('success', 'আপনার সাবস্ক্রিপশন অনুরোধ ও ব্যাংক পেমেন্ট তথ্য সফলভাবে জমা হয়েছে। অ্যাডমিন ভেরিফিকেশনের পর প্ল্যানটি সক্রিয় হবে।');
    }
}
