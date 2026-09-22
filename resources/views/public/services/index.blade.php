@extends('layouts.app')

@section('title', 'Taxi & Travel Services in Bilaspur | Vaishnavi Tours')
@section('meta_description', 'Complete taxi, cab booking, and car rental services in Bilaspur, Chhattisgarh: Local hourly cabs, outstation travel to Raipur, airport transfers, one-way rides & group travel.')
@section('canonical', route('services'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => route('services')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'What taxi services does Vaishnavi Tours provide in Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Vaishnavi Tours provides 24/7 local city cabs in Bilaspur, outstation round-trip and one-way taxis across Chhattisgarh, airport pick and drop transfers to Raipur and Bilaspur airports, and corporate group rentals.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'How can I book a cab with Vaishnavi Tours?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'You can book instantly online through our booking page, call our 24/7 dispatch desk at 9244784443 / 9179484443, or message us on WhatsApp.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Are cabs available for late night and early morning travel?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Yes, our dispatch desk operates 24 hours a day, 7 days a week. We recommend booking in advance for night or early morning airport and train station trips.'
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
            <x-icon name="car-front" size="14" /> Professional Travel Solutions • Bilaspur (C.G.)
        </div>
        <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; line-height: 1.2; margin-bottom: 1rem;">
            Taxi &amp; Cab <span style="color: var(--primary);">Services in Bilaspur</span>
        </h1>
        <p style="font-size: 1.1rem; color: var(--slate-300); max-width: 680px; margin: 0 auto;">
            Reliable, clean, and punctual chauffeur-driven cabs for local commutes, intercity highway travel, and airport connections across Chhattisgarh.
        </p>
    </div>
</div>

<!-- Services Grid Section -->
<section style="padding: 4.5rem 0; background: var(--slate-50);">
    <div class="container">
        <div class="grid grid-3 gap-3">
            <!-- 1. Local Taxi -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--primary); border-radius: var(--radius-lg); padding: 2rem;">
                <div>
                    <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;">
                        <x-icon name="map-pin" size="28" />
                    </div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.75rem;">Local Taxi Service</h2>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Doorstep city pickup across Bilaspur for railway station transfers, hospital visits, business meetings, and shopping. Available on point-to-point or hourly rental packages.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; font-size: 0.9rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.5rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> Bilaspur Junction Station Pickup</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> Apollo Hospital Direct Medical Trips</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> 4 Hr / 8 Hr Hourly Rental Packages</li>
                    </ul>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('services.local-taxi') }}" class="btn btn-outline btn-sm flex-1" style="justify-content: center;">Learn More</a>
                    <a href="{{ route('booking') }}?type=Local+Hourly" class="btn btn-primary btn-sm flex-1" style="justify-content: center;">Book Local</a>
                </div>
            </div>

            <!-- 2. Outstation Cabs -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid #800020; border-radius: var(--radius-lg); padding: 2rem;">
                <div>
                    <div class="icon-box icon-box-lg icon-box-maroon" style="margin-bottom: 1.25rem;">
                        <x-icon name="route" size="28" />
                    </div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.75rem;">Outstation Cabs</h2>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Comfortable highway cabs from Bilaspur to all major cities including Raipur, Korba, Ambikapur, Raigarh, and Durg-Bhilai. Available for both one-way and round trips.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; font-size: 0.9rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.5rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #800020;" /> Transparent per-km rates from ₹11/km</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #800020;" /> Professional highway experienced drivers</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #800020;" /> No hidden fuel or return surge fees</li>
                    </ul>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('services.outstation-taxi') }}" class="btn btn-outline btn-sm flex-1" style="justify-content: center;">Learn More</a>
                    <a href="{{ route('booking') }}?type=Round-Trip" class="btn btn-primary btn-sm flex-1" style="justify-content: center;">Book Outstation</a>
                </div>
            </div>

            <!-- 3. Airport Transfer -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--primary); border-radius: var(--radius-lg); padding: 2rem;">
                <div>
                    <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;">
                        <x-icon name="plane" size="28" />
                    </div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.75rem;">Airport Transfers</h2>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Guaranteed on-time airport pickup and drop between Bilaspur and Raipur Swami Vivekananda Airport (RPR) as well as Bilaspur Bilasa Devi Airport (PAB).
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; font-size: 0.9rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.5rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> Bilaspur to Raipur Airport (135 km)</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> Chauffeur meets you at terminal gate</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> Spacious boot space for flight luggage</li>
                    </ul>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('services.airport-transfer') }}" class="btn btn-outline btn-sm flex-1" style="justify-content: center;">Learn More</a>
                    <a href="{{ route('booking') }}?type=Airport+Transfer" class="btn btn-primary btn-sm flex-1" style="justify-content: center;">Book Airport</a>
                </div>
            </div>

            <!-- 4. One-Way Cabs -->
            <div class="card" id="oneway" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--dark-900); border-radius: var(--radius-lg); padding: 2rem;">
                <div>
                    <div class="icon-box icon-box-lg icon-box-dark" style="margin-bottom: 1.25rem; background: var(--dark-900); color: #fff;">
                        <x-icon name="arrow-right" size="28" />
                    </div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.75rem;">One-Way Drops</h2>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Pay only for one-side travel when moving between Bilaspur and neighboring cities. No return fare obligation on supported high-frequency transit corridors.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; font-size: 0.9rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.5rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> Popular: Bilaspur to Raipur One-Way</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> Popular: Bilaspur to Korba One-Way</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" class="text-primary" /> Transparent fixed single-journey fares</li>
                    </ul>
                </div>
                <div>
                    <a href="{{ route('booking') }}?type=One-Way" class="btn btn-primary btn-sm btn-block" style="justify-content: center;">Book One-Way</a>
                </div>
            </div>

            <!-- 5. Corporate & Group Rental -->
            <div class="card" id="corporate" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid #16A34A; border-radius: var(--radius-lg); padding: 2rem;">
                <div>
                    <div class="icon-box icon-box-lg" style="margin-bottom: 1.25rem; background: #ECFDF5; color: #16A34A;">
                        <x-icon name="briefcase" size="28" />
                    </div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.75rem;">Corporate &amp; Fleet Hire</h2>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Tailored cab rentals for corporate plants, business delegations, wedding events, and pilgrim group tours across Chhattisgarh in Innova Crysta and 14-seater Tempo Travellers.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; font-size: 0.9rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.5rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #16A34A;" /> GST Compliant Tax Invoicing</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #16A34A;" /> Dedicated Chauffeur &amp; Vehicle Standby</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #16A34A;" /> Group Coaches for 6 to 14 Passengers</li>
                    </ul>
                </div>
                <div>
                    <a href="{{ route('enquiry') }}" class="btn btn-outline btn-sm btn-block" style="justify-content: center;">Corporate Enquiry</a>
                </div>
            </div>

            <!-- 6. 24/7 Road Assistance -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid #DC2626; border-radius: var(--radius-lg); padding: 2rem;">
                <div>
                    <div class="icon-box icon-box-lg" style="margin-bottom: 1.25rem; background: #FEF2F2; color: #DC2626;">
                        <x-icon name="life-buoy" size="28" />
                    </div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.75rem;">24/7 Emergency Dispatch</h2>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Urgent patient drop, hospital transfers to Raipur, and rapid standby cab assistance when stranded on Bilaspur highway corridors.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; font-size: 0.9rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.5rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #DC2626;" /> Immediate phone response: {{ config('vaishnavi.phone_primary') }}</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #DC2626;" /> AIIMS Raipur / Medanta connectivity</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="15" style="color: #DC2626;" /> 24/7 night dispatch operations</li>
                    </ul>
                </div>
                <div>
                    <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="btn btn-danger btn-sm btn-block" style="justify-content: center;"><x-icon name="phone" size="15" style="margin-right: 6px;" /> Call Desk: {{ config('vaishnavi.phone_primary') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section style="padding: 4.5rem 0; background: #FFFFFF;">
    <div class="container" style="max-width: 860px;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="display: inline-block; font-size: 0.85rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Frequently Asked Questions</div>
            <h2 style="font-size: 2.15rem; font-weight: 800; color: var(--dark-900);">Common Questions About Our Services</h2>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="card" style="padding: 1.5rem; border-radius: var(--radius-md);">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">How early should I book my taxi in Bilaspur?</h3>
                <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin: 0;">For local trips within Bilaspur, we can often dispatch within 20–30 minutes subject to fleet availability. For airport transfers to Raipur or outstation trips, booking 4 to 12 hours in advance ensures vehicle allocation and on-time chauffeur arrival.</p>
            </div>
            <div class="card" style="padding: 1.5rem; border-radius: var(--radius-md);">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">Are tolls, state taxes, and parking included in the fare?</h3>
                <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin: 0;">Our base rates include fuel, clean AC vehicle, and driver charges. Toll plaza charges, state border entry taxes (if traveling outside CG), and airport parking fees are payable as actuals per genuine receipts.</p>
            </div>
            <div class="card" style="padding: 1.5rem; border-radius: var(--radius-md);">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">What payment methods do you accept?</h3>
                <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin: 0;">We accept UPI (Google Pay, PhonePe, Paytm), cash, bank transfer (IMPS/NEFT), and debit/credit cards. GST invoices are provided for business and corporate travelers.</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="{{ route('rates') }}" class="btn btn-outline" style="margin-right: 0.75rem;">View Rate Matrix</a>
            <a href="{{ route('booking') }}" class="btn btn-primary">Book a Taxi Online</a>
        </div>
    </div>
</section>
@endsection
