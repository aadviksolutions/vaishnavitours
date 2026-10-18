@extends('layouts.admin')

@section('title', 'Create New Booking')
@section('page_title', 'New Booking Creation')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.bookings.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to All Bookings
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Create Manual Cab Booking</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Create a dispatch reservation for phone, walk-in, or corporate customer bookings.</p>
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

@if(isset($prefill) && $prefill)
    <div class="alert alert-info mb-4" style="background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; padding: 0.85rem 1.25rem; border-radius: 8px;">
        <strong style="display: flex; align-items: center; gap: 6px;">
            <x-icon name="mail" size="16" /> 
            Converting Enquiry #{{ $prefill->enquiry_id }} ({{ $prefill->name }} · {{ $prefill->phone }})
        </strong>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem;">
            Customer and trip route details have been automatically filled from the original enquiry record.
        </p>
        <input type="hidden" name="from_enquiry_id" value="{{ $prefill->id }}">
    </div>
@endif

<form action="{{ route('admin.bookings.store') }}" method="POST">
    @csrf

    <!-- SECTION 4: Mandatory Booking Category Selection (CORP, MAN, TP) -->
    <div class="card mb-4" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
        <div style="font-weight: 800; font-size: 1rem; color: var(--dark-900); margin-bottom: 0.75rem;">
            🏷️ 1. Booking Category / Source Classification *
        </div>
        <p style="font-size: 0.8rem; color: var(--slate-500); margin: 0 0 0.85rem 0;">
            Select the booking source. Unique collision-safe booking ID sequence will be assigned with appropriate prefix (CORP, MAN, TP).
        </p>
        <div class="grid grid-3 gap-3">
            <label style="border: 2px solid {{ old('booking_category', isset($prefill) && $prefill->source === 'corporate' ? 'CORP' : (isset($prefill) && $prefill->source === 'third_party' ? 'TP' : 'MAN')) === 'MAN' ? '#2563EB' : 'var(--slate-200)' }}; background: {{ old('booking_category', 'MAN') === 'MAN' ? '#EFF6FF' : '#fff' }}; padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;" id="lblCatMan">
                <input type="radio" name="booking_category" value="MAN" {{ old('booking_category', isset($prefill) && $prefill->source === 'corporate' ? 'CORP' : (isset($prefill) && $prefill->source === 'third_party' ? 'TP' : 'MAN')) === 'MAN' ? 'checked' : '' }} onchange="toggleCategoryFields()">
                <div>
                    <strong style="color: #1E40AF; display: block; font-size: 13px;">MAN — Manual Booking</strong>
                    <span style="font-size: 11px; color: var(--slate-500);">Direct phone / walk-in customer</span>
                </div>
            </label>

            <label style="border: 2px solid {{ old('booking_category') === 'CORP' ? '#7C3AED' : 'var(--slate-200)' }}; background: {{ old('booking_category') === 'CORP' ? '#F5F3FF' : '#fff' }}; padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;" id="lblCatCorp">
                <input type="radio" name="booking_category" value="CORP" {{ old('booking_category', isset($prefill) && $prefill->source === 'corporate' ? 'CORP' : '') === 'CORP' ? 'checked' : '' }} onchange="toggleCategoryFields()">
                <div>
                    <strong style="color: #6D28D9; display: block; font-size: 13px;">CORP — Corporate Booking</strong>
                    <span style="font-size: 11px; color: var(--slate-500);">B2B Company account & GST billing</span>
                </div>
            </label>

            <label style="border: 2px solid {{ old('booking_category') === 'TP' ? '#D97706' : 'var(--slate-200)' }}; background: {{ old('booking_category') === 'TP' ? '#FFFBEB' : '#fff' }}; padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;" id="lblCatTp">
                <input type="radio" name="booking_category" value="TP" {{ old('booking_category', isset($prefill) && $prefill->source === 'third_party' ? 'TP' : '') === 'TP' ? 'checked' : '' }} onchange="toggleCategoryFields()">
                <div>
                    <strong style="color: #B45309; display: block; font-size: 13px;">TP — Travel Partner / Agent</strong>
                    <span style="font-size: 11px; color: var(--slate-500);">Third-party partner reservation</span>
                </div>
            </label>
        </div>

        <!-- Corporate Dedicated Workflow Fields (Section 4 requirement) -->
        <div id="corporateFieldsCard" style="display: {{ old('booking_category', isset($prefill) && $prefill->source === 'corporate' ? 'CORP' : 'MAN') === 'CORP' ? 'block' : 'none' }}; margin-top: 1rem; padding: 1rem; background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 6px;">
            <div style="font-weight: 700; font-size: 13px; color: #5B21B6; margin-bottom: 0.5rem;">
                🏢 Corporate Account & GST Billing Profile
            </div>
            <div class="grid grid-3 gap-2 mb-2">
                <div>
                    <label style="font-size: 11px; font-weight: 600; color: #4C1D95; display: block; margin-bottom: 2px;">Company / Client Name *</label>
                    <input type="text" name="company_name" class="form-control form-control-sm" placeholder="e.g. Jindal Steel / NTPC Limited" value="{{ old('company_name') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 600; color: #4C1D95; display: block; margin-bottom: 2px;">Contact Person</label>
                    <input type="text" name="corporate_contact_person" class="form-control form-control-sm" placeholder="e.g. Mr. Anil Verma (Admin Manager)" value="{{ old('corporate_contact_person') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 600; color: #4C1D95; display: block; margin-bottom: 2px;">Corporate GSTIN</label>
                    <input type="text" name="corporate_gstin" class="form-control form-control-sm" placeholder="e.g. 22AAAAA0000A1Z5" value="{{ old('corporate_gstin') }}">
                </div>
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 600; color: #4C1D95; display: block; margin-bottom: 2px;">Corporate Billing Address</label>
                <input type="text" name="billing_address" class="form-control form-control-sm" placeholder="Registered office address for tax invoice..." value="{{ old('billing_address') }}">
            </div>
        </div>

        <!-- Travel Partner Dedicated Workflow Fields -->
        <div id="travelPartnerFieldsCard" style="display: {{ old('booking_category', isset($prefill) && $prefill->source === 'third_party' ? 'TP' : 'MAN') === 'TP' ? 'block' : 'none' }}; margin-top: 1rem; padding: 1.15rem; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px;">
            <div style="font-weight: 800; font-size: 13.5px; color: #92400E; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 6px;">
                🤝 Travel Partner / Agent Referral Details
            </div>
            <div class="grid grid-3 gap-3 mb-2">
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Travel Partner / Agency Name *</label>
                    <input type="text" name="travel_partner_name" class="form-control form-control-sm" placeholder="e.g. MakeMyTrip, Sharma Tour & Travels" value="{{ old('travel_partner_name', isset($prefill) && $prefill->source === 'third_party' ? $prefill->name : '') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Mobile / Phone *</label>
                    <input type="text" name="travel_partner_phone" class="form-control form-control-sm" placeholder="e.g. 9876543210" value="{{ old('travel_partner_phone', isset($prefill) && $prefill->source === 'third_party' ? $prefill->phone : '') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Email</label>
                    <input type="email" name="travel_partner_email" class="form-control form-control-sm" placeholder="e.g. partner@travelagency.com" value="{{ old('travel_partner_email', isset($prefill) && $prefill->source === 'third_party' ? $prefill->email : '') }}">
                </div>
            </div>
            <div class="grid grid-2 gap-3 mb-2">
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Office Address</label>
                    <input type="text" name="travel_partner_address" class="form-control form-control-sm" placeholder="Shop / Office address, City, Pin..." value="{{ old('travel_partner_address') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Commission / Payout (₹)</label>
                    <input type="number" step="0.01" name="travel_partner_commission" class="form-control form-control-sm" placeholder="e.g. 500.00" value="{{ old('travel_partner_commission') }}">
                </div>
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Reference Notes / Special Agreement</label>
                <input type="text" name="travel_partner_notes" class="form-control form-control-sm" placeholder="e.g. Reference Voucher #MMT-8921, Payout after trip completion" value="{{ old('travel_partner_notes') }}">
            </div>
        </div>
    </div>

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Customer & Trip Details -->
        <div style="grid-column: span 2;">
            <!-- Customer Selection Card -->
            <div class="card mb-4">
                <div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
                    <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0;">👤 2. Passenger & Customer Details</h3>
                    <!-- Mode Switch Buttons -->
                    <div class="d-flex gap-1" style="background: var(--slate-100); padding: 4px; border-radius: 8px;">
                        <button type="button" id="modeExistingBtn" class="btn btn-sm" onclick="setCustomerMode('existing')" style="border-radius: 6px; font-weight: 700; font-size: 0.8rem; padding: 5px 14px; transition: all 0.2s;">
                            👥 Existing Customer
                        </button>
                        <button type="button" id="modeNewBtn" class="btn btn-sm" onclick="setCustomerMode('new')" style="border-radius: 6px; font-weight: 700; font-size: 0.8rem; padding: 5px 14px; transition: all 0.2s;">
                            ➕ New Customer
                        </button>
                    </div>
                </div>

                <input type="hidden" name="customer_mode" id="customer_mode" value="{{ old('customer_mode', (isset($prefill) || old('new_customer_name') || old('new_customer_phone')) ? 'new' : 'existing') }}">

                <!-- Existing Customer Mode -->
                <div id="existingCustomerSection">
                    <div class="form-group mb-2">
                        <label for="customer_id" class="form-label">Select Registered Customer *</label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">-- Choose Existing Customer --</option>
                            <option value="new" style="font-weight: 700; color: var(--primary);">✨ + Add New Customer Instead</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} (📞 {{ $c->phone }} | ✉️ {{ $c->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p style="font-size: 0.8rem; color: var(--slate-500); margin: 0;">
                        Can't find the customer? Click <strong>➕ New Customer</strong> above to enter customer name & phone directly.
                    </p>
                </div>

                <!-- New Customer Mode -->
                <div id="newCustomerSection" style="display: none; background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 8px; padding: 1.25rem;">
                    <div class="d-flex align-center gap-2 mb-3">
                        <span style="font-size: 1.25rem;">✨</span>
                        <div>
                            <strong style="color: #92400E; font-size: 0.95rem;">Enter New Passenger / Customer Information</strong>
                            <p style="font-size: 0.8rem; color: #B45309; margin: 0;">A customer account will be created automatically and linked to this booking.</p>
                        </div>
                    </div>

                    <div class="grid grid-2 gap-3 mb-3">
                        <div class="form-group">
                            <label for="new_customer_name" class="form-label">Full Name *</label>
                            <input type="text" name="new_customer_name" id="new_customer_name" class="form-control" placeholder="e.g. Rajesh Sharma" value="{{ old('new_customer_name', $prefill->name ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label for="new_customer_phone" class="form-label">Mobile / WhatsApp Number *</label>
                            <input type="tel" name="new_customer_phone" id="new_customer_phone" class="form-control" placeholder="e.g. 9876543210" value="{{ old('new_customer_phone', $prefill->phone ?? '') }}">
                        </div>
                    </div>

                    <div class="grid grid-3 gap-3">
                        <div class="form-group">
                            <label for="new_customer_email" class="form-label">Email Address (Optional)</label>
                            <input type="email" name="new_customer_email" id="new_customer_email" class="form-control" placeholder="e.g. customer@gmail.com" value="{{ old('new_customer_email', $prefill->email ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label for="new_customer_city" class="form-label">City / Area</label>
                            <input type="text" name="new_customer_city" id="new_customer_city" class="form-control" placeholder="e.g. Bilaspur / Raipur" value="{{ old('new_customer_city', 'Bilaspur') }}">
                        </div>

                        <div class="form-group">
                            <label for="new_customer_address" class="form-label">Pickup Address / Landmark</label>
                            <input type="text" name="new_customer_address" id="new_customer_address" class="form-control" placeholder="e.g. Near Railway Station" value="{{ old('new_customer_address') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Route & Schedule Card -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">🚖 3. Route & Schedule Information</h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="trip_type" class="form-label">Trip Type *</label>
                        <select name="rate_category" id="rate_category" class="form-select" required>
                            @foreach(['outstation' => 'Outstation Route', 'airport' => 'Quick Airport Booking', 'round_trip' => 'Round Trip', 'local_8h_80km' => 'Local 8 Hours / 80 KM', 'local_4h_40km' => 'Local 4 Hours / 40 KM'] as $key => $label)
                                <option value="{{ $key }}" {{ old('rate_category', isset($prefill) && $prefill->booking_type === 'Round-Trip' ? 'round_trip' : 'outstation') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tour_package_id" class="form-label">Linked Tour Package (Optional)</label>
                        <select name="tour_package_id" id="tour_package_id" class="form-select">
                            <option value="">-- No Tour Package --</option>
                            @if(isset($tourPackages))
                                @foreach($tourPackages as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tour_package_id') == $tp->id ? 'selected' : '' }}>{{ $tp->name }} ({{ $tp->duration_days }} Days)</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group">
                        <label for="pickup_location" class="form-label">Pickup Location *</label>
                        <input type="text" name="pickup_location" id="pickup_location" class="form-control" list="pickupLocationsList" value="{{ old('pickup_location', $prefill->pickup_location ?? 'Bilaspur') }}" required>
                        <datalist id="pickupLocationsList">
                            @foreach($locations as $location)<option value="{{ $location }}">@endforeach
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label for="via_location" class="form-label">Via Location (Round-Trip)</label>
                        <input type="text" name="via_location" id="via_location" class="form-control" placeholder="e.g. Amarkantak" value="{{ old('via_location', $prefill->via_location ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="destination" class="form-label">Drop Destination *</label>
                        <input type="text" name="destination" id="destination" class="form-control" list="destinationLocationsList" value="{{ old('destination', $prefill->destination ?? 'Raipur') }}" required>
                        <datalist id="destinationLocationsList">
                            @foreach($locations as $location)<option value="{{ $location }}">@endforeach
                        </datalist>
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="vehicle_category" class="form-label">Rate vehicle category *</label>
                        <select name="vehicle_category" id="vehicle_category" class="form-select" required>
                            <option value="">Select vehicle category</option>
                            @foreach($vehicleCategories as $category)<option value="{{ $category }}" {{ old('vehicle_category') === $category ? 'selected' : '' }}>{{ $category }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group" id="estimatedKmGroup" style="display: none;">
                        <label for="estimated_km" class="form-label">Estimated round-trip distance (KM) *</label>
                        <input type="number" min="0.01" step="0.1" name="estimated_km" id="estimated_km" class="form-control" value="{{ old('estimated_km') }}">
                    </div>
                </div>

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group">
                        <label for="travel_date" class="form-label">Travel Date *</label>
                        <input type="date" name="travel_date" id="travel_date" class="form-control" value="{{ old('travel_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="travel_time" class="form-label">Pickup Time *</label>
                        <input type="time" name="travel_time" id="travel_time" class="form-control" value="{{ old('travel_time', '09:00') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="return_date" class="form-label">Return Date (If Round Trip)</label>
                        <input type="date" name="return_date" id="return_date" class="form-control" value="{{ old('return_date') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Dispatcher Notes / Passenger Requests</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Luggage details, flight timings, special route requests...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Fleet & Driver Assignment Card -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">🚗 Immediate Fleet & Chauffeur Assignment (Optional)</h3>

                <div class="grid grid-2 gap-3">
                    <div class="form-group">
                        <label for="vehicle_id" class="form-label">Assign Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-select">
                            <option value="">-- Assign Later --</option>
                            @foreach($vehicles as $veh)
                                <option value="{{ $veh->id }}" {{ old('vehicle_id') == $veh->id ? 'selected' : '' }}>
                                    {{ $veh->name }} - {{ $veh->registration_number }} ({{ $veh->vehicle_type }} • {{ $veh->status }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="driver_id" class="form-label">Assign Chauffeur / Driver</label>
                        <select name="driver_id" id="driver_id" class="form-select">
                            <option value="">-- Assign Later --</option>
                            @foreach($drivers as $drv)
                                <option value="{{ $drv->id }}" {{ old('driver_id') == $drv->id ? 'selected' : '' }}>
                                    {{ $drv->name }} (📞 {{ $drv->mobile }} • {{ $drv->status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Financials & Status -->
        <div>
            <!-- Status & Billing Card -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">💳 Fare & Booking Status</h3>

                <div class="form-group mb-3">
                    <label for="booking_status" class="form-label">Initial Booking Status *</label>
                    <select name="booking_status" id="booking_status" class="form-select" required>
                        @foreach(['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned'] as $st)
                            <option value="{{ $st }}" {{ old('booking_status', 'Confirmed') == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <div class="d-flex justify-between align-center mb-1">
                        <label for="total_amount" class="form-label" style="margin: 0;">Configured Trip Fare (₹) *</label>
                        <label style="font-size: 0.75rem; color: var(--slate-600); cursor: pointer; user-select: none;">
                            <input type="checkbox" id="customFareToggle" onchange="toggleCustomFare(this)"> Custom Fare
                        </label>
                    </div>
                    <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" value="{{ old('total_amount', '0.00') }}" readonly required>
                    <input type="hidden" name="is_custom_fare" id="is_custom_fare" value="{{ old('is_custom_fare', '0') }}">
                    <div id="fareDetails" style="font-size: 0.8rem; color: var(--slate-600); margin-top: 0.35rem;" aria-live="polite">Select route and vehicle category.</div>
                </div>

                <div class="form-group mb-3">
                    <label for="paid_amount" class="form-label">Advance / Paid Amount (₹)</label>
                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control" value="{{ old('paid_amount', '500.00') }}">
                </div>

                <div class="form-group mb-4">
                    <label for="payment_status" class="form-label">Payment Status *</label>
                    <select name="payment_status" id="payment_status" class="form-select" required>
                        @foreach(['Pending', 'Partial', 'Paid'] as $pst)
                            <option value="{{ $pst }}" {{ old('payment_status', 'Partial') == $pst ? 'selected' : '' }}>{{ $pst }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="text-align: center; font-weight: 800;">
                    🚀 Confirm & Create Booking
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    const rateOptions = @json($rateOptions);
    const rateCategory = document.querySelector('#rate_category');
    const pickup = document.querySelector('#pickup_location');
    const destination = document.querySelector('#destination');
    const vehicleCategory = document.querySelector('#vehicle_category');
    const estimatedKm = document.querySelector('#estimated_km');
    const estimatedKmGroup = document.querySelector('#estimatedKmGroup');
    const totalAmount = document.querySelector('#total_amount');
    const fareDetails = document.querySelector('#fareDetails');

    function updateFare() {
        if (document.getElementById('customFareToggle')?.checked) {
            return;
        }
        const category = rateCategory.value;
        const globalRate = category.startsWith('local_') || category === 'round_trip';
        estimatedKmGroup.style.display = category === 'round_trip' ? 'block' : 'none';
        estimatedKm.required = category === 'round_trip';
        if (category.startsWith('local_') && pickup.value) destination.value = pickup.value;
        const key = [category, globalRate ? '' : pickup.value, globalRate ? '' : destination.value, vehicleCategory.value].join('|');
        const quote = rateOptions[key];
        if (!quote) {
            const isCustom = document.getElementById('customFareToggle')?.checked;
            if (!isCustom) {
                totalAmount.value = (totalAmount.value && Number(totalAmount.value) > 0) ? totalAmount.value : '0.00';
                fareDetails.innerHTML = '<span style="color:#D97706; font-weight:700;">⚠️ Rate not pre-configured for this route.</span> Please tick <label for="customFareToggle" style="text-decoration:underline; cursor:pointer; font-weight:700; color:#2563EB;">Custom Fare</label> above to enter custom trip fare.';
            }
            return;
        }
        let amount = Number(quote.total_amount);
        if (quote.details.vehicle_rent !== null) {
            amount = Number(quote.details.vehicle_rent) + Number(estimatedKm.value || 0) * Number(quote.details.per_km_rate);
        }
        totalAmount.value = amount.toFixed(2);
        fareDetails.textContent = [
            quote.details.included_km ? `${quote.details.included_km} KM included` : '',
            quote.details.included_hours ? `${quote.details.included_hours} hours included` : '',
            quote.details.extra_km_rate !== null ? `Extra KM ₹${quote.details.extra_km_rate}` : '',
            quote.details.extra_hour_rate !== null ? `Extra hour ₹${quote.details.extra_hour_rate}` : '',
            quote.details.gst_applicable ? 'GST applicable' : '',
        ].filter(Boolean).join(' · ');
    }

    function toggleCustomFare(checkbox) {
        const customInput = document.getElementById('is_custom_fare');
        if (checkbox.checked) {
            totalAmount.removeAttribute('readonly');
            totalAmount.style.background = '#fff';
            totalAmount.style.borderColor = '#2563EB';
            totalAmount.style.boxShadow = '0 0 0 3px rgba(37,99,235,0.15)';
            if (customInput) customInput.value = '1';
            fareDetails.innerHTML = '✏️ <strong style="color:#2563EB;">Custom Fare Active:</strong> Enter your custom trip fare. Booking will be confirmed with this rate.';
            totalAmount.focus();
            if (totalAmount.value === '0.00') {
                totalAmount.select();
            }
        } else {
            totalAmount.setAttribute('readonly', 'readonly');
            totalAmount.style.background = '';
            totalAmount.style.borderColor = '';
            totalAmount.style.boxShadow = '';
            if (customInput) customInput.value = '0';
            updateFare();
        }
    }

    function setCustomerMode(mode) {
        const modeInput = document.getElementById('customer_mode');
        const existingSection = document.getElementById('existingCustomerSection');
        const newSection = document.getElementById('newCustomerSection');
        const existingBtn = document.getElementById('modeExistingBtn');
        const newBtn = document.getElementById('modeNewBtn');
        const customerSelect = document.getElementById('customer_id');
        const newName = document.getElementById('new_customer_name');
        const newPhone = document.getElementById('new_customer_phone');

        if (!modeInput) return;
        modeInput.value = mode;

        if (mode === 'new') {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            if (customerSelect) customerSelect.required = false;
            if (newName) newName.required = true;
            if (newPhone) newPhone.required = true;
            newBtn.style.background = 'var(--primary)';
            newBtn.style.color = '#fff';
            existingBtn.style.background = 'transparent';
            existingBtn.style.color = 'var(--slate-700)';
            setTimeout(() => { if (newName) newName.focus(); }, 50);
        } else {
            newSection.style.display = 'none';
            existingSection.style.display = 'block';
            if (customerSelect) customerSelect.required = true;
            if (newName) newName.required = false;
            if (newPhone) newPhone.required = false;
            existingBtn.style.background = 'var(--primary)';
            existingBtn.style.color = '#fff';
            newBtn.style.background = 'transparent';
            newBtn.style.color = 'var(--slate-700)';
        }
    }

    const customerSelectElem = document.getElementById('customer_id');
    if (customerSelectElem) {
        customerSelectElem.addEventListener('change', function() {
            if (this.value === 'new') {
                this.value = '';
                setCustomerMode('new');
            }
        });
    }

    const initialCustomerMode = "{{ old('customer_mode', (old('new_customer_name') || old('new_customer_phone')) ? 'new' : 'existing') }}";
    setCustomerMode(initialCustomerMode);

    function toggleCategoryFields() {
        const selected = document.querySelector('input[name="booking_category"]:checked')?.value || 'MAN';
        const corpCard = document.getElementById('corporateFieldsCard');
        const tpCard = document.getElementById('travelPartnerFieldsCard');
        if (corpCard) {
            corpCard.style.display = (selected === 'CORP') ? 'block' : 'none';
        }
        if (tpCard) {
            tpCard.style.display = (selected === 'TP') ? 'block' : 'none';
        }
        const lblMan = document.getElementById('lblCatMan');
        const lblCorp = document.getElementById('lblCatCorp');
        const lblTp = document.getElementById('lblCatTp');
        if (lblMan) lblMan.style.borderColor = (selected === 'MAN') ? '#2563EB' : 'var(--slate-200)';
        if (lblCorp) lblCorp.style.borderColor = (selected === 'CORP') ? '#7C3AED' : 'var(--slate-200)';
        if (lblTp) lblTp.style.borderColor = (selected === 'TP') ? '#D97706' : 'var(--slate-200)';
    }
    toggleCategoryFields();

    [rateCategory, pickup, destination, vehicleCategory, estimatedKm].forEach((input) => input.addEventListener('change', updateFare));
    updateFare();
</script>
@endsection
