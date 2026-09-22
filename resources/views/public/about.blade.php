@extends('layouts.app')

@section('title', 'About Vaishnavi Tours - 24/7 Cab & Taxi Service Bilaspur')
@section('meta_description', 'About Vaishnavi Tours: reliable 24/7 taxi service, local and outstation travel across Chhattisgarh, professional drivers, and customer-focused service.')

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3.5rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Company Overview</span>
        <h1 style="color: #fff; font-size: 2.5rem; margin-top: 0.25rem;">About Vaishnavi Tours</h1>
        <p style="color: var(--slate-300); max-width: 650px; margin: 0.5rem auto 0;">Your reliable taxi and travel partner based in Bilaspur, Chhattisgarh.</p>
    </div>
</section>

<section style="padding: 4.5rem 0 5rem; background: var(--slate-50);">
    <div class="container" style="max-width: 1080px;">
        <div class="card" style="padding: 2.5rem; margin-bottom: 2.5rem;">
            <div class="grid grid-2 gap-4 align-center">
                <div>
                    <span style="color: var(--primary-hover); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Bilaspur Headquarters</span>
                    <h2 style="font-size: 2rem; margin-top: 0.35rem; margin-bottom: 1.25rem; color: var(--dark-900);">
                        Committed to Safe, Dependable Road Travel
                    </h2>
                    <p style="font-size: 1.05rem; color: var(--slate-700); line-height: 1.75; margin-bottom: 1rem;">
                        <strong>Vaishnavi Tours</strong> is a dedicated taxi, cab, and travel service based in Bilaspur, Chhattisgarh. We operate <strong>24/7 taxi/cab service</strong> catering to passengers requiring seamless <strong>local and outstation travel</strong>.
                    </p>
                    <p style="font-size: 0.95rem; color: var(--slate-600); line-height: 1.7; margin-bottom: 1.25rem;">
                        Whether you need a daily commute within the city, an airport transfer to Swami Vivekananda Airport in Raipur, an intercity business trip to Korba or Raigarh, or outstation family travel, we provide well-maintained vehicles and courteous service.
                    </p>
                    <div style="background: var(--slate-100); padding: 1rem 1.25rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary); font-size: 0.875rem; color: var(--dark-900);">
                        📍 <strong>Office Address:</strong><br>
                        {{ config('vaishnavi.address') }}
                    </div>
                </div>

                <div>
                    <div class="about-visual-wrap">
                        <img src="{{ asset('assets/images/about-travel.jpg') }}" alt="Professional chauffeur driven travel by Vaishnavi Tours" class="about-visual-img" style="height: 380px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Pillars -->
        <div class="grid grid-3 gap-3">
            <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
                <div class="why-icon">🕒</div>
                <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">24/7 Cab Service</h3>
                <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                    Active dispatch available all day and night for scheduled pickups, late night journeys, and emergency travel needs.
                </p>
            </div>

            <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
                <div class="why-icon">👨‍✈️</div>
                <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">Professional Drivers</h3>
                <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                    Verified, skilled chauffeurs who understand passenger safety, regional highway conditions, and courteous service.
                </p>
            </div>

            <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
                <div class="why-icon">🤝</div>
                <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">Customer-Focused Service</h3>
                <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                    Clean, air-conditioned cars with clear transparent billing and dependable on-time doorstep arrival.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
