@extends('layouts.app')

@section('title', 'Register Account - Vaishnavi Tours')

@section('content')
<section style="padding: 3.5rem 0 5rem;">
    <div class="container" style="max-width: 580px;">
        <div class="card" style="padding: 2.5rem; border-top: 5px solid var(--primary); box-shadow: var(--shadow-lg);">
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div class="brand-logo-badge" style="width: 58px; height: 58px; margin: 0 auto 0.75rem; border-width: 2.5px;">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--dark-900);">Create Customer Account</h1>
                <p style="font-size: 0.875rem; color: var(--slate-500);">Join Vaishnavi Tours to manage bookings, download invoices & track cabs.</p>
            </div>

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Your Full Name" value="{{ old('name') }}" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="grid grid-2 gap-2">
                    <div class="form-group">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile" value="{{ old('phone') }}" required>
                        @error('phone') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" value="{{ old('email') }}" required>
                        @error('email') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid grid-2 gap-2">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" placeholder="e.g. Bilaspur" value="{{ old('city', 'Bilaspur') }}">
                        @error('city') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Locality / Address</label>
                        <input type="text" name="address" class="form-control" placeholder="e.g. Mangal Chowk, Bilaspur" value="{{ old('address') }}">
                        @error('address') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid grid-2 gap-2">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                        @error('password') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.5rem; margin-bottom: 1.25rem;">
                    Create My Account
                </button>

                <div style="text-align: center; font-size: 0.9rem; color: var(--slate-600);">
                    Already have an account? <a href="{{ route('login') }}" style="color: var(--primary-dark); font-weight: 700;">Login here</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
