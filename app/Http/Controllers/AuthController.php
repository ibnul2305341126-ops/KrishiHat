<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

   
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'role' => ['required', 'in:farmer,buyer'],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => $validated['password'],
    ]);

    // Role সরাসরি সেট করা হচ্ছে, তাই fillable-এ লাগবে না।
    $user->role = $validated['role'];
    $user->save();

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('home')
        ->with('success', 'অ্যাকাউন্ট তৈরি হয়েছে!');
}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', 'লগইন সফল হয়েছে!');
        }

        return back()
            ->withErrors([
                'email' => 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।',
            ])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'লগআউট সফল হয়েছে!');
    }
}