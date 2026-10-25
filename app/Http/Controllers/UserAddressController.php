<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use App\Services\AddressValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the user's saved addresses.
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()
            ->with(['division', 'district', 'upazila', 'union', 'urbanArea', 'postOffice'])
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $addresses->map(fn ($addr) => [
                    'id' => $addr->id,
                    'title' => $addr->title,
                    'recipient_name' => $addr->recipient_name,
                    'phone' => $addr->phone,
                    'division_id' => $addr->division_id,
                    'district_id' => $addr->district_id,
                    'upazila_id' => $addr->upazila_id,
                    'union_id' => $addr->union_id,
                    'urban_area_id' => $addr->urban_area_id,
                    'post_office_id' => $addr->post_office_id,
                    'postal_code' => $addr->postal_code,
                    'street_address' => $addr->street_address,
                    'formatted_address' => $addr->formatted_address,
                    'is_default' => $addr->is_default,
                ]),
            ]);
        }

        return view('profile.addresses', compact('addresses'));
    }

    /**
     * Store a newly created address for the user with hierarchical validation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'upazila_id' => ['required', 'integer', 'exists:upazilas,id'],
            'union_id' => ['nullable', 'integer', 'exists:unions,id'],
            'urban_area_id' => ['nullable', 'integer', 'exists:urban_areas,id'],
            'post_office_id' => ['nullable', 'integer', 'exists:post_offices,id'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'street_address' => ['required', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        // Enforce strict administrative hierarchy
        $validationService = app(AddressValidationService::class);
        $resolved = $validationService->validateChain($validated);

        if (!empty($resolved['post_office']) && empty($validated['postal_code'])) {
            $validated['postal_code'] = $resolved['post_office']->post_code;
        }

        $user = $request->user();
        $isFirst = $user->addresses()->count() === 0;
        $makeDefault = !empty($validated['is_default']) || $isFirst;

        $address = DB::transaction(function () use ($user, $validated, $makeDefault) {
            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                'title' => $validated['title'] ?? 'Home',
                'recipient_name' => $validated['recipient_name'],
                'phone' => $validated['phone'],
                'division_id' => $validated['division_id'],
                'district_id' => $validated['district_id'],
                'upazila_id' => $validated['upazila_id'],
                'union_id' => $validated['union_id'] ?? null,
                'urban_area_id' => $validated['urban_area_id'] ?? null,
                'post_office_id' => $validated['post_office_id'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'street_address' => $validated['street_address'],
                'is_default' => $makeDefault,
            ]);
        });

        $address->load(['division', 'district', 'upazila', 'union', 'urbanArea', 'postOffice']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ডেলিভারি ঠিকানা সফলভাবে সংরক্ষণ করা হয়েছে।',
                'data' => $address,
            ], 201);
        }

        return back()->with('success', 'ডেলিভারি ঠিকানা সফলভাবে সংরক্ষণ করা হয়েছে।');
    }

    /**
     * Update the specified address with hierarchical validation.
     */
    public function update(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'upazila_id' => ['required', 'integer', 'exists:upazilas,id'],
            'union_id' => ['nullable', 'integer', 'exists:unions,id'],
            'urban_area_id' => ['nullable', 'integer', 'exists:urban_areas,id'],
            'post_office_id' => ['nullable', 'integer', 'exists:post_offices,id'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'street_address' => ['required', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validationService = app(AddressValidationService::class);
        $resolved = $validationService->validateChain($validated);

        if (!empty($resolved['post_office']) && empty($validated['postal_code'])) {
            $validated['postal_code'] = $resolved['post_office']->post_code;
        }

        $user = $request->user();
        $makeDefault = !empty($validated['is_default']);

        DB::transaction(function () use ($user, $address, $validated, $makeDefault) {
            if ($makeDefault) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                'title' => $validated['title'] ?? $address->title,
                'recipient_name' => $validated['recipient_name'],
                'phone' => $validated['phone'],
                'division_id' => $validated['division_id'],
                'district_id' => $validated['district_id'],
                'upazila_id' => $validated['upazila_id'],
                'union_id' => $validated['union_id'] ?? null,
                'urban_area_id' => $validated['urban_area_id'] ?? null,
                'post_office_id' => $validated['post_office_id'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'street_address' => $validated['street_address'],
                'is_default' => $makeDefault ? true : $address->is_default,
            ]);
        });

        $address->load(['division', 'district', 'upazila', 'union', 'urbanArea', 'postOffice']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ডেলিভারি ঠিকানা সফলভাবে আপডেট করা হয়েছে।',
                'data' => $address,
            ]);
        }

        return back()->with('success', 'ডেলিভারি ঠিকানা সফলভাবে আপডেট করা হয়েছে।');
    }

    /**
     * Set the address as default.
     */
    public function setDefault(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ডিফল্ট ঠিকানা হিসেবে সেট করা হয়েছে।',
            ]);
        }

        return back()->with('success', 'ডিফল্ট ঠিকানা হিসেবে সেট করা হয়েছে।');
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $request->user()->addresses()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ঠিকানাটি মুছে ফেলা হয়েছে।',
            ]);
        }

        return back()->with('success', 'ঠিকানাটি মুছে ফেলা হয়েছে।');
    }
}
