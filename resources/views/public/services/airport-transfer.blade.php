@extends('layouts.app')

@section('title', 'Airport Taxi Bilaspur to Raipur Airport | Cab Booking | Vaishnavi Tours')
@section('meta_description', 'Reliable airport taxi transfers between Bilaspur and Swami Vivekananda Airport Raipur (RPR) & Bilasa Devi Airport (PAB). On-time pickup, sanitized AC cabs, experienced chauffeurs.')
@section('canonical', route('services.airport-transfer'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => route('services')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Airport Transfers', 'item' => route('services.airport-transfer')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => 'Airport Transfer Service',
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
        '@type' => 'Airport',
        'name' => 'Swami Vivekananda Airport, Raipur (RPR)',
    ],
    'description' => 'Dedicated on-time airport taxi pickup and drop service between Bilaspur and Raipur Airport (RPR) with flight-schedule synchronization.',
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'What is the travel distance and time from Bilaspur to Raipur Airport?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'The distance from Bilaspur to Swami Vivekananda Airport in Raipur (RPR) is approximately 135 km via NH 130, typically taking 2.5 to 3 hours depending on traffic.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Can I book a cab for arrival pickup at Raipur Airport to Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Yes, our chauffeur will be stationed at the airport arrival terminal with your name placard and coordinate directly with you upon landing.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Which vehicle is recommended for airport travel with luggage?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'For 1 to 3 passengers with 2 medium bags, Maruti Dzire is ideal. For families with 3 or more large trolley bags, we recommend Maruti Ertiga or Toyota Innova Crysta for comfortable boot space.'
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
            <x-icon name="plane" size="14" /> Punctual Flight Transfers • Bilaspur ⇄ Raipur Airport
        </div>
        <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; line-height: 1.2; margin-bottom: 1rem;">
            Airport Taxi & <span style="color: var(--primary);">Cab Pickup / Drop</span>
        </h1>
        <p style="font-size: 1.1rem; color: var(--slate-300); max-width: 680px; margin: 0 auto;">
            Guaranteed on-time transit to Swami Vivekananda Airport Raipur (RPR) and Bilasa Devi Airport Bilaspur (PAB).
        </p>
    </div>
</div>

<section style="padding: 4.5rem 0; background: #FFFFFF;">
    <div class="container">
        <div class="grid grid-2 gap-4 align-center">
            <div>
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Never Miss a Flight</div>
                <h2 style="font-size: 2.15rem; font-weight: 800; color: var(--dark-900); margin-bottom: 1.25rem;">
                    Stress-Free Bilaspur to Raipur Airport Transfers
                </h2>
                <p style="color: var(--slate-600); font-size: 1rem; line-height: 1.7; margin-bottom: 1.25rem;">
                    Air travelers connecting from Bilaspur know the importance of timely departure. Our chauffeurs arrive 15 minutes before your scheduled pickup, assist with heavy baggage, and ensure smooth cruising along the NH 130 corridor.
                </p>
                <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
                    <div style="font-weight: 800; color: var(--dark-900); font-size: 1.05rem; margin-bottom: 0.75rem;">Key Airport Transfer Details:</div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.95rem; color: var(--slate-700);">
                        <li style="display: flex; align-items: center; gap: 8px;"><x-icon name="circle-check" size="16" class="text-primary" /> <strong>Distance:</strong> ~135 km from Bilaspur to Raipur Airport (RPR)</li>
                        <li style="display: flex; align-items: center; gap: 8px;"><x-icon name="circle-check" size="16" class="text-primary" /> <strong>Estimated Duration:</strong> 2.5 to 3 hours via NH 130 expressway</li>
                        <li style="display: flex; align-items: center; gap: 8px;"><x-icon name="circle-check" size="16" class="text-primary" /> <strong>Airport Terminal Pickup:</strong> Driver coordinates upon flight touchdown</li>
                        <li style="display: flex; align-items: center; gap: 8px;"><x-icon name="circle-check" size="16" class="text-primary" /> <strong>Clean Fleet:</strong> Sanitized AC sedans and spacious Innova Crysta</li>
                    </ul>
                </div>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="{{ route('booking') }}?type=Airport+Transfer" class="btn btn-primary"><x-icon name="plane" size="16" style="margin-right: 6px;" /> Book Airport Cab</a>
                    <a href="https://wa.me/919244784443?text=Hello%20Vaishnavi%20Tours,%20I%20would%20like%20to%20book%20an%20airport%20cab" target="_blank" class="btn btn-outline" style="color: #25D366; border-color: #25D366;"><x-icon.whatsapp size="16" style="margin-right: 6px;" /> WhatsApp Booking</a>
                </div>
            </div>
            <div>
                <img src="{{ asset('assets/images/muv.jpg') }}" alt="Airport Taxi Bilaspur to Raipur - Vaishnavi Tours" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); object-fit: cover; max-height: 420px;" loading="lazy">
            </div>
        </div>
    </div>
</section>
@endsection
