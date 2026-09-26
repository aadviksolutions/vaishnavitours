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
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">👤 Customer & Passenger</h3>

                <div class="form-group mb-3">
                    <label for="customer_id" class="form-label">Select Registered Customer *</label>
                    <select name="customer_id" id="customer_id" class="form-select" required>
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} (📞 {{ $c->phone }} | ✉️ {{ $c->email }})
                            </option>
                        @endforeach
                    </select>
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
                    <label for="total_amount" class="form-label">Configured Trip Fare (₹)</label>
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
        const category = rateCategory.value;
        const globalRate = category.startsWith('local_') || category === 'round_trip';
        estimatedKmGroup.style.display = category === 'round_trip' ? 'block' : 'none';
        estimatedKm.required = category === 'round_trip';
        if (category.startsWith('local_') && pickup.value) destination.value = pickup.value;
        const key = [category, globalRate ? '' : pickup.value, globalRate ? '' : destination.value, vehicleCategory.value].join('|');
        const quote = rateOptions[key];
        if (!quote) {
            totalAmount.value = '0.00';
            fareDetails.textContent = 'Rate not configured. Please contact Vaishnavi Tours.';
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

    [rateCategory, pickup, destination, vehicleCategory, estimatedKm].forEach((input) => input.addEventListener('change', updateFare));
    updateFare();
</script>
@endsection
