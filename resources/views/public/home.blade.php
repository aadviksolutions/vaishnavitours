@extends('layouts.app')

@section('title', 'Bilaspur Taxi & Cab Service | 24/7 Car Rental | Vaishnavi Tours')
@section('meta_description', 'Book trusted 24/7 taxi & cab service in Bilaspur, Chhattisgarh with Vaishnavi Tours. Clean AC cabs, outstation travel to Raipur & Korba, airport transfers, transparent pricing.')
@section('canonical', route('home'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'What taxi and cab services does Vaishnavi Tours provide in Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Vaishnavi Tours provides 24/7 local city taxi travel, outstation cabs from Bilaspur to all Chhattisgarh districts, dedicated airport pickup and drop transfers to Raipur Airport (RPR), and group rental coaches.'
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
            'name' => 'What is the starting per-kilometer rate for cab rental in Bilaspur?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Our verified rates start at ₹11/km for hatchbacks (Tiago/WagonR), ₹13/km for sedans (Dzire), ₹17/km for 6-seater MUVs (Ertiga), and ₹22/km for luxury Innova Crysta.'
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
                        <x-icon name="car-front" size="14" class="text-primary" style="margin-right: 4px;" /> 24/7 Bilaspur & Chhattisgarh Travel
                    </div>
                    <h1 class="hero-title">
                        Reliable 24/7 Taxi &amp; Cab <span>Service in Bilaspur</span>
                    </h1>
                    <p class="hero-desc">
                        Reliable taxi service for local and outstation travel.
                    </p>

                    <div class="d-flex gap-3 flex-wrap align-center">
                        <a href="#booking-card-section" class="btn btn-primary btn-lg">
                            <x-icon name="calendar-check" size="18" style="margin-right: 6px;" /> Book Your Taxi
                        </a>
                        <a href="#vehicles-fleet" class="btn btn-dark btn-lg" style="border: 1px solid rgba(255,255,255,0.2);">
                            <x-icon name="car" size="18" style="margin-right: 6px;" /> View Vehicles
                        </a>
                    </div>
                </div>

                <!-- Hero Right Media (Real Visual) -->
                <div>
                    <div class="hero-visual-wrap">
                        <img src="{{ asset('assets/images/hero-taxi.jpg') }}" alt="Vaishnavi Tours Taxi Service" class="hero-visual-img">
                        <div class="hero-visual-badge">
                            <div style="font-weight: 800; font-size: 0.95rem;">Vaishnavi Tours Bilaspur</div>
                            <div style="font-size: 0.775rem; color: var(--slate-300);">Doorstep pickup • Outstation & Local</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Booking Form Section -->
    <section style="padding: 3rem 0; background: var(--slate-50);" id="booking-card-section">
        <div class="container" style="max-width: 960px;">
            <div class="booking-hero-card" style="margin-top: -3.5rem; position: relative; z-index: 10;">
                <div class="d-flex justify-between align-center flex-wrap gap-2" style="margin-bottom: 1.25rem;">
                    <div>
                        <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--dark-900);">Book Your Taxi</h2>
                        <p style="font-size: 0.875rem; color: var(--slate-500); margin-top: 2px;">Instant doorstep reservation with verified chauffeur dispatch.</p>
                    </div>
                    <span class="badge badge-available" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                        <x-icon name="zap" size="14" style="margin-right: 4px;" /> Instant Booking
                    </span>
                </div>

                <form action="{{ route('booking.store') }}" method="POST" id="homeBookingForm">
                    @csrf
                    <input type="hidden" name="trip_type" value="One-Way">

                    <div class="grid grid-2 gap-3">
                        <div class="form-group">
                            <label class="form-label">Pickup Location</label>
                            <input type="text" name="pickup_location" class="form-control" placeholder="e.g. Mangal Chowk, Bilaspur" value="{{ old('pickup_location', 'Mangal Chowk, Bilaspur') }}" required>
                            @error('pickup_location') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Destination</label>
                            <input type="text" name="destination" class="form-control" placeholder="e.g. Swami Vivekananda Airport, Raipur" value="{{ old('destination') }}" required>
                            @error('destination') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-3 gap-3">
                        <div class="form-group">
                            <label class="form-label">Travel Date</label>
                            <input type="date" name="travel_date" class="form-control" value="{{ old('travel_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            @error('travel_date') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Travel Time</label>
                            <input type="time" name="travel_time" class="form-control" value="{{ old('travel_time', '09:00') }}" required>
                            @error('travel_time') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vehicle</label>
                            <select name="vehicle_id" class="form-select">
                                <option value="">-- Best Available Vehicle --</option>
                                @foreach($vehicles as $veh)
                                    <option value="{{ $veh->id }}" {{ old('vehicle_id') == $veh->id ? 'selected' : '' }}>
                                        {{ $veh->name }} ({{ $veh->vehicle_type }} • {{ $veh->seating_capacity }} Seats)
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-2 gap-3">
                        <div class="form-group">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Enter Full Name" value="{{ Auth::check() ? Auth::user()->name : old('customer_name') }}" required>
                            @error('customer_name') <div class="form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" name="mobile" class="form-control" placeholder="10-digit mobile number" value="{{ Auth::check() ? Auth::user()->phone : old('mobile') }}" required>
                            @error('mobile') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.5rem;">
                        Book Now <x-icon name="arrow-right" size="16" style="margin-left: 4px;" />
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- 3. Why Book With Us Section -->
    <section style="padding: 4.5rem 0; background: #FFFFFF;">
        <div class="container">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 3rem;">
                <span style="color: var(--primary-hover); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Service Guarantee</span>
                <h2 style="font-size: 2.25rem; margin-top: 0.35rem;">Why Book With Us</h2>
                <p style="color: var(--slate-500); margin-top: 0.5rem;">Committed to delivering safe, reliable, and punctual road travel in Bilaspur and beyond.</p>
            </div>

            <div class="grid grid-4 gap-3">
                <div class="why-card">
                    <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;"><x-icon name="clock-3" size="28" /></div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">24/7 Service</h3>
                    <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                        Around-the-clock taxi availability for midnight arrivals, early morning airport transfers, and emergency trips.
                    </p>
                </div>

                <div class="why-card">
                    <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;"><x-icon name="badge-check" size="28" /></div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">Experienced Drivers</h3>
                    <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                        Polite, route-knowledgeable chauffeurs thoroughly trained for highway and city driving throughout Chhattisgarh.
                    </p>
                </div>

                <div class="why-card">
                    <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;"><x-icon name="shield-check" size="28" /></div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">Clean Vehicles</h3>
                    <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                        Impeccably maintained, sanitized, and comfortable air-conditioned cabs ready for pleasant journeys.
                    </p>
                </div>

                <div class="why-card">
                    <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;"><x-icon name="receipt" size="28" /></div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">Transparent Pricing</h3>
                    <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                        Clear rates with no hidden charges or unexpected peak surge costs. Verified distance and rate calculation.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. How It Works Section -->
    <section style="padding: 4.5rem 0; background: var(--slate-50);">
        <div class="container">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 3rem;">
                <span style="color: var(--primary-hover); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Simple Process</span>
                <h2 style="font-size: 2.25rem; margin-top: 0.35rem;">How It Works</h2>
                <p style="color: var(--slate-500); margin-top: 0.5rem;">Three quick steps to confirm your comfortable chauffeur ride.</p>
            </div>

            <div class="grid grid-3 gap-3">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.35rem;">Enter Trip Details</h3>
                        <p style="font-size: 0.9rem; color: var(--slate-600); line-height: 1.6;">
                            Specify your pickup location, destination, date, time, and select your preferred vehicle.
                        </p>
                    </div>
                </div>

                <div class="step-card">
                    <div class="step-number">02</div>
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.35rem;">Confirm Booking</h3>
                        <p style="font-size: 0.9rem; color: var(--slate-600); line-height: 1.6;">
                            Receive your instant booking confirmation number and assigned chauffeur details directly.
                        </p>
                    </div>
                </div>

                <div class="step-card">
                    <div class="step-number">03</div>
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.35rem;">Travel Comfortably</h3>
                        <p style="font-size: 0.9rem; color: var(--slate-600); line-height: 1.6;">
                            Our driver arrives at your doorstep on time. Sit back, relax, and reach your destination safely.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Vehicle Fleet Section -->
    <section style="padding: 4.5rem 0; background: #FFFFFF;" id="vehicles-fleet">
        <div class="container">
            <div class="d-flex justify-between align-center flex-wrap gap-2" style="margin-bottom: 2.5rem;">
                <div>
                    <span style="color: var(--primary-hover); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Our Fleet</span>
                    <h2 style="font-size: 2.25rem; margin-top: 0.25rem;">Available Vehicles</h2>
                </div>
                <a href="{{ route('vehicles') }}" class="btn btn-outline btn-sm">
                    View Complete Fleet <x-icon name="arrow-right" size="16" style="margin-left: 4px;" />
                </a>
            </div>

            <div class="grid grid-3 gap-3">
                @foreach($vehicles as $vehicle)
                    <div class="vehicle-card">
                        <div class="vehicle-card-img">
                            <img src="{{ $vehicle->icon_url }}" alt="{{ $vehicle->name }}">
                        </div>
                        <div class="vehicle-card-body">
                            <div class="d-flex justify-between align-center" style="margin-bottom: 0.5rem;">
                                <h3 style="font-size: 1.2rem; font-weight: 800;">{{ $vehicle->name }}</h3>
                                <span class="badge {{ $vehicle->status === 'Available' ? 'badge-available' : 'badge-pending' }}">
                                    {{ $vehicle->status }}
                                </span>
                            </div>

                            <div style="display: flex; gap: 0.75rem; font-size: 0.825rem; color: var(--slate-600); margin-bottom: 0.75rem; flex-wrap: wrap;">
                                <span class="badge badge-outline" style="font-weight: 700;">{{ $vehicle->vehicle_type }}</span>
                                <span><x-icon name="users" size="14" style="margin-right: 4px;" /> {{ $vehicle->seating_capacity }} Seats</span>
                                <span>•</span>
                                <span><x-icon name="snowflake" size="14" style="margin-right: 4px;" /> {{ $vehicle->ac_non_ac }}</span>
                            </div>

                            <p style="font-size: 0.85rem; color: var(--slate-600); margin-bottom: 1.25rem; flex: 1; line-height: 1.5;">
                                {{ $vehicle->notes }}
                            </p>

                            <div class="d-flex justify-between align-center" style="border-top: 1px solid var(--slate-200); padding-top: 0.85rem;">
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--slate-400); text-transform: uppercase;">Tariff from</div>
                                    <div style="font-size: 1.2rem; font-weight: 800; color: var(--dark-900);">
                                        ₹{{ number_format($vehicle->per_km_rate, 0) }} <span style="font-size: 0.8rem; font-weight: 500; color: var(--slate-500);">/ km</span>
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

    <!-- 6. Emergency / Ambulance Service Section -->
    <section class="emergency-banner" id="emergency-service">
        <div class="container">
            <div class="grid grid-2 gap-4 align-center">
                <div>
                    <div style="background: rgba(245, 158, 11, 0.2); display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">
                        <x-icon name="life-buoy" size="16" style="margin-right: 4px;" /> Standby Road Assistance
                    </div>
                    <h2 style="color: #FFFFFF; font-size: 2.25rem; margin-bottom: 1rem;">
                        Emergency & Ambulance Service
                    </h2>
                    <p style="font-size: 1.05rem; color: var(--slate-300); margin-bottom: 1.5rem; line-height: 1.7;">
                        Priority patient transport and urgent hospital transfers from Bilaspur to medical centers, Apollo Bilaspur, CIMS, and AIIMS Raipur. Dedicated dispatch on active standby.
                    </p>
                    <ul style="color: var(--slate-300); font-size: 0.95rem; margin-bottom: 2rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li style="display: flex; align-items: center; gap: 8px;"><x-icon name="circle-check" size="16" class="text-primary" style="flex-shrink: 0;" /><span>Rapid medical hospital pick and drop service</span></li>
                        <li style="display: flex; align-items: center; gap: 8px;"><x-icon name="circle-check" size="16" class="text-primary" style="flex-shrink: 0;" /><span>Direct highway connectivity between Bilaspur and Raipur hospitals</span></li>
                        <li style="display: flex; align-items: center; gap: 8px;"><x-icon name="circle-check" size="16" class="text-primary" style="flex-shrink: 0;" /><span>Patient-friendly, careful chauffeurs available 24/7</span></li>
                    </ul>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="btn btn-primary btn-lg">
                            <x-icon name="phone" size="18" style="margin-right: 6px;" /> Call Us: {{ config('vaishnavi.phone_primary') }}
                        </a>
                        <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" class="btn btn-outline btn-lg" style="color: #fff; border-color: rgba(255,255,255,0.4); text-decoration: none;">
                            <x-icon.whatsapp size="20" style="margin-right: 6px;" /> Chat on WhatsApp
                        </a>
                    </div>
                </div>

                <div>
                    <div class="emergency-visual-wrap">
                        <img src="{{ asset('assets/images/emergency-ambulance.jpg') }}" alt="Emergency and Ambulance Transport Service" class="emergency-visual-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. About Section -->
    <section style="padding: 4.5rem 0; background: #FFFFFF;">
        <div class="container">
            <div class="grid grid-2 gap-4 align-center">
                <div>
                    <div class="about-visual-wrap">
                        <img src="{{ asset('assets/images/about-travel.jpg') }}" alt="About Vaishnavi Tours Chauffeur Service" class="about-visual-img">
                    </div>
                </div>

                <div>
                    <span style="color: var(--primary-hover); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">About Vaishnavi Tours</span>
                    <h2 style="font-size: 2.25rem; margin-top: 0.35rem; margin-bottom: 1rem;">
                        Your Trusted Travel Partner in Bilaspur
                    </h2>
                    <p style="font-size: 1.05rem; color: var(--slate-700); line-height: 1.7; margin-bottom: 1rem;">
                        Vaishnavi Tours provides dedicated 24/7 taxi and cab services headquartered in Bilaspur, Chhattisgarh. We offer comprehensive local city rentals, outstation trips, airport transfers, and emergency medical travel assistance.
                    </p>
                    <p style="font-size: 0.95rem; color: var(--slate-600); line-height: 1.7; margin-bottom: 1.5rem;">
                        Our fleet features clean, sanitized, and well-maintained sedans, MUVs, SUVs, and travellers. With professional, verified drivers focused on customer comfort and safety, we make every road journey reliable and hassle-free.
                    </p>

                    <div style="background: var(--slate-50); padding: 1.25rem; border-radius: var(--radius-md); border-left: 4px solid var(--primary); margin-bottom: 1.75rem;">
                        <strong style="color: var(--dark-900); display: block; margin-bottom: 0.25rem;">Location:</strong>
                        <span style="color: var(--slate-600); font-size: 0.9rem;">
                            {{ config('vaishnavi.address') }}
                        </span>
                    </div>

                    <a href="{{ route('about') }}" class="btn btn-outline">
                        Learn More About Us <x-icon name="arrow-right" size="16" style="margin-left: 4px;" />
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. Testimonials Section -->
    @if($feedbacks->isNotEmpty())
        <section style="padding: 4rem 0 5rem; background: var(--slate-50);">
            <div class="container">
                <div style="text-align: center; max-width: 600px; margin: 0 auto 2.5rem;">
                    <span style="color: var(--primary-hover); font-weight: 800; text-transform: uppercase; font-size: 0.85rem;">Customer Experiences</span>
                    <h2 style="font-size: 2.15rem; margin-top: 0.35rem;">What Travelers Say</h2>
                </div>

                <div class="grid grid-3 gap-3">
                    @foreach($feedbacks as $fb)
                        <div class="card" style="padding: 1.5rem;">
                            <div style="color: #F59E0B; font-size: 1rem; margin-bottom: 0.5rem;">
                                @for($i = 1; $i <= 5; $i++)
                                        <x-icon name="star" size="16" style="fill: {{ $i <= $fb->rating ? '#F59E0B' : 'none' }}; color: {{ $i <= $fb->rating ? '#F59E0B' : '#CBD5E1' }};" />
                                    @endfor
                            </div>
                            <p style="font-size: 0.9rem; color: var(--slate-700); line-height: 1.6; margin-bottom: 1rem;">
                                "{{ $fb->comment }}"
                            </p>
                            <div style="font-weight: 700; font-size: 0.9rem; color: var(--dark-900);">{{ $fb->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--slate-400);">Verified Traveler</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    <!-- Local Travel & FAQ Section -->
    <section style="padding: 4.5rem 0; background: #FFFFFF;" id="faqs-section">
        <div class="container" style="max-width: 900px;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Got Questions?</div>
                <h2 style="font-size: 2.15rem; font-weight: 800; color: var(--dark-900);">Common Questions About Bilaspur Taxi Booking</h2>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="card" style="padding: 1.5rem; border-radius: var(--radius-md);">
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">What taxi and cab services does Vaishnavi Tours provide in Bilaspur?</h3>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin: 0;">Vaishnavi Tours provides 24/7 local city taxi travel, outstation cabs from Bilaspur to all Chhattisgarh districts, dedicated airport pickup and drop transfers to Raipur Airport (RPR), and group rental coaches.</p>
                </div>
                <div class="card" style="padding: 1.5rem; border-radius: var(--radius-md);">
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">How can I book an outstation cab from Bilaspur to Raipur or Korba?</h3>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin: 0;">You can book directly through our <a href="{{ route('booking') }}" style="color: var(--primary-dark); font-weight: 700;">online reservation form</a>, call our 24/7 dispatch desk at <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: var(--primary-dark); font-weight: 700;">{{ config('vaishnavi.phone_primary') }}</a>, or chat with us on WhatsApp.</p>
                </div>
                <div class="card" style="padding: 1.5rem; border-radius: var(--radius-md);">
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">What are the starting taxi fares per kilometer in Bilaspur?</h3>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin: 0;">Our verified rates start at ₹11/km for hatchbacks (Tiago/WagonR), ₹13/km for sedans (Dzire), ₹17/km for 6-seater MUVs (Ertiga), and ₹22/km for luxury Innova Crysta. Check our full <a href="{{ route('rates') }}" style="color: var(--primary-dark); font-weight: 700;">Rate Card</a> for transparent details.</p>
                </div>
                <div class="card" style="padding: 1.5rem; border-radius: var(--radius-md);">
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">Do you provide airport transfer from Bilaspur to Raipur Airport?</h3>
                    <p style="color: var(--slate-600); font-size: 0.95rem; line-height: 1.6; margin: 0;">Yes, we provide dedicated <a href="{{ route('services.airport-transfer') }}" style="color: var(--primary-dark); font-weight: 700;">Airport Transfer Service</a> with punctual doorstep pickup in Bilaspur and terminal drop at Raipur Airport (~135 km via NH 130) with flight schedule coordination.</p>
                </div>
            </div>
        </div>
    </section>

@endsection
