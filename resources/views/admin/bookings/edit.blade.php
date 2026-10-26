@extends('layouts.admin')

@section('title', 'Edit Booking #' . $booking->booking_id)
@section('page_title', 'Edit Booking #' . $booking->booking_id)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.bookings.show', $booking->id) }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to Booking Details
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Edit Booking #{{ $booking->booking_id }}</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Modify itinerary, passenger assignment, fleet allocation, and fare structure.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Details -->
        <div style="grid-column: span 2;">
            <!-- Customer -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <x-icon name="user" :size="16" /> Customer & Passenger
                </h3>

                <div class="form-group mb-3">
                    <label for="customer_id" class="form-label">Customer *</label>
                    <select name="customer_id" id="customer_id" class="form-select" required>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id', $booking->customer_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }} | {{ $c->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($booking->booking_category === 'TP' || $booking->travel_partner_name)
                <!-- Travel Partner Dedicated Workflow Fields -->
                <div class="card mb-4" style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px;">
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: #92400E; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                        <x-icon name="users" :size="16" /> Travel Partner / Agent Referral Details
                    </h3>
                    <div class="grid grid-3 gap-3 mb-3">
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #78350F;">Partner / Agency Name</label>
                            <input type="text" name="travel_partner_name" class="form-control" value="{{ old('travel_partner_name', $booking->travel_partner_name) }}" placeholder="e.g. MakeMyTrip, Agency">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #78350F;">Partner Mobile / Phone</label>
                            <input type="text" name="travel_partner_phone" class="form-control" value="{{ old('travel_partner_phone', $booking->travel_partner_phone) }}" placeholder="e.g. 9876543210">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #78350F;">Partner Email</label>
                            <input type="email" name="travel_partner_email" class="form-control" value="{{ old('travel_partner_email', $booking->travel_partner_email) }}" placeholder="e.g. partner@agency.com">
                        </div>
                    </div>
                    <div class="grid grid-2 gap-3 mb-3">
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #78350F;">Partner Office Address</label>
                            <input type="text" name="travel_partner_address" class="form-control" value="{{ old('travel_partner_address', $booking->travel_partner_address) }}" placeholder="Office / Shop Address">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #78350F;">Partner Commission / Payout (₹)</label>
                            <input type="number" step="0.01" name="travel_partner_commission" class="form-control" value="{{ old('travel_partner_commission', $booking->travel_partner_commission) }}" placeholder="500.00">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 700; color: #78350F;">Partner Notes / Reference</label>
                        <textarea name="travel_partner_notes" class="form-control" rows="2" placeholder="Special partner instructions...">{{ old('travel_partner_notes', $booking->travel_partner_notes) }}</textarea>
                    </div>
                </div>
            @endif

            <!-- Route & Schedule -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <x-icon name="map-pin" :size="16" /> Route & Schedule Information
                </h3>

                <div class="form-group mb-3">
                    <label for="trip_type" class="form-label" style="font-weight: 700;">Trip Type / Service *</label>
                    <select name="trip_type" id="trip_type" class="form-select" required>
                        @php
                            $types = [
                                'One-Way' => '🚗 Oneway trip',
                                'Round-Trip' => '🔄 Round trip',
                                'Tour Package' => '🌴 Tour Package',
                                'Ambulance' => '🚑 Ambulance',
                                'Airport Transfer' => '✈️ Airport Transfer',
                                'Local Hourly' => '⏱️ Local Hourly Rental',
                            ];
                        @endphp
                        @foreach($types as $val => $lbl)
                            <option value="{{ $val }}" {{ old('trip_type', $booking->trip_type) === $val || (str_contains($booking->trip_type ?? '', $val)) ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Google Maps & Routing Coordinate Hidden Fields -->
                <input type="hidden" name="pickup_latitude" id="adminPickupLat" value="{{ old('pickup_latitude', $booking->pickup_latitude) }}">
                <input type="hidden" name="pickup_longitude" id="adminPickupLng" value="{{ old('pickup_longitude', $booking->pickup_longitude) }}">
                <input type="hidden" name="pickup_place_id" id="adminPickupPlaceId" value="{{ old('pickup_place_id', $booking->pickup_place_id) }}">

                <input type="hidden" name="via_latitude" id="adminViaLat" value="{{ old('via_latitude', $booking->via_latitude) }}">
                <input type="hidden" name="via_longitude" id="adminViaLng" value="{{ old('via_longitude', $booking->via_longitude) }}">
                <input type="hidden" name="via_place_id" id="adminViaPlaceId" value="{{ old('via_place_id', $booking->via_place_id) }}">

                <input type="hidden" name="destination_latitude" id="adminDestinationLat" value="{{ old('destination_latitude', $booking->destination_latitude) }}">
                <input type="hidden" name="destination_longitude" id="adminDestinationLng" value="{{ old('destination_longitude', $booking->destination_longitude) }}">
                <input type="hidden" name="destination_place_id" id="adminDestinationPlaceId" value="{{ old('destination_place_id', $booking->destination_place_id) }}">

                <input type="hidden" name="route_distance_km" id="adminRouteDistanceKm" value="{{ old('route_distance_km', $booking->route_distance_km) }}">
                <input type="hidden" name="route_duration_text" id="adminRouteDurationText" value="{{ old('route_duration_text', $booking->route_duration_text) }}">
                <input type="hidden" name="route_duration_minutes" id="adminRouteDurationMinutes" value="{{ old('route_duration_minutes', $booking->route_duration_minutes) }}">
                <input type="hidden" name="route_geometry" id="adminRouteGeometry" value="{{ old('route_geometry', $booking->route_geometry) }}">

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="pickup_location" class="form-label" style="margin: 0;">Pickup Location *</label>
                            <button type="button" class="btn btn-xs btn-outline btn-admin-select-map" data-target="pickup" style="font-size: 11px; padding: 2px 7px; border-radius: 4px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                                <x-icon name="map-pin" :size="11" /> Pick Map
                            </button>
                        </div>
                        <input type="text" name="pickup_location" id="pickup_location" class="form-control" value="{{ old('pickup_location', $booking->pickup_location) }}" required>
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="via_location" class="form-label" style="margin: 0;">Via Location (Round-Trip)</label>
                            <button type="button" class="btn btn-xs btn-outline btn-admin-select-map" data-target="via" style="font-size: 11px; padding: 2px 7px; border-radius: 4px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                                <x-icon name="map-pin" :size="11" /> Pick Map
                            </button>
                        </div>
                        <input type="text" name="via_location" id="via_location" class="form-control" placeholder="Optional via stop" value="{{ old('via_location', $booking->via_location) }}">
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="destination" class="form-label" style="margin: 0;">Drop Destination *</label>
                            <button type="button" class="btn btn-xs btn-outline btn-admin-select-map" data-target="destination" style="font-size: 11px; padding: 2px 7px; border-radius: 4px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                                <x-icon name="map-pin" :size="11" /> Pick Map
                            </button>
                        </div>
                        <input type="text" name="destination" id="destination" class="form-control" value="{{ old('destination', $booking->destination) }}" required>
                    </div>
                </div>

                <!-- Admin Route & Distance Preview Container -->
                <div id="adminRoutePreviewBox" style="margin-bottom: 1rem; border: 1px solid #BFDBFE; border-radius: 8px; overflow: hidden; background: #EFF6FF;">
                    <div style="padding: 0.6rem 0.85rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; border-bottom: 1px solid #BFDBFE; background: #DBEAFE;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <x-icon name="navigation" :size="14" style="color: #1E40AF;" />
                            <strong style="color: #1E40AF; font-size: 12.5px;">Google Road Route & Driving Distance</strong>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span id="adminRouteDistDisplay" style="font-weight: 800; font-size: 13px; color: #1D4ED8; background: #fff; padding: 2px 7px; border-radius: 4px; border: 1px solid #BFDBFE;">{{ $booking->route_distance_km ? number_format($booking->route_distance_km, 1) . ' KM' : '-- KM' }}</span>
                            <span id="adminRouteDurDisplay" style="font-size: 12px; color: #1E40AF; background: #fff; padding: 2px 7px; border-radius: 4px; border: 1px solid #BFDBFE;">{{ $booking->route_duration_text ?: '--' }}</span>
                            <button type="button" id="btnAdminRecalcRoute" class="btn btn-xs btn-outline" style="font-size: 11px; padding: 2px 8px; background: #fff; border-color: #2563EB; color: #2563EB;">🔄 Calculate</button>
                        </div>
                    </div>
                    <div style="height: 200px; width: 100%;">
                        <div id="adminRouteMapCanvas" style="width: 100%; height: 100%;"></div>
                    </div>
                    <div id="adminRouteLegsList" style="padding: 0.4rem 0.85rem; font-size: 11px; color: #1E40AF; display: flex; gap: 6px; flex-wrap: wrap;"></div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="vehicle_category" class="form-label">Booked Vehicle Category *</label>
                        <select name="vehicle_category" id="vehicle_category" class="form-select" onchange="onAdminEditCategoryChange()">
                            <option value="">-- Select Category --</option>
                            @if(isset($activeVehicleCategories))
                                @foreach($activeVehicleCategories as $cat)
                                    <option value="{{ $cat->name }}" data-id="{{ $cat->id }}" 
                                        {{ old('vehicle_category', $booking->effective_vehicle_category) === $cat->name ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ $cat->is_capacity_configurable ? 'Configurable '.$cat->min_seating_capacity.'-'.$cat->max_seating_capacity.' Seats' : $cat->default_seating_capacity.' Seats' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <input type="hidden" name="vehicle_category_id" id="edit_vehicle_category_id" value="{{ old('vehicle_category_id', $booking->vehicle_category_id) }}">
                    </div>

                    <div class="form-group">
                        <label for="vehicle_model_id" class="form-label">Vehicle Model Preference (Optional)</label>
                        <select name="vehicle_model_id" id="vehicle_model_id" class="form-select" onchange="onAdminEditModelChange()">
                            <option value="">-- Any / All Models in Category --</option>
                        </select>
                        <input type="hidden" name="vehicle_model" id="edit_vehicle_model" value="{{ old('vehicle_model', $booking->effective_vehicle_model) }}">
                    </div>
                </div>

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group">
                        <label for="travel_date" class="form-label">Travel Date *</label>
                        <input type="date" name="travel_date" id="travel_date" class="form-control" value="{{ old('travel_date', $booking->travel_date ? $booking->travel_date->format('Y-m-d') : '') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="travel_time" class="form-label">Pickup Time *</label>
                        <input type="time" name="travel_time" id="travel_time" class="form-control" value="{{ old('travel_time', substr($booking->travel_time, 0, 5)) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="return_date" class="form-label">Return Date</label>
                        <input type="date" name="return_date" id="return_date" class="form-control" value="{{ old('return_date', $booking->return_date ? $booking->return_date->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Special Notes / Requests</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $booking->notes) }}</textarea>
                </div>
            </div>

            <!-- Fleet & Driver -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <x-icon name="car" :size="16" /> Fleet & Chauffeur Assignment
                </h3>

                <div class="grid grid-2 gap-3">
                    <div class="form-group">
                        <label for="vehicle_id" class="form-label" style="font-weight: 700;">Assign Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-select">
                            <option value="">-- No Vehicle Assigned --</option>
                            <optgroup label="🏢 Own Company Fleet">
                                @foreach($vehicles->where('is_third_party', false) as $veh)
                                    <option value="{{ $veh->id }}" {{ old('vehicle_id', $booking->vehicle_id) == $veh->id ? 'selected' : '' }}>
                                        {{ $veh->name }} - {{ $veh->registration_number }} ({{ $veh->vehicle_type }} • {{ $veh->status }})
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="🤝 Third Party Vehicles (Travel Partner)">
                                @foreach($vehicles->where('is_third_party', true) as $tpVeh)
                                    <option value="{{ $tpVeh->id }}" {{ old('vehicle_id', $booking->vehicle_id) == $tpVeh->id ? 'selected' : '' }}>
                                        {{ $tpVeh->registration_number }} - {{ $tpVeh->name }} (Partner: {{ $tpVeh->vendor_name ?: 'Partner' }})
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="driver_id" class="form-label">Chauffeur / Driver</label>
                        <select name="driver_id" id="driver_id" class="form-select">
                            <option value="">-- No Driver Assigned --</option>
                            @foreach($drivers as $drv)
                                <option value="{{ $drv->id }}" {{ old('driver_id', $booking->driver_id) == $drv->id ? 'selected' : '' }}>
                                    {{ $drv->name }} ({{ $drv->mobile }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Financials & Status -->
        <div>
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <x-icon name="credit-card" :size="16" /> Status & Pricing
                </h3>

                <div class="form-group mb-3">
                    <label for="booking_status" class="form-label">Booking Status *</label>
                    <select name="booking_status" id="booking_status" class="form-select" required>
                        @foreach(['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way', 'Completed', 'Cancelled'] as $st)
                            <option value="{{ $st }}" {{ old('booking_status', $booking->booking_status) == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="total_amount" class="form-label">Total Agreed Fare (₹) *</label>
                    <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" value="{{ old('total_amount', $booking->total_amount) }}" required>
                </div>

                <div class="form-group mb-3">
                    <label for="paid_amount" class="form-label">Paid / Collected Amount (₹) *</label>
                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control" value="{{ old('paid_amount', $booking->paid_amount) }}" required>
                </div>

                <div class="form-group mb-4">
                    <label for="payment_status" class="form-label">Payment Status *</label>
                    <select name="payment_status" id="payment_status" class="form-select" required>
                        @foreach(['Pending', 'Partial', 'Paid', 'Failed', 'Refunded'] as $pst)
                            <option value="{{ $pst }}" {{ old('payment_status', $booking->payment_status) == $pst ? 'selected' : '' }}>{{ $pst }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-between gap-2">
                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary flex-1" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        <x-icon name="check-circle" :size="15" /> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<x-location-picker-modal />

<script>
    const activeVehicleCategoriesData = @json($activeVehicleCategories ?? []);
    const initialModelId = @json(old('vehicle_model_id', $booking->vehicle_model_id));
    const initialModelName = @json(old('vehicle_model', $booking->effective_vehicle_model));
    let initialCategoryLoaded = false;

    function onAdminEditCategoryChange() {
        const catSelect = document.getElementById('vehicle_category');
        const catIdInput = document.getElementById('edit_vehicle_category_id');
        const modelSelect = document.getElementById('vehicle_model_id');
        const modelNameInput = document.getElementById('edit_vehicle_model');

        const selectedOpt = catSelect.options[catSelect.selectedIndex];
        const catId = selectedOpt ? selectedOpt.dataset.id : null;
        if (catIdInput) catIdInput.value = catId || '';

        const previousVal = modelSelect.value;
        modelSelect.innerHTML = '<option value="">-- Any / All Models in Category --</option>';
        if (catId) {
            const catObj = activeVehicleCategoriesData.find(c => c.id == catId);
            if (catObj && catObj.active_models && catObj.active_models.length > 0) {
                catObj.active_models.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.name + (m.brand ? ` (${m.brand})` : '');
                    opt.dataset.name = m.name;
                    if (!initialCategoryLoaded) {
                        if (initialModelId == m.id || initialModelName == m.name) {
                            opt.selected = true;
                        }
                    } else if (previousVal == m.id) {
                        opt.selected = true;
                    }
                    modelSelect.appendChild(opt);
                });
            }
        }
        if (modelNameInput) {
            const curOpt = modelSelect.options[modelSelect.selectedIndex];
            modelNameInput.value = curOpt && curOpt.value ? curOpt.dataset.name : '';
        }
        initialCategoryLoaded = true;
    }

    function onAdminEditModelChange() {
        const modelSelect = document.getElementById('vehicle_model_id');
        const modelNameInput = document.getElementById('edit_vehicle_model');
        const selectedOpt = modelSelect.options[modelSelect.selectedIndex];
        if (modelNameInput) {
            modelNameInput.value = selectedOpt && selectedOpt.value ? (selectedOpt.dataset.name || selectedOpt.textContent) : '';
        }
    }

    let adminRouteMap = null;
    let adminRoutePolyline = null;
    let adminRouteMarkers = [];

    document.addEventListener('DOMContentLoaded', () => {
        onAdminEditCategoryChange();
        // Location picker button triggers
        document.querySelectorAll('.btn-admin-select-map').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.target;
                let inputEl, latEl, lngEl, placeIdEl, label;

                if (target === 'pickup') {
                    inputEl = document.getElementById('pickup_location');
                    latEl = document.getElementById('adminPickupLat');
                    lngEl = document.getElementById('adminPickupLng');
                    placeIdEl = document.getElementById('adminPickupPlaceId');
                    label = 'Pickup Origin';
                } else if (target === 'via') {
                    inputEl = document.getElementById('via_location');
                    latEl = document.getElementById('adminViaLat');
                    lngEl = document.getElementById('adminViaLng');
                    placeIdEl = document.getElementById('adminViaPlaceId');
                    label = 'Via Intermediate Stop';
                } else {
                    inputEl = document.getElementById('destination');
                    latEl = document.getElementById('adminDestinationLat');
                    lngEl = document.getElementById('adminDestinationLng');
                    placeIdEl = document.getElementById('adminDestinationPlaceId');
                    label = 'Drop Destination';
                }

                if (window.VaishnaviLocationPicker) {
                    window.VaishnaviLocationPicker.open({
                        targetLabel: label,
                        inputEl: inputEl,
                        latEl: latEl,
                        lngEl: lngEl,
                        placeIdEl: placeIdEl,
                        currentLat: latEl ? parseFloat(latEl.value) : null,
                        currentLng: lngEl ? parseFloat(lngEl.value) : null,
                        currentName: inputEl ? inputEl.value : '',
                        onConfirm: () => {
                            calculateAdminRoute();
                        }
                    });
                }
            });
        });

        // Recalculate button trigger
        document.getElementById('btnAdminRecalcRoute')?.addEventListener('click', calculateAdminRoute);

        // Location input changes
        ['pickup_location', 'destination', 'via_location'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', calculateAdminRoute);
        });

        // Initial render if coordinates or locations exist
        setTimeout(calculateAdminRoute, 500);
    });

    async function calculateAdminRoute() {
        const pickup = document.getElementById('pickup_location')?.value.trim();
        const destination = document.getElementById('destination')?.value.trim();
        const via = document.getElementById('via_location')?.value.trim();
        const tripType = document.getElementById('trip_type')?.value || '';
        const isRoundTrip = tripType.toLowerCase().includes('round');

        if (!pickup || !destination) return;

        const payload = {
            pickup_location: pickup,
            destination: destination,
            via_location: via || null,
            trip_type: isRoundTrip ? 'Round-Trip' : 'One-Way',
            pickup_latitude: document.getElementById('adminPickupLat')?.value || null,
            pickup_longitude: document.getElementById('adminPickupLng')?.value || null,
            pickup_place_id: document.getElementById('adminPickupPlaceId')?.value || null,
            via_latitude: document.getElementById('adminViaLat')?.value || null,
            via_longitude: document.getElementById('adminViaLng')?.value || null,
            via_place_id: document.getElementById('adminViaPlaceId')?.value || null,
            destination_latitude: document.getElementById('adminDestinationLat')?.value || null,
            destination_longitude: document.getElementById('adminDestinationLng')?.value || null,
            destination_place_id: document.getElementById('adminDestinationPlaceId')?.value || null
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const resp = await fetch('/api/maps/calculate-route', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json();
            if (data && data.status === 'success') {
                const distEl = document.getElementById('adminRouteDistDisplay');
                const durEl = document.getElementById('adminRouteDurDisplay');
                const legsEl = document.getElementById('adminRouteLegsList');

                if (distEl) distEl.textContent = `${data.total_distance_km} KM`;
                if (durEl) durEl.textContent = data.total_duration_text || '';

                const distInput = document.getElementById('adminRouteDistanceKm');
                const durInput = document.getElementById('adminRouteDurationText');
                const geomInput = document.getElementById('adminRouteGeometry');

                if (distInput) distInput.value = data.total_distance_km;
                if (durInput) durInput.value = data.total_duration_text || '';
                if (geomInput && data.geometry) geomInput.value = data.geometry;

                // Sync lat/lng if geocoded by backend
                if (data.origin_coordinates) {
                    const pLat = document.getElementById('adminPickupLat');
                    const pLng = document.getElementById('adminPickupLng');
                    if (pLat && !pLat.value) pLat.value = data.origin_coordinates.lat;
                    if (pLng && !pLng.value) pLng.value = data.origin_coordinates.lng;
                }
                if (data.destination_coordinates) {
                    const dLat = document.getElementById('adminDestinationLat');
                    const dLng = document.getElementById('adminDestinationLng');
                    if (dLat && !dLat.value) dLat.value = data.destination_coordinates.lat;
                    if (dLng && !dLng.value) dLng.value = data.destination_coordinates.lng;
                }

                if (legsEl && data.legs) {
                    legsEl.innerHTML = data.legs.map((leg, i) => `
                        <div style="background: #fff; padding: 2px 6px; border-radius: 4px; border: 1px solid #BFDBFE;">
                            <strong>Leg ${i + 1}:</strong> ${leg.from_name} &rarr; ${leg.to_name} (<b>${leg.distance_km} KM</b>)
                        </div>
                    `).join('');
                }

                renderAdminRouteMap(data);
            }
        } catch (e) {
            console.error('Admin route calc error:', e);
        }
    }

    function renderAdminRouteMap(data) {
        const canvas = document.getElementById('adminRouteMapCanvas');
        if (!canvas || typeof L === 'undefined') return;

        if (!adminRouteMap) {
            adminRouteMap = L.map(canvas).setView([22.0797, 82.1409], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap | Vaishnavi Tours'
            }).addTo(adminRouteMap);
        }

        adminRouteMarkers.forEach(m => adminRouteMap.removeLayer(m));
        adminRouteMarkers = [];
        if (adminRoutePolyline) adminRouteMap.removeLayer(adminRoutePolyline);

        const points = [];
        if (data.legs) {
            data.legs.forEach((leg, idx) => {
                if (leg.start_location && leg.start_location.lat) {
                    const startPos = [leg.start_location.lat, leg.start_location.lng];
                    if (idx === 0) {
                        const m = L.marker(startPos).addTo(adminRouteMap).bindPopup(`🟢 ${leg.from_name}`);
                        adminRouteMarkers.push(m);
                        points.push(startPos);
                    }
                }
                if (leg.end_location && leg.end_location.lat) {
                    const endPos = [leg.end_location.lat, leg.end_location.lng];
                    const label = (idx === data.legs.length - 1) ? (data.is_round_trip ? '🏁 Return Drop' : '🔴 Destination') : `🟡 Via ${leg.to_name}`;
                    const m = L.marker(endPos).addTo(adminRouteMap).bindPopup(`${label}: ${leg.to_name}`);
                    adminRouteMarkers.push(m);
                    points.push(endPos);
                }
            });
        }

        if (points.length >= 2) {
            adminRoutePolyline = L.polyline(points, {
                color: '#2563EB',
                weight: 4,
                opacity: 0.9,
                dashArray: data.is_round_trip ? '6, 5' : '0'
            }).addTo(adminRouteMap);
            adminRouteMap.fitBounds(adminRoutePolyline.getBounds(), { padding: [20, 20] });
        }
        setTimeout(() => adminRouteMap.invalidateSize(), 200);
    }
</script>
@endsection
