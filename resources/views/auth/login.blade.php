@extends('layouts.app')

@section('title', 'Login - Vaishnavi Tours Account')

@section('content')
<section style="padding: 4rem 0 6rem;">
    <div class="container" style="max-width: 480px;">
        <div class="card" style="padding: 2.25rem; border-top: 5px solid var(--primary); box-shadow: var(--shadow-lg);">
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div class="brand-logo-badge" style="width: 58px; height: 58px; margin: 0 auto 0.75rem; border-width: 2.5px;">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--dark-900);">Login to Account</h1>
                <p style="font-size: 0.875rem; color: var(--slate-500);">Access your bookings, track live trip status & view invoices.</p>
            </div>

            <!-- Demo Credentials Quick Fill Buttons -->
            <div style="background: var(--slate-100); border-radius: var(--radius-md); padding: 0.85rem; margin-bottom: 1.5rem; font-size: 0.825rem;">
                <div style="font-weight: 700; margin-bottom: 0.5rem; color: var(--dark-800);">🚀 Quick Demo Logins:</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline btn-sm" onclick="fillCreds('admin@vaishnavitours.com', 'Password@123')" style="flex: 1; font-size: 0.775rem;">
                        Admin Login
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="fillCreds('rajesh.sharma@gmail.com', 'Password@123')" style="flex: 1; font-size: 0.775rem;">
                        Customer Login
                    </button>
                </div>
            </div>

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Email or Mobile Number</label>
                    <input type="text" name="email" id="loginEmail" class="form-control" placeholder="Enter your email or phone" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="d-flex justify-between align-center" style="margin-bottom: 0.35rem;">
                        <label class="form-label" style="margin-bottom: 0;">Password</label>
                        <a href="{{ route('password.request') }}" style="font-size: 0.8rem; color: var(--primary-dark); font-weight: 600;">Forgot Password?</a>
                    </div>
                    <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="remember" id="rememberMe">
                    <label for="rememberMe" style="font-size: 0.875rem; color: var(--slate-600); cursor: pointer;">Remember me on this device</label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 1.25rem;">
                    Sign In to Portal
                </button>

                <div style="text-align: center; font-size: 0.9rem; color: var(--slate-600);">
                    Don't have an account? <a href="{{ route('register') }}" style="color: var(--primary-dark); font-weight: 700;">Register here</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function fillCreds(email, pwd) {
        document.getElementById('loginEmail').value = email;
        document.getElementById('loginPassword').value = pwd;
    }
</script>
@endpush
