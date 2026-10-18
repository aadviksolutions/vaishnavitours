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
                    <a href="tel:+919131613010" style="color: #1E40AF; font-weight: 700; text-decoration: underline;">+91 91316 13010</a>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 1.75rem;">
                <span style="color: var(--accent-gold); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.8px;">Direct Reservation & Quotations</span>
                <h1 style="font-size: 1.65rem; font-weight: 700; margin-top: 0.25rem; color: var(--dark-900);">Vaishnavi Tour Cab Booking</h1>
                <p style="color: var(--slate-500); font-size: 0.875rem; margin-top: 0.35rem;">Clean air-conditioned fleet, polite experienced chauffeurs, and transparent GST invoices.</p>
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
                                <label for="pickupLocation" style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">
                                    1. Pickup Location *
                                </label>
                                <select id="pickupLocation" name="pickup_location" class="form-select form-select-sm" required style="width: 100%;">
                                    <option value="">Select pickup city</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}" {{ old('pickup_location', request('pickup_location')) === $location ? 'selected' : '' }}>{{ $location }}</option>
                                    @endforeach
                                    <option value="Other" {{ old('pickup_location') === 'Other' || old('pickup_other') ? 'selected' : '' }}>➕ Other Location (Specify below)</option>
                                </select>
                                <div id="pickupOtherGroup" style="display: {{ old('pickup_other') || old('pickup_location') === 'Other' ? 'block' : 'none' }}; margin-top: 0.35rem;">
                                    <input type="text" name="pickup_other" id="pickupOtherInput" class="form-control form-control-sm" placeholder="Enter custom pickup city / area..." value="{{ old('pickup_other') }}" style="border-color: #3B82F6; background: #F0F9FF;">
                                    <div style="font-size: 10.5px; color: #1E40AF; margin-top: 2px;">* Custom location: No immediate fare shown. Quote will be sent by dispatch.</div>
                                </div>
                            </div>

                            <!-- 2. Via Location (Required principally for Round-Trip) -->
                            <div id="viaLocationGroup">
                                <label for="viaLocation" style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">
                                    2. Via Location <span style="font-weight: 400; color: var(--slate-400);">(Optional / Round-Trip)</span>
                                </label>
                                <input type="text" id="viaLocation" name="via_location" class="form-control form-control-sm" placeholder="e.g. Amarkantak, Ratanpur..." value="{{ old('via_location') }}">
                            </div>

                            <!-- 3. Final Drop Location -->
                            <div>
                                <label for="destinationLocation" style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600; display: block; margin-bottom: 0.25rem;">
                                    3. Final Drop Location *
                                </label>
                                <select id="destinationLocation" name="destination" class="form-select form-select-sm" required style="width: 100%;">
                                    <option value="">Select final drop</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}" {{ old('destination', request('destination')) === $location ? 'selected' : '' }}>{{ $location }}</option>
                                    @endforeach
                                    <option value="Other" {{ old('destination') === 'Other' || old('destination_other') ? 'selected' : '' }}>➕ Other Location (Specify below)</option>
                                </select>
                                <div id="destinationOtherGroup" style="display: {{ old('destination_other') || old('destination') === 'Other' ? 'block' : 'none' }}; margin-top: 0.35rem;">
                                    <input type="text" name="destination_other" id="destinationOtherInput" class="form-control form-control-sm" placeholder="Enter custom drop city / area..." value="{{ old('destination_other') }}" style="border-color: #3B82F6; background: #F0F9FF;">
                                    <div style="font-size: 10.5px; color: #1E40AF; margin-top: 2px;">* Custom location: No immediate fare shown. Quote will be sent by dispatch.</div>
                                </div>
                            </div>
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
                            @forelse($vehicleCategories as $category)
                                <label style="border: 1px solid var(--slate-200); padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.15s ease;" class="vehicle-category-opt">
                                    <input type="radio" name="vehicle_category" value="{{ $category }}" {{ old('vehicle_category', request('vehicle_category')) === $category ? 'checked' : '' }} required>
                                    <div>
                                        <span style="font-weight: 600; font-size: 0.85rem; color: var(--dark-900); display: block;">{{ $category }}</span>
                                        <span style="font-size: 11px; color: var(--slate-500);">Sedan / SUV / Hatchback</span>
                                    </div>
                                </label>
                            @empty
                                <div style="grid-column: 1 / -1; padding: 1.25rem; background: var(--slate-100); border: 1px dashed var(--slate-300); border-radius: 6px; text-align: center; color: var(--slate-600);">
                                    <p style="margin-bottom: 0.35rem; font-weight: 600; color: var(--dark-900); font-size: 0.95rem;">Fleet rates configuring...</p>
                                    <a href="tel:+919131613010" class="btn btn-primary btn-sm">Call Dispatch directly</a>
                                </div>
                            @endforelse
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
            alert('Notice: Website bookings must be scheduled at least 4 hours in advance.\n\nFor urgent or late-night bookings, please call our 24/7 hotline directly: +91 91316 13010.');
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

    document.querySelectorAll('input[name="vehicle_category"]').forEach((input) => input.addEventListener('change', updateRatePreview));
    [pickupInput, destinationInput].forEach((input) => input.addEventListener('change', updateRatePreview));
    updateRatePreview();

    // Inline "Add New Location" Modal Logic
    const modal = document.getElementById('newLocationModal');
    const btnOpen = document.getElementById('btnOpenNewLocationModal');
    const btnClose = document.getElementById('btnCloseNewLocationModal');
    const btnCancel = document.getElementById('btnCancelLocationModal');
    const quickForm = document.getElementById('quickLocationForm');

    btnOpen.addEventListener('click', () => { modal.style.display = 'flex'; });
    btnClose.addEventListener('click', () => { modal.style.display = 'none'; });
    btnCancel.addEventListener('click', () => { modal.style.display = 'none'; });

    quickForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const name = document.getElementById('newLocName').value.trim();
        const state = document.getElementById('newLocState').value.trim();
        const address = document.getElementById('newLocAddress').value.trim();

        if (!name) return;

        try {
            const resp = await fetch('{{ route("locations.quick-store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name: name, state: state, address: address })
            });

            const result = await resp.json();

            if (result.success) {
                // Add option to pickup and destination selects
                const opt1 = new Option(result.location.name, result.location.name, true, true);
                const opt2 = new Option(result.location.name, result.location.name, false, false);
                pickupInput.add(opt1);
                destinationInput.add(opt2);
                modal.style.display = 'none';
                quickForm.reset();
                updateRatePreview();
            } else {
                alert(result.message || 'Error saving location.');
            }
        } catch (err) {
            console.error(err);
            // Fallback: Add option directly to client UI
            const opt1 = new Option(name, name, true, true);
            const opt2 = new Option(name, name, false, false);
            pickupInput.add(opt1);
            destinationInput.add(opt2);
            modal.style.display = 'none';
            quickForm.reset();
            updateRatePreview();
        }
    });
</script>
@endpush
