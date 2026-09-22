@extends('layouts.app')

@section('title', 'Outstation Taxi from Bilaspur | One-Way & Round Trip Cabs | Vaishnavi Tours')
@section('meta_description', 'Book outstation cabs from Bilaspur to Raipur, Korba, Ambikapur, Durg, Bhilai & Raigarh. One-way and round-trip intercity taxi with verified chauffeurs and transparent per-km billing.')
@section('canonical', route('services.outstation-taxi'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => route('services')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Outstation Taxi', 'item' => route('services.outstation-taxi')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => 'Outstation Taxi Service',
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
        '@type' => 'State',
        'name' => 'Chhattisgarh',
    ],
    'description' => 'Comfortable outstation cabs from Bilaspur to all major Chhattisgarh cities and tourist corridors with experienced highway chauffeurs.',
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'How are outstation taxi fares calculated from Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Outstation fares are calculated based on the actual kilometer distance traveled (subject to minimum daily kilometer standards, typically 250–300 km/day), vehicle per-km rate, and driver daily allowance.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Which cities can I travel to from Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'We cover all major Chhattisgarh destinations including Raipur, Korba, Ambikapur, Raigarh, Durg-Bhilai, Champa, Janjgir, Ratanpur, Amarkantak, Jagdalpur, and neighboring states.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Is one-way outstation cab booking available from Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Yes, one-way drops are offered on popular transit routes including Bilaspur to Raipur and Bilaspur to Korba without paying two-way charges.'
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
            <x-icon name="route" size="14" /> Intercity Highway Travel • One-Way & Round Trips
        </div>
        <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; line-height: 1.2; margin-bottom: 1rem;">
            Outstation Taxi & <span style="color: var(--primary);">Intercity Cabs from Bilaspur</span>
        </h1>
        <p style="font-size: 1.1rem; color: var(--slate-300); max-width: 680px; margin: 0 auto;">
            Smooth highway journeys with verified, courteous chauffeurs across Chhattisgarh and surrounding states.
        </p>
    </div>
</div>

<section style="padding: 4.5rem 0; background: #FFFFFF;">
    <div class="container">
        <div class="grid grid-2 gap-4 align-center">
            <div>
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Explore Beyond Bilaspur</div>
                <h2 style="font-size: 2.15rem; font-weight: 800; color: var(--dark-900); margin-bottom: 1.25rem;">
                    Reliable Highway Rides for Business, Family & Pilgrimage
                </h2>
                <p style="color: var(--slate-600); font-size: 1rem; line-height: 1.7; margin-bottom: 1.25rem;">
                    When leaving Bilaspur for an outstation journey, comfort and driver dependability make all the difference. Vaishnavi Tours provides spacious sedans, family MUVs (Ertiga & Innova Crysta), and tempo travellers equipped with GPS, luggage room, and smooth suspensions.
                </p>
                <div class="grid grid-2 gap-2" style="margin-bottom: 2rem;">
                    <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary);">
                        <strong style="color: var(--dark-900); display: block; font-size: 0.95rem;">Bilaspur → Raipur (115 km)</strong>
                        <span style="font-size: 0.85rem; color: var(--slate-600);">NH 130 express route (~2.5 hrs).</span>
                    </div>
                    <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary);">
                        <strong style="color: var(--dark-900); display: block; font-size: 0.95rem;">Bilaspur → Korba (95 km)</strong>
                        <span style="font-size: 0.85rem; color: var(--slate-600);">Industrial belt transit (~2.5 hrs).</span>
                    </div>
                    <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary);">
                        <strong style="color: var(--dark-900); display: block; font-size: 0.95rem;">Bilaspur → Ambikapur (220 km)</strong>
                        <span style="font-size: 0.85rem; color: var(--slate-600);">Northern hills corridor (~5.5 hrs).</span>
                    </div>
                    <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary);">
                        <strong style="color: var(--dark-900); display: block; font-size: 0.95rem;">Bilaspur → Amarkantak (118 km)</strong>
                        <span style="font-size: 0.85rem; color: var(--slate-600);">Holy Narmada source & hills.</span>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="{{ route('booking') }}?type=Round-Trip" class="btn btn-primary"><x-icon name="route" size="16" style="margin-right: 6px;" /> Book Outstation Cab</a>
                    <a href="{{ route('rates') }}" class="btn btn-outline"><x-icon name="receipt" size="16" style="margin-right: 6px;" /> View Per-KM Rates</a>
                </div>
            </div>
            <div>
                <img src="{{ asset('assets/images/suv.jpg') }}" alt="Outstation Cab Booking from Bilaspur - Vaishnavi Tours" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); object-fit: cover; max-height: 420px;" loading="lazy">
            </div>
        </div>
    </div>
</section>
@endsection
