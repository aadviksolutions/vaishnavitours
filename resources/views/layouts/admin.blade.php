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
    <style>
        /* Enterprise Sidebar & Header Micro-Refinements */
        .admin-sidebar {
            width: 254px;
            background: #172B4D;
        }
        .admin-sidebar-header {
            padding: 0.95rem 1.15rem;
            min-height: 60px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .admin-sidebar-header .brand-title {
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: -0.2px;
            line-height: 1.2;
        }
        .admin-sidebar-header .brand-sub {
            color: #8993A4;
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .admin-menu-heading {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #8993A4;
            padding: 0.85rem 0.65rem 0.3rem;
        }
        .admin-nav-item {
            color: #CBD5E1;
            font-size: 13px;
            font-weight: 500;
            height: 36px;
            padding: 0 0.75rem;
            border-radius: 6px;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .admin-nav-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #FFFFFF;
        }
        .admin-nav-item.active {
            background: #2563EB;
            color: #FFFFFF;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
        }
        .admin-topbar {
            height: 60px;
            background: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .topbar-search-form {
            position: relative;
            display: flex;
            align-items: center;
        }
        .topbar-search-input {
            width: 260px;
            height: 34px;
            padding: 0 0.75rem 0 2rem;
            font-size: 12.5px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            color: #172033;
            transition: all 0.15s ease;
        }
        .topbar-search-input:focus {
            outline: none;
            background: #FFFFFF;
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            width: 300px;
        }
        .topbar-search-icon {
            position: absolute;
            left: 0.65rem;
            color: #94A3B8;
            pointer-events: none;
        }
        .admin-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            height: 34px;
            padding: 0 0.95rem;
            font-size: 13px;
            font-weight: 600;
            color: #FFFFFF;
            background: #2563EB;
            border: 1px solid #1D4ED8;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
            white-space: nowrap;
        }
        .admin-btn-primary:hover {
            background: #1D4ED8;
            color: #FFFFFF;
        }
        @media (max-width: 992px) {
            .topbar-search-input {
                width: 170px;
            }
            .topbar-search-input:focus {
                width: 200px;
            }
        }
        @media (max-width: 768px) {
            .topbar-search-form {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <!-- Sidebar Brand Header -->
        <div class="admin-sidebar-header">
            <a href="{{ route('admin.dashboard') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <div style="width: 32px; height: 32px; border-radius: 6px; overflow: hidden; background: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div>
                    <div class="brand-title">Vaishnavi Tours</div>
                    <div class="brand-sub">Operations Management</div>
                </div>
            </a>
            <button id="sidebarCloseBtn" style="background: none; border: none; color: #94A3B8; cursor: pointer; display: none; font-size: 18px; padding: 4px;" class="mobile-only-btn" aria-label="Close sidebar">✕</button>
        </div>

        <!-- Navigation Menu Sections -->
        <nav class="admin-sidebar-menu">
            <!-- 1. OPERATIONS -->
            <div class="admin-menu-heading">Operations</div>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <x-icon name="layout-dashboard" :size="15" /> Dashboard
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="admin-nav-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <x-icon name="calendar-check" :size="15" /> Bookings
            </a>
            <a href="{{ route('admin.calendar.index') }}" class="admin-nav-item {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                <x-icon name="calendar" :size="15" /> Booking Calendar
            </a>
            <a href="{{ route('admin.trips.index') }}" class="admin-nav-item {{ request()->routeIs('admin.trips.*') ? 'active' : '' }}">
                <x-icon name="navigation" :size="15" /> Trips & Tracking
            </a>
            <a href="{{ route('admin.enquiries.index') }}" class="admin-nav-item {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}">
                <x-icon name="inbox" :size="15" /> Enquiries
            </a>
            <a href="{{ route('admin.customers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <x-icon name="users" :size="15" /> Customers & Partners
            </a>

            <!-- 2. FLEET MANAGEMENT -->
            <div class="admin-menu-heading">Fleet Management</div>
            <a href="{{ route('admin.vehicles.index') }}" class="admin-nav-item {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                <x-icon name="car" :size="15" /> Vehicles Fleet
            </a>
            <a href="{{ route('admin.vehicle-categories.index') }}" class="admin-nav-item {{ request()->routeIs('admin.vehicle-categories.*') ? 'active' : '' }}">
                <x-icon name="layers" :size="15" /> Vehicle Categories & Models
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                <x-icon name="user-check" :size="15" /> Drivers & Chauffeurs
            </a>
            <a href="{{ route('admin.tour-packages.index') }}" class="admin-nav-item {{ request()->routeIs('admin.tour-packages.*') ? 'active' : '' }}">
                <x-icon name="compass" :size="15" /> Tour Packages
            </a>
            <a href="{{ route('admin.locations.index') }}" class="admin-nav-item {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                <x-icon name="map-pin" :size="15" /> Pickup Locations
            </a>
            <a href="{{ route('admin.booking-categories.index') }}" class="admin-nav-item {{ request()->routeIs('admin.booking-categories.*') ? 'active' : '' }}">
                <x-icon name="tag" :size="15" /> Booking Categories
            </a>

            <!-- 3. FINANCE & BILLING -->
            <div class="admin-menu-heading">Finance & Billing</div>
            <a href="{{ route('admin.payments.index') }}" class="admin-nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <x-icon name="credit-card" :size="15" /> Payments
            </a>
            <a href="{{ route('admin.expenses.index') }}" class="admin-nav-item {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                <x-icon name="dollar-sign" :size="15" /> Expenses
            </a>
            <a href="{{ route('admin.invoices.index') }}" class="admin-nav-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                <x-icon name="receipt" :size="15" /> Invoices
            </a>
            <a href="{{ route('admin.reports.index') }}" class="admin-nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <x-icon name="bar-chart-3" :size="15" /> Reports & Analytics
            </a>

            <!-- 4. SYSTEM & SUPPORT -->
            <div class="admin-menu-heading">System & Support</div>
            <a href="{{ route('admin.complaints.index') }}" class="admin-nav-item {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
                <x-icon name="alert-triangle" :size="15" /> Complaints & Issues
            </a>
            <a href="{{ route('admin.rates.index') }}" class="admin-nav-item {{ request()->routeIs('admin.rates.*') ? 'active' : '' }}">
                <x-icon name="indian-rupee" :size="15" /> Rates Matrix
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="admin-nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                <x-icon name="bell" :size="15" /> Notifications
            </a>
            <a href="{{ route('admin.feedback.index') }}" class="admin-nav-item {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}">
                <x-icon name="star" :size="15" /> Customer Reviews
            </a>
            <a href="{{ route('admin.whatsapp.scan') }}" class="admin-nav-item {{ request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}">
                <x-icon name="message-circle" :size="15" /> WhatsApp Gateway
            </a>
            <a href="{{ route('admin.settings.index') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <x-icon name="settings" :size="15" /> Settings
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div style="padding: 0.85rem 1.15rem; border-top: 1px solid rgba(255,255,255,0.08); font-size: 12px;">
            <a href="{{ route('home') }}" target="_blank" style="color: #60A5FA; display: flex; align-items: center; gap: 6px; font-weight: 500; text-decoration: none;">
                <x-icon name="external-link" :size="13" /> View Public Site
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <!-- Left: Mobile Toggle & Breadcrumbs -->
            <div class="d-flex align-center gap-3">
                <button id="sidebarOpenBtn" class="btn btn-outline btn-sm" style="display: none; padding: 4px 8px; border-color: #E2E8F0;" aria-label="Toggle Menu">
                    <x-icon name="menu" :size="16" />
                </button>
                <div>
                    <div style="font-size: 10.5px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1;">Operations</div>
                    <h1 style="font-size: 15px; font-weight: 700; margin: 2px 0 0 0; color: #172033; line-height: 1.2;">@yield('page_title', 'Dashboard')</h1>
                </div>
            </div>

            <!-- Right: Search, Create Booking, Notifications & Profile -->
            <div class="d-flex align-center gap-3">
                <!-- Search Connected to Existing Bookings Search -->
                <form action="{{ route('admin.bookings.index') }}" method="GET" class="topbar-search-form">
                    <span class="topbar-search-icon">
                        <x-icon name="search" :size="14" />
                    </span>
                    <input type="text" name="search" class="topbar-search-input" placeholder="Search booking ID, customer, phone..." value="{{ request('search') }}">
                </form>

                <!-- Single Prominent Primary Button -->
                <a href="{{ route('admin.bookings.create') }}" class="admin-btn-primary">
                    <x-icon name="plus" :size="14" /> + Create Booking
                </a>

                <!-- Notifications with Unread Count Badge -->
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline btn-sm" title="Notifications" style="padding: 0 9px; height: 34px; color: #475569; border-color: #E2E8F0; position: relative; display: inline-flex; align-items: center; justify-content: center;">
                    <x-icon name="bell" :size="15" />
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span style="position: absolute; top: -4px; right: -4px; background: #DC2626; color: #fff; font-size: 9.5px; font-weight: 800; border-radius: 9999px; min-width: 16px; height: 16px; padding: 0 4px; display: inline-flex; align-items: center; justify-content: center; line-height: 1; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <!-- User Profile & Direct Logout -->
                <div class="d-flex align-center gap-2" style="border-left: 1px solid #E2E8F0; padding-left: 0.85rem;">
                    <div style="width: 32px; height: 32px; background: #172B4D; color: #FFFFFF; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 11.5px; flex-shrink: 0;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'VT', 0, 2)) }}
                    </div>
                    <div class="d-none d-md-block" style="line-height: 1.25;">
                        <div style="font-weight: 600; font-size: 12.5px; color: #172033;">{{ Auth::user()->name }}</div>
                        <div style="font-size: 10.5px; color: #64748B;">Administrator</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: 0.25rem;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="font-size: 11.5px; height: 30px; padding: 0 0.55rem; color: #64748B; border-color: #E2E8F0;" title="Logout">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 6px; margin-bottom: 1.25rem;">
                    <x-icon name="circle-check" :size="16" /> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 6px; margin-bottom: 1.25rem;">
                    <x-icon name="alert-circle" :size="16" /> {{ session('error') }}
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info" style="background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; border-radius: 6px; margin-bottom: 1.25rem;">
                    <x-icon name="info" :size="16" /> {{ session('info') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script>
    // Responsive Mobile Sidebar Toggle
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

    // Global Digits-Only Mobile Number Enforcer
    (function() {
        function isPhoneInput(el) {
            if (!el || !el.tagName || el.tagName.toLowerCase() !== 'input') return false;
            const type = (el.type || '').toLowerCase();
            const name = (el.name || '').toLowerCase();
            const id = (el.id || '').toLowerCase();
            return type === 'tel' ||
                   name === 'mobile' ||
                   name === 'phone' ||
                   name.includes('mobile') ||
                   name.includes('phone') ||
                   id === 'mobile' ||
                   id === 'phone' ||
                   el.classList.contains('phone-input');
        }

        document.addEventListener('input', function(e) {
            if (isPhoneInput(e.target)) {
                const clean = e.target.value.replace(/[^0-9]/g, '').slice(0, 10);
                if (e.target.value !== clean) {
                    e.target.value = clean;
                }
            }
        }, true);

        document.addEventListener('keypress', function(e) {
            if (isPhoneInput(e.target)) {
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                if (e.key && e.key.length === 1 && !/[0-9]/.test(e.key)) {
                    e.preventDefault();
                    return false;
                }
                if (e.target.value.length >= 10 && e.target.selectionStart === e.target.selectionEnd) {
                    e.preventDefault();
                    return false;
                }
            }
        }, true);

        document.addEventListener('paste', function(e) {
            if (isPhoneInput(e.target)) {
                setTimeout(function() {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 10);
                }, 10);
            }
        }, true);
    })();
</script>
@stack('scripts')
</body>
</html>
