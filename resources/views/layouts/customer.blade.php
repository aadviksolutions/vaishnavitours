<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Customer Portal') - Vaishnavi Tours</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body style="background-color: var(--slate-100);">

    <!-- Customer Topbar -->
    <header style="background: var(--dark-900); color: #fff; padding: 0.85rem 0; box-shadow: var(--shadow-md);">
        <div class="container d-flex align-center justify-between flex-wrap gap-2">
            <a href="{{ route('home') }}" class="brand-logo-wrap brand-logo-dark">
                <div class="brand-logo-badge" style="width: 40px; height: 40px;">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <div>
                    <div class="logo-brand" style="color: #fff; font-size: 1.2rem;">Vaishnavi <span style="color: var(--primary);">Tours</span></div>
                    <div class="logo-sub" style="color: var(--slate-400);">Customer Portal</div>
                </div>
            </a>

            <div class="d-flex align-center gap-2">
                <a href="{{ route('booking') }}" class="btn btn-primary btn-sm">🚖 Quick Book Taxi</a>
                <div class="d-flex align-center gap-1" style="background: rgba(255,255,255,0.08); padding: 0.35rem 0.85rem; border-radius: var(--radius-md);">
                    <span style="font-weight: 700; font-size: 0.875rem;">👤 {{ Auth::user()->name }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" style="color: #fff; border-color: rgba(255,255,255,0.2);">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Sub Navigation Tabs -->
    <div style="background: #fff; border-bottom: 1px solid var(--slate-200); padding: 0.5rem 0;">
        <div class="container d-flex align-center gap-2 flex-wrap">
            <a href="{{ route('customer.dashboard') }}" class="btn btn-sm {{ request()->routeIs('customer.dashboard') ? 'btn-primary' : 'btn-outline' }}">
                📊 Dashboard
            </a>
            <a href="{{ route('customer.bookings.index') }}" class="btn btn-sm {{ request()->routeIs('customer.bookings.index') && !request('filter') ? 'btn-primary' : 'btn-outline' }}">
                🚖 My Bookings
            </a>
            <a href="{{ route('customer.bookings.index') }}?filter=upcoming" class="btn btn-sm {{ request('filter') === 'upcoming' ? 'btn-primary' : 'btn-outline' }}">
                🗓️ Upcoming Trips
            </a>
            <a href="{{ route('customer.bookings.index') }}?filter=payments" class="btn btn-sm {{ request('filter') === 'payments' ? 'btn-primary' : 'btn-outline' }}">
                💳 Payment History & Invoices
            </a>
            <a href="{{ route('customer.notifications') }}" class="btn btn-sm {{ request()->routeIs('customer.notifications') ? 'btn-primary' : 'btn-outline' }}">
                🔔 Notifications
            </a>
            <a href="{{ route('customer.profile') }}" class="btn btn-sm {{ request()->routeIs('customer.profile') ? 'btn-primary' : 'btn-outline' }}">
                ⚙️ Profile
            </a>
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline" style="margin-left: auto;">
                ← Public Website
            </a>
        </div>
    </div>

    <!-- Main Body -->
    <main class="container" style="padding-top: 1.75rem; padding-bottom: 3rem;">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">⚠️ {{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">ℹ️ {{ session('info') }}</div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
