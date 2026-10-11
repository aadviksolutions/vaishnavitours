@extends('layouts.app')

@section('title', 'Book a Taxi | Vaishnavi Tours')
@section('meta_description', 'Book airport, outstation, round-trip, or local taxi services from configured pickup locations.')
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
    $tripOptions = [
        ['trip_type' => 'One-Way', 'category' => 'outstation', 'title' => 'One-Way Taxi', 'description' => 'Configured route fare', 'icon' => 'arrow-right'],
        ['trip_type' => 'Round-Trip', 'category' => 'round_trip', 'title' => 'Round Trip', 'description' => 'Vehicle rent + per KM', 'icon' => 'repeat'],
        ['trip_type' => 'Tour Package', 'category' => 'tour_package', 'title' => 'Tour Package', 'description' => 'Sightseeing & multi-day tour', 'icon' => 'map-pin'],
        ['trip_type' => 'Ambulance Service', 'category' => 'emergency', 'title' => 'Ambulance (24/7)', 'description' => 'Emergency patient transport', 'icon' => 'heart'],
        ['trip_type' => 'Airport Transfer', 'category' => 'airport', 'title' => 'Airport Transfer', 'description' => 'Bilaspur ↔ Raipur Airport', 'icon' => 'plane'],
        ['trip_type' => 'Local 8 Hours', 'category' => 'local_8h_80km', 'title' => 'Local Rental', 'description' => '8 hours / 80 KM', 'icon' => 'clock'],
    ];
@endphp
<section style="padding: 2.25rem 0 3.5rem;">
    <div class="container">
        <div style="max-width: 860px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <span style="color: var(--accent-gold); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.8px;">Direct Reservation</span>
                <h1 style="font-size: 1.65rem; font-weight: 600; margin-top: 0.25rem; color: var(--dark-900);">Book Your Journey with Vaishnavi Tours</h1>
                <p style="color: var(--slate-500); font-size: 0.875rem; margin-top: 0.35rem;">Select your route and vehicle category to calculate official fixed rates.</p>
            </div>

            <div class="card" style="padding: 1.5rem; border: 1px solid var(--slate-200); box-shadow: var(--shadow-sm);">
                @if($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
                        <strong style="display: flex; align-items: center; gap: 6px;"><x-icon name="alert-circle" size="16" /><span>Please correct the following errors:</span></strong>
                        <ul style="margin: 0.35rem 0 0 1.25rem; padding: 0;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('booking.store') }}" method="POST">
                    @csrf

                    <input type="hidden" name="rate_category" id="rateCategory" value="{{ $defaultRateCategory }}">

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">1. Trip Type</label>
                        <div class="grid grid-3 gap-2" id="bookingTripTypes">
                            @foreach($tripOptions as $option)
                                <label style="border: 1px solid var(--slate-200); padding: 0.65rem 0.75rem; border-radius: var(--radius-md); text-align: center; cursor: pointer; display: block; transition: var(--transition);" class="trip-opt {{ $defaultRateCategory === $option['category'] ? 'active' : '' }}">
                                    <input type="radio" name="trip_type" value="{{ $option['trip_type'] }}" data-rate-category="{{ $option['category'] }}" {{ $defaultRateCategory === $option['category'] ? 'checked' : '' }} required>
                                    <div class="icon-box icon-box-sm icon-box-primary" style="margin: 0 auto 0.35rem; width: 28px; height: 28px;"><x-icon :name="$option['icon']" size="14" /></div>
                                    <div style="font-weight: 600; font-size: 0.85rem; margin-top: 0.2rem; color: var(--dark-900);">{{ $option['title'] }}</div>
                                    <div style="font-size: 0.72rem; color: var(--slate-500);">{{ $option['description'] }}</div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">2. Route Details</label>
                        <div class="grid grid-2 gap-2">
                            <div>
                                <label for="pickupLocation" style="font-size: 0.75rem; color: var(--slate-500); font-weight: 500; display: block; margin-bottom: 0.25rem;">Pickup Location</label>
                                <select id="pickupLocation" name="pickup_location" class="form-select form-select-sm" required>
                                    <option value="">Select pickup city</option>
                                    @foreach($locations as $location)<option value="{{ $location }}" {{ old('pickup_location', request('pickup_location')) === $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label for="destinationLocation" style="font-size: 0.75rem; color: var(--slate-500); font-weight: 500; display: block; margin-bottom: 0.25rem;">Destination</label>
                                <select id="destinationLocation" name="destination" class="form-select form-select-sm" required>
                                    <option value="">Select destination</option>
                                    @foreach($locations as $location)<option value="{{ $location }}" {{ old('destination', request('destination')) === $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">3. Date & Pickup Time</label>
                        <div class="grid grid-2 gap-2">
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-500); font-weight: 500; display: block; margin-bottom: 0.25rem;">Travel Date</label>
                                <input type="date" name="travel_date" class="form-control form-control-sm" value="{{ old('travel_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-500); font-weight: 500; display: block; margin-bottom: 0.25rem;">Pickup Time</label>
                                <input type="time" name="travel_time" class="form-control form-control-sm" value="{{ old('travel_time', '08:00') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">4. Vehicle Category</label>
                        <div class="grid grid-3 gap-2">
                            @forelse($vehicleCategories as $category)
                                <label style="border: 1px solid var(--slate-200); padding: 0.75rem; border-radius: var(--radius-md); cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: var(--transition);" class="vehicle-category-opt">
                                    <input type="radio" name="vehicle_category" value="{{ $category }}" {{ old('vehicle_category', request('vehicle_category')) === $category ? 'checked' : '' }} required>
                                    <span style="font-weight: 600; font-size: 0.85rem; color: var(--dark-900);">{{ $category }}</span>
                                </label>
                            @empty
                                <div style="grid-column: 1 / -1; padding: 1.25rem; background: var(--slate-100); border: 1px dashed var(--slate-300); border-radius: var(--radius-md); text-align: center; color: var(--slate-600);">
                                    <p style="margin-bottom: 0.35rem; font-weight: 600; color: var(--dark-900); font-size: 0.95rem;">No vehicle rates are currently configured.</p>
                                    <a href="{{ route('contact') }}" class="btn btn-primary btn-sm">Contact Vaishnavi Tours</a>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="preferredVehicle" class="form-label">Preferred Fleet Vehicle (Optional)</label>
                        <select id="preferredVehicle" name="vehicle_id" class="form-select form-select-sm">
                            <option value="">Let dispatch assign a vehicle</option>
                            @foreach($fleetVehicles as $fleetVehicle)
                                <option value="{{ $fleetVehicle->id }}" {{ old('vehicle_id', request('vehicle_id')) == $fleetVehicle->id ? 'selected' : '' }}>{{ $fleetVehicle->name }} · {{ $fleetVehicle->registration_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="estimatedKmGroup" class="form-group" style="margin-bottom: 1.25rem; display: none;">
                        <label for="estimatedKm" class="form-label">Estimated Round-Trip Distance (KM)</label>
                        <input type="number" name="estimated_km" id="estimatedKm" class="form-control form-control-sm" min="0.01" step="0.1" value="{{ old('estimated_km') }}">
                    </div>

                    <div id="ratePreview" class="card" style="padding: 1rem; margin-bottom: 1.25rem; border-left: 3px solid var(--primary); background: #f8fafc;" aria-live="polite">
                        <div style="font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--slate-500);">Applicable Fare</div>
                        <div id="ratePreviewAmount" style="font-size: 1.25rem; font-weight: 600; margin: 0.25rem 0; color: var(--dark-900);">Select route and vehicle</div>
                        <div id="ratePreviewDetails" style="font-size: 0.8rem; color: var(--slate-600);"></div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">5. Passenger Information</label>
                        <div class="grid grid-3 gap-2">
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-500); font-weight: 500; display: block; margin-bottom: 0.25rem;">Passenger Name</label>
                                <input type="text" name="customer_name" class="form-control form-control-sm" placeholder="Full Name" value="{{ Auth::check() ? Auth::user()->name : old('customer_name') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-500); font-weight: 500; display: block; margin-bottom: 0.25rem;">Mobile Number</label>
                                <input type="tel" name="mobile" class="form-control form-control-sm" placeholder="10-digit mobile" value="{{ Auth::check() ? Auth::user()->phone : old('mobile') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: var(--slate-500); font-weight: 500; display: block; margin-bottom: 0.25rem;">Email (Optional)</label>
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="email@example.com" value="{{ Auth::check() ? Auth::user()->email : old('email') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Special Notes / Luggage Instructions</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. 2 large suitcases, patient on board, flight number, etc.">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Terms & Conditions Acceptance Checkbox -->
                    <div class="form-group" style="background: #ffffff; padding: 0.85rem 1rem; border: 1px solid {{ $errors->has('terms_accepted') ? '#dc2626' : 'var(--slate-200)' }}; border-radius: var(--radius-md); margin-bottom: 1.25rem;">
                        <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; margin: 0; font-size: 0.85rem; color: var(--dark-900); font-weight: 500; line-height: 1.45;">
                            <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required style="width: 1.1rem; height: 1.1rem; margin-top: 0.1rem; accent-color: var(--primary); cursor: pointer; flex-shrink: 0;">
                            <span>
                                I have read and agree to the 
                                <a href="{{ route('terms-and-conditions') }}" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Terms & Conditions</a>, 
                                <a href="{{ route('terms-and-conditions') }}#privacy-policy" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Privacy Policy</a>, and 
                                <a href="{{ route('cancellation-refund-policy') }}" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Cancellation Policy</a>.
                            </span>
                        </label>
                        @error('terms_accepted')
                            <div style="color: #dc2626; font-size: 0.8rem; font-weight: 500; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                                <span style="display: inline-flex; align-items: center; gap: 4px;"><x-icon name="alert-circle" size="14" /> {{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div style="background: var(--navy-50); border: 1px solid var(--navy-100); padding: 1rem 1.25rem; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="font-weight: 600; font-size: 0.95rem; color: var(--navy-900);">Transparent Business Fare</div>
                            <div style="font-size: 0.775rem; color: var(--slate-600);">Fixed pricing with no hidden driver surcharges.</div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="font-size: 0.9rem; font-weight: 600; padding: 0.5rem 1.5rem; height: 38px;">
                            Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const rateOptions = @json($rateOptions);
    const categoryInput = document.querySelector('#rateCategory');
    const pickupInput = document.querySelector('#pickupLocation');
    const destinationInput = document.querySelector('#destinationLocation');
    const estimatedKmGroup = document.querySelector('#estimatedKmGroup');
    const estimatedKmInput = document.querySelector('#estimatedKm');
    const previewAmount = document.querySelector('#ratePreviewAmount');
    const previewDetails = document.querySelector('#ratePreviewDetails');

    function updateRatePreview() {
        const selectedTrip = document.querySelector('input[name="trip_type"]:checked');
        const selectedVehicle = document.querySelector('input[name="vehicle_category"]:checked');
        if (selectedTrip) {
            categoryInput.value = selectedTrip.dataset.rateCategory;
        }

        const category = categoryInput.value;
        const isRoundTrip = category === 'round_trip';
        estimatedKmGroup.style.display = isRoundTrip ? 'block' : 'none';
        estimatedKmInput.required = isRoundTrip;

        if (category.startsWith('local_') && pickupInput.value) {
            destinationInput.value = pickupInput.value;
        }

        if (!pickupInput.value || !destinationInput.value || !selectedVehicle) {
            previewAmount.textContent = 'Select route and vehicle';
            previewDetails.textContent = '';
            return;
        }

        const quoteKey = [category, category.startsWith('local_') || isRoundTrip ? '' : pickupInput.value, category.startsWith('local_') || isRoundTrip ? '' : destinationInput.value, selectedVehicle.value].join('|');
        const quote = rateOptions[quoteKey];
        if (!quote) {
            previewAmount.textContent = 'Rate not configured. Please contact Vaishnavi Tours.';
            previewDetails.textContent = '';
            return;
        }

        const details = quote.details;
        const pieces = [];
        if (details.included_km !== null) pieces.push(`${details.included_km} KM included`);
        if (details.included_hours !== null) pieces.push(`${details.included_hours} hours included`);
        if (details.extra_km_rate !== null) pieces.push(`Extra KM ₹${Number(details.extra_km_rate).toFixed(2)}`);
        if (details.extra_hour_rate !== null) pieces.push(`Extra hour ₹${Number(details.extra_hour_rate).toFixed(2)}`);
        if (details.toll_type) pieces.push(`Toll ${details.toll_type}`);
        if (details.parking_type) pieces.push(`Parking ${details.parking_type}`);
        if (details.border_tax_type) pieces.push(`Border tax ${details.border_tax_type}`);
        if (details.night_charge !== null) pieces.push(`Night charge ₹${Number(details.night_charge).toFixed(2)}`);
        if (details.driver_food_type) pieces.push(`Driver food ${details.driver_food_type}`);
        if (details.gst_applicable) pieces.push('GST applicable');
        previewDetails.textContent = pieces.join(' · ');

        if (details.vehicle_rent !== null) {
            const distance = Number(estimatedKmInput.value || 0);
            if (distance > 0) {
                const estimatedFare = Number(details.vehicle_rent) + distance * Number(details.per_km_rate);
                previewAmount.textContent = `₹${estimatedFare.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
            } else {
                previewAmount.textContent = `₹${Number(details.vehicle_rent).toLocaleString('en-IN')} rent + ₹${Number(details.per_km_rate).toFixed(2)}/KM`;
            }
        } else {
            const gstText = details.gst_applicable ? ' + GST' : '';
            previewAmount.textContent = `₹${Number(quote.total_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}${gstText}`;
        }
    }

    document.querySelectorAll('input[name="trip_type"], input[name="vehicle_category"]').forEach((input) => input.addEventListener('change', updateRatePreview));
    [pickupInput, destinationInput, estimatedKmInput].forEach((input) => input.addEventListener('change', updateRatePreview));
    updateRatePreview();
</script>
@endpush
