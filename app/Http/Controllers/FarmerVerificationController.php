<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\WorkflowNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FarmerVerificationController extends Controller
{
    /**
     * Submit or re-submit NID and Trade License / Agricultural documents for verification.
     */
    public function submit(Request $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'farmer', 403);

        $validated = $request->validate([
            'farm_name' => ['nullable', 'string', 'max:255'],
            'nid_number' => ['required', 'string', 'min:10', 'max:50'],
            'trade_license_number' => ['required', 'string', 'min:3', 'max:100'],
            'nid_front_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'nid_back_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'trade_license_doc' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'nid_number.required' => 'জাতীয় পরিচয়পত্র (NID) নম্বর প্রদান করা বাধ্যতামূলক।',
            'trade_license_number.required' => 'ট্রেড লাইসেন্স বা কৃষি কার্ড নম্বর প্রদান করা বাধ্যতামূলক।',
        ]);

        if ($request->hasFile('nid_front_photo')) {
            if ($user->nid_front_path) {
                Storage::disk('public')->delete($user->nid_front_path);
            }
            $user->nid_front_path = $request->file('nid_front_photo')->store('farmer-verifications', 'public');
        }

        if ($request->hasFile('nid_back_photo')) {
            if ($user->nid_back_path) {
                Storage::disk('public')->delete($user->nid_back_path);
            }
            $user->nid_back_path = $request->file('nid_back_photo')->store('farmer-verifications', 'public');
        }

        if ($request->hasFile('trade_license_doc')) {
            if ($user->trade_license_path) {
                Storage::disk('public')->delete($user->trade_license_path);
            }
            $user->trade_license_path = $request->file('trade_license_doc')->store('farmer-verifications', 'public');
        }

        $user->farm_name = $validated['farm_name'] ?? $user->farm_name;
        $user->nid_number = $validated['nid_number'];
        $user->trade_license_number = $validated['trade_license_number'];
        $user->verification_status = User::VERIFY_STATUS_PENDING;
        $user->verification_submitted_at = now();
        $user->verification_rejection_reason = null;
        $user->save();

        // Notify admins of new pending verification
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new WorkflowNotification(
                "নতুন কৃষক ভেরিফিকেশন: {$user->name} ({$user->nid_number}) NID ও ট্রেড লাইসেন্স জমা দিয়েছেন।",
                route('admin.verifications.show', $user)
            ));
        }

        return redirect()->route('profile.show')
            ->with('success', 'আপনার জাতীয় পরিচয়পত্র (NID) ও ট্রেড লাইসেন্স সফলভাবে জমা হয়েছে। অ্যাডমিন প্যানেলে যাচাই-বাছাইয়ের পর অনুমোদন করা হবে।');
    }
}
