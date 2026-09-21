@extends('layouts.app')

@section('title', 'Reset Password - Vaishnavi Tours')

@section('content')
<section style="padding: 4rem 0 6rem;">
    <div class="container" style="max-width: 460px;">
        <div class="card" style="padding: 2.25rem; border-top: 5px solid var(--primary);">
            <h1 style="font-size: 1.6rem; margin-bottom: 0.5rem; text-align: center;">Reset Password</h1>
            <p style="font-size: 0.875rem; color: var(--slate-500); margin-bottom: 1.5rem; text-align: center;">
                Enter your registered email address and we will send you password reset instructions.
            </p>

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 1rem;">
                    Send Reset Instructions
                </button>

                <div style="text-align: center; font-size: 0.875rem;">
                    <a href="{{ route('login') }}" style="color: var(--primary-dark); font-weight: 700;">← Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
