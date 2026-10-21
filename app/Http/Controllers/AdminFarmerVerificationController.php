<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\WorkflowNotification;
use Illuminate\Http\Request;

class AdminFarmerVerificationController extends Controller
{
    /**
     * List all farmer verification requests.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = User::query()
            ->where('role', 'farmer')
            ->with('verifiedByAdmin');

        if ($status === 'pending') {
            $query->where('verification_status', User::VERIFY_STATUS_PENDING);
        } elseif ($status === 'verified') {
            $query->where('verification_status', User::VERIFY_STATUS_VERIFIED);
        } elseif ($status === 'rejected') {
            $query->where('verification_status', User::VERIFY_STATUS_REJECTED);
        } elseif ($status === 'unverified') {
            $query->where('verification_status', User::VERIFY_STATUS_UNVERIFIED);
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('nid_number', 'like', "%{$term}%")
                  ->orWhere('trade_license_number', 'like', "%{$term}%")
                  ->orWhere('farm_name', 'like', "%{$term}%");
            });
        }

        $counts = [
            'all' => User::where('role', 'farmer')->count(),
            'pending' => User::where('role', 'farmer')->where('verification_status', User::VERIFY_STATUS_PENDING)->count(),
            'verified' => User::where('role', 'farmer')->where('verification_status', User::VERIFY_STATUS_VERIFIED)->count(),
            'rejected' => User::where('role', 'farmer')->where('verification_status', User::VERIFY_STATUS_REJECTED)->count(),
            'unverified' => User::where('role', 'farmer')->where('verification_status', User::VERIFY_STATUS_UNVERIFIED)->count(),
        ];

        $farmers = $query->latest('verification_submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.verifications.index', compact('farmers', 'counts', 'status'));
    }

    /**
     * Show farmer verification details.
     */
    public function show(User $user)
    {
        abort_unless($user->role === 'farmer', 404);
        $user->load(['verifiedByAdmin', 'products']);

        return view('admin.verifications.show', compact('user'));
    }

    /**
     * Approve and verify farmer profile.
     */
    public function approve(Request $request, User $user)
    {
        abort_unless($user->role === 'farmer', 404);

        $user->verification_status = User::VERIFY_STATUS_VERIFIED;
        $user->farmer_verified_at = now();
        $user->verified_by_admin_id = $request->user()->id;
        $user->verification_rejection_reason = null;
        $user->save();

        $user->notify(new WorkflowNotification(
            'অভিনন্দন! আপনার NID ও ট্রেড লাইসেন্স যাচাই সফলভাবে অনুমোদিত হয়েছে। আপনি এখন KrishiHat-এ পণ্য আপলোড ও বিক্রয় করতে পারবেন।',
            route('farmer.dashboard')
        ));

        return redirect()->back()
            ->with('success', "কৃষক '{$user->name}'-এর প্রোফাইল ও ডকুমেন্টস সফলভাবে ভেরিফাই ও অনুমোদিত হয়েছে।");
    }

    /**
     * Reject farmer verification request with reason.
     */
    public function reject(Request $request, User $user)
    {
        abort_unless($user->role === 'farmer', 404);

        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'বাতিল করার কারণ উল্লেখ করা আবশ্যক।',
        ]);

        $reason = $request->input('reason');
        $user->verification_status = User::VERIFY_STATUS_REJECTED;
        $user->verification_rejection_reason = $reason;
        $user->farmer_verified_at = null;
        $user->verified_by_admin_id = $request->user()->id;
        $user->save();

        $user->notify(new WorkflowNotification(
            "আপনার ভেরিফিকেশন আবেদনটি প্রত্যাখ্যাত হয়েছে। কারণ: {$reason}। সঠিক তথ্য দিয়ে পুনরায় জমা দিন।",
            route('profile.show')
        ));

        return redirect()->back()
            ->with('warning', "কৃষক '{$user->name}'-এর ভেরিফিকেশন আবেদন বাতিল করা হয়েছে এবং কারণ পাঠানো হয়েছে।");
    }
}
