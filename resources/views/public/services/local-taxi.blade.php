@extends('layouts.app')

@section('title', 'Local Taxi Service in Bilaspur | Hourly & City Cabs | Vaishnavi Tours')
@section('meta_description', 'Book 24/7 local taxi and cab service in Bilaspur, Chhattisgarh. Doorstep pickup at Mangal Chowk, Bilaspur Junction railway station, Apollo Hospital, city markets & full day packages.')
@section('canonical', route('services.local-taxi'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => route('services')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Local Taxi Service', 'item' => route('services.local-taxi')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => 'Local Taxi Service',
    'provider' => [
        '@type' => 'TaxiService',
        'name' => config('vaishnavi.business_name'),
        'telephone' => config('vaishnavi.phone_primary_tel'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => config('vaishnavi.address'),
            'addressLocality' => 'Bilaspur',
            'addressRegion' => 'Chhattisgarh',
            'addressCountry' => 'IN',
        ],
    ],
    'areaServed' => [
        '@type' => 'City',
        'name' => 'Bilaspur',
    ],
    'description' => 'Chauffeur-driven local city taxi service across Bilaspur for railway station transfers, hospital visits, and hourly car rentals.',
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Do you provide pickup from Bilaspur Junction railway station?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Yes, we provide 24/7 dedicated cab pickup and drop at Bilaspur Railway Station (BSP) with prompt platform coordination.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'What local hourly rental packages are available in Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'We offer convenient local rental slabs: 4 Hours / 40 KM and 8 Hours / 80 KM, with transparent per-hour and per-km extensions as needed.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Can I hire a cab for Apollo Hospital visits in Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Yes, we frequently support patient pickup, outpatient consultations, and patient discharge trips with courteous, careful chauffeurs.'
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<div class="page-header" style="background: linear-gradient(135deg, #090E17 0%, #1E293B 100%); padding: 4.5rem 0 3.5rem; color: #FFFFFF; text-align: center;">
    <div class="container">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(245, 158, 11, 0.15); color: var(--primary); padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem; border: 1px solid rgba(245, 158, 11, 0.3);">
            <x-icon name="map-pin" size="14" /> Bilaspur Local Cab Booking • 24/7 Doorstep Pickup
        </div>
        <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; line-height: 1.2; margin-bottom: 1rem;">
            Local Taxi Service & <span style="color: var(--primary);">City Cabs in Bilaspur</span>
        </h1>
        <p style="font-size: 1.1rem; color: var(--slate-300); max-width: 680px; margin: 0 auto;">
            Doorstep cab dispatch across Mangal Chowk, Vyapar Vihar, Link Road, Nehru Nagar, Sirgitti, and all corners of Bilaspur.
        </p>
    </div>
</div>

<section style="padding: 4.5rem 0; background: #FFFFFF;">
    <div class="container">
        <div class="grid grid-2 gap-4 align-center">
            <div>
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">City Travel Made Effortless</div>
                <h2 style="font-size: 2.15rem; font-weight: 800; color: var(--dark-900); margin-bottom: 1.25rem;">
                    Prompt & Punctual Taxi Travel Across Bilaspur
                </h2>
                <p style="color: var(--slate-600); font-size: 1rem; line-height: 1.7; margin-bottom: 1.25rem;">
                    Whether you need a quick pickup from <strong>Bilaspur Junction (BSP)</strong> railway station, daily business meetings across commercial hubs, or a family visit to local temples and markets, Vaishnavi Tours delivers clean, sanitized air-conditioned vehicles right to your doorstep.
                </p>
                <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 2rem;">
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <div class="icon-box icon-box-sm icon-box-primary"><x-icon name="train-front" size="18" /></div>
                        <div>
                            <strong style="color: var(--dark-900); display: block; font-size: 1rem;">Railway Station Transfers (24/7)</strong>
                            <span style="color: var(--slate-600); font-size: 0.9rem;">Never worry about missing your train. Dedicated chauffeur dispatch coordinated with train arrival times.</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <div class="icon-box icon-box-sm icon-box-primary"><x-icon name="clock" size="18" /></div>
                        <div>
                            <strong style="color: var(--dark-900); display: block; font-size: 1rem;">Hourly Packages (4h/40km & 8h/80km)</strong>
                            <span style="color: var(--slate-600); font-size: 0.9rem;">Retain the cab for shopping, wedding attendance, or corporate visits with zero hassle.</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <div class="icon-box icon-box-sm icon-box-primary"><x-icon name="shield-check" size="18" /></div>
                        <div>
                            <strong style="color: var(--dark-900); display: block; font-size: 1rem;">Safe Medical Hospital Runs</strong>
                            <span style="color: var(--slate-600); font-size: 0.9rem;">Patient-friendly chauffeurs with smooth driving for visits to Apollo Hospital, CIMS, and city clinics.</span>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="{{ route('booking') }}?type=Local+Hourly" class="btn btn-primary"><x-icon name="calendar-check" size="16" style="margin-right: 6px;" /> Book Local Taxi</a>
                    <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="btn btn-outline"><x-icon name="phone" size="16" style="margin-right: 6px;" /> Call {{ config('vaishnavi.phone_primary') }}</a>
                </div>
            </div>
            <div>
                <img src="{{ asset('assets/vehicles/maruti-suzuki-dzire.jpg') }}" alt="Local Taxi Service in Bilaspur - Vaishnavi Tours" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); object-fit: cover; max-height: 420px;" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- Available Vehicles for Local Hire -->
<section style="padding: 4.5rem 0; background: var(--slate-50);">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">City Fleet Options</div>
            <h2 style="font-size: 2.15rem; font-weight: 800; color: var(--dark-900);">Popular Vehicles for Local Travel</h2>
        </div>
        <div class="grid grid-3 gap-3">
            @foreach($vehicles->take(3) as $vehicle)
                <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                    <img src="{{ $vehicle->icon_url }}" alt="{{ $vehicle->name }} - Local Cab Bilaspur" style="width: 100%; height: 200px; object-fit: cover;" loading="lazy">
                    <div style="padding: 1.5rem;">
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">{{ $vehicle->name }}</h3>
                        <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--slate-600); margin-bottom: 1rem;">
                            <span><x-icon name="users" size="14" /> {{ $vehicle->seating_capacity }} Seats</span>
                            <span><x-icon name="snowflake" size="14" /> {{ $vehicle->ac_non_ac }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--slate-200); padding-top: 1rem;">
                            <div>
                                <span style="font-size: 0.75rem; color: var(--slate-500); display: block;">Local Hourly Rate</span>
                                <strong style="font-size: 1.2rem; color: var(--primary-dark);">₹{{ number_format($vehicle->per_hour_rate, 0) }} / hr</strong>
                            </div>
                            <a href="{{ route('booking') }}?vehicle_id={{ $vehicle->id }}" class="btn btn-primary btn-sm">Select</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
