@extends('layouts.app')

@section('title', 'Book a Taxi Online - Vaishnavi Tours Bilaspur')

@section('content')
<section style="padding: 3rem 0 5rem;">
    <div class="container">
        <div style="max-width: 860px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 2.5rem;">
                <span style="color: var(--primary-hover); font-weight: 800; text-transform: uppercase; font-size: 0.85rem;">Instant Reservation</span>
                <h1 style="font-size: 2.5rem; margin-top: 0.25rem;">Book Your Cab with Vaishnavi Tours</h1>
                <p style="color: var(--slate-500); margin-top: 0.5rem;">Outstation Cabs, One-Way Drops, Local 8hr Packages & 24/7 Airport Transfers.</p>
            </div>

            <div class="card" style="padding: 2.5rem; border: 2px solid var(--primary); box-shadow: var(--shadow-lg);">
                @if($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                        <strong style="display: flex; align-items: center; gap: 6px;"><x-icon name="alert-circle" size="18" /><span>Please correct the following errors:</span></strong>
                        <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('booking.store') }}" method="POST">
                    @csrf

                    <!-- 1. Trip Type -->
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label class="form-label">1. Select Trip Type</label>
                        <div class="grid grid-4 gap-2" id="bookingTripTypes">
                            <label style="border: 1.5px solid var(--slate-300); padding: 0.85rem; border-radius: var(--radius-md); text-align: center; cursor: pointer; display: block;" class="trip-opt active">
                                <input type="radio" name="trip_type" value="One-Way" checked style="display: none;">
                                <div class="icon-box icon-box-sm icon-box-primary" style="margin: 0 auto 0.5rem;"><x-icon name="arrow-right" size="18" /></div>
                                <div style="font-weight: 700; font-size: 0.95rem; margin-top: 0.25rem;">One-Way Drop</div>
                                <div style="font-size: 0.75rem; color: var(--slate-400);">Pay only for 1 side</div>
                            </label>

                            <label style="border: 1.5px solid var(--slate-300); padding: 0.85rem; border-radius: var(--radius-md); text-align: center; cursor: pointer; display: block;" class="trip-opt">
                                <input type="radio" name="trip_type" value="Round-Trip" style="display: none;">
                                <div class="icon-box icon-box-sm icon-box-primary" style="margin: 0 auto 0.5rem;"><x-icon name="repeat" size="18" /></div>
                                <div style="font-weight: 700; font-size: 0.95rem; margin-top: 0.25rem;">Round-Trip</div>
                                <div style="font-size: 0.75rem; color: var(--slate-400);">Multi-day outstation</div>
                            </label>

                            <label style="border: 1.5px solid var(--slate-300); padding: 0.85rem; border-radius: var(--radius-md); text-align: center; cursor: pointer; display: block;" class="trip-opt">
                                <input type="radio" name="trip_type" value="Airport Transfer" style="display: none;">
                                <div class="icon-box icon-box-sm icon-box-primary" style="margin: 0 auto 0.5rem;"><x-icon name="plane" size="18" /></div>
                                <div style="font-weight: 700; font-size: 0.95rem; margin-top: 0.25rem;">Airport Transfer</div>
                                <div style="font-size: 0.75rem; color: var(--slate-400);">Raipur Airport Drop</div>
                            </label>

                            <label style="border: 1.5px solid var(--slate-300); padding: 0.85rem; border-radius: var(--radius-md); text-align: center; cursor: pointer; display: block;" class="trip-opt">
                                <input type="radio" name="trip_type" value="Local Hourly" style="display: none;">
                                <div style="font-size: 1.25rem;">⏱️</div>
                                <div style="font-weight: 700; font-size: 0.95rem; margin-top: 0.25rem;">Local Hourly</div>
                                <div style="font-size: 0.75rem; color: var(--slate-400);">8 Hr / 80 Km pkg</div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Locations -->
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label class="form-label">2. Route Details</label>
                        <div class="grid grid-2 gap-2">
                            <div>
                                <label style="font-size: 0.8rem; color: var(--slate-500); font-weight: 600;">PICKUP ADDRESS IN BILASPUR</label>
                                <input type="text" name="pickup_location" class="form-control" placeholder="House/Colony, Landmark, Bilaspur" value="{{ old('pickup_location', 'Mangal Chowk, Bilaspur') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.8rem; color: var(--slate-500); font-weight: 600;">DESTINATION CITY / ADDRESS</label>
                                <input type="text" name="destination" class="form-control" placeholder="City or Destination (e.g. Raipur, Korba)" value="{{ old('destination', 'Raipur') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Schedule -->
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label class="form-label">3. Date & Pickup Time</label>
                        <div class="grid grid-2 gap-2">
                            <div>
                                <label style="font-size: 0.8rem; color: var(--slate-500); font-weight: 600;">TRAVEL DATE</label>
                                <input type="date" name="travel_date" class="form-control" value="{{ old('travel_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.8rem; color: var(--slate-500); font-weight: 600;">PICKUP TIME</label>
                                <input type="time" name="travel_time" class="form-control" value="{{ old('travel_time', '08:00') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Vehicle Selection -->
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label class="form-label">4. Select Vehicle Category</label>
                        <div class="grid grid-3 gap-2">
                            @forelse($vehicles as $veh)
                                <label style="border: 1.5px solid var(--slate-300); padding: 1rem; border-radius: var(--radius-md); cursor: pointer; display: flex; flex-direction: column; justify-content: space-between;" class="veh-opt {{ (request('vehicle_id') == $veh->id || $loop->first) ? 'active' : '' }}">
                                    <div>
                                        <div class="d-flex justify-between align-center" style="margin-bottom: 0.5rem;">
                                            <input type="radio" name="vehicle_id" value="{{ $veh->id }}" {{ (request('vehicle_id') == $veh->id || $loop->first) ? 'checked' : '' }} required>
                                            <span class="badge badge-available">{{ $veh->ac_non_ac }}</span>
                                        </div>
                                        <div style="font-weight: 800; font-size: 1rem; color: var(--dark-900);">{{ $veh->name }}</div>
                                        <div style="font-size: 0.775rem; color: var(--slate-500);">{{ $veh->vehicle_type }} • {{ $veh->seating_capacity }} Seater</div>
                                    </div>
                                    <div style="margin-top: 1rem; border-top: 1px solid var(--slate-200); padding-top: 0.5rem; font-weight: 800; color: var(--primary-dark); font-size: 1.1rem;">
                                        ₹{{ number_format($veh->per_km_rate, 0) }} <span style="font-size: 0.75rem; font-weight: 500; color: var(--slate-400);">/ km</span>
                                    </div>
                                </label>
                            @empty
                                <div style="grid-column: 1 / -1; padding: 1.75rem; background: var(--slate-100); border: 1.5px dashed var(--slate-300); border-radius: var(--radius-md); text-align: center; color: var(--slate-600);">
                                    <p style="margin-bottom: 0.5rem; font-weight: 700; color: var(--dark-900); font-size: 1.05rem;">No vehicles currently available. Please contact us.</p>
                                    <p style="font-size: 0.875rem; margin-bottom: 1rem; color: var(--slate-500);">No vehicles are currently configured. Please contact Vaishnavi Tours.</p>
                                    <a href="{{ route('contact') }}" class="btn btn-primary btn-sm">Contact Vaishnavi Tours</a>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- 5. Passenger Contact -->
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label class="form-label">5. Passenger Information</label>
                        <div class="grid grid-3 gap-2">
                            <div>
                                <label style="font-size: 0.8rem; color: var(--slate-500); font-weight: 600;">PASSENGER NAME</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Full Name" value="{{ Auth::check() ? Auth::user()->name : old('customer_name') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.8rem; color: var(--slate-500); font-weight: 600;">MOBILE NUMBER</label>
                                <input type="tel" name="mobile" class="form-control" placeholder="10-digit mobile" value="{{ Auth::check() ? Auth::user()->phone : old('mobile') }}" required>
                            </div>
                            <div>
                                <label style="font-size: 0.8rem; color: var(--slate-500); font-weight: 600;">EMAIL (OPTIONAL)</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" value="{{ Auth::check() ? Auth::user()->email : old('email') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Special Notes / Luggage Instructions</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. 2 large suitcases, patient on board, flight number, etc.">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Terms & Conditions Acceptance Checkbox -->
                    <div class="form-group" style="background: #ffffff; padding: 1.15rem 1.25rem; border: 1.5px solid {{ $errors->has('terms_accepted') ? '#dc2626' : 'var(--slate-200)' }}; border-radius: var(--radius-md); margin-bottom: 1.25rem;">
                        <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; margin: 0; font-size: 0.925rem; color: var(--dark-900); font-weight: 500; line-height: 1.5;">
                            <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required style="width: 1.25rem; height: 1.25rem; margin-top: 0.15rem; accent-color: var(--primary); cursor: pointer; flex-shrink: 0;">
                            <span>
                                I have read and agree to the 
                                <a href="{{ route('terms-and-conditions') }}" target="_blank" style="color: var(--primary-dark); font-weight: 700; text-decoration: underline;">Terms & Conditions</a>, 
                                <a href="{{ route('terms-and-conditions') }}#privacy-policy" target="_blank" style="color: var(--primary-dark); font-weight: 700; text-decoration: underline;">Privacy Policy</a>, and 
                                <a href="{{ route('cancellation-refund-policy') }}" target="_blank" style="color: var(--primary-dark); font-weight: 700; text-decoration: underline;">Cancellation Policy</a>.
                            </span>
                        </label>
                        @error('terms_accepted')
                            <div style="color: #dc2626; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                                <span style="display: inline-flex; align-items: center; gap: 4px;"><x-icon name="alert-circle" size="14" /> {{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div style="background: var(--primary-light); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="font-weight: 800; font-size: 1.1rem; color: var(--dark-950);">Transparent Billing Guarantee</div>
                            <div style="font-size: 0.85rem; color: var(--dark-800);">Toll tax & parking as per actual receipts. Zero dynamic peak surge multipliers.</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg" style="font-size: 1.1rem; font-weight: 800; padding: 0.85rem 2rem;">
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
    // Trip options style toggle
    document.querySelectorAll('.trip-opt').forEach(opt => {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.trip-opt').forEach(o => {
                o.classList.remove('active');
                o.style.borderColor = 'var(--slate-300)';
                o.style.background = '#fff';
            });
            this.classList.add('active');
            this.style.borderColor = 'var(--primary)';
            this.style.background = 'var(--primary-subtle)';
            this.querySelector('input').checked = true;
        });
    });

    // Vehicle options style toggle
    document.querySelectorAll('.veh-opt').forEach(opt => {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.veh-opt').forEach(o => {
                o.classList.remove('active');
                o.style.borderColor = 'var(--slate-300)';
                o.style.background = '#fff';
            });
            this.classList.add('active');
            this.style.borderColor = 'var(--primary)';
            this.style.background = 'var(--primary-subtle)';
            this.querySelector('input').checked = true;
        });
    });
</script>
@endpush
