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
                    <div class="d-flex justify-between align-center mb-1">
                        <label for="customer_search_input" class="form-label mb-0" style="font-weight: 700;">
                            Customer *
                        </label>
                        <span id="customerCountBadge" class="badge" style="background: #E2E8F0; color: #475569; font-size: 0.75rem; font-weight: 600; padding: 3px 8px; border-radius: 9999px;">
                            {{ count($customers) }} Registered
                        </span>
                    </div>

                    <!-- Search Input with Icon and Clear Button -->
                    <div style="position: relative;" class="customer-search-container mb-2">
                        <div style="position: relative; display: flex; align-items: center;">
                            <span style="position: absolute; left: 12px; color: var(--slate-400); pointer-events: none; display: flex; align-items: center;">
                                <x-icon name="search" :size="16" />
                            </span>
                            <input type="text" 
                                   id="customer_search_input" 
                                   class="form-control" 
                                   placeholder="🔍 Type Name or Mobile number to switch customer..." 
                                   autocomplete="off"
                                   style="padding-left: 38px; padding-right: 36px; height: 42px; border-radius: 8px; font-size: 0.875rem; border: 1.5px solid var(--slate-300);">
                            <button type="button" 
                                    id="clear_customer_search" 
                                    style="display: none; position: absolute; right: 10px; background: transparent; border: none; color: var(--slate-400); cursor: pointer; font-size: 16px; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"
                                    title="Clear search">✕</button>
                        </div>

                        <!-- Live Floating Autocomplete Suggestions -->
                        <div id="customer_search_dropdown" 
                             style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1px solid var(--slate-200); border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); max-height: 260px; overflow-y: auto; z-index: 1050;">
                        </div>
                    </div>

                    <!-- Selected Customer Confirmation Card -->
                    <div id="selected_customer_card" style="display: none; background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 8px; padding: 10px 14px; margin-bottom: 10px;">
                        <div class="d-flex justify-between align-center">
                            <div class="d-flex align-center gap-2">
                                <span style="background: #16A34A; color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">✓</span>
                                <div>
                                    <div style="font-weight: 700; color: #166534; font-size: 0.875rem;" id="selected_customer_name_display"></div>
                                    <div style="font-size: 0.775rem; color: #15803D;" id="selected_customer_detail_display"></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm" id="btn_change_customer" style="font-size: 0.75rem; padding: 3px 10px; background: #DCFCE7; border: 1px solid #86EFAC; color: #166534; border-radius: 6px; font-weight: 600;">
                                Change
                            </button>
                        </div>
                    </div>

                    <!-- Customer Select Dropdown -->
                    <div class="customer-select-wrapper">
                        <label for="customer_id" class="form-label" style="font-size: 0.75rem; color: var(--slate-500); margin-bottom: 3px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            Or select from registered customer list:
                        </label>
                        <select name="customer_id" id="customer_id" class="form-select" style="border-radius: 8px; font-size: 0.875rem; height: 40px;" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" 
                                        data-name="{{ $c->name }}" 
                                        data-phone="{{ $c->phone }}" 
                                        data-email="{{ $c->email ?? '' }}"
                                        {{ old('customer_id', $booking->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone }}{{ $c->email ? ' | ' . $c->email : '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if($booking->booking_category === 'CORP' || $booking->corporate_company_name)
                <!-- Corporate Dedicated Workflow Fields -->
                <div class="card mb-4" id="corporateFieldsCard" style="background: #F5F3FF; border: 1.5px solid #DDD6FE; border-radius: 8px;">
                    <div class="d-flex justify-between align-center mb-2 flex-wrap gap-2">
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #5B21B6; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <x-icon name="building-2" :size="16" /> Corporate Account & GST Billing Profile
                        </h3>
                        <span class="badge" style="background: #EDE9FE; color: #6D28D9; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 9999px;">
                            {{ count($corporateClients) }} Saved Corporate Accounts
                        </span>
                    </div>

                    <!-- Corporate Client Search & Dropdown Picker -->
                    <div style="background: #ffffff; border: 1.5px solid #C4B5FD; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px;">
                        <div class="d-flex justify-between align-center mb-1">
                            <label style="font-size: 11.5px; font-weight: 700; color: #5B21B6; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                Select Saved Corporate Client or Add New
                            </label>
                            <button type="button" onclick="setNewCorporateClientMode()" style="background: none; border: none; font-size: 11.5px; color: #7C3AED; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;">
                                + Clear & Add New Corporate Client
                            </button>
                        </div>

                        <!-- Live Search Box with Icon -->
                        <div style="position: relative;" class="mb-2">
                            <div style="position: relative; display: flex; align-items: center;">
                                <span style="position: absolute; left: 10px; color: #8B5CF6; pointer-events: none; display: flex; align-items: center;">
                                    <x-icon name="search" :size="14" />
                                </span>
                                <input type="text" 
                                       id="corporate_search_input" 
                                       class="form-control form-control-sm" 
                                       placeholder="🔍 Search saved corporate by Company Name, Contact Person, Mobile, GSTIN..." 
                                       autocomplete="off"
                                       style="padding-left: 32px; padding-right: 30px; border: 1.5px solid #DDD6FE; border-radius: 6px; font-size: 12.5px;"
                                       onkeyup="handleCorporateSearch(this.value)">
                                <button type="button" 
                                        id="btn_clear_corp_search" 
                                        onclick="clearCorporateSearch()" 
                                        style="display: none; position: absolute; right: 8px; background: transparent; border: none; color: #8B5CF6; cursor: pointer; font-size: 14px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;"
                                        title="Clear search">✕</button>
                            </div>

                            <!-- Live floating search results dropdown -->
                            <div id="corporate_search_dropdown" 
                                 style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1.5px solid #8B5CF6; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); max-height: 220px; overflow-y: auto; z-index: 1060;">
                            </div>
                        </div>

                        <!-- Dropdown Select directly -->
                        <div class="d-flex align-center gap-2">
                            <select id="corporate_client_select" class="form-select form-select-sm" onchange="onCorporateDropdownChange(this)" style="font-size: 12px; border: 1.5px solid #DDD6FE; border-radius: 6px;">
                                <option value="">-- Choose Existing Saved Corporate Client --</option>
                                <option value="new" style="font-weight: 800; color: #6D28D9; background: #F5F3FF;">➕ + Add New Corporate Client</option>
                                @foreach($corporateClients as $index => $cc)
                                    <option value="{{ $index }}"
                                            data-company="{{ $cc['company_name'] }}"
                                            data-contact="{{ $cc['contact_person'] }}"
                                            data-phone="{{ $cc['phone'] }}"
                                            data-email="{{ $cc['email'] }}"
                                            data-gstin="{{ $cc['gstin'] }}"
                                            data-address="{{ $cc['billing_address'] }}"
                                            data-user-id="{{ $cc['user_id'] }}"
                                            {{ old('company_name', $booking->corporate_company_name) === $cc['company_name'] ? 'selected' : '' }}>
                                        {{ $cc['company_name'] }} — {{ $cc['contact_person'] ?: 'No contact' }} ({{ $cc['phone'] ? '📞 ' . $cc['phone'] : ($cc['gstin'] ? 'GST: ' . $cc['gstin'] : 'Saved') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selected Corporate Client Badge -->
                        <div id="corporate_selected_card" style="display: {{ old('company_name', $booking->corporate_company_name) ? 'block' : 'none' }}; margin-top: 8px; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; padding: 6px 10px; font-size: 12px; color: #065F46;">
                            <div class="d-flex justify-between align-center">
                                <span id="corporate_selected_text" style="font-weight: 700;">
                                    ✓ Corporate Account: {{ old('company_name', $booking->corporate_company_name) }}
                                </span>
                                <button type="button" onclick="setNewCorporateClientMode()" style="background: none; border: none; color: #047857; text-decoration: underline; font-size: 11px; cursor: pointer; padding: 0;">Change / Clear</button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-3 gap-3 mb-3">
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #4C1D95;">Company / Client Name *</label>
                            <input type="text" name="company_name" id="corp_company_name" class="form-control" value="{{ old('company_name', $booking->corporate_company_name) }}" placeholder="e.g. Jindal Steel / NTPC Limited">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #4C1D95;">Contact Person</label>
                            <input type="text" name="corporate_contact_person" id="corp_contact_person" class="form-control" value="{{ old('corporate_contact_person', $booking->corporate_contact_person) }}" placeholder="e.g. Mr. Anil Verma (Admin Manager)">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #4C1D95;">Corporate GSTIN</label>
                            <input type="text" name="corporate_gstin" id="corp_gstin" class="form-control" value="{{ old('corporate_gstin', $booking->corporate_gstin) }}" placeholder="e.g. 22AAAAA0000A1Z5">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 700; color: #4C1D95;">Corporate Billing Address</label>
                        <input type="text" name="billing_address" id="corp_billing_address" class="form-control" value="{{ old('billing_address', $booking->corporate_billing_address) }}" placeholder="Registered office address for tax invoice...">
                    </div>
                </div>
            @endif

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
                    <select name="trip_type" id="trip_type" class="form-select" onchange="onAdminEditTripTypeChange()" required>
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
                    <div class="form-group" id="vehicleCategoryGroup">
                        <label for="vehicle_category" class="form-label" id="vehicleCategoryLabel">Booked Vehicle Category *</label>
                        <select name="vehicle_category" id="vehicle_category" class="form-select" onchange="onAdminEditCategoryChange()">
                            <option value="">-- Select Category --</option>
                            @php
                                $ambNames = ['Ambulance with Body Freezer', 'Ambulance with Ventilator', 'ICU Ambulance', 'Dead Body Transfer'];
                                $isAmbulanceBooking = str_contains(strtolower($booking->trip_type ?? ''), 'ambulance');
                            @endphp
                            @if($isAmbulanceBooking)
                                @foreach($ambNames as $ambName)
                                    <option value="{{ $ambName }}" {{ old('vehicle_category', $booking->effective_vehicle_category) === $ambName ? 'selected' : '' }}>
                                        {{ $ambName }}
                                    </option>
                                @endforeach
                            @else
                                @if(isset($activeVehicleCategories))
                                    @foreach($activeVehicleCategories as $cat)
                                        @if(!in_array($cat->name, $ambNames))
                                            <option value="{{ $cat->name }}" data-id="{{ $cat->id }}" 
                                                {{ old('vehicle_category', $booking->effective_vehicle_category) === $cat->name ? 'selected' : '' }}>
                                                {{ $cat->name }} ({{ $cat->is_capacity_configurable ? 'Configurable '.$cat->min_seating_capacity.'-'.$cat->max_seating_capacity.' Seats' : $cat->default_seating_capacity.' Seats' }})
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            @endif
                        </select>
                        <input type="hidden" name="vehicle_category_id" id="edit_vehicle_category_id" value="{{ old('vehicle_category_id', $booking->vehicle_category_id) }}">
                    </div>

                    <div class="form-group" id="vehicleModelGroup" style="{{ $isAmbulanceBooking ? 'display: none;' : '' }}">
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
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="vehicle_id" class="form-label mb-0" style="font-weight: 700;">Assign Vehicle</label>
                            <button type="button" onclick="openQuickVehicleModal()" style="background: none; border: none; color: #2563EB; font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: underline; padding: 0;">
                                + Add New Vehicle
                            </button>
                        </div>
                        <select name="vehicle_id" id="vehicle_id" class="form-select" onchange="onAdminVehicleSelectChange(this)">
                            <option value="">-- No Vehicle Assigned --</option>
                            <option value="new_vehicle" style="font-weight: 800; color: #2563EB; background: #EFF6FF;">➕ + Add New Vehicle</option>
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

<!-- Quick Add Fleet Vehicle Modal -->
<div id="quickVehicleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 500px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.25); max-height: 90vh; overflow-y: auto;">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">➕ Quick Add Fleet Vehicle</h3>
            <button type="button" onclick="closeQuickVehicleModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="quickVehicleForm" onsubmit="submitQuickVehicle(event)">
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Vehicle Name & Make *</label>
                <input type="text" id="qv_name" class="form-control form-control-sm" placeholder="e.g. Maruti Suzuki Dzire VXI" required>
            </div>
            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">RTO Registration Number *</label>
                    <input type="text" id="qv_reg" class="form-control form-control-sm" placeholder="e.g. CG 10 AB 4501" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Vehicle Category *</label>
                    <select id="qv_category" class="form-select form-select-sm" required>
                        @foreach($activeVehicleCategories ?? [] as $cat)
                            <option value="{{ $cat->id }}" data-name="{{ $cat->name }}" data-capacity="{{ $cat->default_seating_capacity }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Seating Capacity *</label>
                    <input type="number" id="qv_capacity" class="form-control form-control-sm" value="4" min="1" max="70" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Air Conditioning</label>
                    <select id="qv_ac" class="form-select form-select-sm">
                        <option value="AC" selected>AC</option>
                        <option value="Non-AC">Non-AC</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Ownership</label>
                <select id="qv_ownership" class="form-select form-select-sm">
                    <option value="owned" selected>Own Fleet Vehicle</option>
                    <option value="third_party">Third-Party Travel Partner Cab</option>
                </select>
            </div>
            <div id="qv_error" style="display: none; color: #dc2626; font-size: 12px; margin-bottom: 10px;"></div>
            <div class="d-flex justify-end gap-2">
                <button type="button" onclick="closeQuickVehicleModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" id="qv_submit_btn" class="btn btn-primary btn-sm">Register & Assign Vehicle</button>
            </div>
        </form>
    </div>
</div>

<script>
    const corporateClientsData = @json($corporateClients ?? []);
    let lastValidVehicle = @json(old('vehicle_id', $booking->vehicle_id));

    function handleCorporateSearch(q) {
        q = (q || '').trim().toLowerCase();
        const dropdown = document.getElementById('corporate_search_dropdown');
        const clearBtn = document.getElementById('btn_clear_corp_search');
        if (!dropdown) return;

        if (!q) {
            dropdown.style.display = 'none';
            if (clearBtn) clearBtn.style.display = 'none';
            return;
        }

        if (clearBtn) clearBtn.style.display = 'flex';

        const filtered = corporateClientsData.filter(c => {
            return (c.company_name && c.company_name.toLowerCase().includes(q)) ||
                   (c.contact_person && c.contact_person.toLowerCase().includes(q)) ||
                   (c.phone && c.phone.includes(q)) ||
                   (c.gstin && c.gstin.toLowerCase().includes(q));
        });

        let html = '';
        html += `<div onclick="setNewCorporateClientMode('${q.replace(/'/g, "\\'")}')" style="padding: 9px 12px; border-bottom: 1.5px solid #DDD6FE; background: #F5F3FF; cursor: pointer; font-weight: 700; color: #6D28D9; font-size: 12px; display: flex; align-items: center; gap: 6px;">
            <span>➕</span> <span>+ Add New Corporate Client: <strong>"${q}"</strong></span>
        </div>`;

        if (filtered.length === 0) {
            html += `<div style="padding: 10px 12px; font-size: 12px; color: var(--slate-500);">No saved corporate accounts matching "${q}". Click above to add as new.</div>`;
        } else {
            filtered.forEach(c => {
                const origIdx = corporateClientsData.indexOf(c);
                html += `<div onclick="selectCorporateClient(${origIdx})" style="padding: 8px 12px; border-bottom: 1px solid var(--slate-100); cursor: pointer; font-size: 12px; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <div style="font-weight: 700; color: #1E293B;">${c.company_name}</div>
                    <div style="font-size: 11px; color: var(--slate-500); display: flex; gap: 8px; flex-wrap: wrap;">
                        <span>👤 ${c.contact_person || 'No contact'}</span>
                        ${c.phone ? `<span>📞 ${c.phone}</span>` : ''}
                        ${c.gstin ? `<span>🏛️ GST: ${c.gstin}</span>` : ''}
                    </div>
                </div>`;
            });
        }

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
    }

    function clearCorporateSearch() {
        const inp = document.getElementById('corporate_search_input');
        if (inp) inp.value = '';
        const dropdown = document.getElementById('corporate_search_dropdown');
        if (dropdown) dropdown.style.display = 'none';
        const clearBtn = document.getElementById('btn_clear_corp_search');
        if (clearBtn) clearBtn.style.display = 'none';
    }

    function onCorporateDropdownChange(sel) {
        if (sel.value === 'new') {
            setNewCorporateClientMode();
        } else if (sel.value !== '') {
            selectCorporateClient(parseInt(sel.value));
        }
    }

    function selectCorporateClient(idx) {
        const c = corporateClientsData[idx];
        if (!c) return;

        document.getElementById('corp_company_name').value = c.company_name || '';
        document.getElementById('corp_contact_person').value = c.contact_person || '';
        document.getElementById('corp_gstin').value = c.gstin || '';
        document.getElementById('corp_billing_address').value = c.billing_address || '';

        const sel = document.getElementById('corporate_client_select');
        if (sel) sel.value = idx;

        const card = document.getElementById('corporate_selected_card');
        const text = document.getElementById('corporate_selected_text');
        if (card && text) {
            text.textContent = `✓ Selected: ${c.company_name} (Contact: ${c.contact_person || 'N/A'})`;
            card.style.display = 'block';
            card.style.background = '#ECFDF5';
            card.style.borderColor = '#A7F3D0';
            card.style.color = '#065F46';
        }

        const dropdown = document.getElementById('corporate_search_dropdown');
        if (dropdown) dropdown.style.display = 'none';
    }

    function setNewCorporateClientMode(prefillName = '') {
        document.getElementById('corp_company_name').value = prefillName;
        document.getElementById('corp_contact_person').value = '';
        document.getElementById('corp_gstin').value = '';
        document.getElementById('corp_billing_address').value = '';

        const sel = document.getElementById('corporate_client_select');
        if (sel) sel.value = 'new';

        const card = document.getElementById('corporate_selected_card');
        const text = document.getElementById('corporate_selected_text');
        if (card && text) {
            text.textContent = `➕ New Corporate Client (Details will be updated upon saving)`;
            card.style.display = 'block';
            card.style.background = '#EFF6FF';
            card.style.borderColor = '#BFDBFE';
            card.style.color = '#1E40AF';
        }

        clearCorporateSearch();
        document.getElementById('corp_company_name').focus();
    }

    // Quick Add Vehicle functions
    function openQuickVehicleModal() {
        const m = document.getElementById('quickVehicleModal');
        if (m) {
            m.style.display = 'flex';
            document.getElementById('qv_name').focus();
        }
    }

    function closeQuickVehicleModal() {
        const m = document.getElementById('quickVehicleModal');
        if (m) m.style.display = 'none';
        const vSel = document.getElementById('vehicle_id');
        if (vSel && vSel.value === 'new_vehicle') {
            vSel.value = lastValidVehicle;
        }
    }

    function onAdminVehicleSelectChange(sel) {
        if (sel.value === 'new_vehicle') {
            openQuickVehicleModal();
        } else {
            lastValidVehicle = sel.value;
        }
    }

    async function submitQuickVehicle(e) {
        e.preventDefault();
        const btn = document.getElementById('qv_submit_btn');
        const err = document.getElementById('qv_error');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        err.style.display = 'none';

        const name = document.getElementById('qv_name').value.trim();
        const reg = document.getElementById('qv_reg').value.trim();
        const catSelect = document.getElementById('qv_category');
        const catId = catSelect ? catSelect.value : null;
        const catName = catSelect ? catSelect.options[catSelect.selectedIndex]?.dataset?.name : 'Sedan';
        const cap = parseInt(document.getElementById('qv_capacity').value) || 4;
        const ac = document.getElementById('qv_ac').value;
        const own = document.getElementById('qv_ownership').value;

        try {
            const resp = await fetch('{{ route('admin.vehicles.quick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    registration_number: reg,
                    vehicle_category_id: catId,
                    vehicle_type: catName,
                    seating_capacity: cap,
                    ac_non_ac: ac,
                    ownership_type: own
                })
            });
            const data = await resp.json();
            if (data.success && data.vehicle) {
                const veh = data.vehicle;
                const vSel = document.getElementById('vehicle_id');
                const opt = document.createElement('option');
                opt.value = veh.id;
                opt.textContent = `${veh.name} - ${veh.registration_number} (${veh.vehicle_type} • ${veh.status})`;
                vSel.appendChild(opt);
                vSel.value = veh.id;
                lastValidVehicle = veh.id;
                closeQuickVehicleModal();
                alert(`✓ Vehicle '${veh.name} (${veh.registration_number})' registered and allocated!`);
            } else {
                err.textContent = data.message || 'Error saving vehicle.';
                err.style.display = 'block';
            }
        } catch (ex) {
            err.textContent = 'Server error: ' + ex.message;
            err.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Register & Assign Vehicle';
        }
    }
    const activeVehicleCategoriesData = @json($activeVehicleCategories ?? []);
    const initialModelId = @json(old('vehicle_model_id', $booking->vehicle_model_id));
    const initialModelName = @json(old('vehicle_model', $booking->effective_vehicle_model));
    let initialCategoryLoaded = false;

    const ambulanceCategoryOptions = [
        { name: 'Ambulance with Body Freezer', label: '❄️ Ambulance with Body Freezer' },
        { name: 'Ambulance with Ventilator', label: '🫁 Ambulance with Ventilator' },
        { name: 'ICU Ambulance', label: '🏥 ICU Ambulance' },
        { name: 'Dead Body Transfer', label: '🕊️ Dead Body Transfer' }
    ];
    const ambulanceCategoryNames = ambulanceCategoryOptions.map(a => a.name);
    const standardCategoriesList = activeVehicleCategoriesData.filter(c => !ambulanceCategoryNames.includes(c.name));

    function syncEditVehicleCategoriesForTripType(preferredCategory) {
        const tripTypeSelect = document.getElementById('trip_type');
        const tripVal = tripTypeSelect ? tripTypeSelect.value : '';
        const isAmbulance = tripVal === 'Ambulance' || tripVal.toLowerCase().includes('ambulance');
        const vehCatSelect = document.getElementById('vehicle_category');
        const vehCatLabel = document.getElementById('vehicleCategoryLabel');
        const modelGroup = document.getElementById('vehicleModelGroup');

        if (!vehCatSelect) return;

        const currentVal = (preferredCategory !== undefined && preferredCategory !== '') ? preferredCategory : vehCatSelect.value;
        vehCatSelect.innerHTML = '';

        if (isAmbulance) {
            if (vehCatLabel) vehCatLabel.textContent = 'Ambulance Category *';
            if (modelGroup) modelGroup.style.display = 'none';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Choose Ambulance Category --';
            vehCatSelect.appendChild(defaultOpt);

            ambulanceCategoryOptions.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.name;
                opt.textContent = item.label;
                const dbCat = activeVehicleCategoriesData.find(c => c.name === item.name);
                if (dbCat) {
                    opt.dataset.id = dbCat.id;
                }
                if (currentVal === item.name) {
                    opt.selected = true;
                }
                vehCatSelect.appendChild(opt);
            });
        } else {
            if (vehCatLabel) vehCatLabel.textContent = 'Booked Vehicle Category *';
            if (modelGroup) modelGroup.style.display = 'block';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Select Category --';
            vehCatSelect.appendChild(defaultOpt);

            standardCategoriesList.forEach(catObj => {
                const opt = document.createElement('option');
                opt.value = catObj.name;
                opt.dataset.id = catObj.id;
                const cap = catObj.is_capacity_configurable ? `Configurable ${catObj.min_seating_capacity}-${catObj.max_seating_capacity} Seats` : `${catObj.default_seating_capacity} Seats`;
                opt.textContent = `${catObj.name} (${cap})`;
                if (currentVal === catObj.name) {
                    opt.selected = true;
                }
                vehCatSelect.appendChild(opt);
            });
        }

        onAdminEditCategoryChange();
    }

    function onAdminEditTripTypeChange() {
        syncEditVehicleCategoriesForTripType();
    }

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
        syncEditVehicleCategoriesForTripType(@json(old('vehicle_category', $booking->effective_vehicle_category)));
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

    // Searchable Customer Dropdown & Autocomplete Handler
    (function initEditCustomerSearchableSelect() {
        const searchInput = document.getElementById('customer_search_input');
        const clearBtn = document.getElementById('clear_customer_search');
        const dropdown = document.getElementById('customer_search_dropdown');
        const select = document.getElementById('customer_id');
        const countBadge = document.getElementById('customerCountBadge');
        const selectedCard = document.getElementById('selected_customer_card');
        const selectedNameDisplay = document.getElementById('selected_customer_name_display');
        const selectedDetailDisplay = document.getElementById('selected_customer_detail_display');
        const btnChange = document.getElementById('btn_change_customer');

        if (!searchInput || !select || !dropdown) return;

        const customers = [];
        Array.from(select.options).forEach(opt => {
            if (!opt.value) return;
            const name = opt.getAttribute('data-name') || opt.text.split('(')[0].trim();
            const phone = opt.getAttribute('data-phone') || '';
            const email = opt.getAttribute('data-email') || '';
            const cleanPhone = (phone || '').replace(/\D/g, '');

            customers.push({
                id: opt.value,
                name: name,
                phone: phone,
                cleanPhone: cleanPhone,
                email: email,
                label: `${name} (${phone}${email ? ' | ' + email : ''})`
            });
        });

        let activeIndex = -1;
        let currentFiltered = [];

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function highlightMatch(text, query) {
            if (!query || !query.trim()) return escapeHtml(text);
            const escapedQuery = query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escapedQuery})`, 'gi');
            return escapeHtml(text).replace(regex, '<mark style="background:#FEF08A;color:#854D0E;padding:0 2px;border-radius:2px;font-weight:700;">$1</mark>');
        }

        function renderDropdown(items, query) {
            currentFiltered = items;
            activeIndex = -1;
            dropdown.innerHTML = '';

            if (items.length === 0) {
                const noMatch = document.createElement('div');
                noMatch.style.padding = '14px';
                noMatch.style.textAlign = 'center';
                noMatch.style.color = 'var(--slate-500)';
                noMatch.style.fontSize = '0.85rem';
                const qSafe = escapeHtml(query || '');
                noMatch.innerHTML = `<div>No registered customer matches <strong>"${qSafe}"</strong></div>`;
                dropdown.appendChild(noMatch);
                dropdown.style.display = 'block';
                return;
            }

            const maxDisplay = 15;
            const displayItems = items.slice(0, maxDisplay);

            displayItems.forEach((c, idx) => {
                const itemEl = document.createElement('div');
                itemEl.className = 'customer-search-item';
                itemEl.dataset.id = c.id;
                itemEl.dataset.index = idx;
                itemEl.style.cssText = 'padding: 9px 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.15s;';

                const nameHtml = highlightMatch(c.name, query);
                const phoneHtml = c.phone ? highlightMatch(c.phone, query) : '';
                const emailHtml = c.email ? `<span style="font-size: 0.75rem; color: #64748b; margin-left: 6px;">${escapeHtml(c.email)}</span>` : '';

                itemEl.innerHTML = `
                    <div style="flex: 1; min-width: 0; padding-right: 8px;">
                        <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            👤 ${nameHtml}
                        </div>
                        ${emailHtml ? `<div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${emailHtml}</div>` : ''}
                    </div>
                    ${phoneHtml ? `
                    <div style="flex-shrink: 0;">
                        <span style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; padding: 2px 7px; border-radius: 6px; font-size: 0.775rem; font-weight: 700; font-family: monospace;">
                            📞 ${phoneHtml}
                        </span>
                    </div>` : ''}
                `;

                itemEl.addEventListener('mouseenter', () => {
                    setActiveIndex(idx);
                });

                itemEl.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectCustomer(c.id);
                });

                dropdown.appendChild(itemEl);
            });

            if (items.length > maxDisplay) {
                const footerEl = document.createElement('div');
                footerEl.style.cssText = 'padding: 6px 12px; font-size: 0.75rem; color: #64748b; background: #f8fafc; text-align: center; font-style: italic;';
                footerEl.textContent = `Showing 15 of ${items.length} matching customers. Type more to narrow down.`;
                dropdown.appendChild(footerEl);
            }

            dropdown.style.display = 'block';
        }

        function setActiveIndex(index) {
            activeIndex = index;
            const allItems = dropdown.querySelectorAll('.customer-search-item');
            allItems.forEach((el, idx) => {
                if (idx === index) {
                    el.style.background = '#EFF6FF';
                } else {
                    el.style.background = '#ffffff';
                }
            });
        }

        function filterCustomers(query) {
            const trimmed = (query || '').trim().toLowerCase();
            const cleanQueryDigits = trimmed.replace(/\D/g, '');

            if (!trimmed) {
                return customers;
            }

            return customers.filter(c => {
                const nameMatch = c.name.toLowerCase().includes(trimmed);
                const emailMatch = c.email.toLowerCase().includes(trimmed);
                const rawPhoneMatch = c.phone.toLowerCase().includes(trimmed);
                const digitsMatch = cleanQueryDigits.length > 0 && c.cleanPhone.includes(cleanQueryDigits);
                return nameMatch || emailMatch || rawPhoneMatch || digitsMatch;
            });
        }

        function syncSelectOptions(matchingItems) {
            const matchingIds = new Set(matchingItems.map(m => String(m.id)));
            const currentVal = select.value;

            select.innerHTML = '';

            customers.forEach(c => {
                if (matchingIds.has(String(c.id))) {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.setAttribute('data-name', c.name);
                    opt.setAttribute('data-phone', c.phone);
                    opt.setAttribute('data-email', c.email);
                    opt.textContent = c.label;
                    if (String(c.id) === String(currentVal)) {
                        opt.selected = true;
                    }
                    select.appendChild(opt);
                }
            });

            if (countBadge) {
                countBadge.textContent = `${matchingItems.length} Available`;
            }
        }

        function selectCustomer(id, updateSelect = true) {
            const c = customers.find(item => String(item.id) === String(id));
            if (!c) return;

            if (updateSelect) {
                let opt = select.querySelector(`option[value="${c.id}"]`);
                if (!opt) {
                    opt = document.createElement('option');
                    opt.value = c.id;
                    opt.setAttribute('data-name', c.name);
                    opt.setAttribute('data-phone', c.phone);
                    opt.setAttribute('data-email', c.email);
                    opt.textContent = c.label;
                    select.appendChild(opt);
                }
                select.value = c.id;
                select.dispatchEvent(new Event('change'));
            }

            searchInput.value = `${c.name} — ${c.phone}`;
            if (clearBtn) clearBtn.style.display = 'flex';

            if (selectedCard) {
                if (selectedNameDisplay) selectedNameDisplay.textContent = c.name;
                if (selectedDetailDisplay) {
                    const details = [
                        c.phone ? `📞 ${c.phone}` : '',
                        c.email ? `✉️ ${c.email}` : ''
                    ].filter(Boolean).join('  ·  ');
                    selectedDetailDisplay.textContent = details;
                }
                selectedCard.style.display = 'block';
            }

            dropdown.style.display = 'none';
        }

        function clearSelection() {
            searchInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            if (selectedCard) selectedCard.style.display = 'none';

            syncSelectOptions(customers);
            select.value = '';
            select.dispatchEvent(new Event('change'));

            renderDropdown(customers.slice(0, 15), '');
            searchInput.focus();
        }

        searchInput.addEventListener('input', function() {
            const q = this.value;
            if (clearBtn) {
                clearBtn.style.display = q.length > 0 ? 'flex' : 'none';
            }
            const filtered = filterCustomers(q);
            renderDropdown(filtered, q);
            syncSelectOptions(filtered);
        });

        searchInput.addEventListener('focus', function() {
            const q = this.value;
            const filtered = filterCustomers(q);
            renderDropdown(filtered, q);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (dropdown.style.display === 'none') {
                if (e.key === 'ArrowDown') {
                    const filtered = filterCustomers(this.value);
                    renderDropdown(filtered, this.value);
                    e.preventDefault();
                }
                return;
            }

            const visibleCount = currentFiltered.slice(0, 15).length;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (visibleCount > 0) {
                    setActiveIndex((activeIndex + 1) % visibleCount);
                    const activeEl = dropdown.querySelector(`[data-index="${activeIndex}"]`);
                    if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (visibleCount > 0) {
                    setActiveIndex((activeIndex - 1 + visibleCount) % visibleCount);
                    const activeEl = dropdown.querySelector(`[data-index="${activeIndex}"]`);
                    if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && activeIndex < currentFiltered.length) {
                    e.preventDefault();
                    selectCustomer(currentFiltered[activeIndex].id);
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearSelection();
            });
        }

        if (btnChange) {
            btnChange.addEventListener('click', function(e) {
                e.preventDefault();
                clearSelection();
            });
        }

        select.addEventListener('change', function() {
            if (this.value) {
                selectCustomer(this.value, false);
            } else {
                searchInput.value = '';
                if (clearBtn) clearBtn.style.display = 'none';
                if (selectedCard) selectedCard.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            const container = searchInput.closest('.customer-search-container');
            if (container && !container.contains(e.target) && e.target !== btnChange) {
                dropdown.style.display = 'none';
            }
        });

        if (select.value) {
            selectCustomer(select.value, false);
        }
    })();
</script>
@endsection
