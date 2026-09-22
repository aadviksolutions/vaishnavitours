<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vaishnavi Tours - 24/7 Cab & Taxi Service Bilaspur')</title>
    <meta name="description" content="@yield('meta_description', 'Vaishnavi Tours offers premium 24/7 cab booking, outstation taxi, airport transfer, local hourly car rental, and emergency services in Bilaspur, Chhattisgarh.')">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <!-- TaxiService LocalBusiness Structured Data -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'TaxiService',
        'name' => config('vaishnavi.business_name'),
        'telephone' => config('vaishnavi.phone_primary_tel'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => config('vaishnavi.address'),
            'addressLocality' => config('vaishnavi.city'),
            'addressRegion' => config('vaishnavi.state'),
            'addressCountry' => 'IN',
        ],
        'url' => config('app.url'),
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container d-flex align-center justify-between flex-wrap gap-1">
            <div class="d-flex align-center gap-2 flex-wrap">
                <span>📍 {{ config('vaishnavi.address') }}</span>
                <span>📞 <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: var(--primary); font-weight: 700;">{{ config('vaishnavi.phone_primary') }}</a></span>
            </div>
            <div class="d-flex align-center gap-2 flex-wrap">
                <a href="{{ route('contact') }}#emergency" class="top-badge-emergency">
                    🚨 24/7 Ambulance & Emergency
                </a>
                @auth
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" style="font-weight: 700; color: var(--primary);">⚙️ Admin Panel</a>
                    @else
                        <a href="{{ route('customer.dashboard') }}" style="font-weight: 700; color: var(--primary);">👤 My Account</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: var(--slate-300); cursor: pointer; font-size: 0.85rem;">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    <span>|</span>
                    <a href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Navbar -->
    <nav class="navbar">
        <div class="container nav-wrapper">
            <a href="{{ route('home') }}" class="logo">
                <div class="brand-logo-badge">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <div>
                    <div class="logo-brand">Vaishnavi <span>Tours</span></div>
                    <div class="logo-sub">24/7 Cabs & Car Rentals</div>
                </div>
            </a>

            <!-- Mobile Toggle -->
            <button class="mobile-nav-toggle" id="mobile-nav-btn" aria-label="Toggle navigation">
                <svg width="24" height="24" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Nav Links -->
            <div class="nav-links" id="nav-links-menu">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('rates') }}" class="nav-link {{ request()->routeIs('rates') ? 'active' : '' }}">Rates</a>
                <a href="{{ route('vehicles') }}" class="nav-link {{ request()->routeIs('vehicles') ? 'active' : '' }}">Vehicles</a>
                <a href="{{ route('service-network') }}" class="nav-link {{ request()->routeIs('service-network') || request()->routeIs('network') ? 'active' : '' }}">Service Network</a>
                <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                @auth
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link" style="color: var(--primary-dark); font-weight: 700;">Admin</a>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="nav-link" style="color: var(--primary-dark); font-weight: 700;">Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
                @endauth

                <div class="nav-actions d-flex align-center gap-2">
                    <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="btn btn-outline btn-sm" style="color: var(--dark-950); font-weight: 700; border-color: var(--primary); text-decoration: none;">
                        📞 {{ config('vaishnavi.phone_primary') }}
                    </a>
                    <a href="{{ route('booking') }}" class="btn btn-primary btn-sm">🚖 Book Taxi</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Alerts -->
    <div class="container" style="margin-top: 1rem;">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">⚠️ {{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">ℹ️ {{ session('info') }}</div>
        @endif
    </div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="grid grid-4 gap-3">
                <!-- Col 1 -->
                <div>
                    <div class="d-flex align-center gap-2" style="margin-bottom: 1rem;">
                        <div class="brand-logo-badge" style="width: 42px; height: 42px;">
                            <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                        </div>
                        <div style="font-family: var(--font-heading); font-weight: 800; font-size: 1.25rem; color: #fff;">Vaishnavi <span style="color: var(--primary);">Tours</span></div>
                    </div>
                    <p style="font-size: 0.9rem; margin-bottom: 1rem; color: var(--slate-400);">
                        Chhattisgarh's premier chauffeur-driven taxi service. Reliable outstation cabs, hourly rentals, airport transfers, and 24/7 road emergency dispatch.
                    </p>
                    <div style="font-size: 0.85rem; color: var(--slate-300);">
                        <strong>Location:</strong> {{ config('vaishnavi.address') }}
                    </div>
                </div>

                <!-- Col 2 -->
                <div>
                    <h4 class="footer-title">Our Services</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('booking') }}?type=One-Way">One-Way Intercity Cabs</a></li>
                        <li><a href="{{ route('booking') }}?type=Round-Trip">Round-Trip Outstation</a></li>
                        <li><a href="{{ route('booking') }}?type=Airport+Transfer">Airport Pick & Drop</a></li>
                        <li><a href="{{ route('booking') }}?type=Local+Hourly">Local Hourly Rental</a></li>
                        <li><a href="{{ route('booking') }}?type=Emergency">24/7 Emergency Ambulance Cab</a></li>
                    </ul>
                </div>

                <!-- Col 3 -->
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('vehicles') }}">Vehicle Fleet & Tariffs</a></li>
                        <li><a href="{{ route('rates') }}">Transparent Rate Card</a></li>
                        <li><a href="{{ route('network') }}">Chhattisgarh Route Network</a></li>
                        <li><a href="{{ route('about') }}">About Company</a></li>
                        <li><a href="{{ route('feedback') }}">Customer Testimonials</a></li>
                        <li><a href="{{ route('enquiry') }}">Corporate & Group Booking</a></li>
                    </ul>
                </div>

                <!-- Col 4 -->
                <div>
                    <h4 class="footer-title">Head Office</h4>
                    <p style="font-size: 0.9rem; margin-bottom: 0.75rem; color: var(--slate-300);">
                        {{ config('vaishnavi.business_name') }}<br>
                        {{ config('vaishnavi.address') }}
                    </p>
                    <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                        <strong>Call Us:</strong><br>
                        <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: var(--primary); font-weight: 700;">{{ config('vaishnavi.phone_primary') }}</a><br>
                        <a href="tel:{{ config('vaishnavi.phone_secondary_tel') }}" style="color: var(--primary); font-weight: 700;">{{ config('vaishnavi.phone_secondary') }}</a>
                    </p>
                    <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                        <strong>WhatsApp:</strong><br>
                        <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" style="color: #25D366; font-weight: 700; text-decoration: none;">💬 {{ config('vaishnavi.whatsapp') }}</a>
                    </p>
                    <p style="font-size: 0.9rem;">
                        <strong>Email:</strong><br>
                        <a href="mailto:{{ config('vaishnavi.contact_email') }}">{{ config('vaishnavi.contact_email') }}</a>
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    © {{ date('Y') }} Vaishnavi Tours. All rights reserved. Registered Taxi Operator, Bilaspur (C.G.).
                </div>
                <div class="d-flex gap-2 flex-wrap align-center">
                    <a href="{{ route('terms-and-conditions') }}">Terms & Conditions</a>
                    <span>•</span>
                    <a href="{{ route('terms-and-conditions') }}#privacy-policy">Privacy Policy</a>
                    <span>•</span>
                    <a href="{{ route('cancellation-refund-policy') }}">Cancellation Policy</a>
                    <span>•</span>
                    <a href="{{ route('contact') }}">Support</a>
                    <span>•</span>
                    <a href="{{ route('login') }}">Driver & Staff Portal</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Navigation Toggle
        const navBtn = document.getElementById('mobile-nav-btn');
        const navMenu = document.getElementById('nav-links-menu');
        if (navBtn && navMenu) {
            navBtn.addEventListener('click', () => {
                navMenu.classList.toggle('show');
            });
        }
    </script>
    @stack('scripts')
    <!-- Floating WhatsApp Action -->
    <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" class="floating-whatsapp-btn" style="position: fixed; bottom: 24px; right: 24px; z-index: 999; background: #25D366; color: #ffffff; padding: 10px 18px; border-radius: 50px; font-weight: 700; font-size: 0.925rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
        <span style="font-size: 1.25rem;">💬</span>
        <span>Chat on WhatsApp</span>
    </a>
</body>
</html>
