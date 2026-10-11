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

<form action="{{ route('admin.bookings.store') }}" method="POST">
    @csrf

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Customer & Trip Details -->
        <div style="grid-column: span 2;">
            <!-- Customer Selection Card -->
            <div class="card mb-4">
                <div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
                    <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0;">👤 Customer & Passenger Details</h3>
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

                <input type="hidden" name="customer_mode" id="customer_mode" value="{{ old('customer_mode', (old('new_customer_name') || old('new_customer_phone')) ? 'new' : 'existing') }}">

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
                            <input type="text" name="new_customer_name" id="new_customer_name" class="form-control" placeholder="e.g. Rajesh Sharma" value="{{ old('new_customer_name') }}">
                        </div>

                        <div class="form-group">
                            <label for="new_customer_phone" class="form-label">Mobile / WhatsApp Number *</label>
                            <input type="tel" name="new_customer_phone" id="new_customer_phone" class="form-control" placeholder="e.g. 9876543210" value="{{ old('new_customer_phone') }}">
                        </div>
                    </div>

                    <div class="grid grid-3 gap-3">
                        <div class="form-group">
                            <label for="new_customer_email" class="form-label">Email Address (Optional)</label>
                            <input type="email" name="new_customer_email" id="new_customer_email" class="form-control" placeholder="e.g. customer@gmail.com" value="{{ old('new_customer_email') }}">
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
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">🚖 Route & Schedule Information</h3>

                <div class="form-group mb-3">
                    <label for="trip_type" class="form-label">Trip Type *</label>
                    <select name="rate_category" id="rate_category" class="form-select" required>
                        @foreach(['outstation' => 'Outstation Route', 'airport' => 'Quick Airport Booking', 'round_trip' => 'Round Trip', 'local_8h_80km' => 'Local 8 Hours / 80 KM', 'local_4h_40km' => 'Local 4 Hours / 40 KM'] as $key => $label)
                            <option value="{{ $key }}" {{ old('rate_category', 'outstation') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="pickup_location" class="form-label">Pickup Location *</label>
                        <select name="pickup_location" id="pickup_location" class="form-select" required>
                            <option value="">Select pickup</option>
                            @foreach($locations as $location)<option value="{{ $location }}" {{ old('pickup_location') === $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="destination" class="form-label">Drop Destination *</label>
                        <select name="destination" id="destination" class="form-select" required>
                            <option value="">Select destination</option>
                            @foreach($locations as $location)<option value="{{ $location }}" {{ old('destination') === $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach
                        </select>
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
            totalAmount.value = '0.00';
            fareDetails.textContent = 'Rate not configured. Check "Custom Fare" to set manual price.';
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
        if (checkbox.checked) {
            totalAmount.removeAttribute('readonly');
            totalAmount.style.background = '#fff';
            fareDetails.textContent = '✏️ Manual fare mode enabled. Enter any custom amount.';
            totalAmount.focus();
        } else {
            totalAmount.setAttribute('readonly', 'readonly');
            totalAmount.style.background = '';
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

    [rateCategory, pickup, destination, vehicleCategory, estimatedKm].forEach((input) => input.addEventListener('change', updateFare));
    updateFare();
</script>
@endsection
