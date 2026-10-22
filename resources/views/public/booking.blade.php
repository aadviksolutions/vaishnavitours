@extends('layouts.app')

@section('title', 'Book a Taxi & Outstation Cabs | Vaishnavi Tours')
@section('meta_description', 'Book airport, outstation, round-trip, or local taxi services with Vaishnavi Tours. Verified drivers and transparent billing.')
@section('canonical', route('booking'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Book a Taxi', 'item' => route('booking')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
@php
    $defaultRateCategory = old('rate_category', request('rate_category', 'outstation'));
    $supportPhone = \App\Models\Setting::get('phone_primary', config('vaishnavi.phone_primary', '9244784443'));
    $cleanPhone = preg_replace('/[^0-9]/', '', $supportPhone);
    $supportTel = (strlen($cleanPhone) === 10) ? '+91' . $cleanPhone : (str_starts_with($cleanPhone, '91') ? '+' . $cleanPhone : '+91' . $cleanPhone);
    $tripOptions = [
        ['trip_type' => 'One-Way', 'category' => 'outstation', 'title' => 'One-Way Taxi', 'description' => 'Direct city-to-city drop', 'icon' => 'arrow-right'],
        ['trip_type' => 'Round-Trip', 'category' => 'round_trip', 'title' => 'Round Trip', 'description' => 'Pickup · Via · Final Drop', 'icon' => 'repeat'],
        ['trip_type' => 'Airport Transfer', 'category' => 'airport', 'title' => 'Airport Transfer', 'description' => 'Raipur Airport ↔ Bilaspur', 'icon' => 'plane'],
        ['trip_type' => 'Local 4 Hours', 'category' => 'local_4h_40km', 'title' => 'Local (4h / 40km)', 'description' => 'Half-day city rental', 'icon' => 'clock'],
        ['trip_type' => 'Local 8 Hours', 'category' => 'local_8h_80km', 'title' => 'Local (8h / 80km)', 'description' => 'Full-day city rental', 'icon' => 'clock-3'],
        ['trip_type' => 'Ambulance Service', 'category' => 'emergency', 'title' => 'Ambulance (24/7)', 'description' => 'Emergency dispatch', 'icon' => 'heart'],
    ];
@endphp

<section style="padding: 2.25rem 0 3.5rem;">
    <div class="container">
        <div style="max-width: 860px; margin: 0 auto;">
            <!-- Urgent / Late-Night 4-Hour Notice Banner -->
            <div class="alert alert-info" style="margin-bottom: 1.5rem; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; padding: 0.85rem 1rem; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <x-icon name="clock" size="18" class="text-blue-600" />
                    <span style="font-size: 13px; font-weight: 500;">
                        Online bookings require at least <strong>4 hours advance notice</strong>.
                    </span>
                </div>
                <div style="font-size: 13px;">
                    For urgent or late-night bookings, please call us: 
                    <a href="tel:{{ $supportTel }}" style="color: #1E40AF; font-weight: 700; text-decoration: underline;">{{ $supportPhone }}</a>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 1.5rem;">
                <span style="color: var(--accent-gold); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.8px;">Direct Reservation & Quotations</span>
                <h1 style="font-size: 1.65rem; font-weight: 700; margin-top: 0.25rem; color: var(--dark-900);">Book Your Cab with Vaishnavi Tours</h1>
                <p style="color: var(--slate-500); font-size: 0.875rem; margin-top: 0.35rem;">Clean air-conditioned fleet, polite experienced chauffeurs, and transparent GST invoices.</p>
            </div>

            <!-- =========================================================================
                 VAISHNAVI TOURS — PROMINENT BOOKING SUPPORT BANNER
                 ========================================================================= -->
            <div class="booking-support-banner" style="background: linear-gradient(135deg, #0F2744 0%, #16365C 100%); border: 1.5px solid #C59B27; border-radius: 10px; padding: 1.15rem 1.35rem; margin-bottom: 1.75rem; box-shadow: 0 4px 16px rgba(15, 39, 68, 0.15); color: #ffffff;">
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

            <div class="card" style="padding: 1.5rem; border: 1px solid var(--slate-200); box-shadow: var(--shadow-sm); border-radius: 8px; background: #fff;">
                @if($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 1.25rem; background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px;">
                        <strong style="display: flex; align-items: center; gap: 6px;"><x-icon name="alert-circle" size="16" /><span>Please check the following details:</span></strong>
                        <ul style="margin: 0.35rem 0 0 1.25rem; padding: 0; font-size: 13px;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
                    @csrf

                    <input type="hidden" name="rate_category" id="rateCategory" value="{{ $defaultRateCategory }}">
                    <input type="hidden" name="is_custom_query" id="isCustomQuery" value="{{ old('is_custom_query', '0') }}">

                    <!-- Google Maps & Routing Hidden Coordinate Fields -->
                    <input type="hidden" name="pickup_latitude" id="pickupLatitude" value="{{ old('pickup_latitude') }}">
                    <input type="hidden" name="pickup_longitude" id="pickupLongitude" value="{{ old('pickup_longitude') }}">
                    <input type="hidden" name="pickup_place_id" id="pickupPlaceId" value="{{ old('pickup_place_id') }}">

                    <input type="hidden" name="via_latitude" id="viaLatitude" value="{{ old('via_latitude') }}">
                    <input type="hidden" name="via_longitude" id="viaLongitude" value="{{ old('via_longitude') }}">
                    <input type="hidden" name="via_place_id" id="viaPlaceId" value="{{ old('via_place_id') }}">

                    <input type="hidden" name="destination_latitude" id="destinationLatitude" value="{{ old('destination_latitude') }}">
                    <input type="hidden" name="destination_longitude" id="destinationLongitude" value="{{ old('destination_longitude') }}">
                    <input type="hidden" name="destination_place_id" id="destinationPlaceId" value="{{ old('destination_place_id') }}">

                    <input type="hidden" name="route_distance_km" id="routeDistanceKm" value="{{ old('route_distance_km') }}">
                    <input type="hidden" name="route_duration_text" id="routeDurationText" value="{{ old('route_duration_text') }}">
                    <input type="hidden" name="route_duration_minutes" id="routeDurationMinutes" value="{{ old('route_duration_minutes') }}">
                    <input type="hidden" name="route_geometry" id="routeGeometry" value="{{ old('route_geometry') }}">

                    <!-- 1. Trip Type -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">1. Trip Type</label>
                        <div class="grid grid-3 gap-2" id="bookingTripTypes">
                            @foreach($tripOptions as $option)
                                <label style="border: 1px solid var(--slate-200); padding: 0.65rem 0.75rem; border-radius: 6px; text-align: center; cursor: pointer; display: block; transition: all 0.15s ease;" class="trip-opt {{ $defaultRateCategory === $option['category'] ? 'active ring-2 ring-blue-500 bg-blue-50 border-blue-400' : '' }}">
                                    <input type="radio" name="trip_type" value="{{ $option['trip_type'] }}" data-rate-category="{{ $option['category'] }}" {{ $defaultRateCategory === $option['category'] ? 'checked' : '' }} required style="display: none;">
                                    <div class="icon-box icon-box-sm icon-box-primary" style="margin: 0 auto 0.35rem; width: 28px; height: 28px;"><x-icon :name="$option['icon']" size="14" /></div>
                                    <div style="font-weight: 600; font-size: 0.85rem; margin-top: 0.2rem; color: var(--dark-900);">{{ $option['title'] }}</div>
                                    <div style="font-size: 0.72rem; color: var(--slate-500);">{{ $option['description'] }}</div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 2. Route Details (Pickup, Via, Drop) -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <div class="d-flex justify-between align-center mb-1">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin: 0;">2. Route & Journey Details</label>
                            <!-- ADD NEW LOCATION button -->
                            <button type="button" id="btnOpenNewLocationModal" class="btn btn-sm btn-outline" style="font-size: 11px; padding: 2px 8px; color: #2563EB; border-color: #93C5FD;">
                                + Add New Location
                            </button>
                        </div>

                        <div class="grid grid-3 gap-2" id="routeFieldsGrid">
                            <!-- 1. Pickup Location -->
                            <div>
                                <div class="d-flex justify-between align-center mb-1">
                                    <label for="pickupLocation" style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; margin: 0;">
                                        1. Pickup Location *
                                    </label>
                                    <button type="button" class="btn btn-xs btn-outline btn-select-map" data-target="pickup" style="font-size: 10.5px; padding: 1px 6px; color: #2563EB; border-color: #BFDBFE; display: inline-flex; align-items: center; gap: 3px; border-radius: 4px; background: #EFF6FF;">
                                        🗺️ Map
                                    </button>
                                </div>
                                <select id="pickupLocation" name="pickup_location" class="form-select form-select-sm" required style="width: 100%;">
                                    <option value="">Select pickup city</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}" {{ old('pickup_location', request('pickup_location')) === $location ? 'selected' : '' }}>{{ $location }}</option>
                                    @endforeach
                                    <option value="Other" {{ old('pickup_location') === 'Other' || old('pickup_other') ? 'selected' : '' }}>➕ Other Location (Specify below)</option>
                                </select>
                                <div id="pickupOtherGroup" style="display: {{ old('pickup_other') || old('pickup_location') === 'Other' ? 'block' : 'none' }}; margin-top: 0.35rem;">
                                    <div class="d-flex gap-1">
                                        <input type="text" name="pickup_other" id="pickupOtherInput" class="form-control form-control-sm" placeholder="Enter custom pickup city / area..." value="{{ old('pickup_other') }}" style="border-color: #3B82F6; background: #F0F9FF;">
                                        <button type="button" class="btn btn-xs btn-primary btn-select-map" data-target="pickup_other" style="padding: 2px 8px; font-size: 11px; white-space: nowrap;">🗺️ Pick</button>
                                    </div>
                                    <div style="font-size: 10.5px; color: #1E40AF; margin-top: 2px;">* Custom location: No immediate fare shown. Quote will be sent by dispatch.</div>
                                </div>
                            </div>

                            <!-- 2. Via Location (Required principally for Round-Trip) -->
                            <div id="viaLocationGroup">
                                <div class="d-flex justify-between align-center mb-1">
                                    <label for="viaLocation" style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; margin: 0;">
                                        2. Via Location <span style="font-weight: 400; color: var(--slate-400);">(Round-Trip)</span>
                                    </label>
                                    <button type="button" class="btn btn-xs btn-outline btn-select-map" data-target="via" style="font-size: 10.5px; padding: 1px 6px; color: #2563EB; border-color: #BFDBFE; display: inline-flex; align-items: center; gap: 3px; border-radius: 4px; background: #EFF6FF;">
                                        🗺️ Map
                                    </button>
                                </div>
                                <input type="text" id="viaLocation" name="via_location" class="form-control form-control-sm" placeholder="e.g. Amarkantak, Ratanpur..." value="{{ old('via_location') }}">
                            </div>

                            <!-- 3. Final Drop Location -->
                            <div>
                                <div class="d-flex justify-between align-center mb-1">
                                    <label for="destinationLocation" style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; margin: 0;">
                                        3. Final Drop Location *
                                    </label>
                                    <button type="button" class="btn btn-xs btn-outline btn-select-map" data-target="destination" style="font-size: 10.5px; padding: 1px 6px; color: #2563EB; border-color: #BFDBFE; display: inline-flex; align-items: center; gap: 3px; border-radius: 4px; background: #EFF6FF;">
                                        🗺️ Map
                                    </button>
                                </div>
                                <select id="destinationLocation" name="destination" class="form-select form-select-sm" required style="width: 100%;">
                                    <option value="">Select final drop</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}" {{ old('destination', request('destination')) === $location ? 'selected' : '' }}>{{ $location }}</option>
                                    @endforeach
                                    <option value="Other" {{ old('destination') === 'Other' || old('destination_other') ? 'selected' : '' }}>➕ Other Location (Specify below)</option>
                                </select>
                                <div id="destinationOtherGroup" style="display: {{ old('destination_other') || old('destination') === 'Other' ? 'block' : 'none' }}; margin-top: 0.35rem;">
                                    <div class="d-flex gap-1">
                                        <input type="text" name="destination_other" id="destinationOtherInput" class="form-control form-control-sm" placeholder="Enter custom drop city / area..." value="{{ old('destination_other') }}" style="border-color: #3B82F6; background: #F0F9FF;">
                                        <button type="button" class="btn btn-xs btn-primary btn-select-map" data-target="destination_other" style="padding: 2px 8px; font-size: 11px; white-space: nowrap;">🗺️ Pick</button>
                                    </div>
                                    <div style="font-size: 10.5px; color: #1E40AF; margin-top: 2px;">* Custom location: No immediate fare shown. Quote will be sent by dispatch.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Interactive Route Map & Road Distance Preview Container -->
                        <div id="bookingRouteMapContainer" style="display: none; margin-top: 0.9rem; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; background: #ffffff;">
                            <div style="padding: 0.65rem 0.9rem; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 16px;">🛣️</span>
                                    <div>
                                        <strong style="font-size: 12.5px; color: #0F172A;" id="routeMapTitle">Road Route & Planned Distance</strong>
                                        <div style="font-size: 10.5px; color: #64748B;" id="routeMapSubtitle">Genuine highway driving path</div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="background: #EFF6FF; border: 1px solid #BFDBFE; padding: 2px 8px; border-radius: 4px; font-size: 12px; color: #1D4ED8; font-weight: 700;">
                                        <span id="routeDistanceDisplay">-- KM</span>
                                    </div>
                                    <div style="background: #F1F5F9; border: 1px solid #E2E8F0; padding: 2px 8px; border-radius: 4px; font-size: 11.5px; color: #334155; font-weight: 600;" id="routeDurationBadge">
                                        <span id="routeDurationDisplay">--</span>
                                    </div>
                                </div>
                            </div>

                            <div style="position: relative; height: 240px; width: 100%; background: #E2E8F0;">
                                <div id="bookingRouteMapCanvas" style="width: 100%; height: 100%;"></div>
                                <div id="routeCalcSpinner" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.75); z-index: 500; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 600; color: #2563EB;">
                                    <span>🔄 Calculating road route & distance...</span>
                                </div>
                            </div>

                            <!-- Error notice if route calculation fails -->
                            <div id="routeCalcErrorBanner" style="display: none; padding: 6px 10px; background: #FEF2F2; border-top: 1px solid #FECACA; color: #991B1B; font-size: 11.5px; align-items: center; justify-content: space-between;">
                                <span id="routeCalcErrorMsg">Could not calculate road distance between selected locations.</span>
                                <button type="button" id="btnRetryRouteCalc" class="btn btn-xs btn-outline" style="border-color: #F87171; color: #991B1B; padding: 1px 6px; font-size: 10.5px;">Retry</button>
                            </div>

                            <!-- Route legs breakdown -->
                            <div id="routeLegsContainer" style="padding: 0.5rem 0.9rem; background: #FAFAFA; border-top: 1px solid #E2E8F0; font-size: 11px; display: flex; gap: 6px; flex-wrap: wrap;"></div>
                        </div>
                    </div>

                    <!-- 3. Date, Time & Duration -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">3. Travel Schedule</label>
                        <div class="grid grid-3 gap-2">
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">Pickup Date *</label>
                                <input type="date" name="travel_date" id="travelDateInput" class="form-control form-control-sm" value="{{ old('travel_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">Pickup Time *</label>
                                <input type="time" name="travel_time" id="travelTimeInput" class="form-control form-control-sm" value="{{ old('travel_time', '08:00') }}" required>
                            </div>
                            <div id="returnDateGroup">
                                <label style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">Return Date / Trip End</label>
                                <input type="date" name="return_date" id="returnDateInput" class="form-control form-control-sm" value="{{ old('return_date') }}" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Vehicle Category Selection -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">4. Vehicle Category Preference *</label>
                        <div class="grid grid-3 gap-2">
                            @if(isset($activeVehicleCategories) && $activeVehicleCategories->isNotEmpty())
                                @foreach($activeVehicleCategories as $cat)
                                    <label style="border: 1px solid var(--slate-200); padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.15s ease;" 
                                           class="vehicle-category-opt {{ old('vehicle_category', request('vehicle_category', $loop->first ? $cat->name : '')) === $cat->name ? 'active ring-2 ring-blue-500 bg-blue-50 border-blue-400' : '' }}"
                                           data-category-id="{{ $cat->id }}"
                                           data-category-name="{{ $cat->name }}"
                                           data-models="{{ json_encode($cat->activeModels) }}">
                                        <input type="radio" name="vehicle_category" value="{{ $cat->name }}" {{ old('vehicle_category', request('vehicle_category', $loop->first ? $cat->name : '')) === $cat->name ? 'checked' : '' }} required>
                                        <div>
                                            <span style="font-weight: 700; font-size: 0.85rem; color: var(--dark-900); display: block;">{{ $cat->name }}</span>
                                            <span style="font-size: 11px; color: var(--slate-500);">
                                                @if($cat->activeModels->isNotEmpty())
                                                    {{ $cat->activeModels->pluck('name')->implode(', ') }} ({{ $cat->is_capacity_configurable ? $cat->min_seating_capacity.'-'.$cat->max_seating_capacity : $cat->default_seating_capacity }} Seats)
                                                @else
                                                    {{ $cat->is_capacity_configurable ? 'Configurable '.$cat->min_seating_capacity.'-'.$cat->max_seating_capacity.' Seats' : $cat->default_seating_capacity.' Seats' }}
                                                @endif
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            @else
                                @forelse($vehicleCategories as $category)
                                    <label style="border: 1px solid var(--slate-200); padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.15s ease;" class="vehicle-category-opt">
                                        <input type="radio" name="vehicle_category" value="{{ $category }}" {{ old('vehicle_category', request('vehicle_category')) === $category ? 'checked' : '' }} required>
                                        <div>
                                            <span style="font-weight: 600; font-size: 0.85rem; color: var(--dark-900); display: block;">{{ $category }}</span>
                                            <span style="font-size: 11px; color: var(--slate-500);">AC Commercial Cab</span>
                                        </div>
                                    </label>
                                @empty
                                    <div style="grid-column: 1 / -1; padding: 1.25rem; background: var(--slate-100); border: 1px dashed var(--slate-300); border-radius: 6px; text-align: center; color: var(--slate-600);">
                                        <p style="margin-bottom: 0.35rem; font-weight: 600; color: var(--dark-900); font-size: 0.95rem;">Fleet rates configuring...</p>
                                        <a href="tel:+919131613010" class="btn btn-primary btn-sm">Call Dispatch directly</a>
                                    </div>
                                @endforelse
                            @endif
                        </div>

                        <!-- Optional Vehicle Model selection box -->
                        <div id="bookingModelSelectionBox" style="display: none; margin-top: 0.75rem; background: #F8FAFC; border: 1px solid var(--slate-200); border-radius: 6px; padding: 0.65rem 0.85rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                <label style="font-size: 11.5px; font-weight: 700; color: var(--dark-900); margin: 0;">
                                    🚗 Preferred Vehicle Model (Optional)
                                </label>
                                <span style="font-size: 10.5px; color: var(--slate-500);">Choose specific car or leave as any</span>
                            </div>
                            <div id="bookingModelChipsWrap" style="display: flex; gap: 6px; flex-wrap: wrap;"></div>
                            <input type="hidden" name="vehicle_category_id" id="bookingVehicleCategoryId" value="{{ old('vehicle_category_id') }}">
                            <input type="hidden" name="vehicle_model_id" id="bookingVehicleModelId" value="{{ old('vehicle_model_id') }}">
                            <input type="hidden" name="vehicle_model" id="bookingVehicleModel" value="{{ old('vehicle_model') }}">
                        </div>
                    </div>

                    <!-- Estimated KM (Only for Round-Trip Query) -->
                    <div id="estimatedKmGroup" class="form-group" style="margin-bottom: 1.25rem; display: none;">
                        <label for="estimatedKm" class="form-label" style="font-weight: 600; font-size: 13px;">Estimated Planned Distance (KM) <span style="font-weight: 400; color: var(--slate-400);">(Optional)</span></label>
                        <input type="number" name="estimated_km" id="estimatedKm" class="form-control form-control-sm" min="0.01" step="0.1" placeholder="e.g. 450" value="{{ old('estimated_km') }}">
                    </div>

                    <!-- CRITICAL RULE: NO AUTOMATIC FARE ESTIMATE FOR ROUND-TRIP -->
                    <!-- Standard Fixed Fare Preview (Only for One-Way, Airport, Local) -->
                    <div id="standardRatePreview" class="card" style="padding: 1rem; margin-bottom: 1.25rem; border-left: 3.5px solid var(--primary); background: #f8fafc;" aria-live="polite">
                        <div style="font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--slate-500);">Applicable Fixed Fare</div>
                        <div id="ratePreviewAmount" style="font-size: 1.25rem; font-weight: 700; margin: 0.25rem 0; color: var(--dark-900);">Select route and vehicle</div>
                        <div id="ratePreviewDetails" style="font-size: 0.8rem; color: var(--slate-600);"></div>
                    </div>

                    <!-- Round-Trip Custom Quotation Notice (STRICTLY NO AUTOMATIC PRICE SHOWN) -->
                    <div id="roundTripQuotationNotice" class="card" style="padding: 1rem; margin-bottom: 1.25rem; border-left: 3.5px solid #D97706; background: #FFFBEB; display: none;">
                        <div style="font-weight: 700; font-size: 0.85rem; color: #92400E; display: flex; align-items: center; gap: 6px;">
                            <x-icon name="send" size="15" /> Round-Trip Custom Quotation Request
                        </div>
                        <p style="font-size: 12.5px; color: #B45309; margin: 0.25rem 0 0 0; line-height: 1.45;">
                            Round-trip and multi-city journey fares depend on exact route, halt duration, and vehicle availability. 
                            Submit your trip enquiry and our reservations desk will review and provide a customized best-price quotation directly to you.
                        </p>
                    </div>

                    <!-- 5. Passenger Information -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">5. Customer Contact Information</label>
                        <div class="grid grid-3 gap-2">
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">Your Full Name *</label>
                                <input type="text" name="customer_name" class="form-control form-control-sm" placeholder="Full Name" value="{{ Auth::check() ? Auth::user()->name : old('customer_name') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">Mobile Number *</label>
                                <input type="tel" name="mobile" class="form-control form-control-sm" placeholder="10-digit mobile" value="{{ Auth::check() ? Auth::user()->phone : old('mobile') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">Email Address (Optional)</label>
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="email@example.com" value="{{ Auth::check() ? Auth::user()->email : old('email') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Special Instructions / Luggage Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. 3 large bags, AC temperature preference, flight number, patient assistance, etc.">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Terms & Conditions Acceptance -->
                    <div class="form-group" style="background: #ffffff; padding: 0.85rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px; margin-bottom: 1.25rem;">
                        <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; margin: 0; font-size: 0.85rem; color: var(--dark-900); font-weight: 500; line-height: 1.45;">
                            <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" {{ old('terms_accepted', '1') ? 'checked' : '' }} required style="width: 1.1rem; height: 1.1rem; margin-top: 0.1rem; accent-color: var(--primary); cursor: pointer; flex-shrink: 0;">
                            <span>
                                I agree to Vaishnavi Tours 
                                <a href="{{ route('terms-and-conditions') }}" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Terms & Conditions</a> and 
                                <a href="{{ route('cancellation-refund-policy') }}" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Cancellation Policy</a>.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button Row -->
                    <div style="background: #F8FAFC; border: 1px solid var(--slate-200); padding: 1rem 1.25rem; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: var(--dark-900);" id="submitHeaderTitle">Confirmed Booking Dispatch</div>
                            <div style="font-size: 0.775rem; color: var(--slate-600);" id="submitHeaderSub">Standard 5% GST tax billing applicable.</div>
                        </div>
                        <button type="submit" id="btnSubmitBooking" class="btn btn-primary" style="font-size: 0.95rem; font-weight: 700; padding: 0.55rem 1.75rem; height: 42px;">
                            Confirm Booking
                        </button>
                    </div>

                    <!-- Compact Booking Form Assistance Help Message -->
                    <div class="booking-submit-help" style="margin-top: 0.85rem; padding: 0.65rem 1rem; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 6px; text-align: center; font-size: 0.85rem; color: #92400E; display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                        <span>Facing an issue with your booking? Call us at <a href="tel:{{ $supportTel }}" style="color: #B45309; font-weight: 700; text-decoration: underline;">{{ $supportPhone }}</a>.</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Inline Modal: Add New Location (Requirement: Section 6) -->
<div id="newLocationModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 440px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--dark-900);">Add New Pickup Location</h3>
            <button type="button" id="btnCloseNewLocationModal" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="quickLocationForm">
            <div style="margin-bottom: 0.75rem;">
                <label style="font-size: 12px; font-weight: 600; color: var(--slate-700); display: block; margin-bottom: 2px;">City / Area Name *</label>
                <input type="text" id="newLocName" class="form-control form-control-sm" placeholder="e.g. Korba, Champa, Janjgir..." required>
            </div>
            <div style="margin-bottom: 0.75rem;">
                <label style="font-size: 12px; font-weight: 600; color: var(--slate-700); display: block; margin-bottom: 2px;">State</label>
                <input type="text" id="newLocState" class="form-control form-control-sm" value="Chhattisgarh">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 12px; font-weight: 600; color: var(--slate-700); display: block; margin-bottom: 2px;">Address / Landmark (Optional)</label>
                <input type="text" id="newLocAddress" class="form-control form-control-sm" placeholder="Railway station, bus stand, etc.">
            </div>
            <div class="d-flex justify-end gap-2">
                <button type="button" id="btnCancelLocationModal" class="btn btn-secondary btn-sm">Cancel</button>
                <button type="submit" id="btnSaveNewLocation" class="btn btn-primary btn-sm">Save & Select Location</button>
            </div>
        </form>
    </div>
</div>

<x-location-picker-modal />
@endsection

@push('scripts')
<script>
    const rateOptions = @json($rateOptions);
    const categoryInput = document.querySelector('#rateCategory');
    const pickupInput = document.querySelector('#pickupLocation');
    const destinationInput = document.querySelector('#destinationLocation');
    const viaLocationInput = document.querySelector('#viaLocation');
    const estimatedKmGroup = document.querySelector('#estimatedKmGroup');
    const estimatedKmInput = document.querySelector('#estimatedKm');
    const standardRatePreview = document.querySelector('#standardRatePreview');
    const roundTripQuotationNotice = document.querySelector('#roundTripQuotationNotice');
    const previewAmount = document.querySelector('#ratePreviewAmount');
    const previewDetails = document.querySelector('#ratePreviewDetails');
    const btnSubmitBooking = document.querySelector('#btnSubmitBooking');
    const submitHeaderTitle = document.querySelector('#submitHeaderTitle');
    const submitHeaderSub = document.querySelector('#submitHeaderSub');

    // 4-hour advance booking validation
    const travelDateInput = document.querySelector('#travelDateInput');
    const travelTimeInput = document.querySelector('#travelTimeInput');

    function checkFourHourNotice() {
        if (!travelDateInput.value || !travelTimeInput.value) return;
        const pickupDateTime = new Date(`${travelDateInput.value}T${travelTimeInput.value}:00`);
        const now = new Date();
        const diffHours = (pickupDateTime - now) / (1000 * 60 * 60);

        if (diffHours < 3.9) {
            alert('Notice: Website bookings must be scheduled at least 4 hours in advance.\n\nFor urgent or late-night bookings, please call our 24/7 hotline directly: {{ $supportPhone }}.');
        }
    }

    travelTimeInput.addEventListener('change', checkFourHourNotice);
    travelDateInput.addEventListener('change', checkFourHourNotice);

    const pickupOtherGroup = document.querySelector('#pickupOtherGroup');
    const pickupOtherInput = document.querySelector('#pickupOtherInput');
    const destinationOtherGroup = document.querySelector('#destinationOtherGroup');
    const destinationOtherInput = document.querySelector('#destinationOtherInput');
    const isCustomQueryInput = document.querySelector('#isCustomQuery');

    function toggleOtherLocationInputs() {
        if (pickupInput.value === 'Other') {
            pickupOtherGroup.style.display = 'block';
            pickupOtherInput.focus();
        } else {
            pickupOtherGroup.style.display = 'none';
        }

        if (destinationInput.value === 'Other') {
            destinationOtherGroup.style.display = 'block';
            destinationOtherInput.focus();
        } else {
            destinationOtherGroup.style.display = 'none';
        }
    }

    pickupInput.addEventListener('change', toggleOtherLocationInputs);
    destinationInput.addEventListener('change', toggleOtherLocationInputs);
    if (pickupOtherInput) pickupOtherInput.addEventListener('input', updateRatePreview);
    if (destinationOtherInput) destinationOtherInput.addEventListener('input', updateRatePreview);

    function updateRatePreview() {
        const selectedTrip = document.querySelector('input[name="trip_type"]:checked');
        const selectedVehicle = document.querySelector('input[name="vehicle_category"]:checked');
        if (selectedTrip) {
            categoryInput.value = selectedTrip.dataset.rateCategory;
        }

        const category = categoryInput.value;
        const isRoundTrip = (category === 'round_trip');

        // Check if user selected "Other" location -> NO PRICE SHOWN, BECOMES A QUERY
        const isCustomPickup = pickupInput.value === 'Other' || (pickupOtherInput && pickupOtherInput.value.trim().length > 0);
        const isCustomDest = destinationInput.value === 'Other' || (destinationOtherInput && destinationOtherInput.value.trim().length > 0);
        const isOtherLocation = isCustomPickup || isCustomDest;

        if (isOtherLocation) {
            if (isCustomQueryInput) isCustomQueryInput.value = '1';
            estimatedKmGroup.style.display = 'none';
            standardRatePreview.style.display = 'block';
            roundTripQuotationNotice.style.display = 'none';

            previewAmount.innerHTML = '<span style="color: #D97706; font-size: 1.15rem; font-weight: 700;">Price on Request (Custom Quote)</span>';
            previewDetails.textContent = 'Custom location selected. Price is not calculated automatically. A quotation query will be sent to the admin dashboard for pricing.';

            btnSubmitBooking.textContent = 'Submit Travel Query';
            btnSubmitBooking.classList.remove('btn-primary');
            btnSubmitBooking.classList.add('bg-amber-600', 'hover:bg-amber-700', 'text-white');
            submitHeaderTitle.textContent = 'Custom Location Quotation Query';
            submitHeaderSub.textContent = 'No immediate charge. Our dispatch team will review your location and send a customized quote.';
            return;
        }

        if (isCustomQueryInput) isCustomQueryInput.value = '0';

        // Toggle UI modes for Round-Trip vs One-Way
        if (isRoundTrip) {
            estimatedKmGroup.style.display = 'block';
            standardRatePreview.style.display = 'none';
            roundTripQuotationNotice.style.display = 'block';

            // Round-trip enquiry mode
            btnSubmitBooking.textContent = 'Send Quotation Query';
            btnSubmitBooking.classList.remove('btn-primary');
            btnSubmitBooking.classList.add('bg-amber-600', 'hover:bg-amber-700', 'text-white');
            submitHeaderTitle.textContent = 'Round-Trip Quotation Enquiry';
            submitHeaderSub.textContent = 'No immediate charge. Our team will review your route and send an official quote.';
            return;
        } else {
            estimatedKmGroup.style.display = 'none';
            standardRatePreview.style.display = 'block';
            roundTripQuotationNotice.style.display = 'none';

            btnSubmitBooking.textContent = 'Confirm Booking';
            btnSubmitBooking.classList.remove('bg-amber-600', 'hover:bg-amber-700');
            btnSubmitBooking.classList.add('btn-primary');
            submitHeaderTitle.textContent = 'Confirmed Booking Dispatch';
            submitHeaderSub.textContent = 'Standard 5% GST tax billing applicable.';
        }

        if (category.startsWith('local_') && pickupInput.value) {
            destinationInput.value = pickupInput.value;
        }

        if (!pickupInput.value || !destinationInput.value || !selectedVehicle) {
            previewAmount.textContent = 'Select route and vehicle';
            previewDetails.textContent = '';
            return;
        }

        const quoteKey = [category, category.startsWith('local_') ? '' : pickupInput.value, category.startsWith('local_') ? '' : destinationInput.value, selectedVehicle.value].join('|');
        const quote = rateOptions[quoteKey];
        if (!quote) {
            previewAmount.textContent = 'Custom Route — Fixed Rate on Review';
            previewDetails.textContent = 'Standard fixed distance fare will be applied by dispatch.';
            return;
        }

        const details = quote.details;
        const pieces = [];
        if (details.included_km !== null) pieces.push(`${details.included_km} KM included`);
        if (details.included_hours !== null) pieces.push(`${details.included_hours} hours included`);
        if (details.extra_km_rate !== null) pieces.push(`Extra KM ₹${Number(details.extra_km_rate).toFixed(2)}`);
        if (details.extra_hour_rate !== null) pieces.push(`Extra hour ₹${Number(details.extra_hour_rate).toFixed(2)}`);
        if (details.toll_type) pieces.push(`Toll ${details.toll_type}`);
        previewDetails.textContent = pieces.join(' · ');

        const gstText = ' + 5% GST';
        previewAmount.textContent = `₹${Number(quote.total_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}${gstText}`;
    }

    // Form submit validation & sync
    const bookingFormEl = document.querySelector('#bookingForm');
    if (bookingFormEl) {
        bookingFormEl.addEventListener('submit', function(e) {
            if (pickupInput.value === 'Other' || (pickupOtherInput && pickupOtherInput.value.trim().length > 0)) {
                const val = pickupOtherInput ? pickupOtherInput.value.trim() : '';
                if (!val) {
                    alert('Please enter your custom pickup location.');
                    if (pickupOtherInput) pickupOtherInput.focus();
                    e.preventDefault();
                    return false;
                }
                let opt = Array.from(pickupInput.options).find(o => o.value === val);
                if (!opt) {
                    opt = new Option(val, val, true, true);
                    pickupInput.add(opt);
                }
                pickupInput.value = val;
                if (isCustomQueryInput) isCustomQueryInput.value = '1';
            }

            if (destinationInput.value === 'Other' || (destinationOtherInput && destinationOtherInput.value.trim().length > 0)) {
                const val = destinationOtherInput ? destinationOtherInput.value.trim() : '';
                if (!val) {
                    alert('Please enter your custom drop location.');
                    if (destinationOtherInput) destinationOtherInput.focus();
                    e.preventDefault();
                    return false;
                }
                let opt = Array.from(destinationInput.options).find(o => o.value === val);
                if (!opt) {
                    opt = new Option(val, val, true, true);
                    destinationInput.add(opt);
                }
                destinationInput.value = val;
                if (isCustomQueryInput) isCustomQueryInput.value = '1';
            }
        });
    }

    // Trip type selection styling
    document.querySelectorAll('.trip-opt').forEach((label) => {
        label.addEventListener('click', function () {
            document.querySelectorAll('.trip-opt').forEach((l) => l.classList.remove('active', 'ring-2', 'ring-blue-500', 'bg-blue-50', 'border-blue-400'));
            this.classList.add('active', 'ring-2', 'ring-blue-500', 'bg-blue-50', 'border-blue-400');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                updateRatePreview();
            }
        });
    });

    function renderBookingModelPills(parent) {
        const box = document.getElementById('bookingModelSelectionBox');
        const wrap = document.getElementById('bookingModelChipsWrap');
        const catIdInput = document.getElementById('bookingVehicleCategoryId');
        const modelIdInput = document.getElementById('bookingVehicleModelId');
        const modelNameInput = document.getElementById('bookingVehicleModel');

        if (!box || !wrap) return;

        const catId = parent ? parent.dataset.categoryId : null;
        if (catIdInput) catIdInput.value = catId || '';

        let models = [];
        try {
            models = parent && parent.dataset.models ? JSON.parse(parent.dataset.models) : [];
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

    document.querySelectorAll('.vehicle-category-opt').forEach((label) => {
        label.addEventListener('click', function () {
            document.querySelectorAll('.vehicle-category-opt').forEach((l) => l.classList.remove('active', 'ring-2', 'ring-blue-500', 'bg-blue-50', 'border-blue-400'));
            this.classList.add('active', 'ring-2', 'ring-blue-500', 'bg-blue-50', 'border-blue-400');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
            renderBookingModelPills(this);
            updateRatePreview();
        });
    });

    const initialCheckedBookingCat = document.querySelector('.vehicle-category-opt input[type="radio"]:checked');
    if (initialCheckedBookingCat) {
        renderBookingModelPills(initialCheckedBookingCat.closest('.vehicle-category-opt'));
    }

    [pickupInput, destinationInput].forEach((input) => input.addEventListener('change', updateRatePreview));
    updateRatePreview();

    // Google Maps & Location Picker Integration
    const pickupLatInput = document.getElementById('pickupLatitude');
    const pickupLngInput = document.getElementById('pickupLongitude');
    const pickupPlaceIdInput = document.getElementById('pickupPlaceId');

    const viaLatInput = document.getElementById('viaLatitude');
    const viaLngInput = document.getElementById('viaLongitude');
    const viaPlaceIdInput = document.getElementById('viaPlaceId');

    const destLatInput = document.getElementById('destinationLatitude');
    const destLngInput = document.getElementById('destinationLongitude');
    const destPlaceIdInput = document.getElementById('destinationPlaceId');

    const routeDistanceKmInput = document.getElementById('routeDistanceKm');
    const routeDurationTextInput = document.getElementById('routeDurationText');
    const routeDurationMinutesInput = document.getElementById('routeDurationMinutes');
    const routeGeometryInput = document.getElementById('routeGeometry');

    const routeMapContainer = document.getElementById('bookingRouteMapContainer');
    const routeMapCanvas = document.getElementById('bookingRouteMapCanvas');
    const routeDistanceDisplay = document.getElementById('routeDistanceDisplay');
    const routeDurationDisplay = document.getElementById('routeDurationDisplay');
    const routeCalcSpinner = document.getElementById('routeCalcSpinner');
    const routeCalcErrorBanner = document.getElementById('routeCalcErrorBanner');
    const routeCalcErrorMsg = document.getElementById('routeCalcErrorMsg');
    const btnRetryRouteCalc = document.getElementById('btnRetryRouteCalc');
    const routeLegsContainer = document.getElementById('routeLegsContainer');

    let bookingRouteMap = null;
    let bookingRoutePolyline = null;
    let bookingRouteMarkers = [];

    // Attach click listeners to all "🗺️ Map" buttons
    document.querySelectorAll('.btn-select-map').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.dataset.target;

            if (target === 'pickup') {
                window.VaishnaviLocationPicker.open({
                    label: 'Pickup Location',
                    inputEl: pickupInput,
                    latEl: pickupLatInput,
                    lngEl: pickupLngInput,
                    placeIdEl: pickupPlaceIdInput,
                    onConfirm: () => {
                        updateRatePreview();
                        triggerRouteCalculation();
                    }
                });
            } else if (target === 'pickup_other') {
                window.VaishnaviLocationPicker.open({
                    label: 'Custom Pickup Area / Landmark',
                    inputEl: pickupOtherInput,
                    latEl: pickupLatInput,
                    lngEl: pickupLngInput,
                    placeIdEl: pickupPlaceIdInput,
                    onConfirm: () => {
                        updateRatePreview();
                        triggerRouteCalculation();
                    }
                });
            } else if (target === 'via') {
                window.VaishnaviLocationPicker.open({
                    label: 'Via Intermediate Location (Round-Trip)',
                    inputEl: viaLocationInput,
                    latEl: viaLatInput,
                    lngEl: viaLngInput,
                    placeIdEl: viaPlaceIdInput,
                    onConfirm: () => {
                        triggerRouteCalculation();
                    }
                });
            } else if (target === 'destination') {
                window.VaishnaviLocationPicker.open({
                    label: 'Final Destination Drop',
                    inputEl: destinationInput,
                    latEl: destLatInput,
                    lngEl: destLngInput,
                    placeIdEl: destPlaceIdInput,
                    onConfirm: () => {
                        updateRatePreview();
                        triggerRouteCalculation();
                    }
                });
            } else if (target === 'destination_other') {
                window.VaishnaviLocationPicker.open({
                    label: 'Custom Destination Area / Landmark',
                    inputEl: destinationOtherInput,
                    latEl: destLatInput,
                    lngEl: destLngInput,
                    placeIdEl: destPlaceIdInput,
                    onConfirm: () => {
                        updateRatePreview();
                        triggerRouteCalculation();
                    }
                });
            }
        });
    });

    // Auto-calculate route when locations change
    pickupInput.addEventListener('change', triggerRouteCalculation);
    destinationInput.addEventListener('change', triggerRouteCalculation);
    if (viaLocationInput) viaLocationInput.addEventListener('change', triggerRouteCalculation);
    if (pickupOtherInput) pickupOtherInput.addEventListener('change', triggerRouteCalculation);
    if (destinationOtherInput) destinationOtherInput.addEventListener('change', triggerRouteCalculation);
    btnRetryRouteCalc?.addEventListener('click', triggerRouteCalculation);

    async function triggerRouteCalculation() {
        const originVal = (pickupInput.value === 'Other' && pickupOtherInput) ? pickupOtherInput.value.trim() : pickupInput.value;
        const destVal = (destinationInput.value === 'Other' && destinationOtherInput) ? destinationOtherInput.value.trim() : destinationInput.value;
        const viaVal = viaLocationInput ? viaLocationInput.value.trim() : '';

        if (!originVal || !destVal) {
            if (routeMapContainer) routeMapContainer.style.display = 'none';
            return;
        }

        const selectedTrip = document.querySelector('input[name="trip_type"]:checked');
        const isRoundTrip = selectedTrip ? (selectedTrip.value === 'Round-Trip' || selectedTrip.dataset.rateCategory === 'round_trip') : false;

        // Origin parameter (lat/lng object if available, else text)
        const originParam = (pickupLatInput && pickupLatInput.value && pickupLngInput && pickupLngInput.value)
            ? { lat: parseFloat(pickupLatInput.value), lng: parseFloat(pickupLngInput.value), address: originVal }
            : originVal;

        const destParam = (destLatInput && destLatInput.value && destLngInput && destLngInput.value)
            ? { lat: parseFloat(destLatInput.value), lng: parseFloat(destLngInput.value), address: destVal }
            : destVal;

        const viaParam = viaVal
            ? ((viaLatInput && viaLatInput.value && viaLngInput && viaLngInput.value)
                ? { lat: parseFloat(viaLatInput.value), lng: parseFloat(viaLngInput.value), address: viaVal }
                : viaVal)
            : null;

        if (routeMapContainer) routeMapContainer.style.display = 'block';
        if (routeCalcSpinner) routeCalcSpinner.style.display = 'flex';
        if (routeCalcErrorBanner) routeCalcErrorBanner.style.display = 'none';

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
                    via: viaParam,
                    is_round_trip: isRoundTrip
                })
            });

            const data = await resp.json();
            if (routeCalcSpinner) routeCalcSpinner.style.display = 'none';

            if (data && data.success) {
                // Populate hidden inputs
                if (routeDistanceKmInput) routeDistanceKmInput.value = data.total_distance_km;
                if (routeDurationTextInput) routeDurationTextInput.value = data.total_duration_text;
                if (routeDurationMinutesInput) routeDurationMinutesInput.value = data.total_duration_minutes;
                if (routeGeometryInput) routeGeometryInput.value = data.route_geometry || '';

                // Also sync estimatedKm field if present
                if (estimatedKmInput && isRoundTrip) {
                    estimatedKmInput.value = data.total_distance_km;
                }

                // Update UI badges
                if (routeDistanceDisplay) routeDistanceDisplay.textContent = `${data.total_distance_km} KM`;
                if (routeDurationDisplay) routeDurationDisplay.textContent = `⏱️ ${data.total_duration_text}`;

                // Render Route Legs
                if (routeLegsContainer && data.legs) {
                    routeLegsContainer.innerHTML = data.legs.map((leg, idx) => `
                        <div style="background: #fff; border: 1px solid #CBD5E1; padding: 3px 8px; border-radius: 4px; display: flex; align-items: center; gap: 4px;">
                            <strong style="color: #2563EB;">Leg ${idx + 1}:</strong>
                            <span>${leg.from_name} &rarr; ${leg.to_name}</span>
                            <span style="font-weight: 700; color: #0F172A; background: #F1F5F9; padding: 1px 4px; border-radius: 2px;">${leg.distance_km} KM</span>
                            <span style="color: #64748B;">(${leg.duration_text})</span>
                        </div>
                    `).join('');
                }

                renderRouteMap(data);
            } else {
                throw new Error(data.message || 'Route calculation could not be completed.');
            }
        } catch (err) {
            console.error('Route calculation error:', err);
            if (routeCalcSpinner) routeCalcSpinner.style.display = 'none';
            if (routeCalcErrorBanner) {
                routeCalcErrorBanner.style.display = 'flex';
                if (routeCalcErrorMsg) routeCalcErrorMsg.textContent = 'Notice: Road routing calculation failed. ' + (err.message || 'Please check selected locations and click Retry.');
            }
        }
    }

    function renderRouteMap(data) {
        if (!routeMapCanvas || typeof L === 'undefined') return;

        if (!bookingRouteMap) {
            bookingRouteMap = L.map(routeMapCanvas).setView([22.0797, 82.1409], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap | Vaishnavi Tours'
            }).addTo(bookingRouteMap);
        }

        // Clear existing markers and polylines
        bookingRouteMarkers.forEach(m => bookingRouteMap.removeLayer(m));
        bookingRouteMarkers = [];
        if (bookingRoutePolyline) {
            bookingRouteMap.removeLayer(bookingRoutePolyline);
        }

        const points = [];
        if (data.legs && data.legs.length > 0) {
            data.legs.forEach((leg, idx) => {
                if (leg.start_location && leg.start_location.lat) {
                    const startPos = [leg.start_location.lat, leg.start_location.lng];
                    if (idx === 0) {
                        const m = L.marker(startPos, { title: leg.from_name }).addTo(bookingRouteMap)
                            .bindPopup(`<strong>🟢 Pickup:</strong> ${leg.from_name}`);
                        bookingRouteMarkers.push(m);
                        points.push(startPos);
                    }
                }
                if (leg.end_location && leg.end_location.lat) {
                    const endPos = [leg.end_location.lat, leg.end_location.lng];
                    const isLast = (idx === data.legs.length - 1);
                    const label = isLast ? (data.is_round_trip ? '🏁 Return Drop' : '🔴 Destination') : `🟡 Via: ${leg.to_name}`;
                    const m = L.marker(endPos, { title: leg.to_name }).addTo(bookingRouteMap)
                        .bindPopup(`<strong>${label}:</strong> ${leg.to_name}`);
                    bookingRouteMarkers.push(m);
                    points.push(endPos);
                }
            });
        }

        if (points.length >= 2) {
            bookingRoutePolyline = L.polyline(points, {
                color: '#2563EB',
                weight: 5,
                opacity: 0.85,
                dashArray: data.is_round_trip ? '8, 6' : '0'
            }).addTo(bookingRouteMap);

            bookingRouteMap.fitBounds(bookingRoutePolyline.getBounds(), { padding: [25, 25] });
        }

        setTimeout(() => bookingRouteMap.invalidateSize(), 250);
    }
</script>
@endpush

@push('styles')
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

