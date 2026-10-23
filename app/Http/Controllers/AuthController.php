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

    $previousCart = $request->session()->get('cart', []);

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

    if (!empty($previousCart)) {
        $request->session()->put('cart', $previousCart);
    }

    return redirect()->route($user->role === 'farmer' ? 'farmer.dashboard' : 'home')
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

        $previousCart = $request->session()->get('cart', []);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (!empty($previousCart)) {
                $request->session()->put('cart', $previousCart);
            }

            $user = $request->user();
            if ($user->role === 'admin') {
                $destination = route('admin.dashboard');
            } elseif ($user->role === 'farmer') {
                $destination = route('farmer.dashboard');
            } else {
                $destination = route('home');
            }

            return redirect()->to($destination)
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

    public function switchAccount(Request $request)
    {
        abort_unless(
            app()->environment('local', 'testing'),
            403,
            'ডেমো অ্যাকাউন্ট সুইচার শুধুমাত্র লোকাল অথবা টেস্টিং পরিবেশের জন্য প্রযোজ্য।'
        );

        $validated = $request->validate([
            'target_role' => ['required', 'in:farmer,buyer,admin'],
        ]);

        $currentUser = $request->user();

        // Security check: non-admins cannot elevate their privilege to admin
        if ($validated['target_role'] === 'admin' && $currentUser->role !== 'admin') {
            abort(403, 'অ্যাডমিন অ্যাকাউন্টে অননুমোদিতভাবে সুইচ করার অধিকার আপনার নেই। অ্যাডমিন হিসেবে লগইন করতে নিয়মিত লগইন ফর্ম ব্যবহার করুন।');
        }

        // Strictly whitelisted demo accounts only - arbitrary user_id or email not permitted
        $demoRoleEmails = [
            'farmer' => 'farmer.mokbul@demo.krishihat.test',
            'buyer' => 'buyer.karim@demo.krishihat.test',
            'admin' => 'admin@demo.krishihat.test',
        ];

        $targetEmail = $demoRoleEmails[$validated['target_role']] ?? null;
        if (! $targetEmail) {
            return back()->with('error', 'অবৈধ ডেমো রোল অনুরোধ।');
        }

        $targetUser = User::where('email', $targetEmail)->first();

        if (! $targetUser) {
            return back()->with('error', 'কাঙ্ক্ষিত ডেমো অ্যাকাউন্টটি ডাটাবেজে পাওয়া যায়নি।');
        }

        Auth::login($targetUser);
        $request->session()->regenerate();

        if ($targetUser->role === 'admin') {
            $destination = route('admin.dashboard');
        } elseif ($targetUser->role === 'farmer') {
            $destination = route('farmer.dashboard');
        } else {
            $destination = route('profile.show');
        }

        $roleLabels = [
            'admin' => 'সিস্টেম অ্যাডমিন',
            'farmer' => 'খামারি / বিক্রেতা',
            'buyer' => 'পাইকারি ক্রেতা',
        ];
        $roleName = $roleLabels[$targetUser->role] ?? ucfirst($targetUser->role);

        return redirect()->to($destination)
            ->with('success', "সফলভাবে {$targetUser->name} ({$roleName}) অ্যাকাউন্টে পরিবর্তন করা হয়েছে!");
    }
}
