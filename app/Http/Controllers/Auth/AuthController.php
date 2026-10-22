<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('customer.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        // Support login by email OR phone
        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $attempt = [
            $loginField => $credentials['email'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($attempt, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, Admin!');
            }

            return redirect()->intended(route('customer.dashboard'))
                ->with('success', 'Logged in successfully!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if ($request->filled('phone')) {
            $rawPhone = (string) $request->input('phone');
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '91')) {
                $cleanPhone = substr($cleanPhone, 2);
            } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
                $cleanPhone = substr($cleanPhone, 1);
            }
            $request->merge(['phone' => $cleanPhone]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/', 'unique:users'],
            'password' => 'required|string|min:6|confirmed',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
        ], [
            'phone.regex' => 'Please enter a valid 10-digit mobile number containing only numbers.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => 'customer',
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        Customer::create([
            'user_id' => $user->id,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? 'Bilaspur',
            'state' => 'Chhattisgarh',
            'pincode' => '495001',
        ]);

        Auth::login($user);

        return redirect()->route('customer.dashboard')
            ->with('success', 'Your account has been created successfully! Welcome to Vaishnavi Tours.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'You have been logged out.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        // Demo password reset acknowledgement
        return back()->with('success', 'A password reset instructions link has been sent to your email.');
    }
}
