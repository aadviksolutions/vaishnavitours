<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Page Title & Primary SEO Metadata -->
    <title>@yield('title', config('seo.default_title'))</title>
    <meta name="description" content="@yield('meta_description', config('seo.default_description'))">
    <meta name="keywords" content="@yield('meta_keywords', config('seo.default_keywords'))">
    <meta name="author" content="Vaishnavi Tours">
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Local SEO Geo Meta Tags (Bilaspur, Chhattisgarh) -->
    <meta name="geo.region" content="IN-CT">
    <meta name="geo.placename" content="Bilaspur, Chhattisgarh">
    <meta name="geo.position" content="22.0797;82.1409">
    <meta name="ICBM" content="22.0797, 82.1409">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Vaishnavi Tours">
    <meta property="og:locale" content="en_IN">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', config('seo.default_title'))))">
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description', config('seo.default_description'))))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset(config('seo.default_image')))">
    <meta property="og:image:alt" content="Vaishnavi Tours Taxi & Cab Service Bilaspur">

    <!-- Twitter / X Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', trim($__env->yieldContent('title', config('seo.default_title'))))">
    <meta name="twitter:description" content="@yield('twitter_description', trim($__env->yieldContent('meta_description', config('seo.default_description'))))">
    <meta name="twitter:image" content="@yield('twitter_image', trim($__env->yieldContent('og_image', asset(config('seo.default_image')))))">

    <!-- Favicon & App Icons -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <!-- Rich Schema.org Structured Data: TaxiService LocalBusiness & WebSite -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'TaxiService',
                '@id' => config('app.url') . '/#taxiservice',
                'name' => config('vaishnavi.business_name'),
                'description' => 'Premier 24/7 taxi and cab service in Bilaspur, Chhattisgarh. Providing verified chauffeur-driven local cabs, outstation travel, and airport transfers.',
                'url' => config('app.url'),
                'telephone' => config('vaishnavi.phone_primary_tel'),
                'priceRange' => '₹₹',
                'currenciesAccepted' => 'INR',
                'paymentAccepted' => 'Cash, UPI, Credit Card, Debit Card, Net Banking',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('assets/branding/vaishnavi-tours-logo.png'),
                ],
                'image' => asset('assets/images/hero-taxi.jpg'),
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => config('vaishnavi.address'),
                    'addressLocality' => config('vaishnavi.city'),
                    'addressRegion' => config('vaishnavi.state'),
                    'postalCode' => config('vaishnavi.pincode'),
                    'addressCountry' => 'IN',
                ],
                'geo' => [
                    '@type' => 'GeoCoordinates',
                    'latitude' => 22.0797,
                    'longitude' => 82.1409,
                ],
                'openingHoursSpecification' => [
                    [
                        '@type' => 'OpeningHoursSpecification',
                        'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                        'opens' => '00:00',
                        'closes' => '23:59',
                    ],
                ],
                'areaServed' => array_map(function ($area) {
                    return [
                        '@type' => 'City',
                        'name' => $area,
                    ];
                }, config('seo.service_areas', ['Bilaspur', 'Raipur', 'Korba', 'Ambikapur'])),
                'sameAs' => [
                    config('vaishnavi.whatsapp_link'),
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => config('app.url') . '/#website',
                'url' => config('app.url'),
                'name' => config('vaishnavi.business_name'),
                'description' => config('seo.default_description'),
                'publisher' => [
                    '@id' => config('app.url') . '/#taxiservice',
                ],
                'inLanguage' => 'en-IN',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @stack('schema')
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container top-bar-inner">
            <!-- Desktop Top Bar Content -->
            <div class="top-bar-desktop d-flex align-center justify-between flex-wrap gap-1">
                <div class="d-flex align-center gap-2 flex-wrap">
                    <span><x-icon name="map-pin" size="14" class="text-primary" style="margin-right: 4px;" /> {{ config('vaishnavi.address') }}</span>
                    <span><x-icon name="phone" size="14" class="text-primary" style="margin-right: 4px;" /><a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: var(--primary); font-weight: 700;">{{ config('vaishnavi.phone_primary') }}</a></span>
                </div>
                <div class="d-flex align-center gap-2 flex-wrap">
                    <a href="{{ route('contact') }}#emergency" class="top-badge-emergency">
                        <x-icon name="life-buoy" size="14" style="margin-right: 4px;" /> 24/7 Road Assistance & Emergency
                    </a>
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" style="font-weight: 700; color: var(--primary);"><x-icon name="settings" size="14" style="margin-right: 4px;" /> Admin Panel</a>
                        @else
                            <a href="{{ route('customer.dashboard') }}" style="font-weight: 700; color: var(--primary);"><x-icon name="user" size="14" style="margin-right: 4px;" /> My Account</a>
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

            <!-- Mobile Top Bar Content (Compact, no horizontal scroll, verified numbers 9244784443) -->
            <div class="top-bar-mobile">
                <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="top-bar-mobile-call">
                    <x-icon name="phone" size="13" class="text-primary" style="margin-right: 4px;" />
                    <span>Call: <strong>{{ config('vaishnavi.phone_primary') }}</strong></span>
                </a>
                <a href="{{ route('contact') }}#emergency" class="top-badge-emergency" style="font-size: 0.72rem; padding: 2px 7px;">
                    <x-icon name="life-buoy" size="12" style="margin-right: 3px;" /> 24/7 Support
                </a>
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

            <!-- Mobile Toggle Button -->
            <button class="mobile-nav-toggle" id="mobile-nav-btn" aria-label="Toggle navigation" aria-expanded="false" aria-controls="mobile-nav-drawer" type="button">
                <svg width="24" height="24" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>

            <!-- Nav Links (Desktop) -->
            <div class="nav-links" id="nav-links-menu">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('services') }}" class="nav-link {{ request()->routeIs('services*') ? 'active' : '' }}">Services</a>
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
                        <x-icon name="phone" size="15" style="margin-right: 6px;" /> {{ config('vaishnavi.phone_primary') }}
                    </a>
                    <a href="{{ route('booking') }}" class="btn btn-primary btn-sm"><x-icon name="car-front" size="15" style="margin-right: 6px;" /> Book Taxi</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Backdrop Overlay -->
    <div class="mobile-nav-backdrop" id="mobile-nav-backdrop" aria-hidden="true"></div>

    <!-- Mobile Navigation Drawer (Opens from RIGHT) -->
    <aside class="mobile-nav-drawer" id="mobile-nav-drawer" aria-label="Mobile Navigation" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="mobile-drawer-header">
            <a href="{{ route('home') }}" class="logo d-flex align-center gap-2">
                <div class="brand-logo-badge" style="width: 38px; height: 38px;">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <div>
                    <div class="logo-brand" style="font-size: 1.15rem;">Vaishnavi <span>Tours</span></div>
                    <div class="logo-sub" style="font-size: 0.6rem;">24/7 Cabs & Car Rentals</div>
                </div>
            </a>
            <button class="mobile-drawer-close" id="mobile-drawer-close-btn" aria-label="Close navigation" type="button">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <div class="mobile-drawer-body">
            <nav class="mobile-drawer-nav">
                <a href="{{ route('home') }}" class="mobile-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('services') }}" class="mobile-nav-link {{ request()->routeIs('services*') ? 'active' : '' }}">Services</a>
                <a href="{{ route('rates') }}" class="mobile-nav-link {{ request()->routeIs('rates') ? 'active' : '' }}">Rates</a>
                <a href="{{ route('vehicles') }}" class="mobile-nav-link {{ request()->routeIs('vehicles') ? 'active' : '' }}">Vehicles</a>
                <a href="{{ route('service-network') }}" class="mobile-nav-link {{ request()->routeIs('service-network') || request()->routeIs('network') ? 'active' : '' }}">Service Network</a>
                <a href="{{ route('about') }}" class="mobile-nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                <a href="{{ route('contact') }}" class="mobile-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                @auth
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link" style="color: var(--primary-dark); font-weight: 700;">Admin Panel</a>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="mobile-nav-link" style="color: var(--primary-dark); font-weight: 700;">My Account</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="mobile-nav-link {{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
                @endauth
            </nav>

            <div class="mobile-drawer-actions">
                <a href="{{ route('booking') }}" class="btn btn-primary" style="width: 100%; justify-content: center; min-height: 46px; font-weight: 800;">
                    <x-icon name="car-front" size="18" style="margin-right: 6px;" /> BOOK A TAXI
                </a>
                <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="btn btn-outline" style="width: 100%; justify-content: center; min-height: 44px; color: var(--dark-950); font-weight: 700; border-color: var(--primary); text-decoration: none;">
                    <x-icon name="phone" size="16" style="margin-right: 6px;" /> Call {{ config('vaishnavi.phone_primary') }}
                </a>
            </div>
        </div>
    </aside>

    <!-- Flash Alerts -->
    <div class="container" style="margin-top: 1rem;">
        @if(session('success'))
            <div class="alert alert-success"><x-icon name="circle-check" size="18" style="margin-right: 6px;" /> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger"><x-icon name="alert-triangle" size="18" style="margin-right: 6px;" /> {{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info"><x-icon name="info" size="18" style="margin-right: 6px;" /> {{ session('info') }}</div>
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
                    <div style="font-size: 0.85rem; color: var(--slate-300); margin-bottom: 1.25rem;">
                        <strong>Location:</strong> {{ config('vaishnavi.address') }}
                    </div>
                    <div style="display: flex; gap: 0.65rem; align-items: center;">
                        <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" class="social-link-btn social-wa" aria-label="WhatsApp" title="WhatsApp">
                            <x-icon.whatsapp size="18" />
                        </a>
                        <a href="https://instagram.com" target="_blank" class="social-link-btn" aria-label="Instagram" title="Instagram">
                            <x-icon.instagram size="18" />
                        </a>
                        <a href="https://facebook.com" target="_blank" class="social-link-btn" aria-label="Facebook" title="Facebook">
                            <x-icon.facebook size="18" />
                        </a>
                        <a href="https://youtube.com" target="_blank" class="social-link-btn" aria-label="YouTube" title="YouTube">
                            <x-icon.youtube size="18" />
                        </a>
                    </div>
                </div>

                <!-- Col 2 -->
                <div>
                    <h4 class="footer-title">Our Services</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('services.local-taxi') }}">Local Taxi Service Bilaspur</a></li>
                        <li><a href="{{ route('services.outstation-taxi') }}">Outstation Cabs from Bilaspur</a></li>
                        <li><a href="{{ route('services.airport-transfer') }}">Raipur & Bilaspur Airport Transfer</a></li>
                        <li><a href="{{ route('services') }}#oneway">One-Way Intercity Cabs</a></li>
                        <li><a href="{{ route('services') }}#corporate">Corporate & Event Car Rental</a></li>
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
                        <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" style="color: #25D366; font-weight: 700; text-decoration: none;"><x-icon.whatsapp size="16" style="margin-right: 6px;" /> {{ config('vaishnavi.whatsapp') }}</a>
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

    @stack('scripts')
    <!-- Floating WhatsApp Action -->
    <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" class="floating-whatsapp-btn" style="position: fixed; bottom: 24px; right: 24px; z-index: 999; background: #25D366; color: #ffffff; padding: 10px 18px; border-radius: 50px; font-weight: 700; font-size: 0.925rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
        <x-icon.whatsapp size="20" />
        <span>Chat on WhatsApp</span>
    </a>
</body>
</html>
