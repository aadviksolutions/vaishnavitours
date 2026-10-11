@extends('layouts.app')

@section('title', 'Vaishnavi Tours | 24/7 Taxi & Cab Service Across Chhattisgarh')
@section('meta_description', 'Vaishnavi Tours provides reliable 24/7 taxi and cab services across Chhattisgarh, including Bilaspur, Raipur, Raigarh, Korba, Ambikapur and nearby destinations.')
@section('canonical', route('home'))

@push('styles')
<link rel="preload" as="image" href="{{ asset('assets/hero-loop/ezgif-frame-001.jpg') }}" fetchpriority="high">
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
                    <input type="hidden" name="terms_accepted" value="1">

                    <!-- Airport Transfer Sub-Toggle (Airport Drop vs Airport Pickup) -->
                    <div id="airportDirectionWrap" style="display: none; margin-bottom: 0.85rem;">
                        <div style="display: flex; gap: 8px; align-items: center; background: var(--slate-100); padding: 4px; border-radius: var(--radius-sm); border: 1px solid var(--slate-200); width: fit-content;">
                            <button type="button" class="btn btn-sm btn-primary" id="btnAirportDrop" style="height: 28px; font-size: 11.5px; padding: 0 10px;">
                                <x-icon name="plane-takeoff" size="13" /> Airport Drop (City &rarr; Airport)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline" id="btnAirportPickup" style="height: 28px; font-size: 11.5px; padding: 0 10px;">
                                <x-icon name="plane-landing" size="13" /> Airport Pickup (Airport &rarr; City)
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
                                <label class="form-label" id="pickupLabelText">Pickup Location</label>
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
                                            @foreach($locations as $loc)
                                                <li class="searchable-select-item {{ old('pickup_location', 'Bilaspur') === $loc ? 'is-selected' : '' }}" data-value="{{ $loc }}" role="option" tabindex="0">
                                                    <span>{{ $loc }}</span>
                                                    @if(in_array($loc, ['Bilaspur', 'Raipur', 'Raigarh', 'Korba', 'Ambikapur']))
                                                        <span style="font-size: 10px; color: var(--slate-400); text-transform: uppercase;">Hub</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="searchable-select-empty" id="pickupSelectEmpty" style="display: none;">
                                            <div>No matching location available.</div>
                                            <a href="{{ route('contact') }}" style="color: var(--primary); font-weight: 600; text-decoration: underline; font-size: 11px;">Contact for custom route</a>
                                        </div>
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
                                <label class="form-label" id="destinationLabelText">Destination</label>
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
                                        </ul>
                                        <div class="searchable-select-empty" id="destinationSelectEmpty" style="display: none;">
                                            <div>No matching location available.</div>
                                            <a href="{{ route('contact') }}" style="color: var(--primary); font-weight: 600; text-decoration: underline; font-size: 11px;">Contact for custom route</a>
                                        </div>
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
                            <input type="date" name="travel_date" id="travelDateInput" class="form-control" value="{{ old('travel_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            @error('travel_date') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Pickup Time</label>
                            <input type="time" name="travel_time" id="travelTimeInput" class="form-control" value="{{ old('travel_time', '09:00') }}" required>
                            @error('travel_time') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <!-- Return Date (for Round Trip) -->
                        <div class="form-group" id="returnDateGroup" style="display: none; margin-bottom: 0;">
                            <label class="form-label">Return Date</label>
                            <input type="date" name="return_date" id="returnDateInput" class="form-control" value="{{ old('return_date', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}">
                            @error('return_date') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <!-- Return Time (for Round Trip) -->
                        <div class="form-group" id="returnTimeGroup" style="display: none; margin-bottom: 0;">
                            <label class="form-label">Return Time</label>
                            <input type="time" name="return_time" id="returnTimeInput" class="form-control" value="{{ old('return_time', '18:00') }}">
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
                                <input type="number" name="estimated_km" id="estimatedKmInput" class="form-control" min="1" step="1" placeholder="e.g. 270 KM" value="{{ old('estimated_km', '270') }}">
                            </div>
                            <div style="font-size: 11.5px; color: var(--slate-500); line-height: 1.4; padding-top: 14px;">
                                Fares calculated as fixed daily vehicle rent + actual KM charge.
                            </div>
                        </div>
                    </div>

                    <!-- 4. VEHICLE CATEGORY & FLEET SELECTION -->
                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Vehicle Category</span>
                            <span style="font-size: 11px; font-weight: normal; color: var(--slate-500);">Fleet verified AC vehicles</span>
                        </label>
                        <div class="grid grid-3 gap-2" id="vehicleCategoryChips">
                            @foreach($vehicleCategories as $vCat)
                                <label style="border: 1px solid var(--slate-300); padding: 0.5rem 0.65rem; border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: var(--transition); background: #FFFFFF;" class="v-cat-chip {{ $loop->first ? 'active' : '' }}">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <input type="radio" name="vehicle_category" value="{{ $vCat }}" {{ $loop->first ? 'checked' : '' }} style="accent-color: var(--primary);">
                                        <div>
                                            <div style="font-weight: 600; font-size: 12.5px; color: var(--dark-900);">{{ $vCat }}</div>
                                            <div style="font-size: 10.5px; color: var(--slate-500);">
                                                {{ $vCat === 'Sedan' ? 'Dzire/Etios · 4 Seats' : ($vCat === 'Ertiga' ? 'MUV · 6 Seats' : 'Premium SUV · 7 Seats') }}
                                            </div>
                                        </div>
                                    </div>
                                    <x-icon name="car" size="14" style="color: var(--slate-400);" />
                                </label>
                            @endforeach
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
                            <input type="tel" name="mobile" class="form-control" placeholder="10-digit mobile number" value="{{ Auth::check() ? Auth::user()->phone : old('mobile') }}" required pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number">
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

    <!-- 8. Testimonials Section -->
    @if($feedbacks->isNotEmpty())
        <section style="padding: 2.5rem 0; background: var(--slate-50);">
            <div class="container">
                <div style="text-align: center; max-width: 540px; margin: 0 auto 1.75rem;">
                    <span style="color: var(--accent); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Customer Experiences</span>
                    <h2 style="font-size: 18px; font-weight: 600; margin-top: 2px;">What Travelers Say</h2>
                </div>

                <div class="grid grid-3 gap-2">
                    @foreach($feedbacks as $fb)
                        <div class="card" style="padding: 1rem;">
                            <div style="color: var(--accent); font-size: 13px; margin-bottom: 0.35rem; display: flex; gap: 2px;">
                                @for($i = 1; $i <= 5; $i++)
                                    <x-icon name="star" size="13" style="fill: {{ $i <= $fb->rating ? 'var(--accent)' : 'none' }}; color: {{ $i <= $fb->rating ? 'var(--accent)' : '#CBD5E1' }};" />
                                @endfor
                            </div>
                            <p style="font-size: 12.5px; color: var(--slate-700); line-height: 1.5; margin-bottom: 0.75rem;">
                                "{{ $fb->comment }}"
                            </p>
                            <div style="font-weight: 600; font-size: 12.5px; color: var(--dark-900);">{{ $fb->name }}</div>
                            <div style="font-size: 11px; color: var(--slate-400);">Verified Traveler</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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

        // Toggle field visibilities based on tab
        standardRouteGrid.style.display = (tab === 'tour' || tab === 'ambulance') ? 'none' : 'block';
        tourPackageFields.style.display = tab === 'tour' ? 'block' : 'none';
        ambulanceFields.style.display = tab === 'ambulance' ? 'block' : 'none';
        airportDirectionWrap.style.display = tab === 'airport' ? 'block' : 'none';
        localPackageWrap.style.display = tab === 'local' ? 'block' : 'none';
        flightNumberGroup.style.display = tab === 'airport' ? 'block' : 'none';
        returnDateGroup.style.display = tab === 'roundtrip' ? 'block' : 'none';
        returnTimeGroup.style.display = tab === 'roundtrip' ? 'block' : 'none';
        estimatedKmGroup.style.display = tab === 'roundtrip' ? 'block' : 'none';

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

        if (hiddenInput) hiddenInput.value = value;
        if (displaySpan) displaySpan.textContent = value;

        if (list) {
            list.querySelectorAll('.searchable-select-item').forEach(item => {
                const isSelected = item.dataset.value === value;
                item.classList.toggle('is-selected', isSelected);
                item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        }

        // Prevent identical pickup and drop
        if (sameLocationWarning) {
            if (pickupInput.value && destinationInput.value && pickupInput.value === destinationInput.value && currentTab !== 'local') {
                sameLocationWarning.style.display = 'block';
            } else {
                sameLocationWarning.style.display = 'none';
            }
        }
    }

    setupSearchableSelect('pickup');
    setupSearchableSelect('destination');

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
        });
    }

    // 7. Vehicle Category Selection
    document.querySelectorAll('input[name="vehicle_category"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('#vehicleCategoryChips .v-cat-chip').forEach(c => c.classList.remove('active'));
            const parent = this.closest('.v-cat-chip');
            if (parent) parent.classList.add('active');
            updateFareQuote();
        });
    });

    if (estimatedKmInput) {
        estimatedKmInput.addEventListener('input', updateFareQuote);
    }

    // 8. Real-Time Fare Quote Calculator
    function updateFareQuote() {
        const selectedVCat = document.querySelector('input[name="vehicle_category"]:checked');
        const vCat = selectedVCat ? selectedVCat.value : 'Sedan';
        const pickup = pickupInput.value || '';
        const destination = destinationInput.value || '';
        const category = formRateCategory.value;

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

        // Round Trip
        if (category === 'round_trip') {
            const key = `round_trip|||${vCat}`;
            const quote = rateOptions[key];

            if (quote && quote.details) {
                const rent = Number(quote.details.vehicle_rent || 0);
                const perKm = Number(quote.details.per_km_rate || 0);
                const estKm = Number(estimatedKmInput.value || 0);
                const total = rent + (estKm * perKm);

                fareLabel.textContent = 'Estimated Round-Trip Fare';
                farePrice.textContent = `₹${total.toLocaleString('en-IN')}`;
                fareInclusions.textContent = `₹${rent.toLocaleString('en-IN')} vehicle rent + ₹${perKm.toFixed(2)}/KM (${estKm} KM) · Driver allowance included`;
                fareBadge.textContent = 'Per KM Tariff';
                fareBadge.className = 'badge badge-assigned';
                submitText.textContent = 'Book Round Trip';
            } else {
                showUnconfiguredFare();
            }
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

                fareLabel.textContent = `${hrs} Hours / ${km} KM Package`;
                farePrice.textContent = `₹${amount.toLocaleString('en-IN')}`;
                fareInclusions.textContent = `City local usage (${hrs}h & ${km}km included)${extraKm}${extraHr}`;
                fareBadge.textContent = 'Fixed Package';
                fareBadge.className = 'badge badge-assigned';
                submitText.textContent = 'Book Local Rental';
            } else {
                showUnconfiguredFare();
            }
            return;
        }

        // One-Way & Airport Transfers
        let lookupCategory = category;
        if (pickup.includes('Airport') || destination.includes('Airport')) {
            lookupCategory = 'airport';
        }

        // Try exact key or opposite key
        let key = `${lookupCategory}|${pickup}|${destination}|${vCat}`;
        let quote = rateOptions[key];

        if (!quote && lookupCategory === 'airport') {
            key = `airport|${destination}|${pickup}|${vCat}`;
            quote = rateOptions[key];
        }

        if (!quote && lookupCategory !== 'outstation') {
            key = `outstation|${pickup}|${destination}|${vCat}`;
            quote = rateOptions[key];
        }

        if (quote && quote.details) {
            const amount = Number(quote.total_amount || 0);
            const km = quote.details.included_km ? `${quote.details.included_km} KM included · ` : '';
            const toll = quote.details.toll_type ? `Toll ${quote.details.toll_type}` : 'Tolls as per toll-plaza';
            const extraKm = quote.details.extra_km_rate ? ` · Extra ₹${quote.details.extra_km_rate}/KM` : '';

            fareLabel.textContent = 'Configured Route Fare';
            farePrice.textContent = `₹${amount.toLocaleString('en-IN')}`;
            fareInclusions.textContent = `${km}${toll}${extraKm} · Dedicated AC cab`;
            fareBadge.textContent = 'Fixed Fare';
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
        submitText.textContent = 'Check Fare & Reserve';
    }

    // 9. Prevent Duplicate Submission
    if (form) {
        form.addEventListener('submit', function(e) {
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

    // Initial calculation
    updateFareQuote();
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

