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
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            background: #F8FAFC;
            transition: all 0.2s ease;
        }
        .admin-sidebar {
            width: 254px;
            background: #172B4D;
            flex-shrink: 0;
            transition: margin-left 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 95;
        }
        .admin-wrapper.sidebar-collapsed .admin-sidebar {
            margin-left: -254px;
        }
        .admin-main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            background: #F8FAFC;
        }
        .admin-sidebar-header {
            padding: 0.95rem 1.15rem;
            min-height: 60px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        .sidebar-toggle-btn {
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            height: 34px;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #1E293B;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            font-size: 12px;
            font-weight: 600;
        }
        .sidebar-toggle-btn:hover {
            background: #F1F5F9;
            border-color: #94A3B8;
            color: #0F172A;
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
            padding: 0 1.25rem;
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
            width: 240px;
            height: 34px;
            padding: 0 0.75rem 0 2rem;
            font-size: 12.5px;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            color: #172033;
            transition: all 0.15s ease;
        }
        .topbar-search-input:focus {
            outline: none;
            background: #FFFFFF;
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            width: 280px;
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
            padding: 0 0.85rem;
            font-size: 12.5px;
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
        #sidebarBackdrop {
            display: none;
        }
        @media (max-width: 992px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: -270px;
                width: 270px;
                margin-left: 0 !important;
                z-index: 1050;
                overflow-y: auto;
                box-shadow: none;
            }
            .admin-sidebar.show {
                left: 0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            }
            #sidebarBackdrop {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(15, 23, 42, 0.65);
                backdrop-filter: blur(2px);
                z-index: 1040;
            }
            #sidebarBackdrop.show {
                display: block;
            }
            .sidebar-close-btn {
                display: inline-flex !important;
            }
            .topbar-search-input {
                width: 160px;
            }
            .topbar-search-input:focus {
                width: 190px;
            }
        }
        @media (max-width: 768px) {
            .topbar-search-form {
                display: none;
            }
            .admin-topbar {
                padding: 0 0.75rem;
            }
            .admin-content {
                padding: 0.75rem !important;
            }
            .sidebar-toggle-btn span {
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
            <button id="sidebarCloseBtn" class="sidebar-close-btn" style="background: none; border: none; color: #94A3B8; cursor: pointer; font-size: 16px; padding: 4px 6px; border-radius: 4px;" aria-label="Close sidebar" title="Close Menu">✕</button>
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

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarBackdrop"></div>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <!-- Left: Sidebar Toggle Button & Breadcrumbs -->
            <div class="d-flex align-center gap-2">
                <button id="sidebarToggleBtn" type="button" class="sidebar-toggle-btn" aria-label="Toggle Navigation Menu" title="Toggle Left Sidebar Menu">
                    <x-icon name="menu" :size="16" />
                    <span>Menu</span>
                </button>
                <div>
                    <div style="font-size: 10px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1;">Operations</div>
                    <h1 style="font-size: 14.5px; font-weight: 700; margin: 2px 0 0 0; color: #172033; line-height: 1.2;">@yield('page_title', 'Dashboard')</h1>
                </div>
            </div>

            <!-- Right: Search, Push Notifications, Create Booking, Notifications & Profile -->
            <div class="d-flex align-center gap-2">
                <!-- Search Connected to Existing Bookings Search -->
                <form action="{{ route('admin.bookings.index') }}" method="GET" class="topbar-search-form">
                    <span class="topbar-search-icon">
                        <x-icon name="search" :size="14" />
                    </span>
                    <input type="text" name="search" class="topbar-search-input" placeholder="Search ID, customer, phone..." value="{{ request('search') }}">
                </form>

                <!-- Web Push Alerts Status / Enable Trigger -->
                <button id="pushNotificationBtn" type="button" class="btn btn-outline btn-sm" title="Enable Real-Time Push Notifications on Mobile & Laptop" style="padding: 0 9px; height: 34px; font-size: 11px; font-weight: 600; color: #1D4ED8; border-color: #BFDBFE; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                    <span id="pushBellIcon">🔔</span> <span id="pushStatusText" class="d-none d-md-inline">Live Alerts</span>
                </button>

                <!-- Single Prominent Primary Button -->
                <a href="{{ route('admin.bookings.create') }}" class="admin-btn-primary" title="Create New Booking">
                    <span class="d-none d-sm-inline">Create</span> Booking
                </a>

                <!-- Notifications with Unread Count Badge -->
                <a href="{{ route('admin.notifications.index') }}" id="topbarBellLink" class="btn btn-outline btn-sm" title="Notifications" style="padding: 0 9px; height: 34px; color: #475569; border-color: #CBD5E1; position: relative; display: inline-flex; align-items: center; justify-content: center;">
                    <x-icon name="bell" :size="15" />
                    <span id="topbarUnreadBadge" style="position: absolute; top: -4px; right: -4px; background: #DC2626; color: #fff; font-size: 9.5px; font-weight: 800; border-radius: 9999px; min-width: 16px; height: 16px; padding: 0 4px; display: {{ (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0) ? 'inline-flex' : 'none' }}; align-items: center; justify-content: center; line-height: 1; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                        {{ isset($unreadNotificationsCount) && $unreadNotificationsCount > 99 ? '99+' : ($unreadNotificationsCount ?? 0) }}
                    </span>
                </a>

                <!-- User Profile & Direct Logout -->
                <div class="d-flex align-center gap-2" style="border-left: 1px solid #E2E8F0; padding-left: 0.65rem;">
                    <div style="width: 32px; height: 32px; background: #172B4D; color: #FFFFFF; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 11.5px; flex-shrink: 0;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'VT', 0, 2)) }}
                    </div>
                    <div class="d-none d-lg-block" style="line-height: 1.25;">
                        <div style="font-weight: 600; font-size: 12px; color: #172033; max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Auth::user()->name }}</div>
                        <div style="font-size: 10px; color: #64748B;">Admin</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: 0.15rem;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="font-size: 11px; height: 30px; padding: 0 0.45rem; color: #64748B; border-color: #CBD5E1;" title="Logout">
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

<div id="adminNotificationToastContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; max-width: 360px; width: calc(100% - 40px); pointer-events: none;"></div>

<script>
    // -------------------------------------------------------------
    // 1. Sidebar Toggle & Auto-Hide Controller
    // -------------------------------------------------------------
    const adminWrapper = document.querySelector('.admin-wrapper');
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    const backdrop = document.getElementById('sidebarBackdrop');

    // Restore desktop collapsed state from local storage if saved
    if (window.innerWidth > 992 && localStorage.getItem('vt_admin_sidebar_collapsed') === '1') {
        adminWrapper.classList.add('sidebar-collapsed');
    }

    function toggleAdminSidebar() {
        if (window.innerWidth <= 992) {
            const isOpen = sidebar.classList.contains('show');
            if (isOpen) {
                sidebar.classList.remove('show');
                if (backdrop) backdrop.classList.remove('show');
            } else {
                sidebar.classList.add('show');
                if (backdrop) backdrop.classList.add('show');
            }
        } else {
            adminWrapper.classList.toggle('sidebar-collapsed');
            localStorage.setItem('vt_admin_sidebar_collapsed', adminWrapper.classList.contains('sidebar-collapsed') ? '1' : '0');
        }
    }

    function closeAdminSidebar() {
        if (sidebar) sidebar.classList.remove('show');
        if (backdrop) backdrop.classList.remove('show');
        if (window.innerWidth > 992) {
            adminWrapper.classList.add('sidebar-collapsed');
            localStorage.setItem('vt_admin_sidebar_collapsed', '1');
        }
    }

    if (toggleBtn) toggleBtn.addEventListener('click', toggleAdminSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeAdminSidebar);
    if (backdrop) backdrop.addEventListener('click', closeAdminSidebar);

    // Auto-hide menu when any menu item is selected/clicked
    document.querySelectorAll('.admin-nav-item').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 992) {
                if (sidebar) sidebar.classList.remove('show');
                if (backdrop) backdrop.classList.remove('show');
            }
        });
    });

    // -------------------------------------------------------------
    // 2. Real-Time Web Push Notifications & Audio Chime System
    // -------------------------------------------------------------
    let swRegistration = null;
    let lastPolledNotificationId = null;
    const pushBtn = document.getElementById('pushNotificationBtn');
    const pushStatusText = document.getElementById('pushStatusText');
    const pushBellIcon = document.getElementById('pushBellIcon');
    const unreadBadge = document.getElementById('topbarUnreadBadge');

    // Web Audio API Pleasant Notification Chime (No external sound file needed)
    function playChimeSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            
            // First note (E5)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, ctx.currentTime);
            gain1.gain.setValueAtTime(0.12, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.28);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.3);

            // Second higher note (A5)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880.00, ctx.currentTime + 0.12);
            gain2.gain.setValueAtTime(0.15, ctx.currentTime + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.12);
            osc2.stop(ctx.currentTime + 0.52);
        } catch (e) {
            // Audio context may be restricted before first gesture
        }
    }

    // Register Service Worker for Mobile & Desktop Web Push
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            swRegistration = reg;
        }).catch(err => {
            console.log('SW registration note:', err);
        });
    }

    // Update Push UI Button State
    function updatePushUI() {
        if (!('Notification' in window)) {
            if (pushBtn) pushBtn.style.display = 'none';
            return;
        }

        if (Notification.permission === 'granted') {
            if (pushBtn) {
                pushBtn.style.color = '#059669';
                pushBtn.style.background = '#ECFDF5';
                pushBtn.style.borderColor = '#A7F3D0';
                if (pushBellIcon) pushBellIcon.textContent = '🟢';
                if (pushStatusText) pushStatusText.textContent = 'Push Active';
                pushBtn.title = 'Real-time push notifications are active for bookings & enquiries';
            }
        } else if (Notification.permission === 'denied') {
            if (pushBtn) {
                pushBtn.style.color = '#DC2626';
                pushBtn.style.background = '#FEF2F2';
                pushBtn.style.borderColor = '#FECACA';
                if (pushBellIcon) pushBellIcon.textContent = '🔕';
                if (pushStatusText) pushStatusText.textContent = 'Blocked';
                pushBtn.title = 'Push notifications blocked in browser settings. Please enable to receive alerts.';
            }
        } else {
            if (pushBtn) {
                pushBtn.style.color = '#1D4ED8';
                pushBtn.style.background = '#EFF6FF';
                pushBtn.style.borderColor = '#BFDBFE';
                if (pushBellIcon) pushBellIcon.textContent = '🔔';
                if (pushStatusText) pushStatusText.textContent = 'Enable Push';
                pushBtn.title = 'Click to enable instant notifications on mobile & laptop';
            }
        }
    }

    // Request Notification Permission on user click
    if (pushBtn) {
        pushBtn.addEventListener('click', function() {
            if (!('Notification' in window)) {
                alert('Web notifications are not supported in this browser.');
                return;
            }
            Notification.requestPermission().then(permission => {
                updatePushUI();
                if (permission === 'granted') {
                    playChimeSound();
                    displayInAppToast({
                        title: 'Push Notifications Enabled! 🎉',
                        message: 'You will receive instant alerts for bookings, enquiries, and payments.'
                    });
                }
            });
        });
    }

    // Display In-App Floating Toast Card
    function displayInAppToast(notif) {
        const container = document.getElementById('adminNotificationToastContainer');
        if (!container) return;

        const card = document.createElement('div');
        card.style.cssText = 'background: #1E293B; color: #FFFFFF; border-radius: 8px; padding: 12px 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border-left: 4px solid #2563EB; font-size: 13px; line-height: 1.4; display: flex; align-items: start; justify-content: space-between; gap: 10px; pointer-events: auto; animation: vtToastIn 0.3s ease;';
        
        const contentDiv = document.createElement('div');
        contentDiv.innerHTML = `
            <div style="font-weight: 700; color: #60A5FA; font-size: 13.5px; margin-bottom: 2px;">${notif.title || 'New Alert'}</div>
            <div style="color: #E2E8F0; font-size: 12px;">${notif.message || ''}</div>
            ${notif.action_url ? `<a href="${notif.action_url}" style="color: #93C5FD; font-size: 11.5px; font-weight: 600; text-decoration: underline; margin-top: 5px; display: inline-block;">View Details &rarr;</a>` : ''}
        `;

        const closeX = document.createElement('button');
        closeX.textContent = '✕';
        closeX.style.cssText = 'background: none; border: none; color: #94A3B8; font-size: 14px; cursor: pointer; padding: 0 4px;';
        closeX.onclick = () => card.remove();

        card.appendChild(contentDiv);
        card.appendChild(closeX);
        container.appendChild(card);

        setTimeout(() => {
            if (card && card.parentNode) {
                card.style.opacity = '0';
                card.style.transition = 'opacity 0.4s ease';
                setTimeout(() => card.remove(), 400);
            }
        }, 8000);
    }

    // Poll Endpoint for Live Notifications
    async function pollAdminNotifications() {
        try {
            const url = '{{ route("admin.notifications.poll") }}' + (lastPolledNotificationId !== null ? `?last_id=${lastPolledNotificationId}` : '');
            const res = await fetch(url, { credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();

            // Update unread badge in topbar
            if (unreadBadge) {
                const count = parseInt(data.unread_count || 0);
                if (count > 0) {
                    unreadBadge.textContent = count > 99 ? '99+' : count;
                    unreadBadge.style.display = 'inline-flex';
                } else {
                    unreadBadge.style.display = 'none';
                }
            }

            // On first poll, synchronize latest_id without triggering old alerts
            if (lastPolledNotificationId === null) {
                lastPolledNotificationId = data.latest_id || 0;
                return;
            }

            // If new notifications arrived:
            if (data.notifications && data.notifications.length > 0) {
                lastPolledNotificationId = data.latest_id;
                playChimeSound();

                data.notifications.forEach(item => {
                    // 1. Native Web Push Notification (Desktop & Mobile Lockscreen/Status Bar)
                    if (window.Notification && Notification.permission === 'granted') {
                        if (swRegistration) {
                            swRegistration.showNotification(item.title || 'Vaishnavi Tours Alert', {
                                body: item.message || '',
                                icon: '{{ asset("assets/branding/vaishnavi-tours-logo.png") }}',
                                badge: '{{ asset("assets/branding/vaishnavi-tours-logo.png") }}',
                                tag: 'vt-notif-' + item.id,
                                data: { url: item.action_url || '{{ route("admin.notifications.index") }}' }
                            });
                        } else {
                            try {
                                new Notification(item.title, {
                                    body: item.message,
                                    icon: '{{ asset("assets/branding/vaishnavi-tours-logo.png") }}'
                                });
                            } catch (e) {}
                        }
                    }

                    // 2. In-App Floating Toast Card
                    displayInAppToast(item);
                });
            }
        } catch (err) {
            // Network or temporary server issue - silent retry next cycle
        }
    }

    // Initialize Push & Start Periodic Polling (every 8 seconds)
    updatePushUI();
    pollAdminNotifications();
    setInterval(pollAdminNotifications, 8000);

    // -------------------------------------------------------------
    // 3. Global Digits-Only Mobile Number Enforcer
    // -------------------------------------------------------------
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
