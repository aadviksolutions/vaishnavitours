@extends('layouts.app')

@section('title', 'Vaishnavi Tours | 24/7 Taxi & Cab Service Across Chhattisgarh')
@section('meta_description', 'Vaishnavi Tours provides reliable 24/7 taxi and cab services across Chhattisgarh, including Bilaspur, Raipur, Raigarh, Korba, Ambikapur and nearby destinations.')
@section('canonical', route('home'))

@push('styles')
<link rel="preload" as="image" href="{{ asset('assets/hero-loop/ezgif-frame-001.jpg') }}" fetchpriority="high">
<style>
.booking-support-banner {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.btn-call-support:hover {
    background: #D97706 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.45) !important;
}
.booking-support-banner a.support-phone-link:hover span:last-child {
    text-decoration: underline;
    color: #FBBF24 !important;
}
@media (max-width: 640px) {
    .booking-support-banner {
        padding: 1rem !important;
    }
    .booking-support-banner .support-banner-actions {
        width: 100% !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.65rem !important;
        margin-top: 0.35rem;
    }
    .booking-support-banner .support-phone-link {
        align-items: center !important;
        text-align: center !important;
    }
    .booking-support-banner .btn-call-support {
        width: 100% !important;
        min-height: 44px !important;
        font-size: 1rem !important;
    }
}
</style>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'What taxi and cab services does Vaishnavi Tours provide across Chhattisgarh?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Vaishnavi Tours provides 24/7 local, airport and outstation taxi services across Chhattisgarh, including Bilaspur, Raipur, Raigarh, Korba, Ambikapur, Bhilai and other service locations.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'How can I book an outstation cab from Bilaspur to Raipur or Korba?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'You can book directly through our online reservation form, call our 24/7 dispatch desk at 9244784443 / 9179484443, or chat with us on WhatsApp.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'How can I check current taxi fares?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Use the route and vehicle filters on our Rates page to see the current configured fare and applicable inclusions.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Do you provide airport transfer from Bilaspur to Swami Vivekananda Airport Raipur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Yes, we provide dedicated airport transfers with punctual doorstep pickup in Bilaspur and terminal drop at Raipur Airport (~135 km via NH 130) with flight schedule coordination.'
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
@php
    $supportPhone = \App\Models\Setting::get('phone_primary', config('vaishnavi.phone_primary', '9244784443'));
    $cleanPhone = preg_replace('/[^0-9]/', '', $supportPhone);
    $supportTel = (strlen($cleanPhone) === 10) ? '+91' . $cleanPhone : (str_starts_with($cleanPhone, '91') ? '+' . $cleanPhone : '+91' . $cleanPhone);
@endphp

    <!-- 1. Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="grid grid-2 gap-4 align-center">
                <!-- Hero Left Content -->
                <div>
                    <div class="hero-badge">
                        <x-icon name="car-front" size="13" style="margin-right: 3px; color: #38BDF8;" /> 24/7 Chhattisgarh Taxi Service
                    </div>
                    <h1 class="hero-title">
                        Reliable 24/7 Taxi &amp; Cab <span>Across Chhattisgarh</span>
                    </h1>
                    <p class="hero-desc">
                        Professional chauffeur service for local, airport and outstation travel across Bilaspur, Raipur and all major hubs.
                    </p>

                    <div class="d-flex gap-2 flex-wrap align-center">
                        <a href="#booking-card-section" class="btn btn-primary btn-lg">
                            <x-icon name="calendar-check" size="15" /> Book Your Taxi
                        </a>
                        <a href="#vehicles-fleet" class="btn btn-dark btn-lg" style="border: 1px solid rgba(255,255,255,0.15);">
                            <x-icon name="car" size="15" /> View Fleet
                        </a>
                    </div>
                </div>

                <!-- Hero Right Media - Continuous Car Sequence Loop -->
                <div>
                    <div class="hero-visual-wrap"
                         id="hero-car-sequence"
                         data-total-frames="240"
                         data-fps="26"
                         data-manifest-url="{{ asset('assets/hero-loop/manifest.json') }}"
                         data-frame-pattern="{{ asset('assets/hero-loop/ezgif-frame-%03d.jpg') }}"
                         data-initial-frame="{{ asset('assets/hero-loop/ezgif-frame-001.jpg') }}"
                         data-legacy-image="{{ asset('assets/images/hero-taxi.jpg') }}">
                        <img id="hero-car-fallback"
                             src="{{ asset('assets/hero-loop/ezgif-frame-001.jpg') }}"
                             data-original="{{ asset('assets/images/hero-taxi.jpg') }}"
                             alt="Vaishnavi Tours Taxi Service"
                             class="hero-visual-img"
                             fetchpriority="high"
                             width="1920"
                             height="1080">
                        <canvas id="hero-car-canvas"
                                class="hero-visual-canvas"
                                width="1920"
                                height="1080"
                                aria-hidden="true"></canvas>
                        <noscript>
                            <img src="{{ asset('assets/images/hero-taxi.jpg') }}" alt="Vaishnavi Tours Taxi Service" class="hero-visual-img">
                        </noscript>
                        <div class="hero-visual-badge">
                            <div style="font-weight: 600; font-size: 13px;">Vaishnavi Tours Bilaspur</div>
                            <div style="font-size: 11px; color: var(--slate-300);">Doorstep pickup • Outstation &amp; Local</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Booking Form Section -->
    <section style="padding: 2rem 0; background: var(--slate-50);" id="booking-card-section">
        <div class="container" style="max-width: 960px;">
            <div class="booking-hero-card" style="margin-top: -2.75rem; position: relative; z-index: 10;">
                <div class="d-flex justify-between align-center flex-wrap gap-2" style="margin-bottom: 0.875rem;">
                    <div>
                        <h2 style="font-size: 16px; font-weight: 600; color: var(--dark-900); margin: 0;">Instant Taxi &amp; Travel Booking</h2>
                        <p style="font-size: 12px; color: var(--slate-500); margin-top: 1px;">Doorstep chauffeur dispatch &amp; transparent rates across Chhattisgarh.</p>
                    </div>
                    <div class="d-flex align-center gap-2">
                        <span class="badge badge-available" style="padding: 0.2rem 0.5rem; font-size: 11px;">
                            <x-icon name="zap" size="12" style="margin-right: 3px;" /> 24/7 Dispatch Desk
                        </span>
                    </div>
                </div>

                <!-- =========================================================================
                     VAISHNAVI TOURS — PROMINENT BOOKING SUPPORT BANNER
                     ========================================================================= -->
                <div class="booking-support-banner" style="background: linear-gradient(135deg, #0F2744 0%, #16365C 100%); border: 1.5px solid #C59B27; border-radius: 10px; padding: 1.15rem 1.35rem; margin-bottom: 1.25rem; box-shadow: 0 4px 16px rgba(15, 39, 68, 0.15); color: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.85rem; flex: 1; min-width: 260px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(245, 158, 11, 0.15); border: 1.5px solid #F59E0B; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; color: #F59E0B;">
                                📞
                            </div>
                            <div>
                                <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 0.25rem 0; color: #FFFFFF; line-height: 1.25;">
                                    Need Help with Your Booking?
                                </h2>
                                <p style="color: #CBD5E1; font-size: 0.825rem; line-height: 1.45; margin: 0; max-width: 520px;">
                                    If you face any problem while booking, have questions about your trip, or need assistance, please call our support team.
                                </p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;" class="support-banner-actions">
                            <a href="tel:{{ $supportTel }}" style="text-decoration: none; display: flex; flex-direction: column; align-items: flex-end;" class="support-phone-link" title="Call {{ $supportPhone }}">
                                <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; color: #94A3B8; font-weight: 600;">24/7 Helpline</span>
                                <span style="font-size: 1.35rem; font-weight: 800; color: #F59E0B; font-family: 'Plus Jakarta Sans', var(--font-sans); letter-spacing: 0.5px; line-height: 1.1;">
                                    {{ $supportPhone }}
                                </span>
                            </a>
                            <a href="tel:{{ $supportTel }}" class="btn-call-support" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: #F59E0B; color: #0F172A; font-weight: 700; font-size: 0.95rem; padding: 0.6rem 1.4rem; border-radius: 6px; text-decoration: none; border: none; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3); transition: all 0.2s ease; min-height: 42px; white-space: nowrap;" title="Call Support Now">
                                <span>📞</span>
                                <span>Call Now</span>
                            </a>
                        </div>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 1rem; padding: 0.6rem 0.85rem; font-size: 12.5px;">
                        <div style="font-weight: 600; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
                            <x-icon name="alert-circle" size="14" /> Please check the form errors:
                        </div>
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- 1. Booking Type Segmented Selector -->
                <div class="booking-segmented-bar" id="bookingTypeSegmented" role="tablist" aria-label="Booking Types">
                    <button type="button" class="booking-segmented-btn active" data-tab="oneway" data-trip="One-Way" data-category="outstation" role="tab" aria-selected="true">
                        <x-icon name="arrow-right" size="13" /> One Way
                    </button>
                    <button type="button" class="booking-segmented-btn" data-tab="roundtrip" data-trip="Round-Trip" data-category="round_trip" role="tab" aria-selected="false">
                        <x-icon name="repeat" size="13" /> Round Trip
                    </button>
                    <button type="button" class="booking-segmented-btn" data-tab="airport" data-trip="Airport Transfer" data-category="airport" role="tab" aria-selected="false">
                        <x-icon name="plane" size="13" /> Airport Transfer
                    </button>
                    <button type="button" class="booking-segmented-btn" data-tab="local" data-trip="Local Hourly" data-category="local_8h_80km" role="tab" aria-selected="false">
                        <x-icon name="clock" size="13" /> Local Rental
                    </button>
                    <button type="button" class="booking-segmented-btn" data-tab="tour" data-trip="Tour Package" data-category="tour_package" role="tab" aria-selected="false">
                        <x-icon name="map-pin" size="13" /> Tour Package
                    </button>
                    <button type="button" class="booking-segmented-btn" data-tab="ambulance" data-trip="Ambulance Service" data-category="emergency" role="tab" aria-selected="false">
                        <x-icon name="heart" size="13" /> Ambulance (24/7)
                    </button>
                </div>

                <form action="{{ route('booking.store') }}" method="POST" id="homeBookingForm">
                    @csrf

                    <!-- Core Hidden State Inputs -->
                    <input type="hidden" name="trip_type" id="formTripType" value="{{ old('trip_type', 'One-Way') }}">
                    <input type="hidden" name="rate_category" id="formRateCategory" value="{{ old('rate_category', 'outstation') }}">
                    <input type="hidden" name="pickup_location" id="pickupLocationInput" value="{{ old('pickup_location', 'Bilaspur') }}" required>
                    <input type="hidden" name="destination" id="destinationLocationInput" value="{{ old('destination', 'Raipur Airport') }}" required>
                    <input type="hidden" name="is_custom_query" id="isCustomQueryInput" value="{{ old('is_custom_query', '0') }}">
                    <input type="hidden" name="terms_accepted" value="1">

                    <!-- Google Maps Coordinate Fields -->
                    <input type="hidden" name="pickup_latitude" id="homePickupLat" value="{{ old('pickup_latitude') }}">
                    <input type="hidden" name="pickup_longitude" id="homePickupLng" value="{{ old('pickup_longitude') }}">
                    <input type="hidden" name="pickup_place_id" id="homePickupPlaceId" value="{{ old('pickup_place_id') }}">
                    <input type="hidden" name="destination_latitude" id="homeDestinationLat" value="{{ old('destination_latitude') }}">
                    <input type="hidden" name="destination_longitude" id="homeDestinationLng" value="{{ old('destination_longitude') }}">
                    <input type="hidden" name="destination_place_id" id="homeDestinationPlaceId" value="{{ old('destination_place_id') }}">
                    <input type="hidden" name="route_distance_km" id="homeRouteDistanceKm" value="{{ old('route_distance_km') }}">
                    <input type="hidden" name="route_duration_text" id="homeRouteDurationText" value="{{ old('route_duration_text') }}">
                    <input type="hidden" name="route_duration_minutes" id="homeRouteDurationMinutes" value="{{ old('route_duration_minutes') }}">

                    <!-- Airport Transfer Sub-Toggle (Airport Drop vs Airport Pickup) -->
                    <div id="airportDirectionWrap" style="display: none; margin-bottom: 0.85rem; background: #EFF6FF; border: 1.5px solid #BFDBFE; border-radius: 8px; padding: 0.65rem 0.85rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.45rem;">
                            <span style="font-size: 12px; font-weight: 700; color: #1E40AF; display: flex; align-items: center; gap: 5px;">
                                <x-icon name="plane" size="14" /> Airport Transfer Direction
                            </span>
                            <span style="font-size: 11px; background: #DBEAFE; color: #1E40AF; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                                Swami Vivekananda Airport, Raipur (RPR) Only
                            </span>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <button type="button" class="btn btn-sm btn-primary" id="btnAirportDrop" style="height: 30px; font-size: 11.5px; font-weight: 700; padding: 0 12px;">
                                <x-icon name="plane-takeoff" size="13" /> Airport Drop (City &rarr; Raipur Airport)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline" id="btnAirportPickup" style="height: 30px; font-size: 11.5px; font-weight: 700; padding: 0 12px; background: #fff;">
                                <x-icon name="plane-landing" size="13" /> Airport Pickup (Raipur Airport &rarr; City)
                            </button>
                        </div>
                    </div>

                    <!-- Local Rental Package Sub-Toggle (4h/40km vs 8h/80km) -->
                    <div id="localPackageWrap" style="display: none; margin-bottom: 0.85rem;">
                        <div style="display: flex; gap: 8px; align-items: center; background: var(--slate-100); padding: 4px; border-radius: var(--radius-sm); border: 1px solid var(--slate-200); width: fit-content;">
                            <button type="button" class="btn btn-sm btn-primary" id="btnLocal8h" data-category="local_8h_80km" style="height: 28px; font-size: 11.5px; padding: 0 10px;">
                                <x-icon name="clock" size="13" /> 8 Hours / 80 KM (Full Day)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline" id="btnLocal4h" data-category="local_4h_40km" style="height: 28px; font-size: 11.5px; padding: 0 10px;">
                                <x-icon name="clock-3" size="13" /> 4 Hours / 40 KM (Half Day)
                            </button>
                        </div>
                    </div>

                    <!-- 2. ROUTE & LOCATIONS SECTION -->
                    <div id="standardRouteGrid" style="display: block; margin-bottom: 0.85rem;">
                        <div class="grid grid-2 gap-2 align-end" style="grid-template-columns: 1fr auto 1fr;">
                            <!-- Pickup Location Searchable Dropdown -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="d-flex justify-between align-center mb-1">
                                    <label class="form-label" id="pickupLabelText" style="margin: 0;">Pickup Location</label>
                                    <button type="button" class="btn btn-xs btn-outline btn-home-select-map" data-target="pickup" style="font-size: 10px; padding: 1px 6px; border-radius: 4px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF;">🗺️ Map</button>
                                </div>
                                <div class="searchable-select-wrap" id="pickupSelectWrap">
                                    <button type="button" class="searchable-select-trigger" id="pickupSelectBtn" aria-haspopup="listbox" aria-expanded="false">
                                        <span class="searchable-select-text" id="pickupSelectDisplay">{{ old('pickup_location', 'Bilaspur') }}</span>
                                        <x-icon name="chevron-down" size="14" class="searchable-select-caret" />
                                    </button>
                                    <div class="searchable-select-dropdown" id="pickupSelectMenu" style="display: none;">
                                        <div class="searchable-select-search-box">
                                             <x-icon name="search" size="13" style="color: var(--slate-400); flex-shrink: 0;" />
                                             <input type="text" class="searchable-select-search-input" id="pickupSearchField" placeholder="Search city or location..." autocomplete="off">
                                        </div>
                                        <ul class="searchable-select-list" id="pickupSelectList" role="listbox">
                                            <li class="searchable-select-item is-map-item" data-value="__select_on_map__" role="option" tabindex="0" style="background: #EFF6FF; border-bottom: 1.5px solid #BFDBFE; color: #1D4ED8; font-weight: 700; padding: 0.55rem 0.75rem;">
                                                <span style="display: flex; align-items: center; gap: 4px;">🗺️ Select on Map (Pick location pin & address)</span>
                                                <span style="font-size: 10px; color: #1D4ED8; font-weight: 700; background: #DBEAFE; padding: 1px 5px; border-radius: 3px;">Map Pin</span>
                                            </li>
                                            @foreach($locations as $loc)
                                                <li class="searchable-select-item {{ old('pickup_location', 'Bilaspur') === $loc ? 'is-selected' : '' }}" data-value="{{ $loc }}" role="option" tabindex="0">
                                                    <span>{{ $loc }}</span>
                                                    @if(in_array($loc, ['Bilaspur', 'Raipur', 'Raigarh', 'Korba', 'Ambikapur']))
                                                        <span style="font-size: 10px; color: var(--slate-400); text-transform: uppercase;">Hub</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                            <li class="searchable-select-item is-other-item" data-value="Other" role="option" tabindex="0" style="border-top: 1px dashed var(--slate-300); color: var(--primary); font-weight: 700; background: #F8FAFC;">
                                                <span>➕ Other Location (Specify below)</span>
                                                <span style="font-size: 10px; color: var(--accent); font-weight: 700; background: #EFF6FF; padding: 2px 5px; border-radius: 3px;">Custom</span>
                                            </li>
                                        </ul>
                                        <div class="searchable-select-empty" id="pickupSelectEmpty" style="display: none;">
                                            <div>No matching location in quick list.</div>
                                            <button type="button" id="btnPickupCustomFallback" style="margin-top: 4px; background: none; border: none; color: var(--primary); font-weight: 700; text-decoration: underline; font-size: 11px; cursor: pointer; padding: 0;">
                                                ➕ Specify custom location
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="pickupOtherWrap" style="display: {{ old('pickup_other') || old('pickup_location') === 'Other' ? 'block' : 'none' }}; margin-top: 0.4rem;">
                                    <input type="text" name="pickup_other" id="pickupOtherInput" class="form-control form-control-sm" placeholder="Enter your custom pickup city / area / address..." value="{{ old('pickup_other') }}" style="border-color: #3B82F6; background: #F0F9FF; font-size: 12px;">
                                    <div style="font-size: 10px; color: #1E40AF; margin-top: 2px; font-weight: 500;">
                                        * Custom location: No immediate price charged. Admin will quote customized rate.
                                    </div>
                                </div>
                                @error('pickup_location') <div class="form-error">{{ $message }}</div> @enderror
                            </div>

                            <!-- Swap Locations Button -->
                            <div style="padding-bottom: 2px;">
                                <button type="button" class="btn-swap-locations" id="swapLocationsBtn" title="Swap pickup and destination" aria-label="Swap pickup and destination">
                                    <x-icon name="arrow-left-right" size="13" />
                                </button>
                            </div>

                            <!-- Destination Searchable Dropdown -->
                            <div class="form-group" style="margin-bottom: 0;" id="destinationFormGroup">
                                <div class="d-flex justify-between align-center mb-1">
                                    <label class="form-label" id="destinationLabelText" style="margin: 0;">Destination</label>
                                    <button type="button" class="btn btn-xs btn-outline btn-home-select-map" data-target="destination" style="font-size: 10px; padding: 1px 6px; border-radius: 4px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF;">🗺️ Map</button>
                                </div>
                                <div class="searchable-select-wrap" id="destinationSelectWrap">
                                    <button type="button" class="searchable-select-trigger" id="destinationSelectBtn" aria-haspopup="listbox" aria-expanded="false">
                                        <span class="searchable-select-text" id="destinationSelectDisplay">{{ old('destination', 'Raipur Airport') }}</span>
                                        <x-icon name="chevron-down" size="14" class="searchable-select-caret" />
                                    </button>
                                    <div class="searchable-select-dropdown" id="destinationSelectMenu" style="display: none;">
                                        <div class="searchable-select-search-box">
                                            <x-icon name="search" size="13" style="color: var(--slate-400); flex-shrink: 0;" />
                                            <input type="text" class="searchable-select-search-input" id="destinationSearchField" placeholder="Search destination..." autocomplete="off">
                                        </div>
                                        <ul class="searchable-select-list" id="destinationSelectList" role="listbox">
                                            <li class="searchable-select-item is-map-item" data-value="__select_on_map__" role="option" tabindex="0" style="background: #EFF6FF; border-bottom: 1.5px solid #BFDBFE; color: #1D4ED8; font-weight: 700; padding: 0.55rem 0.75rem;">
                                                <span style="display: flex; align-items: center; gap: 4px;">🗺️ Select on Map (Pick location pin & address)</span>
                                                <span style="font-size: 10px; color: #1D4ED8; font-weight: 700; background: #DBEAFE; padding: 1px 5px; border-radius: 3px;">Map Pin</span>
                                            </li>
                                            @foreach($locations as $loc)
                                                <li class="searchable-select-item {{ old('destination', 'Raipur Airport') === $loc ? 'is-selected' : '' }}" data-value="{{ $loc }}" role="option" tabindex="0">
                                                    <span>{{ $loc }}</span>
                                                    @if(str_contains($loc, 'Airport'))
                                                        <span style="font-size: 10px; color: var(--accent); font-weight: 600;">Airport</span>
                                                    @elseif(in_array($loc, ['Bilaspur', 'Raipur', 'Raigarh', 'Korba', 'Ambikapur']))
                                                        <span style="font-size: 10px; color: var(--slate-400); text-transform: uppercase;">Hub</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                            <li class="searchable-select-item is-other-item" data-value="Other" role="option" tabindex="0" style="border-top: 1px dashed var(--slate-300); color: var(--primary); font-weight: 700; background: #F8FAFC;">
                                                <span>➕ Other Location (Specify below)</span>
                                                <span style="font-size: 10px; color: var(--accent); font-weight: 700; background: #EFF6FF; padding: 2px 5px; border-radius: 3px;">Custom</span>
                                            </li>
                                        </ul>
                                        <div class="searchable-select-empty" id="destinationSelectEmpty" style="display: none;">
                                            <div>No matching destination in quick list.</div>
                                            <button type="button" id="btnDestinationCustomFallback" style="margin-top: 4px; background: none; border: none; color: var(--primary); font-weight: 700; text-decoration: underline; font-size: 11px; cursor: pointer; padding: 0;">
                                                ➕ Specify custom destination
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="destinationOtherWrap" style="display: {{ old('destination_other') || old('destination') === 'Other' ? 'block' : 'none' }}; margin-top: 0.4rem;">
                                    <input type="text" name="destination_other" id="destinationOtherInput" class="form-control form-control-sm" placeholder="Enter your custom drop city / area / address..." value="{{ old('destination_other') }}" style="border-color: #3B82F6; background: #F0F9FF; font-size: 12px;">
                                    <div style="font-size: 10px; color: #1E40AF; margin-top: 2px; font-weight: 500;">
                                        * Custom location: No immediate price charged. Admin will quote customized rate.
                                    </div>
                                </div>
                                @error('destination') <div class="form-error">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Same Location Warning Notification -->
                        <div id="sameLocationWarning" style="display: none; margin-top: 0.35rem; color: var(--danger); font-size: 11.5px; font-weight: 500;">
                            <x-icon name="alert-triangle" size="13" style="margin-right: 3px;" /> Pickup and destination must differ for this trip.
                        </div>
                    </div>

                    <!-- Home Interactive Route Map & Road Distance Preview Container -->
                    <div id="homeRouteMapContainer" style="display: none; margin-bottom: 0.85rem; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; background: #ffffff;">
                        <div style="padding: 0.5rem 0.85rem; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="font-size: 15px;">🛣️</span>
                                <div>
                                    <strong style="font-size: 12px; color: #0F172A;">Road Route & Distance</strong>
                                    <span style="font-size: 10.5px; color: #64748B; margin-left: 4px;">Genuine highway driving path</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; padding: 2px 8px; border-radius: 4px; font-size: 12px; color: #1D4ED8; font-weight: 700;">
                                    <span id="homeRouteDistanceDisplay">-- KM</span>
                                </div>
                                <div style="background: #F1F5F9; border: 1px solid #E2E8F0; padding: 2px 8px; border-radius: 4px; font-size: 11px; color: #334155; font-weight: 600;">
                                    <span id="homeRouteDurationDisplay">--</span>
                                </div>
                            </div>
                        </div>

                        <div style="position: relative; height: 210px; width: 100%; background: #E2E8F0;">
                            <div id="homeRouteMapCanvas" style="width: 100%; height: 100%;"></div>
                            <div id="homeRouteCalcSpinner" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.75); z-index: 500; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #2563EB;">
                                <span>🔄 Calculating road route & distance...</span>
                            </div>
                        </div>

                        <div id="homeRouteCalcErrorBanner" style="display: none; padding: 5px 10px; background: #FEF2F2; border-top: 1px solid #FECACA; color: #991B1B; font-size: 11px; align-items: center; justify-content: space-between;">
                            <span id="homeRouteCalcErrorMsg">Could not calculate route between selected locations.</span>
                            <button type="button" id="btnHomeRetryRouteCalc" class="btn btn-xs btn-outline" style="border-color: #F87171; color: #991B1B; padding: 1px 6px; font-size: 10px;">Retry</button>
                        </div>
                    </div>

                    <!-- Tour Package Specific Fields -->
                    <div id="tourPackageFields" style="display: none; margin-bottom: 0.85rem;">
                        <div class="grid grid-3 gap-2">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Select Sightseeing Package</label>
                                <select class="form-select" id="tourPackageSelect" name="package_name">
                                    <option value="Mainpat Hill Station Tour (Shimla of CG)">Mainpat Hill Station Tour ("Shimla of CG")</option>
                                    <option value="Amarkantak Temple & Narmada Origin Tour">Amarkantak Temple &amp; Narmada Origin Tour</option>
                                    <option value="Bhoramdeo Temple & Kawardha Palace">Bhoramdeo Temple &amp; Kawardha Palace</option>
                                    <option value="Sirpur Heritage & Buddhist Circuit">Sirpur Heritage &amp; Buddhist Circuit</option>
                                    <option value="Chitrakote & Bastar Waterfalls Tour">Chitrakote &amp; Bastar Waterfalls Tour</option>
                                    <option value="Ratanpur Mahamaya Devi & Pali Temple">Ratanpur Mahamaya Devi &amp; Pali Temple</option>
                                    <option value="Custom Chhattisgarh Multi-Day Tour">Custom Chhattisgarh Multi-Day Tour</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Tour Duration</label>
                                <select class="form-select" name="package_duration" id="tourDurationSelect">
                                    <option value="1 Day (Same Day Return)">1 Day (Same Day Return)</option>
                                    <option value="2 Days / 1 Night">2 Days / 1 Night</option>
                                    <option value="3 Days / 2 Nights">3 Days / 2 Nights</option>
                                    <option value="4+ Days Extended Excursion">4+ Days Extended Excursion</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Starting City</label>
                                <select class="form-select" id="tourStartingCitySelect">
                                    <option value="Bilaspur">Bilaspur</option>
                                    <option value="Raipur">Raipur</option>
                                    <option value="Raigarh">Raigarh</option>
                                    <option value="Korba">Korba</option>
                                    <option value="Ambikapur">Ambikapur</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Ambulance Specific Fields -->
                    <div id="ambulanceFields" style="display: none; margin-bottom: 0.85rem;">
                        <div class="grid grid-3 gap-2">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Ambulance Service Category</label>
                                <select class="form-select" name="service_category" id="ambulanceCategorySelect">
                                    <option value="Basic Life Support (BLS) Ambulance">Basic Life Support (BLS) Ambulance</option>
                                    <option value="Patient Transport Vehicle (Eco/Van)">Patient Transport Vehicle (Eco / Van)</option>
                                    <option value="Advanced Cardiac Life Support (ACLS ICU)">Advanced Cardiac Life Support (ACLS ICU)</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Pickup Hospital / Residence</label>
                                <input type="text" class="form-control" id="ambulancePickupInput" placeholder="Hospital / Residence address in CG" value="Bilaspur">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Destination Hospital / City</label>
                                <input type="text" class="form-control" id="ambulanceDestinationInput" placeholder="e.g. AIIMS Raipur / Apollo Bilaspur" value="Apollo Hospital, Bilaspur">
                            </div>
                        </div>
                        <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); border-radius: var(--radius-xs); padding: 0.4rem 0.65rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 4px;">
                            <span style="font-size: 11.5px; color: var(--danger-text); font-weight: 500;">
                                <x-icon name="phone-call" size="12" style="margin-right: 3px;" /> For instant emergency dispatch: Call <strong>{{ config('vaishnavi.phone_primary') }}</strong> directly.
                            </span>
                            <span style="font-size: 11px; color: var(--danger-text); font-weight: 600;">24/7 Priority Emergency Support</span>
                        </div>
                    </div>

                    <!-- 3. DATE, TIME, PASSENGERS & FLIGHT FIELDS -->
                    <div class="grid grid-4 gap-2" id="travelTimeGrid" style="margin-bottom: 0.85rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" id="travelDateLabel">Travel Date</label>
                            <input type="date" name="travel_date" id="travelDateInput" class="form-control" value="{{ old('travel_date', now('Asia/Kolkata')->format('Y-m-d')) }}" min="{{ now('Asia/Kolkata')->format('Y-m-d') }}" required>
                            @error('travel_date') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Pickup Time</label>
                            <input type="time" name="travel_time" id="travelTimeInput" class="form-control" value="{{ old('travel_time', now('Asia/Kolkata')->format('H:i')) }}" required>
                            @error('travel_time') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <!-- Return Date (for Round Trip) -->
                        <div class="form-group" id="returnDateGroup" style="display: none; margin-bottom: 0;">
                            <label class="form-label">Return Date</label>
                            <input type="date" name="return_date" id="returnDateInput" class="form-control" value="{{ old('return_date') }}" min="{{ now('Asia/Kolkata')->format('Y-m-d') }}" disabled>
                            @error('return_date') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <!-- Return Time (for Round Trip) -->
                        <div class="form-group" id="returnTimeGroup" style="display: none; margin-bottom: 0;">
                            <label class="form-label">Return Time</label>
                            <input type="time" name="return_time" id="returnTimeInput" class="form-control" value="{{ old('return_time', '18:00') }}" disabled>
                            @error('return_time') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <!-- Flight Number (for Airport Transfer) -->
                        <div class="form-group" id="flightNumberGroup" style="display: none; margin-bottom: 0;">
                            <label class="form-label">Flight Number (Optional)</label>
                            <input type="text" name="flight_number" id="flightNumberInput" class="form-control" placeholder="e.g. 6E-204" value="{{ old('flight_number') }}">
                        </div>

                        <!-- Passenger Count -->
                        <div class="form-group" id="passengerCountGroup" style="margin-bottom: 0;">
                            <label class="form-label">Travellers</label>
                            <select name="passenger_count" id="passengerCountSelect" class="form-select">
                                <option value="1">1 Passenger</option>
                                <option value="2">2 Passengers</option>
                                <option value="3">3 Passengers</option>
                                <option value="4" selected>4 Passengers</option>
                                <option value="5">5 Passengers</option>
                                <option value="6">6 Passengers</option>
                                <option value="7">7+ Group</option>
                            </select>
                        </div>
                    </div>

                    <!-- Round-Trip Distance input (optional) -->
                    <div id="estimatedKmGroup" style="display: none; margin-bottom: 0.85rem;">
                        <div class="grid grid-2 gap-2 align-center">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Estimated Round-Trip Distance (KM)</label>
                                <input type="number" name="estimated_km" id="estimatedKmInput" class="form-control" min="1" step="1" placeholder="e.g. 270 KM" value="{{ old('estimated_km', '270') }}" disabled>
                            </div>
                            <div style="font-size: 11.5px; color: var(--slate-500); line-height: 1.4; padding-top: 14px;">
                                Fares calculated as fixed daily vehicle rent + actual KM charge.
                            </div>
                        </div>
                    </div>

                    <!-- 4. VEHICLE CATEGORY & FLEET SELECTION (DROPDOWN) -->
                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label class="form-label" for="homeVehicleCategorySelect" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <span>Vehicle Category Preference *</span>
                            <span style="font-size: 11px; font-weight: normal; color: var(--slate-500);">Fleet verified AC commercial cabs</span>
                        </label>
                        <div style="position: relative;">
                            <select name="vehicle_category" id="homeVehicleCategorySelect" class="form-select" required style="width: 100%; height: 42px; font-weight: 600; font-size: 13.5px; border-radius: 6px; border: 1.5px solid var(--slate-300); background-color: #ffffff; cursor: pointer; padding: 0 0.75rem;">
                                <option value="">-- Select Vehicle Category --</option>
                                @if(isset($activeVehicleCategories) && $activeVehicleCategories->isNotEmpty())
                                    @foreach($activeVehicleCategories as $cat)
                                        @php
                                            $modelsText = $cat->activeModels->isNotEmpty() ? $cat->activeModels->pluck('name')->implode(', ') : '';
                                            $capacityText = $cat->is_capacity_configurable ? $cat->min_seating_capacity.'-'.$cat->max_seating_capacity.' Seats' : $cat->default_seating_capacity.' Seats';
                                            $fullLabel = $cat->name . ' (' . ($modelsText ? $modelsText . ' • ' : '') . $capacityText . ')';
                                        @endphp
                                        <option value="{{ $cat->name }}" 
                                                data-category-id="{{ $cat->id }}" 
                                                data-category-name="{{ $cat->name }}"
                                                data-models="{{ json_encode($cat->activeModels) }}"
                                                {{ old('vehicle_category', $loop->first ? $cat->name : '') === $cat->name ? 'selected' : '' }}>
                                            {{ $fullLabel }}
                                        </option>
                                    @endforeach
                                @else
                                    @foreach($vehicleCategories as $vCat)
                                        <option value="{{ $vCat }}" {{ old('vehicle_category', $loop->first ? $vCat : '') === $vCat ? 'selected' : '' }}>
                                            {{ $vCat }} (AC Commercial Cab)
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Optional Vehicle Model preference pills -->
                        <div id="homeModelSelectionBox" style="display: none; margin-top: 0.65rem; background: #F8FAFC; border: 1px solid var(--slate-200); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                <label style="font-size: 11.5px; font-weight: 700; color: var(--dark-900); margin: 0;">
                                    🚗 Preferred Vehicle Model (Optional)
                                </label>
                                <span style="font-size: 10.5px; color: var(--slate-500);">Choose specific car or leave as any</span>
                            </div>
                            <div id="homeModelChipsWrap" style="display: flex; gap: 6px; flex-wrap: wrap;"></div>
                            <input type="hidden" name="vehicle_category_id" id="homeVehicleCategoryId" value="{{ old('vehicle_category_id') }}">
                            <input type="hidden" name="vehicle_model_id" id="homeVehicleModelId" value="{{ old('vehicle_model_id') }}">
                            <input type="hidden" name="vehicle_model" id="homeVehicleModel" value="{{ old('vehicle_model') }}">
                        </div>
                    </div>

                    <!-- Preserved Custom Vehicle Trigger Dropdown (Required by test_mobile_fixes.php) -->
                    @php
                        $selectedVeh = $vehicles->firstWhere('id', old('vehicle_id'));
                        $initialDisplay = $selectedVeh ? $selectedVeh->name : 'Best Available Vehicle';
                    @endphp
                    <div class="form-group custom-select-group" id="vehicle-select-group" style="display: none;">
                        <label class="form-label" id="vehicle-select-label">Preferred Fleet Vehicle</label>
                        <input type="hidden" name="vehicle_id" id="selected-vehicle-id" value="{{ old('vehicle_id', '') }}">
                        <div class="custom-select-wrapper">
                            <button type="button" class="custom-select-trigger" id="vehicle-select-btn" aria-haspopup="listbox" aria-expanded="false" aria-controls="vehicle-select-menu" aria-labelledby="vehicle-select-label vehicle-select-display">
                                <span class="custom-select-trigger-text" id="vehicle-select-display">{{ $initialDisplay }}</span>
                                <svg class="custom-select-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <ul class="custom-select-menu" id="vehicle-select-menu" role="listbox" aria-labelledby="vehicle-select-label" tabindex="-1">
                                <li class="custom-select-option {{ old('vehicle_id') ? '' : 'selected' }}" role="option" data-value="" data-name="Best Available Vehicle" aria-selected="{{ old('vehicle_id') ? 'false' : 'true' }}" tabindex="0">
                                    <div class="option-content"><div class="option-name">Best Available Vehicle</div></div>
                                    <div class="option-radio-indicator" aria-hidden="true"><span class="radio-dot"></span></div>
                                </li>
                                @foreach($vehicles as $veh)
                                    <li class="custom-select-option {{ old('vehicle_id') == $veh->id ? 'selected' : '' }}" role="option" data-value="{{ $veh->id }}" data-name="{{ $veh->name }}" aria-selected="{{ old('vehicle_id') == $veh->id ? 'true' : 'false' }}" tabindex="0">
                                        <div class="option-content">
                                            <div class="option-name">{{ $veh->name }}</div>
                                            <div class="option-desc">{{ $veh->vehicle_type }} &bull; {{ $veh->seating_capacity }} Seats</div>
                                        </div>
                                        <div class="option-radio-indicator" aria-hidden="true"><span class="radio-dot"></span></div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- 5. REAL-TIME CONFIGURED FARE DISPLAY BANNER -->
                    <div id="homeFareBanner" class="fare-quote-banner" aria-live="polite">
                        <div>
                            <div class="fare-quote-label" id="fareQuoteLabel">Configured Route Fare</div>
                            <div class="fare-quote-price" id="fareQuotePrice">Calculating fare...</div>
                            <div class="fare-quote-inclusions" id="fareQuoteInclusions">Loading route parameters...</div>
                        </div>
                        <div id="fareActionNote" style="text-align: right;">
                            <span class="badge badge-assigned" id="fareQuoteBadge" style="font-size: 11px;">Fixed Fare</span>
                            <div style="font-size: 10.5px; color: var(--slate-400); margin-top: 3px;">No hidden driver charges</div>
                        </div>
                    </div>

                    <!-- 6. PASSENGER CONTACT DETAILS -->
                    <div class="grid grid-2 gap-2" style="margin-bottom: 0.85rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Enter Full Name" value="{{ Auth::check() ? Auth::user()->name : old('customer_name') }}" required>
                            @error('customer_name') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" name="mobile" id="homeCustomerMobile" class="form-control phone-input" placeholder="10-digit mobile number" value="{{ Auth::check() ? Auth::user()->phone : old('mobile') }}" required maxlength="10" pattern="[0-9]{10}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" title="Please enter a valid 10-digit mobile number">
                            @error('mobile') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Special Notes & Instructions -->
                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label class="form-label">Special Travel Instructions (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. 2 large luggage bags, doorstep landmark, patient attendant on board" value="{{ old('notes') }}">
                    </div>

                    <!-- Terms Acceptance Agreement -->
                    <div style="font-size: 11.5px; color: var(--slate-500); margin-bottom: 0.85rem; line-height: 1.4;">
                        By confirming, you agree to our 
                        <a href="{{ route('terms-and-conditions') }}" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Terms &amp; Conditions</a> and 
                        <a href="{{ route('cancellation-refund-policy') }}" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Cancellation Policy</a>. 
                        Instant Booking ID will be generated upon submission.
                    </div>

                    <!-- Submit Button with Anti-Duplicate Protection -->
                    <button type="submit" class="btn btn-primary btn-lg" id="homeSubmitBookingBtn" style="width: 100%; height: 40px; font-weight: 600;">
                        <span id="btnSubmitText">Book Now</span>
                        <x-icon name="arrow-right" size="16" id="btnSubmitIcon" style="margin-left: 4px;" />
                        <span id="btnSubmitSpinner" style="display: none; align-items: center; gap: 6px;">
                            <svg class="spinner-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 0.8s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
                            Processing Reservation...
                        </span>
                    </button>

                    <!-- Compact Booking Form Assistance Help Message -->
                    <div class="booking-submit-help" style="margin-top: 0.85rem; padding: 0.65rem 1rem; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 6px; text-align: center; font-size: 0.85rem; color: #92400E; display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                        <span>Facing an issue with your booking? Call us at <a href="tel:{{ $supportTel }}" style="color: #B45309; font-weight: 700; text-decoration: underline;">{{ $supportPhone }}</a>.</span>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- 3. Why Book With Us Section -->
    <section style="padding: 2.5rem 0; background: #FFFFFF;">
        <div class="container">
            <div style="text-align: center; max-width: 540px; margin: 0 auto 1.75rem;">
                <span style="color: var(--accent); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Service Guarantee</span>
                <h2 style="font-size: 18px; font-weight: 600; margin-top: 2px;">Why Book With Us</h2>
                <p style="color: var(--slate-500); margin-top: 4px; font-size: 12.5px;">Punctual, verified road travel across Bilaspur and Chhattisgarh.</p>
            </div>

            <div class="grid grid-4 gap-2">
                <div class="why-card">
                    <div class="icon-box icon-box-md icon-box-primary" style="margin-bottom: 0.75rem;"><x-icon name="clock-3" size="18" /></div>
                    <h3 style="font-size: 13.5px; font-weight: 600; margin-bottom: 0.25rem;">24/7 Dispatch</h3>
                    <p style="font-size: 12px; color: var(--slate-600); line-height: 1.45;">
                        Around-the-clock taxi availability for airport transfers, midnight arrivals and emergency trips.
                    </p>
                </div>

                <div class="why-card">
                    <div class="icon-box icon-box-md icon-box-primary" style="margin-bottom: 0.75rem;"><x-icon name="user-check" size="18" /></div>
                    <h3 style="font-size: 13.5px; font-weight: 600; margin-bottom: 0.25rem;">Verified Chauffeurs</h3>
                    <p style="font-size: 12px; color: var(--slate-600); line-height: 1.45;">
                        Polite, route-knowledgeable drivers trained for highway and city driving throughout Chhattisgarh.
                    </p>
                </div>

                <div class="why-card">
                    <div class="icon-box icon-box-md icon-box-primary" style="margin-bottom: 0.75rem;"><x-icon name="shield-check" size="18" /></div>
                    <h3 style="font-size: 13.5px; font-weight: 600; margin-bottom: 0.25rem;">Maintained Fleet</h3>
                    <p style="font-size: 12px; color: var(--slate-600); line-height: 1.45;">
                        Impeccably sanitized, air-conditioned cabs ready for comfortable short or long journeys.
                    </p>
                </div>

                <div class="why-card">
                    <div class="icon-box icon-box-md icon-box-primary" style="margin-bottom: 0.75rem;"><x-icon name="receipt" size="18" /></div>
                    <h3 style="font-size: 13.5px; font-weight: 600; margin-bottom: 0.25rem;">Transparent Tariffs</h3>
                    <p style="font-size: 12px; color: var(--slate-600); line-height: 1.45;">
                        Clear rates with no hidden fees or unexpected peak surge charges. Accurate fare calculation.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. How It Works Section -->
    <section style="padding: 2.5rem 0; background: var(--slate-50);">
        <div class="container">
            <div style="text-align: center; max-width: 540px; margin: 0 auto 1.75rem;">
                <span style="color: var(--accent); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Reservation Flow</span>
                <h2 style="font-size: 18px; font-weight: 600; margin-top: 2px;">How It Works</h2>
                <p style="color: var(--slate-500); margin-top: 4px; font-size: 12.5px;">Three quick steps to confirm your comfortable chauffeur ride.</p>
            </div>

            <div class="grid grid-3 gap-2">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <div>
                        <h3 style="font-size: 13.5px; font-weight: 600; margin-bottom: 0.2rem;">Enter Trip Details</h3>
                        <p style="font-size: 12px; color: var(--slate-600); line-height: 1.45;">
                            Specify pickup point, destination, date, time and choose your preferred car model.
                        </p>
                    </div>
                </div>

                <div class="step-card">
                    <div class="step-number">02</div>
                    <div>
                        <h3 style="font-size: 13.5px; font-weight: 600; margin-bottom: 0.2rem;">Confirm Booking</h3>
                        <p style="font-size: 12px; color: var(--slate-600); line-height: 1.45;">
                            Receive booking reference and chauffeur dispatch details directly.
                        </p>
                    </div>
                </div>

                <div class="step-card">
                    <div class="step-number">03</div>
                    <div>
                        <h3 style="font-size: 13.5px; font-weight: 600; margin-bottom: 0.2rem;">Travel Comfortably</h3>
                        <p style="font-size: 12px; color: var(--slate-600); line-height: 1.45;">
                            Your driver arrives punctually at your doorstep. Relax and travel safely.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Vehicle Fleet Section -->
    <section style="padding: 2.5rem 0; background: #FFFFFF;" id="vehicles-fleet">
        <div class="container">
            <div class="d-flex justify-between align-center flex-wrap gap-2" style="margin-bottom: 1.5rem;">
                <div>
                    <span style="color: var(--accent); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Our Fleet</span>
                    <h2 style="font-size: 18px; font-weight: 600; margin-top: 2px;">Available Vehicles</h2>
                </div>
                <a href="{{ route('vehicles') }}" class="btn btn-outline btn-sm">
                    View Complete Fleet <x-icon name="arrow-right" size="13" style="margin-left: 2px;" />
                </a>
            </div>

            <div class="grid grid-3 gap-2">
                @foreach($vehicles as $vehicle)
                    <div class="vehicle-card">
                        <div class="vehicle-card-img">
                            <img src="{{ $vehicle->icon_url }}" alt="{{ $vehicle->name }}">
                        </div>
                        <div class="vehicle-card-body">
                            <div class="d-flex justify-between align-center" style="margin-bottom: 0.35rem;">
                                <h3 style="font-size: 13.5px; font-weight: 600;">{{ $vehicle->name }}</h3>
                                <span class="badge {{ $vehicle->status === 'Available' ? 'badge-available' : 'badge-pending' }}">
                                    {{ $vehicle->status }}
                                </span>
                            </div>

                            <div style="display: flex; gap: 0.5rem; font-size: 11.5px; color: var(--slate-600); margin-bottom: 0.5rem; flex-wrap: wrap;">
                                <span class="badge badge-secondary">{{ $vehicle->vehicle_type }}</span>
                                <span><x-icon name="users" size="12" style="margin-right: 2px;" /> {{ $vehicle->seating_capacity }} Seats</span>
                                <span>•</span>
                                <span><x-icon name="snowflake" size="12" style="margin-right: 2px;" /> {{ $vehicle->ac_non_ac }}</span>
                            </div>

                            <p style="font-size: 12px; color: var(--slate-600); margin-bottom: 0.875rem; flex: 1; line-height: 1.4;">
                                {{ $vehicle->notes }}
                            </p>

                            <div class="d-flex justify-between align-center" style="border-top: 1px solid var(--slate-200); padding-top: 0.65rem;">
                                <div>
                                    <div style="font-size: 10.5px; color: var(--slate-400); text-transform: uppercase;">Tariff from</div>
                                    <div>
                                        <a href="{{ route('rates') }}" style="font-size: 12px; font-weight: 600; color: var(--primary);">View rates</a>
                                    </div>
                                </div>
                                <a href="{{ route('booking') }}?vehicle_id={{ $vehicle->id }}" class="btn btn-primary btn-sm">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 6. Ambulance Services Preview -->
    <section class="ambulance-home-preview" id="emergency-service" style="padding: 2.25rem 0;">
        <div class="container">
            <div class="ambulance-home-heading" style="margin-bottom: 1.25rem;">
                <div>
                    <span class="ambulance-kicker" style="font-size: 11px; font-weight: 600; color: var(--accent);">Specialized Emergency Transport</span>
                    <h2 style="font-size: 18px; font-weight: 600; margin: 2px 0 4px;">24/7 Ambulance Services</h2>
                    <p style="font-size: 12.5px; color: var(--slate-600); margin: 0;">Emergency medical transport including ICU, oxygen, ventilator and deceased transfer services.</p>
                </div>
                <a href="{{ route('ambulance-services') }}" class="btn btn-primary btn-sm">
                    View Ambulance Services <x-icon name="arrow-right" size="13" style="margin-left: 4px;" />
                </a>
            </div>
            @include('public.partials.ambulance-service-cards', ['compact' => true])
        </div>
    </section>

    <!-- 7. About Section -->
    <section style="padding: 2.5rem 0; background: #FFFFFF;">
        <div class="container">
            <div class="grid grid-2 gap-3 align-center">
                <div>
                    <div class="about-visual-wrap">
                        <img src="{{ asset('assets/images/about-travel.jpg') }}" alt="About Vaishnavi Tours Chauffeur Service" class="about-visual-img">
                    </div>
                </div>

                <div>
                    <span style="color: var(--accent); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">About Vaishnavi Tours</span>
                    <h2 style="font-size: 18px; font-weight: 600; margin-top: 2px; margin-bottom: 0.75rem;">
                        Trusted Travel Partner in Bilaspur &amp; Chhattisgarh
                    </h2>
                    <p style="font-size: 13px; color: var(--slate-700); line-height: 1.55; margin-bottom: 0.75rem;">
                        Vaishnavi Tours provides dedicated 24/7 taxi and cab services headquartered in Bilaspur, Chhattisgarh. We offer comprehensive local city rentals, outstation trips, airport transfers, and emergency travel assistance.
                    </p>
                    <p style="font-size: 12.5px; color: var(--slate-600); line-height: 1.55; margin-bottom: 1rem;">
                        Our fleet features sanitized sedans, MUVs, SUVs, and travellers with professional, verified chauffeurs focused on passenger safety and punctual arrival.
                    </p>

                    <div style="background: var(--slate-50); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border-left: 3px solid var(--primary); margin-bottom: 1.25rem;">
                        <strong style="color: var(--dark-900); display: block; font-size: 12px; margin-bottom: 2px;">Head Office:</strong>
                        <span style="color: var(--slate-600); font-size: 12px;">
                            {{ config('vaishnavi.address') }}
                        </span>
                    </div>

                    <a href="{{ route('about') }}" class="btn btn-outline btn-sm">
                        Learn More About Us <x-icon name="arrow-right" size="13" style="margin-left: 4px;" />
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. Google Reviews & Testimonials Section -->
    <section class="google-reviews-section" id="google-reviews" style="padding: 3.5rem 0; background: linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%); border-top: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0;">
        <div class="container">
            <!-- Google Header Banner -->
            <div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: 16px; padding: 1.75rem 2rem; box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.06); margin-bottom: 2rem;">
                <div class="d-flex justify-between align-center flex-wrap gap-3">
                    <!-- Left: Google G Logo & Overall Rating -->
                    <div class="d-flex align-center gap-3 flex-wrap">
                        <div style="width: 54px; height: 54px; border-radius: 12px; background: #FFFFFF; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04); flex-shrink: 0;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                            </svg>
                        </div>
                        <div>
                            <div class="d-flex align-center gap-2 mb-1 flex-wrap">
                                <span style="font-size: 1.65rem; font-weight: 800; color: #0F172A; line-height: 1;">{{ \App\Models\Setting::get('google_rating', config('vaishnavi.google_rating', '4.9')) }}</span>
                                <div style="display: flex; gap: 3px; color: #FBBC05;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span class="badge" style="background: #DEF7EC; color: #03543F; font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                                    ✓ Google Verified Business
                                </span>
                            </div>
                            <div style="font-size: 0.875rem; color: #475569;">
                                Based on <strong style="color: #0F172A;">{{ \App\Models\Setting::get('google_review_count', config('vaishnavi.google_review_count', '160+')) }} verified Google reviews</strong> &bull; <span style="color: #64748B;">{{ config('vaishnavi.google_business_name', 'Vaishnavi Tours Taxi Cab Service Car Rental') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Working Action Buttons -->
                    <div class="d-flex align-center gap-2 flex-wrap">
                        <a href="{{ \App\Models\Setting::get('google_write_review_url', config('vaishnavi.google_write_review_url')) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           id="btnGoogleWriteReview"
                           class="btn btn-primary"
                           style="background: #1A73E8; border-color: #1A73E8; color: #FFFFFF; font-weight: 600; padding: 0.65rem 1.25rem; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 2px 6px rgba(26, 115, 232, 0.25);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                            Write a Review
                        </a>

                        <a href="{{ \App\Models\Setting::get('google_all_reviews_url', config('vaishnavi.google_all_reviews_url')) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           id="btnGoogleReadAllReviews"
                           class="btn btn-outline"
                           style="border-color: #CBD5E1; color: #1E293B; background: #FFFFFF; font-weight: 600; padding: 0.65rem 1.25rem; display: inline-flex; align-items: center; gap: 7px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                            </svg>
                            Read All Reviews
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Review Cards Grid -->
            @if($feedbacks->isNotEmpty())
            <div class="grid grid-3 gap-3">
                @php
                    $avatarColors = [
                        ['bg' => '#E0F2FE', 'text' => '#0369A1'],
                        ['bg' => '#FEF3C7', 'text' => '#B45309'],
                        ['bg' => '#DCFCE7', 'text' => '#15803D'],
                        ['bg' => '#F3E8FF', 'text' => '#7E22CE'],
                        ['bg' => '#FFE4E6', 'text' => '#BE123C'],
                        ['bg' => '#E0E7FF', 'text' => '#4338CA'],
                    ];
                @endphp

                @foreach($feedbacks as $index => $fb)
                    @php
                        $color = $avatarColors[$index % count($avatarColors)];
                        $initials = collect(explode(' ', $fb->name))->map(fn($part) => mb_substr($part, 0, 1))->take(2)->join('');
                    @endphp
                    <div class="card" style="padding: 1.35rem 1.35rem 1.15rem; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.18s ease, box-shadow 0.18s ease;">
                        <div>
                            <!-- Header of Card -->
                            <div class="d-flex justify-between align-center mb-2">
                                <div class="d-flex align-center gap-2">
                                    <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $color['bg'] }}; color: {{ $color['text'] }}; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div style="font-size: 0.925rem; font-weight: 700; color: #0F172A; line-height: 1.2;">{{ $fb->name }}</div>
                                        <div style="font-size: 0.725rem; color: #64748B; margin-top: 2px;">
                                            {{ $fb->trip ?? 'Verified Traveler' }}
                                        </div>
                                    </div>
                                </div>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink: 0;">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                                </svg>
                            </div>

                            <!-- Star Rating & Time -->
                            <div class="d-flex align-center gap-2 mb-2">
                                <div style="display: flex; gap: 2px; color: #FBBC05;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="{{ $i <= ($fb->rating ?? 5) ? 'currentColor' : '#E2E8F0' }}">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span style="font-size: 0.725rem; color: #94A3B8;">{{ $fb->date_human ?? ($fb->created_at ? $fb->created_at->diffForHumans() : 'Verified Review') }}</span>
                            </div>

                            <!-- Comment text -->
                            <p style="font-size: 0.85rem; color: #334155; line-height: 1.55; margin-bottom: 1rem;">
                                "{{ $fb->comment }}"
                            </p>
                        </div>

                        <!-- Card Footer -->
                        <div class="d-flex justify-between align-center" style="border-top: 1px solid #F1F5F9; padding-top: 0.65rem; margin-top: 0.25rem;">
                            <span style="font-size: 0.7rem; color: #059669; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Verified Google Review
                            </span>
                            <a href="{{ \App\Models\Setting::get('google_all_reviews_url', config('vaishnavi.google_all_reviews_url')) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               style="font-size: 0.7rem; color: #2563EB; font-weight: 600; text-decoration: none;">
                                View on Google &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 2.25rem 2rem; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
                <div style="max-width: 620px; margin: 0 auto;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #DEF7EC; color: #03543F; font-size: 0.8rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; margin-bottom: 0.85rem;">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        100% Genuine Google Customer Reviews
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #0F172A; margin-bottom: 0.5rem;">
                        Authentic Feedback from Real Passengers
                    </h3>
                    <p style="font-size: 0.9rem; color: #64748B; margin-bottom: 1.5rem; line-height: 1.6;">
                        We believe in genuine trust. Vaishnavi Tours maintains a verified <strong>{{ \App\Models\Setting::get('google_rating', config('vaishnavi.google_rating', '4.9')) }} ★ rating</strong> based on <strong>{{ \App\Models\Setting::get('google_review_count', config('vaishnavi.google_review_count', '160+')) }} traveler reviews</strong> on our official Google Business Profile.
                    </p>
                    <div class="d-flex justify-center gap-2 flex-wrap">
                        <a href="{{ \App\Models\Setting::get('google_all_reviews_url', config('vaishnavi.google_all_reviews_url')) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn btn-primary"
                           style="background: #1A73E8; border-color: #1A73E8; font-size: 0.875rem;">
                            Read Verified Reviews on Google &rarr;
                        </a>
                        <a href="{{ route('feedback') }}" class="btn btn-outline" style="font-size: 0.875rem;">
                            Share Your Experience
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Bottom Attribution & Mobile Buttons -->
            <div class="text-center" style="margin-top: 2rem;">
                <div style="font-size: 0.75rem; color: #64748B; margin-bottom: 0.75rem;">
                    Official reviews verified from <strong>Vaishnavi Tours</strong> Google Business Profile in Bilaspur, Chhattisgarh. Google and the Google logo are trademarks of Google LLC.
                </div>
                <div class="d-flex justify-center gap-2 flex-wrap">
                    <a href="{{ \App\Models\Setting::get('google_all_reviews_url', config('vaishnavi.google_all_reviews_url')) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-outline btn-sm">
                        Read All {{ \App\Models\Setting::get('google_review_count', config('vaishnavi.google_review_count', '160+')) }} Reviews on Google Maps
                    </a>
                    <a href="{{ route('feedback') }}" class="btn btn-outline btn-sm">
                        Browse Website Reviews
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Local Travel & FAQ Section -->
    <section style="padding: 2.5rem 0; background: #FFFFFF;" id="faqs-section">
        <div class="container" style="max-width: 860px;">
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div style="font-size: 11px; font-weight: 600; color: var(--accent); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Frequently Asked Questions</div>
                <h2 style="font-size: 18px; font-weight: 600; color: var(--dark-900); margin: 0;">Common Questions About Bilaspur Taxi Booking</h2>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div class="card" style="padding: 0.875rem 1rem;">
                    <h3 style="font-size: 13px; font-weight: 600; color: var(--dark-900); margin-bottom: 0.25rem;">What taxi and cab services does Vaishnavi Tours provide in Bilaspur?</h3>
                    <p style="color: var(--slate-600); font-size: 12.5px; line-height: 1.5; margin: 0;">Vaishnavi Tours provides 24/7 local city taxi travel, outstation cabs from Bilaspur to all Chhattisgarh districts, dedicated airport pickup and drop transfers to Raipur Airport (RPR), and group rental coaches.</p>
                </div>
                <div class="card" style="padding: 0.875rem 1rem;">
                    <h3 style="font-size: 13px; font-weight: 600; color: var(--dark-900); margin-bottom: 0.25rem;">How can I book an outstation cab from Bilaspur to Raipur or Korba?</h3>
                    <p style="color: var(--slate-600); font-size: 12.5px; line-height: 1.5; margin: 0;">You can book directly through our <a href="{{ route('booking') }}" style="color: var(--primary); font-weight: 600;">online reservation form</a>, call our 24/7 dispatch desk at <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: var(--primary); font-weight: 600;">{{ config('vaishnavi.phone_primary') }}</a>, or chat with us on WhatsApp.</p>
                </div>
                <div class="card" style="padding: 0.875rem 1rem;">
                    <h3 style="font-size: 13px; font-weight: 600; color: var(--dark-900); margin-bottom: 0.25rem;">How do I check taxi fares for my route?</h3>
                    <p style="color: var(--slate-600); font-size: 12.5px; line-height: 1.5; margin: 0;">Choose your origin, destination, trip type, and vehicle on our <a href="{{ route('rates') }}" style="color: var(--primary); font-weight: 600;">Rates page</a> to see the configured fare and applicable charges.</p>
                </div>
                <div class="card" style="padding: 0.875rem 1rem;">
                    <h3 style="font-size: 13px; font-weight: 600; color: var(--dark-900); margin-bottom: 0.25rem;">Do you provide airport transfer from Bilaspur to Raipur Airport?</h3>
                    <p style="color: var(--slate-600); font-size: 12.5px; line-height: 1.5; margin: 0;">Yes, we provide dedicated <a href="{{ route('services.airport-transfer') }}" style="color: var(--primary); font-weight: 600;">Airport Transfer Service</a> with punctual doorstep pickup in Bilaspur and terminal drop at Raipur Airport (~135 km via NH 130) with flight schedule coordination.</p>
                </div>
            </div>
        </div>
    </section>

    <x-location-picker-modal />
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Google Maps Location Picker Trigger for Home Booking Widget
    document.querySelectorAll('.btn-home-select-map').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.dataset.target;
            if (target === 'pickup') {
                window.VaishnaviLocationPicker.open({
                    label: 'Pickup Location',
                    currentValue: document.getElementById('pickupLocationInput')?.value || '',
                    latEl: document.getElementById('homePickupLat'),
                    lngEl: document.getElementById('homePickupLng'),
                    placeIdEl: document.getElementById('homePickupPlaceId'),
                    onConfirm: (loc) => {
                        if (typeof setSelectLocation === 'function') {
                            setSelectLocation('pickup', loc.name);
                        } else {
                            const pInput = document.getElementById('pickupLocationInput');
                            if (pInput) pInput.value = loc.name;
                            const pDisplay = document.getElementById('pickupSelectDisplay');
                            if (pDisplay) pDisplay.textContent = loc.name;
                        }
                        if (typeof updateFareQuote === 'function') updateFareQuote();
                        if (typeof triggerHomeRouteCalculation === 'function') triggerHomeRouteCalculation();
                    }
                });
            } else if (target === 'destination') {
                window.VaishnaviLocationPicker.open({
                    label: 'Destination Location',
                    currentValue: document.getElementById('destinationLocationInput')?.value || '',
                    latEl: document.getElementById('homeDestinationLat'),
                    lngEl: document.getElementById('homeDestinationLng'),
                    placeIdEl: document.getElementById('homeDestinationPlaceId'),
                    onConfirm: (loc) => {
                        if (typeof setSelectLocation === 'function') {
                            setSelectLocation('destination', loc.name);
                        } else {
                            const dInput = document.getElementById('destinationLocationInput');
                            if (dInput) dInput.value = loc.name;
                            const dDisplay = document.getElementById('destinationSelectDisplay');
                            if (dDisplay) dDisplay.textContent = loc.name;
                        }
                        if (typeof updateFareQuote === 'function') updateFareQuote();
                        if (typeof triggerHomeRouteCalculation === 'function') triggerHomeRouteCalculation();
                    }
                });
            }
        });
    });

    const rateOptions = @json($rateOptions);
    const form = document.getElementById('homeBookingForm');
    const formTripType = document.getElementById('formTripType');
    const formRateCategory = document.getElementById('formRateCategory');
    const pickupInput = document.getElementById('pickupLocationInput');
    const destinationInput = document.getElementById('destinationLocationInput');
    const tabButtons = document.querySelectorAll('#bookingTypeSegmented .booking-segmented-btn');

    // Sub-toggles & Field Groups
    const airportDirectionWrap = document.getElementById('airportDirectionWrap');
    const btnAirportDrop = document.getElementById('btnAirportDrop');
    const btnAirportPickup = document.getElementById('btnAirportPickup');
    const localPackageWrap = document.getElementById('localPackageWrap');
    const btnLocal8h = document.getElementById('btnLocal8h');
    const btnLocal4h = document.getElementById('btnLocal4h');
    const standardRouteGrid = document.getElementById('standardRouteGrid');
    const tourPackageFields = document.getElementById('tourPackageFields');
    const ambulanceFields = document.getElementById('ambulanceFields');
    const returnDateGroup = document.getElementById('returnDateGroup');
    const returnTimeGroup = document.getElementById('returnTimeGroup');
    const returnDateInput = document.getElementById('returnDateInput');
    const returnTimeInput = document.getElementById('returnTimeInput');
    const travelDateInput = document.getElementById('travelDateInput');
    const flightNumberGroup = document.getElementById('flightNumberGroup');
    const estimatedKmGroup = document.getElementById('estimatedKmGroup');
    const estimatedKmInput = document.getElementById('estimatedKmInput');
    const destinationFormGroup = document.getElementById('destinationFormGroup');
    const swapLocationsBtn = document.getElementById('swapLocationsBtn');
    const sameLocationWarning = document.getElementById('sameLocationWarning');

    // Fare display elements
    const farePrice = document.getElementById('fareQuotePrice');
    const fareLabel = document.getElementById('fareQuoteLabel');
    const fareInclusions = document.getElementById('fareQuoteInclusions');
    const fareBadge = document.getElementById('fareQuoteBadge');
    const submitBtn = document.getElementById('homeSubmitBookingBtn');
    const submitText = document.getElementById('btnSubmitText');
    const submitIcon = document.getElementById('btnSubmitIcon');
    const submitSpinner = document.getElementById('btnSubmitSpinner');

    // Active state
    let currentTab = 'oneway';
    let airportDirection = 'drop'; // 'drop' = City -> Airport, 'pickup' = Airport -> City

    // 1. Tab Switching Function
    function setBookingType(tab, tripType, rateCategory) {
        currentTab = tab;
        formTripType.value = tripType;
        formRateCategory.value = rateCategory;

        tabButtons.forEach(btn => {
            const isMatch = btn.dataset.tab === tab;
            btn.classList.toggle('active', isMatch);
            btn.setAttribute('aria-selected', isMatch ? 'true' : 'false');
        });

        const isRoundTrip = (tab === 'roundtrip');

        // Toggle field visibilities based on tab
        standardRouteGrid.style.display = (tab === 'tour' || tab === 'ambulance') ? 'none' : 'block';
        tourPackageFields.style.display = tab === 'tour' ? 'block' : 'none';
        ambulanceFields.style.display = tab === 'ambulance' ? 'block' : 'none';
        airportDirectionWrap.style.display = tab === 'airport' ? 'block' : 'none';
        localPackageWrap.style.display = tab === 'local' ? 'block' : 'none';
        flightNumberGroup.style.display = tab === 'airport' ? 'block' : 'none';
        returnDateGroup.style.display = isRoundTrip ? 'block' : 'none';
        returnTimeGroup.style.display = isRoundTrip ? 'block' : 'none';
        estimatedKmGroup.style.display = isRoundTrip ? 'block' : 'none';

        // Manage disabled state and min date constraints for round-trip fields
        if (returnDateInput) {
            returnDateInput.disabled = !isRoundTrip;
            if (isRoundTrip && travelDateInput && travelDateInput.value) {
                returnDateInput.min = travelDateInput.value;
                if (!returnDateInput.value || returnDateInput.value < travelDateInput.value) {
                    returnDateInput.value = travelDateInput.value;
                }
            }
        }
        if (returnTimeInput) {
            returnTimeInput.disabled = !isRoundTrip;
        }
        if (estimatedKmInput) {
            estimatedKmInput.disabled = !isRoundTrip;
        }

        // Swap button visibility (not applicable in local hourly)
        if (swapLocationsBtn) {
            swapLocationsBtn.style.visibility = (tab === 'local') ? 'hidden' : 'visible';
        }

        // Adjust destination field in local rental
        if (tab === 'local') {
            destinationFormGroup.style.opacity = '0.6';
            destinationFormGroup.style.pointerEvents = 'none';
            setSelectLocation('destination', pickupInput.value + ' (Local)');
        } else {
            destinationFormGroup.style.opacity = '1';
            destinationFormGroup.style.pointerEvents = 'auto';
            if (destinationInput.value.includes('(Local)')) {
                setSelectLocation('destination', 'Raipur Airport');
            }
        }

        // Configure default locations for airport transfer
        if (tab === 'airport') {
            updateAirportDirection();
        }

        // Update submit button text
        if (tab === 'tour') {
            submitText.textContent = 'Book Tour Package';
        } else if (tab === 'ambulance') {
            submitText.textContent = 'Request Emergency Ambulance';
        } else if (tab === 'roundtrip') {
            submitText.textContent = 'Book Round Trip';
        } else if (tab === 'local') {
            submitText.textContent = 'Book Local Rental';
        } else if (tab === 'airport') {
            submitText.textContent = 'Book Airport Transfer';
        } else {
            submitText.textContent = 'Book Now';
        }

        updateFareQuote();
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            setBookingType(this.dataset.tab, this.dataset.trip, this.dataset.category);
        });
    });

    // 2. Airport Sub-Toggle (Drop vs Pickup)
    function updateAirportDirection() {
        if (airportDirection === 'drop') {
            btnAirportDrop.className = 'btn btn-sm btn-primary';
            btnAirportPickup.className = 'btn btn-sm btn-outline';
            if (destinationInput.value !== 'Raipur Airport') {
                setSelectLocation('destination', 'Raipur Airport');
            }
            if (pickupInput.value === 'Raipur Airport') {
                setSelectLocation('pickup', 'Bilaspur');
            }
        } else {
            btnAirportDrop.className = 'btn btn-sm btn-outline';
            btnAirportPickup.className = 'btn btn-sm btn-primary';
            if (pickupInput.value !== 'Raipur Airport') {
                setSelectLocation('pickup', 'Raipur Airport');
            }
            if (destinationInput.value === 'Raipur Airport') {
                setSelectLocation('destination', 'Bilaspur');
            }
        }
    }

    if (btnAirportDrop) {
        btnAirportDrop.addEventListener('click', function() {
            airportDirection = 'drop';
            updateAirportDirection();
            updateFareQuote();
        });
    }

    if (btnAirportPickup) {
        btnAirportPickup.addEventListener('click', function() {
            airportDirection = 'pickup';
            updateAirportDirection();
            updateFareQuote();
        });
    }

    // 3. Local Rental Sub-Toggle (8h vs 4h)
    if (btnLocal8h && btnLocal4h) {
        btnLocal8h.addEventListener('click', function() {
            formRateCategory.value = 'local_8h_80km';
            btnLocal8h.className = 'btn btn-sm btn-primary';
            btnLocal4h.className = 'btn btn-sm btn-outline';
            updateFareQuote();
        });
        btnLocal4h.addEventListener('click', function() {
            formRateCategory.value = 'local_4h_40km';
            btnLocal4h.className = 'btn btn-sm btn-primary';
            btnLocal8h.className = 'btn btn-sm btn-outline';
            updateFareQuote();
        });
    }

    // 4. Tour Package & Ambulance Form sync
    const tourPackageSelect = document.getElementById('tourPackageSelect');
    const tourStartingCitySelect = document.getElementById('tourStartingCitySelect');
    if (tourPackageSelect && tourStartingCitySelect) {
        function syncTourFields() {
            pickupInput.value = tourStartingCitySelect.value;
            destinationInput.value = tourPackageSelect.value;
            updateFareQuote();
        }
        tourPackageSelect.addEventListener('change', syncTourFields);
        tourStartingCitySelect.addEventListener('change', syncTourFields);
    }

    const ambulancePickupInput = document.getElementById('ambulancePickupInput');
    const ambulanceDestinationInput = document.getElementById('ambulanceDestinationInput');
    if (ambulancePickupInput && ambulanceDestinationInput) {
        function syncAmbulanceFields() {
            pickupInput.value = ambulancePickupInput.value.trim() || 'Bilaspur';
            destinationInput.value = ambulanceDestinationInput.value.trim() || 'Apollo Hospital Bilaspur';
            updateFareQuote();
        }
        ambulancePickupInput.addEventListener('input', syncAmbulanceFields);
        ambulanceDestinationInput.addEventListener('input', syncAmbulanceFields);
    }

    // 5. Searchable Select Implementation (Pickup & Destination)
    function setupSearchableSelect(type) {
        const wrap = document.getElementById(type + 'SelectWrap');
        const btn = document.getElementById(type + 'SelectBtn');
        const menu = document.getElementById(type + 'SelectMenu');
        const searchInput = document.getElementById(type + 'SearchField');
        const list = document.getElementById(type + 'SelectList');
        const empty = document.getElementById(type + 'SelectEmpty');
        const hiddenInput = document.getElementById(type + 'LocationInput');
        const displaySpan = document.getElementById(type + 'SelectDisplay');

        if (!btn || !menu || !searchInput || !list) return;

        function openMenu() {
            menu.style.display = 'flex';
            btn.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
            searchInput.value = '';
            filterOptions('');
            setTimeout(() => searchInput.focus(), 50);
        }

        function closeMenu() {
            menu.style.display = 'none';
            btn.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (menu.style.display === 'flex') {
                closeMenu();
            } else {
                document.querySelectorAll('.searchable-select-dropdown').forEach(d => d.style.display = 'none');
                document.querySelectorAll('.searchable-select-trigger').forEach(b => b.classList.remove('is-open'));
                openMenu();
            }
        });

        function filterOptions(term) {
            const lower = term.toLowerCase().trim();
            const items = list.querySelectorAll('.searchable-select-item');
            let visibleCount = 0;

            items.forEach(item => {
                const val = (item.dataset.value || '').toLowerCase();
                const matches = val.includes(lower);
                item.style.display = matches ? 'flex' : 'none';
                if (matches) visibleCount++;
            });

            empty.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        searchInput.addEventListener('input', function() {
            filterOptions(this.value);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMenu();
                btn.focus();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                const visible = Array.from(list.querySelectorAll('.searchable-select-item')).filter(i => i.style.display !== 'none');
                if (visible.length > 0) visible[0].focus();
            }
        });

        list.addEventListener('click', function(e) {
            const item = e.target.closest('.searchable-select-item');
            if (item) {
                const val = item.dataset.value;
                setSelectLocation(type, val);
                closeMenu();
                btn.focus();
                updateFareQuote();
            }
        });

        list.addEventListener('keydown', function(e) {
            const item = e.target.closest('.searchable-select-item');
            if (!item) return;
            const visible = Array.from(list.querySelectorAll('.searchable-select-item')).filter(i => i.style.display !== 'none');
            const idx = visible.indexOf(item);

            if (e.key === 'Enter') {
                e.preventDefault();
                setSelectLocation(type, item.dataset.value);
                closeMenu();
                btn.focus();
                updateFareQuote();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (idx < visible.length - 1) visible[idx + 1].focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (idx > 0) visible[idx - 1].focus();
                else searchInput.focus();
            } else if (e.key === 'Escape') {
                closeMenu();
                btn.focus();
            }
        });

        document.addEventListener('click', function(e) {
            if (wrap && !wrap.contains(e.target)) {
                closeMenu();
            }
        });
    }

    function setSelectLocation(type, value) {
        const hiddenInput = document.getElementById(type + 'LocationInput');
        const displaySpan = document.getElementById(type + 'SelectDisplay');
        const list = document.getElementById(type + 'SelectList');
        const otherWrap = document.getElementById(type + 'OtherWrap');
        const otherInput = document.getElementById(type + 'OtherInput');
        const isCustomQueryInput = document.getElementById('isCustomQueryInput');

        if (value === '__select_on_map__') {
            const isPickup = (type === 'pickup');
            window.VaishnaviLocationPicker.open({
                label: isPickup ? 'Pickup Location' : 'Destination Location',
                currentValue: hiddenInput.value || '',
                latEl: document.getElementById(isPickup ? 'homePickupLat' : 'homeDestinationLat'),
                lngEl: document.getElementById(isPickup ? 'homePickupLng' : 'homeDestinationLng'),
                placeIdEl: document.getElementById(isPickup ? 'homePickupPlaceId' : 'homeDestinationPlaceId'),
                onConfirm: (loc) => {
                    setSelectLocation(type, loc.name);
                    updateFareQuote();
                    triggerHomeRouteCalculation();
                }
            });
            return;
        }

        if (hiddenInput) hiddenInput.value = value;
        if (displaySpan) displaySpan.textContent = value === 'Other' ? 'Other Location (Custom)' : value;

        if (list) {
            list.querySelectorAll('.searchable-select-item').forEach(item => {
                const isSelected = item.dataset.value === value;
                item.classList.toggle('is-selected', isSelected);
                item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        }

        if (value === 'Other') {
            if (otherWrap) otherWrap.style.display = 'block';
            if (otherInput) {
                setTimeout(() => otherInput.focus(), 80);
            }
            if (isCustomQueryInput) isCustomQueryInput.value = '1';
        } else {
            if (otherWrap) otherWrap.style.display = 'none';
            if (otherInput) otherInput.value = '';
            const otherType = (type === 'pickup') ? 'destination' : 'pickup';
            const otherHidden = document.getElementById(otherType + 'LocationInput');
            if (isCustomQueryInput && (!otherHidden || otherHidden.value !== 'Other')) {
                isCustomQueryInput.value = '0';
            }
        }

        // Prevent identical pickup and drop
        if (sameLocationWarning) {
            if (pickupInput.value && destinationInput.value && pickupInput.value === destinationInput.value && currentTab !== 'local' && pickupInput.value !== 'Other') {
                sameLocationWarning.style.display = 'block';
            } else {
                sameLocationWarning.style.display = 'none';
            }
        }

        if (typeof triggerHomeRouteCalculation === 'function') {
            triggerHomeRouteCalculation();
        }
    }

    setupSearchableSelect('pickup');
    setupSearchableSelect('destination');

    // Custom fallback button clicks
    const btnPickupCustomFallback = document.getElementById('btnPickupCustomFallback');
    if (btnPickupCustomFallback) {
        btnPickupCustomFallback.addEventListener('click', function(e) {
            e.stopPropagation();
            setSelectLocation('pickup', 'Other');
            const pickupMenu = document.getElementById('pickupSelectMenu');
            if (pickupMenu) pickupMenu.style.display = 'none';
            const pickupBtn = document.getElementById('pickupSelectBtn');
            if (pickupBtn) pickupBtn.classList.remove('is-open');
            updateFareQuote();
        });
    }
    const btnDestinationCustomFallback = document.getElementById('btnDestinationCustomFallback');
    if (btnDestinationCustomFallback) {
        btnDestinationCustomFallback.addEventListener('click', function(e) {
            e.stopPropagation();
            setSelectLocation('destination', 'Other');
            const destMenu = document.getElementById('destinationSelectMenu');
            if (destMenu) destMenu.style.display = 'none';
            const destBtn = document.getElementById('destinationSelectBtn');
            if (destBtn) destBtn.classList.remove('is-open');
            updateFareQuote();
        });
    }

    // Custom text input listeners
    const pickupOtherInput = document.getElementById('pickupOtherInput');
    const destinationOtherInput = document.getElementById('destinationOtherInput');
    if (pickupOtherInput) {
        pickupOtherInput.addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                document.getElementById('isCustomQueryInput').value = '1';
            }
            updateFareQuote();
            triggerHomeRouteCalculation();
        });
    }
    if (destinationOtherInput) {
        destinationOtherInput.addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                document.getElementById('isCustomQueryInput').value = '1';
            }
            updateFareQuote();
            triggerHomeRouteCalculation();
        });
    }

    // 6. Swap Locations Control
    if (swapLocationsBtn) {
        swapLocationsBtn.addEventListener('click', function() {
            if (currentTab === 'local') return;

            const pVal = pickupInput.value;
            const dVal = destinationInput.value;

            setSelectLocation('pickup', dVal);
            setSelectLocation('destination', pVal);

            // In airport tab, swap toggles direction
            if (currentTab === 'airport') {
                airportDirection = (airportDirection === 'drop') ? 'pickup' : 'drop';
                btnAirportDrop.className = airportDirection === 'drop' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline';
                btnAirportPickup.className = airportDirection === 'pickup' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline';
            }

            updateFareQuote();
            triggerHomeRouteCalculation();
        });
    }

    // 7. Vehicle Category Selection & Dynamic Model Pills
    function renderHomeModelPills(optionEl) {
        const box = document.getElementById('homeModelSelectionBox');
        const wrap = document.getElementById('homeModelChipsWrap');
        const catIdInput = document.getElementById('homeVehicleCategoryId');
        const modelIdInput = document.getElementById('homeVehicleModelId');
        const modelNameInput = document.getElementById('homeVehicleModel');

        if (!box || !wrap) return;

        const catId = optionEl ? optionEl.dataset.categoryId : null;
        if (catIdInput) catIdInput.value = catId || '';

        let models = [];
        try {
            models = optionEl && optionEl.dataset.models ? JSON.parse(optionEl.dataset.models) : [];
        } catch (e) {
            models = [];
        }

        wrap.innerHTML = '';
        if (models && models.length > 0) {
            box.style.display = 'block';

            // "Any Model" option
            const anyBtn = document.createElement('button');
            anyBtn.type = 'button';
            anyBtn.className = 'btn btn-xs ' + (!modelIdInput.value ? 'btn-primary' : 'btn-outline');
            anyBtn.style.cssText = 'font-size: 11px; padding: 3px 8px; border-radius: 4px;';
            anyBtn.textContent = '✓ Any / All Models in Category';
            anyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                modelIdInput.value = '';
                modelNameInput.value = '';
                wrap.querySelectorAll('button').forEach(b => {
                    b.className = 'btn btn-xs btn-outline';
                });
                this.className = 'btn btn-xs btn-primary';
            });
            wrap.appendChild(anyBtn);

            models.forEach(m => {
                const btn = document.createElement('button');
                btn.type = 'button';
                const isSelected = modelIdInput.value == m.id || modelNameInput.value == m.name;
                btn.className = 'btn btn-xs ' + (isSelected ? 'btn-primary' : 'btn-outline');
                btn.style.cssText = 'font-size: 11px; padding: 3px 8px; border-radius: 4px;';
                btn.textContent = m.name + (m.default_seating_capacity ? ` (${m.default_seating_capacity} Seater)` : '');
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    modelIdInput.value = m.id;
                    modelNameInput.value = m.name;
                    wrap.querySelectorAll('button').forEach(b => {
                        b.className = 'btn btn-xs btn-outline';
                    });
                    this.className = 'btn btn-xs btn-primary';
                });
                wrap.appendChild(btn);
            });
        } else {
            box.style.display = 'none';
            if (modelIdInput) modelIdInput.value = '';
            if (modelNameInput) modelNameInput.value = '';
        }
    }

    const homeVehicleCatSelect = document.getElementById('homeVehicleCategorySelect');
    if (homeVehicleCatSelect) {
        homeVehicleCatSelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            renderHomeModelPills(selectedOpt);
            updateFareQuote();
        });

        if (homeVehicleCatSelect.selectedIndex >= 0) {
            renderHomeModelPills(homeVehicleCatSelect.options[homeVehicleCatSelect.selectedIndex]);
        }
    }

    if (estimatedKmInput) {
        estimatedKmInput.addEventListener('input', updateFareQuote);
    }

    // 8. Real-Time Fare Quote Calculator
    function updateFareQuote() {
        const vCat = homeVehicleCatSelect ? homeVehicleCatSelect.value : (document.querySelector('input[name="vehicle_category"]:checked')?.value || 'Sedan');
        const pickup = pickupInput.value || '';
        const destination = destinationInput.value || '';
        const category = formRateCategory.value;
        const isCustomQueryVal = document.getElementById('isCustomQueryInput') ? document.getElementById('isCustomQueryInput').value : '0';

        // Check if user selected "Other" location -> NO PRICE SHOWN, BECOMES A QUERY
        const isCustomPickup = pickup === 'Other' || (pickupOtherInput && pickupOtherInput.value.trim().length > 0);
        const isCustomDest = destination === 'Other' || (destinationOtherInput && destinationOtherInput.value.trim().length > 0);
        const isCustomLocationActive = isCustomPickup || isCustomDest || isCustomQueryVal === '1';

        if (isCustomLocationActive && currentTab !== 'tour' && currentTab !== 'ambulance') {
            fareLabel.textContent = 'Custom Location Quotation';
            farePrice.innerHTML = '<span style="color: #D97706; font-size: 1.15rem; font-weight: 800;">Price on Request</span>';
            fareInclusions.textContent = 'Custom location selected. No immediate price displayed. Our dispatch desk will review your query and send a custom quote.';
            fareBadge.textContent = 'Quotation Query';
            fareBadge.className = 'badge badge-pending';
            submitText.textContent = 'Submit Travel Query';
            return;
        }

        // Custom Tour Package
        if (currentTab === 'tour') {
            fareLabel.textContent = 'Custom Tour Package';
            farePrice.innerHTML = '₹ Custom Tariff';
            fareInclusions.textContent = 'Sightseeing coach with professional chauffeur. Personalized quotation confirmed upon booking.';
            fareBadge.textContent = 'Sightseeing';
            fareBadge.className = 'badge badge-primary';
            submitText.textContent = 'Book Tour Package';
            return;
        }

        // Ambulance Service
        if (currentTab === 'ambulance') {
            fareLabel.textContent = '24/7 Ambulance Service';
            farePrice.innerHTML = 'Immediate Dispatch';
            fareInclusions.textContent = 'Prompt emergency dispatch. Distance-based tariff confirmed with priority 24/7 desk.';
            fareBadge.textContent = 'Emergency';
            fareBadge.className = 'badge badge-cancelled';
            submitText.textContent = 'Request Emergency Ambulance';
            return;
        }

        // Round Trip: STRICT REQUIREMENT: Allow vehicle category selection but do not display an automatic fare estimate
        if (category === 'round_trip') {
            fareLabel.textContent = 'Round-Trip Custom Quotation Request';
            farePrice.innerHTML = '<span style="color: #2563EB; font-size: 1.15rem; font-weight: 800;">Quotation on Request</span>';
            fareInclusions.textContent = 'Category: ' + vCat + '. Round-trip fares depend on road itinerary, halts, and return schedule. Our central dispatch team will review your query and provide a confirmed custom quote.';
            fareBadge.textContent = 'Quotation Request';
            fareBadge.className = 'badge badge-primary';
            submitText.textContent = 'Submit Round-Trip Enquiry';
            return;
        }

        // Local Rentals (8h/80km or 4h/40km)
        if (category.startsWith('local_')) {
            const key = `${category}|||${vCat}`;
            const quote = rateOptions[key];

            if (quote && quote.details) {
                const amount = Number(quote.total_amount || 0);
                const hrs = quote.details.included_hours || (category === 'local_8h_80km' ? 8 : 4);
                const km = quote.details.included_km || (category === 'local_8h_80km' ? 80 : 40);
                const extraKm = quote.details.extra_km_rate ? ` · Extra ₹${quote.details.extra_km_rate}/KM` : '';
                const extraHr = quote.details.extra_hour_rate ? ` · Extra ₹${quote.details.extra_hour_rate}/hr` : '';
                const tollInfo = quote.details.toll_type ? ` · Toll ${quote.details.toll_type}` : '';
                const parkingInfo = quote.details.parking_type ? ` · Parking ${quote.details.parking_type}` : '';

                fareLabel.textContent = `${hrs} Hours / ${km} KM Package`;
                farePrice.textContent = `₹${amount.toLocaleString('en-IN')}`;
                fareInclusions.textContent = `City local usage (${hrs}h & ${km}km included)${extraKm}${extraHr}${tollInfo}${parkingInfo}`;
                fareBadge.textContent = 'Fixed Package';
                fareBadge.className = 'badge badge-assigned';
                submitText.textContent = 'Book Local Rental';
            } else {
                showUnconfiguredFare();
            }
            return;
        }

        // One-Way & Airport Transfers: strict category scoping
        let lookupCategory = category;
        if (pickup.includes('Airport') || destination.includes('Airport')) {
            lookupCategory = 'airport';
        }

        // Try exact key or opposite key within same category
        let key = `${lookupCategory}|${pickup}|${destination}|${vCat}`;
        let quote = rateOptions[key];

        if (!quote) {
            key = `${lookupCategory}|${destination}|${pickup}|${vCat}`;
            quote = rateOptions[key];
        }

        if (quote && quote.details) {
            const amount = Number(quote.total_amount || 0);
            const km = quote.details.included_km ? `${quote.details.included_km} KM included · ` : '';
            const hrs = quote.details.included_hours ? `${quote.details.included_hours} hrs duration · ` : '';
            let toll = '';
            if (quote.details.toll_type === 'included' && quote.details.parking_type === 'included') {
                toll = 'Toll & Parking included · ';
            } else if (quote.details.toll_type) {
                toll = `Toll ${quote.details.toll_type} · `;
            }
            const extraKm = quote.details.extra_km_rate ? `Extra ₹${quote.details.extra_km_rate}/KM · ` : '';
            const gstSuffix = quote.details.gst_applicable ? ' + GST' : '';

            fareLabel.textContent = lookupCategory === 'airport' ? 'Airport Transfer Fare' : 'Configured Route Fare';
            farePrice.textContent = `₹${amount.toLocaleString('en-IN')}${gstSuffix}`;
            fareInclusions.textContent = `${km}${hrs}${toll}${extraKm}Dedicated AC cab`;
            fareBadge.textContent = lookupCategory === 'airport' ? 'Airport Rate' : 'Fixed Fare';
            fareBadge.className = 'badge badge-assigned';
            submitText.textContent = 'Book Now';
        } else {
            showUnconfiguredFare();
        }
    }

    function showUnconfiguredFare() {
        fareLabel.textContent = 'Route Fare Rate';
        farePrice.innerHTML = '<span class="fare-quote-unconfigured">Rate not configured. Please contact Vaishnavi Tours.</span>';
        fareInclusions.textContent = 'Direct route fare is not configured in fixed tariff. Our dispatch desk will confirm rate upon submission.';
        fareBadge.textContent = 'Custom Route';
        fareBadge.className = 'badge badge-pending';
        submitText.textContent = 'Submit Travel Query';
    }

    // 9. Interactive Road Route Calculation & Map Polyline Renderer
    const homeRouteContainer = document.getElementById('homeRouteMapContainer');
    const homeRouteCanvas = document.getElementById('homeRouteMapCanvas');
    const homeRouteDistanceDisplay = document.getElementById('homeRouteDistanceDisplay');
    const homeRouteDurationDisplay = document.getElementById('homeRouteDurationDisplay');
    const homeRouteCalcSpinner = document.getElementById('homeRouteCalcSpinner');
    const homeRouteCalcErrorBanner = document.getElementById('homeRouteCalcErrorBanner');
    const homeRouteCalcErrorMsg = document.getElementById('homeRouteCalcErrorMsg');
    const btnHomeRetryRouteCalc = document.getElementById('btnHomeRetryRouteCalc');

    let homeRouteMap = null;
    let homeRoutePolyline = null;
    let homeRouteMarkers = [];

    btnHomeRetryRouteCalc?.addEventListener('click', triggerHomeRouteCalculation);

    function decodeHomePolyline(encoded) {
        if (!encoded) return [];
        let poly = [];
        let index = 0, len = encoded.length;
        let lat = 0, lng = 0;
        while (index < len) {
            let b, shift = 0, result = 0;
            do {
                b = encoded.charCodeAt(index++) - 63;
                result |= (b & 0x1f) << shift;
                shift += 5;
            } while (b >= 0x20);
            let dlat = ((result & 1) ? ~(result >> 1) : (result >> 1));
            lat += dlat;
            shift = 0;
            result = 0;
            do {
                b = encoded.charCodeAt(index++) - 63;
                result |= (b & 0x1f) << shift;
                shift += 5;
            } while (b >= 0x20);
            let dlng = ((result & 1) ? ~(result >> 1) : (result >> 1));
            lng += dlng;
            poly.push([lat / 1e5, lng / 1e5]);
        }
        return poly;
    }

    async function triggerHomeRouteCalculation() {
        if (!homeRouteContainer || !pickupInput || !destinationInput) return;

        if (currentTab === 'tour' || currentTab === 'ambulance') {
            homeRouteContainer.style.display = 'none';
            return;
        }

        const pVal = (pickupInput.value === 'Other' && pickupOtherInput) ? pickupOtherInput.value.trim() : pickupInput.value;
        const dVal = (destinationInput.value === 'Other' && destinationOtherInput) ? destinationOtherInput.value.trim() : destinationInput.value;

        if (!pVal || !dVal || pVal === '__select_on_map__' || dVal === '__select_on_map__' || pVal === dVal) {
            homeRouteContainer.style.display = 'none';
            return;
        }

        const isRoundTrip = (currentTab === 'roundtrip');
        const pLat = document.getElementById('homePickupLat');
        const pLng = document.getElementById('homePickupLng');
        const dLat = document.getElementById('homeDestinationLat');
        const dLng = document.getElementById('homeDestinationLng');

        const originParam = (pLat && pLat.value && pLng && pLng.value)
            ? { lat: parseFloat(pLat.value), lng: parseFloat(pLng.value), address: pVal }
            : pVal;

        const destParam = (dLat && dLat.value && dLng && dLng.value)
            ? { lat: parseFloat(dLat.value), lng: parseFloat(dLng.value), address: dVal }
            : dVal;

        homeRouteContainer.style.display = 'block';
        if (homeRouteCalcSpinner) homeRouteCalcSpinner.style.display = 'flex';
        if (homeRouteCalcErrorBanner) homeRouteCalcErrorBanner.style.display = 'none';

        try {
            const resp = await fetch('{{ route("api.maps.calculate-route") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    origin: originParam,
                    destination: destParam,
                    is_round_trip: isRoundTrip
                })
            });

            const data = await resp.json();
            if (homeRouteCalcSpinner) homeRouteCalcSpinner.style.display = 'none';

            if (data && data.success) {
                const distKm = data.total_distance_km;
                const durText = data.total_duration_text;

                const distKmInput = document.getElementById('homeRouteDistanceKm');
                const durTextInput = document.getElementById('homeRouteDurationText');
                const durMinsInput = document.getElementById('homeRouteDurationMinutes');

                if (distKmInput) distKmInput.value = distKm;
                if (durTextInput) durTextInput.value = durText;
                if (durMinsInput) durMinsInput.value = data.total_duration_minutes;

                if (estimatedKmInput && isRoundTrip) {
                    estimatedKmInput.value = distKm;
                }

                if (homeRouteDistanceDisplay) homeRouteDistanceDisplay.textContent = `${distKm} KM`;
                if (homeRouteDurationDisplay) homeRouteDurationDisplay.textContent = `⏱️ ${durText}`;

                await renderHomeRouteMap(data);
            } else {
                throw new Error(data.message || 'Route calculation could not be completed.');
            }
        } catch (err) {
            console.error('Home route calculation error:', err);
            if (homeRouteCalcSpinner) homeRouteCalcSpinner.style.display = 'none';
            if (homeRouteCalcErrorBanner) {
                homeRouteCalcErrorBanner.style.display = 'flex';
                if (homeRouteCalcErrorMsg) homeRouteCalcErrorMsg.textContent = 'Notice: Could not load driving route. ' + (err.message || '');
            }
        }
    }

    async function renderHomeRouteMap(data) {
        if (!homeRouteCanvas || typeof L === 'undefined') return;

        if (!homeRouteMap) {
            homeRouteMap = L.map(homeRouteCanvas).setView([22.0797, 82.1409], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap | Vaishnavi Tours'
            }).addTo(homeRouteMap);
        }

        homeRouteMarkers.forEach(m => homeRouteMap.removeLayer(m));
        homeRouteMarkers = [];
        if (homeRoutePolyline) {
            homeRouteMap.removeLayer(homeRoutePolyline);
            homeRoutePolyline = null;
        }

        const waypointPoints = [];
        let startCoords = null;
        let endCoords = null;

        if (data.legs && data.legs.length > 0) {
            data.legs.forEach((leg, idx) => {
                const startLoc = leg.start_location || data.origin_coordinates;
                const endLoc = leg.end_location || data.destination_coordinates;

                if (startLoc && startLoc.lat && idx === 0) {
                    startCoords = startLoc;
                    const startPos = [startLoc.lat, startLoc.lng];
                    const m = L.marker(startPos, { title: leg.start_address || leg.from_name }).addTo(homeRouteMap)
                        .bindPopup(`<strong>🟢 Pickup:</strong> ${leg.start_address || leg.from_name}`);
                    homeRouteMarkers.push(m);
                    waypointPoints.push(startPos);
                }
                if (endLoc && endLoc.lat) {
                    const endPos = [endLoc.lat, endLoc.lng];
                    const isLast = (idx === data.legs.length - 1);
                    if (isLast) endCoords = endLoc;
                    const label = isLast ? (data.is_round_trip ? '🏁 Return' : '🔴 Destination') : `🟡 Via`;
                    const m = L.marker(endPos, { title: leg.end_address || leg.to_name }).addTo(homeRouteMap)
                        .bindPopup(`<strong>${label}:</strong> ${leg.end_address || leg.to_name}`);
                    homeRouteMarkers.push(m);
                    waypointPoints.push(endPos);
                }
            });
        }

        let roadCoordinates = [];

        if (data.route_geometry && data.route_geometry.trim().length > 0) {
            const polyStrings = data.route_geometry.split('|');
            polyStrings.forEach(ps => {
                const decoded = decodeHomePolyline(ps.trim());
                if (decoded && decoded.length > 0) {
                    roadCoordinates = roadCoordinates.concat(decoded);
                }
            });
        }

        if (roadCoordinates.length === 0 && startCoords && endCoords) {
            try {
                const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${startCoords.lng},${startCoords.lat};${endCoords.lng},${endCoords.lat}?overview=full&geometries=geojson`;
                const osrmResp = await fetch(osrmUrl);
                const osrmData = await osrmResp.json();
                if (osrmData && osrmData.routes && osrmData.routes[0] && osrmData.routes[0].geometry) {
                    roadCoordinates = osrmData.routes[0].geometry.coordinates.map(coord => [coord[1], coord[0]]);
                }
            } catch (osrmErr) {
                console.warn('OSRM road geometry fetch notice on home widget:', osrmErr);
            }
        }

        const finalPath = roadCoordinates.length > 0 ? roadCoordinates : waypointPoints;

        if (finalPath.length >= 2) {
            homeRoutePolyline = L.polyline(finalPath, {
                color: '#2563EB',
                weight: 5,
                opacity: 0.9,
                dashArray: data.is_round_trip ? '8, 6' : null
            }).addTo(homeRouteMap);

            homeRouteMap.fitBounds(homeRoutePolyline.getBounds(), { padding: [25, 25] });
        } else if (waypointPoints.length > 0) {
            homeRouteMap.setView(waypointPoints[0], 11);
        }

        setTimeout(() => homeRouteMap.invalidateSize(), 250);
    }

    // 10. Prevent Duplicate Submission
    if (form) {
        form.addEventListener('submit', function(e) {
            // Handle custom pickup/drop if Other was selected
            if (pickupInput.value === 'Other' || (pickupOtherInput && pickupOtherInput.value.trim().length > 0)) {
                const val = pickupOtherInput ? pickupOtherInput.value.trim() : '';
                if (!val) {
                    alert('Please enter your custom pickup location.');
                    if (pickupOtherInput) pickupOtherInput.focus();
                    e.preventDefault();
                    return false;
                }
                pickupInput.value = val;
                document.getElementById('isCustomQueryInput').value = '1';
            }

            if (destinationInput.value === 'Other' || (destinationOtherInput && destinationOtherInput.value.trim().length > 0)) {
                const val = destinationOtherInput ? destinationOtherInput.value.trim() : '';
                if (!val) {
                    alert('Please enter your custom destination location.');
                    if (destinationOtherInput) destinationOtherInput.focus();
                    e.preventDefault();
                    return false;
                }
                destinationInput.value = val;
                document.getElementById('isCustomQueryInput').value = '1';
            }

            // Validation check: pickup and destination cannot be identical for one-way and roundtrip
            if (currentTab !== 'local' && pickupInput.value === destinationInput.value) {
                e.preventDefault();
                alert('Pickup location and destination must differ for this journey.');
                return false;
            }

            // Sync notes with extra fields
            if (currentTab === 'tour') {
                destinationInput.value = tourPackageSelect.value;
                pickupInput.value = tourStartingCitySelect.value;
            } else if (currentTab === 'ambulance') {
                pickupInput.value = ambulancePickupInput.value.trim() || 'Bilaspur';
                destinationInput.value = ambulanceDestinationInput.value.trim() || 'Apollo Hospital Bilaspur';
            }

            // Disable submit button & show spinner
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            if (submitIcon) submitIcon.style.display = 'none';
            submitSpinner.style.display = 'inline-flex';
        });
    }

    // Dynamic return date constraints on travel date change
    if (travelDateInput) {
        travelDateInput.addEventListener('change', function() {
            userModifiedTravelDate = true;
            if (returnDateInput) {
                returnDateInput.min = this.value;
                if (returnDateInput.value && returnDateInput.value < this.value) {
                    returnDateInput.value = this.value;
                }
            }
        });
        travelDateInput.addEventListener('input', function() {
            userModifiedTravelDate = true;
        });
    }

    // Live Current Date & Time Synchronization for Quick Booking
    const travelTimeInput = document.getElementById('travelTimeInput');
    let userModifiedTravelTime = {{ old('travel_time') ? 'true' : 'false' }};
    let userModifiedTravelDate = {{ old('travel_date') ? 'true' : 'false' }};

    function getLocalTimeFormatted() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function getLocalDateFormatted() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function syncCurrentBookingDateTime(force = false) {
        const todayStr = getLocalDateFormatted();
        const nowTimeStr = getLocalTimeFormatted();

        if (travelDateInput) {
            travelDateInput.min = todayStr;
            if (force || !userModifiedTravelDate || !travelDateInput.value) {
                travelDateInput.value = todayStr;
            }
        }

        if (travelTimeInput) {
            if (force || !userModifiedTravelTime || !travelTimeInput.value) {
                travelTimeInput.value = nowTimeStr;
            }
        }
    }

    if (travelTimeInput) {
        travelTimeInput.addEventListener('input', function() {
            userModifiedTravelTime = true;
        });
        travelTimeInput.addEventListener('change', function() {
            userModifiedTravelTime = true;
        });
    }

    // Set initial device time on page load
    syncCurrentBookingDateTime();

    // Keep live updated every 30 seconds if user hasn't explicitly changed it
    setInterval(function() {
        syncCurrentBookingDateTime(false);
    }, 30000);

    // Refresh immediately when returning to tab / switching back to page
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            syncCurrentBookingDateTime(false);
        }
    });

    window.addEventListener('pageshow', function() {
        syncCurrentBookingDateTime(false);
    });

    // Sync initial booking tab based on formTripType (handles reloads & validation errors)
    const initialTripType = formTripType ? formTripType.value : 'One-Way';
    const matchingBtn = Array.from(tabButtons).find(btn => btn.dataset.trip === initialTripType);
    if (matchingBtn) {
        setBookingType(matchingBtn.dataset.tab, matchingBtn.dataset.trip, matchingBtn.dataset.category);
    } else {
        setBookingType('oneway', 'One-Way', 'outstation');
    }

    // Initial calculation
    updateFareQuote();
    triggerHomeRouteCalculation();
});

// Hero Car Image Sequence Animation Engine
(function() {
    function initHeroCarAnimation() {
        const container = document.getElementById('hero-car-sequence');
        const canvas = document.getElementById('hero-car-canvas');
        if (!container || !canvas) return;

        const ctx = canvas.getContext('2d', { alpha: false });
        if (!ctx) return;

        // Respect user's reduced-motion preference
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (prefersReducedMotion.matches) {
            return;
        }

        const totalFrames = parseInt(container.getAttribute('data-total-frames') || '240', 10);
        const targetFps = parseInt(container.getAttribute('data-fps') || '26', 10);
        const frameInterval = 1000 / targetFps;
        const framePattern = container.getAttribute('data-frame-pattern') || '/assets/hero-loop/ezgif-frame-%03d.jpg';

        // Pre-build array of deterministic, naturally-sorted URLs
        const frameUrls = new Array(totalFrames);
        for (let i = 0; i < totalFrames; i++) {
            const frameNum = String(i + 1).padStart(3, '0');
            frameUrls[i] = framePattern.replace('%03d', frameNum);
        }

        const frames = new Array(totalFrames);
        let currentIndex = 0;
        let isPlaying = false;
        let isVisible = true;
        let lastTime = 0;
        let rafId = null;
        let activated = false;

        // Progressive queue loader
        const INITIAL_BUFFER = 12;
        const CONCURRENCY = 4;
        let queueIndex = 0;
        let activeLoads = 0;

        function fetchFrame(idx, callback) {
            if (frames[idx]) {
                if (callback) callback(frames[idx]);
                return;
            }
            const img = new Image();
            img.decoding = 'async';
            img.src = frameUrls[idx];
            img.onload = function() {
                frames[idx] = img;
                if (callback) callback(img);
            };
            img.onerror = function() {
                if (callback) callback(null);
            };
        }

        function pumpLoader() {
            while (activeLoads < CONCURRENCY && queueIndex < totalFrames) {
                const idx = queueIndex++;
                if (frames[idx]) continue;
                activeLoads++;
                fetchFrame(idx, function() {
                    activeLoads--;
                    pumpLoader();
                });
            }
        }

        function renderFrame(img) {
            if (!img || !img.complete || img.naturalWidth === 0) return;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            if (!activated) {
                activated = true;
                canvas.classList.add('is-active');
            }
        }

        function tick(timestamp) {
            if (!isPlaying || !isVisible) {
                rafId = null;
                return;
            }

            rafId = requestAnimationFrame(tick);

            if (!lastTime) lastTime = timestamp;
            const delta = timestamp - lastTime;

            if (delta >= frameInterval) {
                lastTime = timestamp - (delta % frameInterval);

                const nextIndex = (currentIndex + 1) % totalFrames;

                // Seamless step: if next frame is ready, advance and render.
                // Otherwise hold current frame to prevent any blank frame or flicker.
                if (frames[nextIndex] && frames[nextIndex].complete) {
                    currentIndex = nextIndex;
                    renderFrame(frames[currentIndex]);
                } else if (frames[(currentIndex + 2) % totalFrames]?.complete) {
                    currentIndex = (currentIndex + 2) % totalFrames;
                    renderFrame(frames[currentIndex]);
                }
            }
        }

        function startLoop() {
            if (isPlaying) return;
            isPlaying = true;
            lastTime = 0;
            if (!rafId) {
                rafId = requestAnimationFrame(tick);
            }
        }

        function stopLoop() {
            isPlaying = false;
            if (rafId) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }
        }

        // Page Visibility API
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopLoop();
            } else if (isVisible) {
                startLoop();
            }
        });

        // Intersection Observer (pause offscreen)
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    isVisible = entry.isIntersecting;
                    if (isVisible) {
                        startLoop();
                    } else {
                        stopLoop();
                    }
                });
            }, { threshold: 0.05 });
            observer.observe(container);
        }

        // Reduced-motion toggle listener
        prefersReducedMotion.addEventListener('change', function(e) {
            if (e.matches) {
                stopLoop();
                canvas.classList.remove('is-active');
            } else {
                canvas.classList.add('is-active');
                startLoop();
            }
        });

        // Bootstrap: preload first buffer, render frame 0 immediately, then start loop
        let initialReady = 0;
        const initialTarget = Math.min(INITIAL_BUFFER, totalFrames);
        for (let i = 0; i < initialTarget; i++) {
            fetchFrame(i, function(img) {
                if (i === 0 && img) {
                    renderFrame(img);
                }
                initialReady++;
                if (initialReady === initialTarget) {
                    startLoop();
                    queueIndex = initialTarget;
                    pumpLoader();
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroCarAnimation);
    } else {
        initHeroCarAnimation();
    }
})();
</script>
@endpush

