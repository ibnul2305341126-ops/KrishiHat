<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $latestOrder = $user->role === 'buyer'
            ? $user->orders()->latest()->first()
            : null;

        $demoUsers = \App\Models\User::query()
            ->whereIn('email', [
                'farmer.mokbul@demo.krishihat.test',
                'buyer.karim@demo.krishihat.test',
                'admin@demo.krishihat.test',
            ])
            ->get();

        return view('profile.show', compact('user', 'latestOrder', 'demoUsers'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        abort_if($request->hasFile('profile_photo') && $user->role !== 'farmer', 403);

        unset($data['profile_photo']);
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) Storage::disk('public')->delete($user->profile_photo_path);
            $user->profile_photo_path = $request->file('profile_photo')->store('farmer-profiles', 'public');
        }

        $user->update($data);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated.');
    }
}
