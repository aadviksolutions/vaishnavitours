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
<div class="page-header" style="background: var(--navy-900); padding: 2.25rem 0; color: #FFFFFF; text-align: center; border-bottom: 1px solid var(--navy-800);">
    <div class="container">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(217, 119, 6, 0.12); color: var(--accent-gold); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.65rem; border: 1px solid rgba(217, 119, 6, 0.25);">
            <x-icon name="car-front" size="13" /> Professional Travel Solutions • Bilaspur (C.G.)
        </div>
        <h1 style="font-size: 1.65rem; font-weight: 600; line-height: 1.25; margin-bottom: 0.35rem; color: #ffffff;">
            Taxi &amp; Cab Services in Bilaspur
        </h1>
        <p style="font-size: 0.875rem; color: var(--slate-300); max-width: 620px; margin: 0 auto;">
            Reliable, clean, and punctual chauffeur-driven cabs for local commutes, highway transit, and airport transfers.
        </p>
    </div>
</div>

<!-- Services Grid Section -->
<section style="padding: 2.5rem 0; background: var(--slate-50);">
    <div class="container">
        <div class="grid grid-3 gap-3">
            <!-- 1. Local Taxi -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--slate-200); border-top: 3px solid var(--primary); border-radius: var(--radius-md); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div>
                    <div class="icon-box icon-box-sm icon-box-primary" style="margin-bottom: 0.85rem; width: 34px; height: 34px;">
                        <x-icon name="map-pin" size="16" />
                    </div>
                    <h2 style="font-size: 1.05rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.35rem;">Local Taxi Service</h2>
                    <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin-bottom: 0.85rem;">
                        Doorstep city pickup across Bilaspur for railway station transfers, hospital visits, business meetings, and shopping. Available on point-to-point or hourly rental packages.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1rem; font-size: 0.8rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.35rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Bilaspur Junction Station Pickup</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Apollo Hospital Direct Medical Trips</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> 4 Hr / 8 Hr Hourly Rental Packages</li>
                    </ul>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('services.local-taxi') }}" class="btn btn-outline btn-sm flex-1" style="justify-content: center; height: 32px;">Learn More</a>
                    <a href="{{ route('booking') }}?type=Local+Hourly" class="btn btn-primary btn-sm flex-1" style="justify-content: center; height: 32px;">Book Local</a>
                </div>
            </div>

            <!-- 2. Outstation Cabs -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--slate-200); border-top: 3px solid var(--navy-800); border-radius: var(--radius-md); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div>
                    <div class="icon-box icon-box-sm icon-box-primary" style="margin-bottom: 0.85rem; width: 34px; height: 34px;">
                        <x-icon name="route" size="16" />
                    </div>
                    <h2 style="font-size: 1.05rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.35rem;">Outstation Cabs</h2>
                    <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin-bottom: 0.85rem;">
                        Comfortable highway cabs from Bilaspur to all major cities including Raipur, Korba, Ambikapur, Raigarh, and Durg-Bhilai. Available for both one-way and round trips.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1rem; font-size: 0.8rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.35rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Route-based fares shown before booking</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Professional highway experienced drivers</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> No hidden fuel or return surge fees</li>
                    </ul>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('services.outstation-taxi') }}" class="btn btn-outline btn-sm flex-1" style="justify-content: center; height: 32px;">Learn More</a>
                    <a href="{{ route('booking') }}?type=Round-Trip" class="btn btn-primary btn-sm flex-1" style="justify-content: center; height: 32px;">Book Outstation</a>
                </div>
            </div>

            <!-- 3. Airport Transfer -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--slate-200); border-top: 3px solid var(--primary); border-radius: var(--radius-md); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div>
                    <div class="icon-box icon-box-sm icon-box-primary" style="margin-bottom: 0.85rem; width: 34px; height: 34px;">
                        <x-icon name="plane" size="16" />
                    </div>
                    <h2 style="font-size: 1.05rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.35rem;">Airport Transfers</h2>
                    <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin-bottom: 0.85rem;">
                        Guaranteed on-time airport pickup and drop between Bilaspur and Raipur Swami Vivekananda Airport (RPR) with flight schedule coordination.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1rem; font-size: 0.8rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.35rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Bilaspur to Raipur Airport (135 km)</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Chauffeur meets you at terminal gate</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Spacious boot space for flight luggage</li>
                    </ul>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('services.airport-transfer') }}" class="btn btn-outline btn-sm flex-1" style="justify-content: center; height: 32px;">Learn More</a>
                    <a href="{{ route('booking') }}?type=Airport+Transfer" class="btn btn-primary btn-sm flex-1" style="justify-content: center; height: 32px;">Book Airport</a>
                </div>
            </div>

            <!-- 4. One-Way Cabs -->
            <div class="card" id="oneway" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--slate-200); border-top: 3px solid var(--navy-900); border-radius: var(--radius-md); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div>
                    <div class="icon-box icon-box-sm icon-box-primary" style="margin-bottom: 0.85rem; width: 34px; height: 34px;">
                        <x-icon name="arrow-right" size="16" />
                    </div>
                    <h2 style="font-size: 1.05rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.35rem;">One-Way Drops</h2>
                    <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin-bottom: 0.85rem;">
                        Pay only for one-side travel when moving between Bilaspur and neighboring cities. No return fare obligation on supported high-frequency transit corridors.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1rem; font-size: 0.8rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.35rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Popular: Bilaspur to Raipur One-Way</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Popular: Bilaspur to Korba One-Way</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" class="text-primary" /> Transparent fixed single-journey fares</li>
                    </ul>
                </div>
                <div>
                    <a href="{{ route('booking') }}?type=One-Way" class="btn btn-primary btn-sm btn-block" style="justify-content: center; height: 32px;">Book One-Way</a>
                </div>
            </div>

            <!-- 5. Corporate & Group Rental -->
            <div class="card" id="corporate" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--slate-200); border-top: 3px solid #16A34A; border-radius: var(--radius-md); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div>
                    <div class="icon-box icon-box-sm" style="margin-bottom: 0.85rem; background: #ECFDF5; color: #16A34A; width: 34px; height: 34px;">
                        <x-icon name="briefcase" size="16" />
                    </div>
                    <h2 style="font-size: 1.05rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.35rem;">Corporate &amp; Fleet Hire</h2>
                    <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin-bottom: 0.85rem;">
                        Tailored cab rentals for corporate plants, business delegations, wedding events, and pilgrim group tours across Chhattisgarh in Innova Crysta and 14-seater Tempo Travellers.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1rem; font-size: 0.8rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.35rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" style="color: #16A34A;" /> GST Compliant Tax Invoicing</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" style="color: #16A34A;" /> Dedicated Chauffeur &amp; Vehicle Standby</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" style="color: #16A34A;" /> Group Coaches for 6 to 14 Passengers</li>
                    </ul>
                </div>
                <div>
                    <a href="{{ route('enquiry') }}" class="btn btn-outline btn-sm btn-block" style="justify-content: center; height: 32px;">Corporate Enquiry</a>
                </div>
            </div>

            <!-- 6. 24/7 Road Assistance -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--slate-200); border-top: 3px solid #DC2626; border-radius: var(--radius-md); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                <div>
                    <div class="icon-box icon-box-sm" style="margin-bottom: 0.85rem; background: #FEF2F2; color: #DC2626; width: 34px; height: 34px;">
                        <x-icon name="life-buoy" size="16" />
                    </div>
                    <h2 style="font-size: 1.05rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.35rem;">24/7 Emergency Dispatch</h2>
                    <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin-bottom: 0.85rem;">
                        Urgent patient drop, hospital transfers to Raipur, and rapid standby cab assistance when stranded on Bilaspur highway corridors.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1rem; font-size: 0.8rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.35rem;">
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" style="color: #DC2626;" /> Immediate phone response</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" style="color: #DC2626;" /> AIIMS Raipur / Medanta connectivity</li>
                        <li style="display: flex; align-items: center; gap: 6px;"><x-icon name="circle-check" size="13" style="color: #DC2626;" /> 24/7 night dispatch operations</li>
                    </ul>
                </div>
                <div>
                    <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="btn btn-danger btn-sm btn-block" style="justify-content: center; height: 32px;"><x-icon name="phone" size="13" style="margin-right: 4px;" /> Call Desk: {{ config('vaishnavi.phone_primary') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section style="padding: 2.5rem 0 3.5rem; background: #FFFFFF;">
    <div class="container" style="max-width: 820px;">
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <div style="display: inline-block; font-size: 0.75rem; font-weight: 600; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.25rem;">Frequently Asked Questions</div>
            <h2 style="font-size: 1.35rem; font-weight: 600; color: var(--dark-900);">Common Questions About Our Services</h2>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div class="card" style="padding: 1rem 1.25rem; border: 1px solid var(--slate-200); border-radius: var(--radius-md);">
                <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.25rem;">How early should I book my taxi in Bilaspur?</h3>
                <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin: 0;">For local trips within Bilaspur, we can often dispatch within 20–30 minutes subject to fleet availability. For airport transfers to Raipur or outstation trips, booking 4 to 12 hours in advance ensures vehicle allocation and on-time chauffeur arrival.</p>
            </div>
            <div class="card" style="padding: 1rem 1.25rem; border: 1px solid var(--slate-200); border-radius: var(--radius-md);">
                <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.25rem;">Are tolls, state taxes, and parking included in the fare?</h3>
                <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin: 0;">Our base rates include fuel, clean AC vehicle, and driver charges. Toll plaza charges, state border entry taxes (if traveling outside CG), and airport parking fees are payable as actuals per genuine receipts.</p>
            </div>
            <div class="card" style="padding: 1rem 1.25rem; border: 1px solid var(--slate-200); border-radius: var(--radius-md);">
                <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--dark-900); margin-bottom: 0.25rem;">What payment methods do you accept?</h3>
                <p style="color: var(--slate-600); font-size: 0.825rem; line-height: 1.5; margin: 0;">We accept UPI (Google Pay, PhonePe, Paytm), cash, bank transfer (IMPS/NEFT), and debit/credit cards. GST invoices are provided for business and corporate travelers.</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('rates') }}" class="btn btn-outline btn-sm" style="margin-right: 0.5rem;">View Rate Matrix</a>
            <a href="{{ route('booking') }}" class="btn btn-primary btn-sm">Book a Taxi Online</a>
        </div>
    </div>
</section>
@endsection
