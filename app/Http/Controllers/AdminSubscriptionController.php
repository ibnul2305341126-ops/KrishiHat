<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSubscriptionController extends Controller
{
    /**
     * List all user subscriptions.
     */
    public function index(Request $request)
    {
        $query = UserSubscription::with(['user', 'plan', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->latest('id')->paginate(15)->withQueryString();
        $plans = MembershipPlan::orderBy('price')->get();
        $users = User::orderBy('name')->get();

        $stats = [
            'total' => UserSubscription::count(),
            'active' => UserSubscription::where('status', UserSubscription::STATUS_ACTIVE)->count(),
            'pending' => UserSubscription::where('status', UserSubscription::STATUS_PENDING)->count(),
            'expired' => UserSubscription::where('status', UserSubscription::STATUS_EXPIRED)->count(),
            'cancelled' => UserSubscription::where('status', UserSubscription::STATUS_CANCELLED)->count(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'plans', 'users', 'stats'));
    }

    /**
     * Manually grant a subscription with mandatory audit reason.
     */
    public function manualGrant(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:membership_plans,id',
            'duration_days' => 'required|integer|min:1|max:3650',
            'audit_reason' => 'required|string|min:5|max:1000',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $plan = MembershipPlan::findOrFail($validated['plan_id']);
        $admin = auth()->user();

        DB::transaction(function () use ($user, $plan, $validated, $admin) {
            // Cancel any current active subscriptions
            $user->subscriptions()
                ->where('status', UserSubscription::STATUS_ACTIVE)
                ->update(['status' => UserSubscription::STATUS_CANCELLED]);

            $sub = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => UserSubscription::STATUS_ACTIVE,
                'starts_at' => now(),
                'expires_at' => now()->addDays($validated['duration_days']),
                'payment_method' => Payment::METHOD_MANUAL,
                'notes' => "ম্যানুয়াল অনুমোদন (অ্যাডমিন: {$admin->name} #{$admin->id})। কারণ: {$validated['audit_reason']}",
            ]);

            Payment::create([
                'payable_type' => UserSubscription::class,
                'payable_id' => $sub->id,
                'user_id' => $user->id,
                'payment_type' => Payment::TYPE_SUBSCRIPTION,
                'payment_method' => Payment::METHOD_MANUAL,
                'amount' => $plan->price,
                'currency' => 'BDT',
                'transaction_id' => Payment::generateTransactionId('KH-ADM-SUB'),
                'status' => Payment::STATUS_COMPLETED,
                'verified_at' => now(),
                'notes' => "অ্যাডমিন ম্যানুয়াল গ্রান্ট অডিট নোট: {$validated['audit_reason']}",
            ]);
        });

        return back()->with('success', "ব্যবহারকারী '{$user->name}'-কে সফলভাবে '{$plan->name}' সাবস্ক্রিপশন ম্যানুয়াল গ্রান্ট করা হয়েছে (অডিট রেকর্ড সংরক্ষিত)।");
    }

    /**
     * Update subscription status (e.g. approve pending, cancel).
     */
    public function updateStatus(Request $request, UserSubscription $subscription)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,pending,expired,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $subscription->status;
        $updates = ['status' => $validated['status']];

        if ($validated['status'] === UserSubscription::STATUS_ACTIVE && $oldStatus !== UserSubscription::STATUS_ACTIVE) {
            $updates['starts_at'] = $subscription->starts_at ?: now();
            $duration = $subscription->plan ? $subscription->plan->duration_days : 30;
            $updates['expires_at'] = now()->addDays($duration);
        }

        if (!empty($validated['notes'])) {
            $updates['notes'] = ($subscription->notes ? $subscription->notes . ' | ' : '') . 'অ্যাডমিন নোট: ' . $validated['notes'];
        }

        $subscription->update($updates);

        return back()->with('success', "সাবস্ক্রিপশন #{$subscription->id}-এর স্ট্যাটাস {$validated['status']}-এ পরিবর্তিত হয়েছে।");
    }
}
