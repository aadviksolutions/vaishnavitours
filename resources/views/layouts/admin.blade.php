<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
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
                <div class="brand-logo-badge" style="width: 32px; height: 32px; border-radius: 6px;">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <div>
                    <div class="logo-brand" style="color: #fff; font-size: 13.5px; font-weight: 600;">Vaishnavi <span style="color: var(--accent);">Tours</span></div>
                    <div class="logo-sub" style="color: var(--slate-400); font-size: 10px; font-weight: 500;">Admin Console</div>
                </div>
            </a>
            <button id="sidebarCloseBtn" style="background: none; border: none; color: var(--slate-400); cursor: pointer; display: none; font-size: 16px;" class="mobile-only-btn">✕</button>
        </div>

        <nav class="admin-sidebar-menu">
            <div class="admin-menu-heading">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <x-icon name="trending-up" size="15" /> Dashboard
            </a>

            <div class="admin-menu-heading">Operations</div>
            <a href="{{ route('admin.bookings.index') }}" class="admin-nav-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <x-icon name="calendar-check" size="15" /> Bookings
            </a>
            <a href="{{ route('admin.calendar.index') }}" class="admin-nav-item {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                <x-icon name="calendar" size="15" /> Booking Calendar
            </a>
            <a href="{{ route('admin.trips.index') }}" class="admin-nav-item {{ request()->routeIs('admin.trips.*') ? 'active' : '' }}">
                <x-icon name="route" size="15" /> Trips & Tracking
            </a>
            <a href="{{ route('admin.enquiries.index') }}" class="admin-nav-item {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}">
                <x-icon name="mail" size="15" /> Enquiries (Web/Manual)
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="admin-nav-item {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                <x-icon name="car-front" size="15" /> Vehicles Fleet
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                <x-icon name="user-check" size="15" /> Drivers & Chauffeurs
            </a>
            <a href="{{ route('admin.tour-packages.index') }}" class="admin-nav-item {{ request()->routeIs('admin.tour-packages.*') ? 'active' : '' }}">
                <x-icon name="compass" size="15" /> Tour Packages
            </a>
            <a href="{{ route('admin.locations.index') }}" class="admin-nav-item {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                <x-icon name="map-pin" size="15" /> Pickup Locations
            </a>
            <a href="{{ route('admin.booking-categories.index') }}" class="admin-nav-item {{ request()->routeIs('admin.booking-categories.*') ? 'active' : '' }}">
                <x-icon name="layers" size="15" /> Booking Categories
            </a>
            <a href="{{ route('admin.customers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <x-icon name="users" size="15" /> Customers
            </a>

            <div class="admin-menu-heading">Finance & Billing</div>
            <a href="{{ route('admin.payments.index') }}" class="admin-nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <x-icon name="credit-card" size="15" /> Payments
            </a>
            <a href="{{ route('admin.expenses.index') }}" class="admin-nav-item {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                <x-icon name="dollar-sign" size="15" /> Expenses (Fuel/Toll)
            </a>
            <a href="{{ route('admin.invoices.index') }}" class="admin-nav-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                <x-icon name="receipt" size="15" /> GST Invoices
            </a>

            <div class="admin-menu-heading">Insights & Quality</div>
            <a href="{{ route('admin.complaints.index') }}" class="admin-nav-item {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
                <x-icon name="alert-triangle" size="15" /> Driver & Vehicle Complaints
            </a>
            <a href="{{ route('admin.rates.index') }}" class="admin-nav-item {{ request()->routeIs('admin.rates.*') ? 'active' : '' }}">
                <x-icon name="indian-rupee" size="15" /> Rates Matrix
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="admin-nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                <x-icon name="bell" size="15" /> Notifications
            </a>
            <a href="{{ route('admin.reports.index') }}" class="admin-nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <x-icon name="file-text" size="15" /> Reports & Analytics
            </a>
            <a href="{{ route('admin.feedback.index') }}" class="admin-nav-item {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}">
                <x-icon name="star" size="15" /> Customer Reviews
            </a>
            <a href="{{ route('admin.settings.index') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <x-icon name="settings" size="15" /> Company Settings
            </a>
        </nav>

        <div style="padding: 0.75rem 1rem; border-top: 1px solid rgba(255,255,255,0.06); font-size: 12px;">
            <a href="{{ route('home') }}" target="_blank" style="color: #38BDF8; display: flex; align-items: center; gap: 6px; font-weight: 500;">
                <x-icon name="external-link" size="13" /> View Public Site
            </a>
        </div>
    </aside>

    <!-- Main Section -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-center gap-2">
                <button id="sidebarOpenBtn" class="btn btn-outline btn-sm" style="display: none; padding: 0 8px;" aria-label="Toggle Menu">
                    <x-icon name="menu" size="16" />
                </button>
                <h1 style="font-size: 15px; font-weight: 600; margin: 0; color: var(--dark-900);">@yield('page_title', 'Admin Dashboard')</h1>
            </div>

            <div class="d-flex align-center gap-2">
                <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">
                    + New Booking
                </a>

                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline btn-sm" title="Notifications" style="padding: 0 8px; color: var(--slate-600);">
                    <x-icon name="bell" size="15" />
                </a>

                <div class="d-flex align-center gap-2" style="border-left: 1px solid var(--slate-200); padding-left: 0.75rem; margin-left: 0.25rem;">
                    <div style="width: 28px; height: 28px; background: var(--dark-900); color: var(--white); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 11px;">
                        VT
                    </div>
                    <div style="line-height: 1.2;">
                        <div style="font-weight: 600; font-size: 12.5px; color: var(--dark-900);">{{ Auth::user()->name }}</div>
                        <div style="font-size: 11px; color: var(--slate-500);">Administrator</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: 0.25rem;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="font-size: 11.5px; height: 28px; padding: 0 0.5rem;" title="Logout">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success"><x-icon name="circle-check" size="16" /> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger"><x-icon name="alert-circle" size="16" /> {{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info"><x-icon name="info" size="16" /> {{ session('info') }}</div>
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
