<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Error') — Vaishnavi Tours</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .error-hero {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            background: linear-gradient(135deg, #090E17 0%, #0F172A 100%);
            color: #FFFFFF;
            position: relative;
            overflow: hidden;
        }
        .error-hero::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0) 70%);
            top: 20%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        .error-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            padding: 3rem 2.5rem;
            max-width: 580px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -2px;
            background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.75rem;
            font-family: 'Outfit', sans-serif;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 1rem;
            font-family: 'Outfit', sans-serif;
        }
        .error-desc {
            font-size: 0.975rem;
            color: #94A3B8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .error-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn-error-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #F59E0B;
            color: #0F172A;
            font-weight: 700;
            font-size: 0.925rem;
            padding: 0.8rem 1.75rem;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-error-primary:hover {
            background: #D97706;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
            color: #0F172A;
        }
        .btn-error-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.08);
            color: #E2E8F0;
            font-weight: 600;
            font-size: 0.925rem;
            padding: 0.8rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-error-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #FFFFFF;
        }
        .error-helpline {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.875rem;
            color: #64748B;
        }
        .error-helpline a {
            color: #F59E0B;
            text-decoration: none;
            font-weight: 600;
        }
        .error-helpline a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Minimal Header with Vaishnavi Tours Logo -->
    <header style="background: #0F172A; padding: 1rem 0; border-bottom: 1px solid #1E293B;">
        <div class="container d-flex align-center justify-between">
            <a href="{{ route('home') }}" class="logo">
                <div class="brand-logo-badge">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <div>
                    <div class="logo-brand">Vaishnavi <span>Tours</span></div>
                    <div class="logo-sub">24/7 Cabs & Car Rentals</div>
                </div>
            </a>
            <div class="d-flex align-center gap-2">
                <a href="tel:{{ config('vaishnavi.phone_primary_tel', '+919244784443') }}" style="color: #F59E0B; font-weight: 700; text-decoration: none; font-size: 0.95rem;">
                    📞 Call Us: {{ config('vaishnavi.phone_primary', '9244784443') }}
                </a>
            </div>
        </div>
    </header>

    <!-- Error Content -->
    <main class="error-hero">
        <div class="error-card">
            <div class="error-code">@yield('code', 'Error')</div>
            <h1 class="error-title">@yield('headline', 'Something went wrong')</h1>
            <p class="error-desc">@yield('message', 'An unexpected error occurred. Please try again or return to the homepage.')</p>

            <div class="error-actions">
                <a href="{{ route('home') }}" class="btn-error-primary">
                    🏠 Back to Home
                </a>
                <a href="{{ route('booking') }}" class="btn-error-secondary">
                    🚖 Book a Cab
                </a>
                <a href="{{ route('contact') }}" class="btn-error-secondary">
                    💬 Contact Us
                </a>
            </div>
        </div>
    </main>

    <!-- Minimal Footer -->
    <footer style="background: #090E17; color: #64748B; text-align: center; padding: 1.5rem 0; font-size: 0.85rem; border-top: 1px solid #1E293B;">
        <div class="container">
            &copy; {{ date('Y') }} Vaishnavi Tours. Bilaspur, Chhattisgarh. All rights reserved.
        </div>
    </footer>

</body>
</html>
