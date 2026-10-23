<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPlanController extends Controller
{
    /**
     * List all membership plans.
     */
    public function index()
    {
        $plans = MembershipPlan::withCount('subscriptions')->orderBy('price')->get();
        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Store a newly created membership plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:membership_plans,slug',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'billing_interval' => 'required|in:monthly,yearly,lifetime',
            'duration_days' => 'required|integer|min:1|max:3650',
            'active_listing_limit' => 'required|integer|min:1|max:100000',
            'role_eligibility' => 'required|in:all,farmer,buyer',
            'can_view_analytics' => 'nullable|boolean',
            'can_bulk_purchase' => 'nullable|boolean',
            'priority_support' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_demo' => 'nullable|boolean',
            'features_text' => 'nullable|string',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['can_view_analytics'] = $request->boolean('can_view_analytics');
        $validated['can_bulk_purchase'] = $request->boolean('can_bulk_purchase');
        $validated['priority_support'] = $request->boolean('priority_support');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_demo'] = $request->boolean('is_demo');
        
        // Parse features list from textarea lines
        if (!empty($validated['features_text'])) {
            $lines = array_filter(array_map('trim', explode("\n", $validated['features_text'])));
            $validated['features'] = array_values($lines);
        } else {
            $validated['features'] = [];
        }
        unset($validated['features_text']);

        MembershipPlan::create($validated);

        return back()->with('success', 'নতুন মেম্বারশিপ প্ল্যান সফলভাবে তৈরি করা হয়েছে।');
    }

    /**
     * Update an existing membership plan.
     */
    public function update(Request $request, MembershipPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'billing_interval' => 'required|in:monthly,yearly,lifetime',
            'duration_days' => 'required|integer|min:1|max:3650',
            'active_listing_limit' => 'required|integer|min:1|max:100000',
            'role_eligibility' => 'required|in:all,farmer,buyer',
            'can_view_analytics' => 'nullable|boolean',
            'can_bulk_purchase' => 'nullable|boolean',
            'priority_support' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_demo' => 'nullable|boolean',
            'features_text' => 'nullable|string',
        ]);

        $validated['can_view_analytics'] = $request->boolean('can_view_analytics');
        $validated['can_bulk_purchase'] = $request->boolean('can_bulk_purchase');
        $validated['priority_support'] = $request->boolean('priority_support');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_demo'] = $request->boolean('is_demo');

        if (!empty($validated['features_text'])) {
            $lines = array_filter(array_map('trim', explode("\n", $validated['features_text'])));
            $validated['features'] = array_values($lines);
        }
        unset($validated['features_text']);

        $plan->update($validated);

        return back()->with('success', "প্ল্যান '{$plan->name}' সফলভাবে আপডেট করা হয়েছে।");
    }

    /**
     * Toggle active status.
     */
    public function toggle(MembershipPlan $plan)
    {
        $plan->update(['is_active' => ! $plan->is_active]);
        $statusText = $plan->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';
        return back()->with('success', "প্ল্যান '{$plan->name}' {$statusText} করা হয়েছে।");
    }
}
