<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Vaishnavi Tours</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>

<div class="admin-wrapper">

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header d-flex align-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="brand-logo-wrap brand-logo-dark">
                <div class="brand-logo-badge" style="width: 38px; height: 38px;">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <div>
                    <div class="logo-brand" style="color: #fff; font-size: 1.15rem;">Vaishnavi <span style="color: var(--primary);">Tours</span></div>
                    <div class="logo-sub" style="color: var(--slate-400); font-size: 0.65rem;">Admin Management</div>
                </div>
            </a>
            <button id="sidebarCloseBtn" style="background: none; border: none; color: #fff; cursor: pointer; display: none;" class="mobile-only-btn">✕</button>
        </div>

        <nav class="admin-sidebar-menu">
            <div class="admin-menu-heading">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>📊</span> Dashboard
            </a>

            <div class="admin-menu-heading">Operations</div>
            <a href="{{ route('admin.bookings.index') }}" class="admin-nav-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <span>🚖</span> Bookings
            </a>
            <a href="{{ route('admin.trips.index') }}" class="admin-nav-item {{ request()->routeIs('admin.trips.*') ? 'active' : '' }}">
                <span>🗺️</span> Trips & Tracking
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="admin-nav-item {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                <span>🚗</span> Vehicles Fleet
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                <span>👨‍✈️</span> Chauffeurs / Drivers
            </a>
            <a href="{{ route('admin.customers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <span>👥</span> Customers
            </a>

            <div class="admin-menu-heading">Finance & Billing</div>
            <a href="{{ route('admin.payments.index') }}" class="admin-nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <span>💳</span> Payments
            </a>
            <a href="{{ route('admin.invoices.index') }}" class="admin-nav-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                <span>🧾</span> Invoices
            </a>

            <div class="admin-menu-heading">Insights & Tools</div>
            <a href="{{ route('admin.notifications.index') }}" class="admin-nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                <span>🔔</span> Notifications
            </a>
            <a href="{{ route('admin.reports.index') }}" class="admin-nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <span>📈</span> Reports & Analytics
            </a>
            <a href="{{ route('admin.settings.index') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <span>⚙️</span> Company Settings
            </a>
        </nav>

        <div style="padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.825rem;">
            <a href="{{ route('home') }}" target="_blank" style="color: var(--primary); display: flex; align-items: center; gap: 6px; font-weight: 700;">
                🌐 View Public Website ↗
            </a>
        </div>
    </aside>

    <!-- Main Section -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-center gap-2">
                <button id="sidebarOpenBtn" class="btn btn-outline btn-sm" style="display: none;" aria-label="Toggle Menu">
                    ☰
                </button>
                <h2 style="font-size: 1.25rem; font-weight: 800;">@yield('page_title', 'Admin Dashboard')</h2>
            </div>

            <div class="d-flex align-center gap-3">
                <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">
                    + New Booking
                </a>

                <a href="{{ route('admin.notifications.index') }}" title="Notifications" style="position: relative; font-size: 1.2rem;">
                    🔔
                </a>

                <div class="d-flex align-center gap-2" style="border-left: 1px solid var(--slate-200); padding-left: 1rem;">
                    <div style="width: 34px; height: 34px; background: var(--dark-900); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                        VT
                    </div>
                    <div style="line-height: 1.2;">
                        <div style="font-weight: 700; font-size: 0.875rem;">{{ Auth::user()->name }}</div>
                        <div style="font-size: 0.725rem; color: var(--slate-500);">Administrator</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: 0.5rem;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" title="Logout">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="admin-content">
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
        </div>
    </div>
</div>

<script>
    // Admin Mobile Sidebar Toggle
    const sidebar = document.getElementById('adminSidebar');
    const openBtn = document.getElementById('sidebarOpenBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');

    function checkWidth() {
        if (window.innerWidth <= 992) {
            if (openBtn) openBtn.style.display = 'inline-flex';
            if (closeBtn) closeBtn.style.display = 'inline-flex';
        } else {
            if (openBtn) openBtn.style.display = 'none';
            if (closeBtn) closeBtn.style.display = 'none';
            if (sidebar) sidebar.classList.remove('show');
        }
    }
    window.addEventListener('resize', checkWidth);
    checkWidth();

    if (openBtn) openBtn.addEventListener('click', () => sidebar.classList.add('show'));
    if (closeBtn) closeBtn.addEventListener('click', () => sidebar.classList.remove('show'));
</script>
@stack('scripts')
</body>
</html>
