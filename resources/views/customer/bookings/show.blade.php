@extends('layouts.customer')

@section('title', 'Booking #' . $booking->booking_id)

@section('content')
<div class="d-flex align-center justify-between flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('customer.bookings.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
            ← Back to All Bookings
        </a>
        <div class="d-flex align-center gap-2 mt-1">
            <h1 style="font-size: 1.75rem; font-weight: 800; margin: 0;">Booking #{{ $booking->booking_id }}</h1>
            <span class="badge badge-lg 
                {{ $booking->booking_status === 'Completed' ? 'badge-success' : '' }}
                {{ in_array($booking->booking_status, ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned']) ? 'badge-primary' : '' }}
                {{ in_array($booking->booking_status, ['Trip Started', 'On The Way']) ? 'badge-warning' : '' }}
                {{ $booking->booking_status === 'Cancelled' ? 'badge-danger' : '' }}
            ">
                {{ $booking->booking_status }}
            </span>
        </div>
    </div>

    <div class="d-flex align-center gap-2">
        <a href="{{ route('invoice.show', $booking->id) }}" class="btn btn-outline btn-sm" target="_blank">
            🧾 View Tax Invoice
        </a>
        @if($booking->cancellation_status === 'Requested' && $booking->booking_status !== 'Cancelled')
            <span class="badge badge-warning">Cancellation Requested</span>
        @elseif(!in_array($booking->booking_status, ['Trip Started', 'On The Way', 'Completed', 'Cancelled']))
            <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('cancelModal').style.display='flex'">
                ✕ Request Cancellation
            </button>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3">
        ✓ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-3">
        ⚠️ {{ session('error') }}
    </div>
@endif

@if($booking->cancellation_status === 'Requested' && $booking->booking_status !== 'Cancelled')
    <div class="alert alert-warning mb-4" style="border-left: 4px solid var(--primary);">
        <strong>⏳ Cancellation Request Pending Dispatch Approval:</strong> {{ $booking->cancellation_reason }}
        <div style="font-size: 0.8rem; color: var(--slate-600); margin-top: 2px;">
            Our support desk has received your cancellation request and will confirm shortly.
        </div>
    </div>
@endif

<!-- Live Trip Lifecycle Status Timeline -->
<div class="card mb-4" style="overflow-x: auto;">
    <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
        <span>📍</span> Live Journey Timeline
    </h3>

    @php
        $milestones = ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way', 'Completed'];
        $isCancelled = $booking->booking_status === 'Cancelled';
        $currentIndex = array_search($booking->booking_status, $milestones);
        if ($currentIndex === false && !$isCancelled) $currentIndex = 0;
    @endphp

    @if($isCancelled)
        <div class="alert alert-danger" style="margin-bottom: 0;">
            <strong>🚫 This booking was cancelled.</strong>
            @if($booking->cancellation_reason)
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Reason: {{ $booking->cancellation_reason }}</p>
            @endif
        </div>
    @else
        <div class="timeline-stepper">
            @foreach($milestones as $idx => $step)
                <div class="timeline-step {{ $idx <= $currentIndex ? 'completed' : '' }} {{ $idx === $currentIndex ? 'current' : '' }}">
                    <div class="step-circle">
                        @if($idx < $currentIndex)
                            ✓
                        @elseif($idx === $currentIndex)
                            ●
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </div>
                    <div class="step-label">{{ $step }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="grid grid-3 gap-4">
    <!-- Left 2 Cols: Trip & Driver Details -->
    <div style="grid-column: span 2;">
        <!-- Route & Schedule Card -->
        <div class="card mb-4">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem;">🚖 Route & Schedule Information</h3>
            
            <div class="grid grid-2 gap-3 mb-3">
                <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md); border-left: 4px solid var(--primary);">
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">Pickup Location</div>
                    <div style="font-weight: 700; font-size: 1.05rem; margin-top: 4px;">{{ $booking->pickup_location }}</div>
                </div>

                <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md); border-left: 4px solid var(--dark-900);">
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">Drop Destination</div>
                    <div style="font-weight: 700; font-size: 1.05rem; margin-top: 4px;">{{ $booking->destination }}</div>
                </div>
            </div>

            <div class="grid grid-3 gap-3" style="font-size: 0.9rem;">
                <div>
                    <span style="color: var(--slate-500); display: block; font-size: 0.8rem;">Trip Type</span>
                    <strong>{{ $booking->trip_type }}</strong>
                </div>
                <div>
                    <span style="color: var(--slate-500); display: block; font-size: 0.8rem;">Pickup Date & Time</span>
                    <strong>{{ $booking->travel_date ? $booking->travel_date->format('d M Y') : '' }} at {{ date('h:i A', strtotime($booking->travel_time)) }}</strong>
                </div>
                <div>
                    <span style="color: var(--slate-500); display: block; font-size: 0.8rem;">Trip Status</span>
                    <strong>{{ $booking->booking_status }}</strong>
                </div>
            </div>

            @if($booking->return_date)
                <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--slate-200); font-size: 0.9rem;">
                    <span style="color: var(--slate-500);">Return Date:</span> 
                    <strong>{{ $booking->return_date->format('d M Y') }}</strong>
                </div>
            @endif

            @if($booking->notes)
                <div style="margin-top: 1rem; background: #fffbeb; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid #fef3c7; font-size: 0.875rem;">
                    <strong>Passenger Request / Notes:</strong> {{ $booking->notes }}
                </div>
            @endif
        </div>

        <!-- Assigned Vehicle & Chauffeur Card -->
        <div class="card mb-4">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.25rem;">🚗 Assigned Vehicle & Chauffeur</h3>

            <div class="grid grid-2 gap-4">
                <!-- Vehicle Info -->
                <div style="border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase; margin-bottom: 0.75rem;">Vehicle Details</div>
                    @if($booking->vehicle)
                        <div class="d-flex align-center gap-3">
                            <img src="{{ asset($booking->vehicle->image ?? 'assets/sedan.svg') }}" alt="{{ $booking->vehicle->name }}" style="width: 80px; height: 50px; object-fit: contain;">
                            <div>
                                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800;">{{ $booking->vehicle->name }}</h4>
                                <div style="color: var(--slate-500); font-size: 0.85rem;">{{ $booking->vehicle->vehicle_type }} • {{ $booking->vehicle->seating_capacity }} Seater ({{ $booking->vehicle->ac_non_ac }})</div>
                                <span class="badge badge-outline mt-1" style="font-size: 0.85rem; font-family: monospace; font-weight: 700;">
                                    {{ $booking->vehicle->registration_number }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div style="text-align: center; padding: 1.5rem 0; color: var(--slate-500);">
                            <div style="font-size: 2rem;">⏳</div>
                            <div style="font-weight: 600; margin-top: 4px;">Vehicle assignment in progress</div>
                            <small>Assigned shortly before trip departure</small>
                        </div>
                    @endif
                </div>

                <!-- Driver Info -->
                <div style="border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase; margin-bottom: 0.75rem;">Chauffeur / Driver</div>
                    @if($booking->driver)
                        <div class="d-flex align-center gap-3">
                            <div style="width: 54px; height: 54px; border-radius: 50%; background: var(--dark-900); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 800;">
                                👨‍✈️
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800;">{{ $booking->driver->name }}</h4>
                                <div style="color: var(--slate-500); font-size: 0.825rem;">Verified Commercial Chauffeur • ⭐ {{ $booking->driver->rating ?? '5.0' }}</div>
                                <a href="tel:{{ $booking->driver->mobile }}" class="btn btn-primary btn-sm mt-1" style="display: inline-flex; align-items: center; gap: 4px;">
                                    📞 Call: {{ $booking->driver->mobile }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div style="text-align: center; padding: 1.5rem 0; color: var(--slate-500);">
                            <div style="font-size: 2rem;">⏳</div>
                            <div style="font-weight: 600; margin-top: 4px;">Chauffeur assignment in progress</div>
                            <small>Driver contact will appear right here</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Status History Audit Trail (Section 6) -->
        <div class="card">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem;">📋 Activity & Status Timeline</h3>
            <div style="border-left: 2px solid var(--slate-200); margin-left: 10px; padding-left: 20px;">
                @forelse($booking->statusHistories as $history)
                    <div style="position: relative; margin-bottom: 1.25rem;">
                        <div style="position: absolute; left: -27px; top: 3px; width: 14px; height: 14px; border-radius: 50%; background: var(--primary); border: 3px solid #fff; box-shadow: 0 0 0 1px var(--slate-300);"></div>
                        <div class="d-flex align-center gap-2">
                            <span class="badge badge-sm badge-primary">{{ $history->new_status }}</span>
                            <span style="font-size: 0.8rem; color: var(--slate-500);">{{ $history->created_at->format('d M Y, h:i A') }}</span>
                            @if($history->changer)
                                <span style="font-size: 0.75rem; color: var(--slate-400);">by {{ $history->changer->name }}</span>
                            @endif
                        </div>
                        <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: var(--slate-700);">
                            {{ $history->remarks ?: $history->comment }}
                        </p>
                    </div>
                @empty
                    <div style="color: var(--slate-500); font-size: 0.875rem;">No status history recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Col: Financials & Support -->
    <div>
        <!-- Booking Compliance & Terms Acceptance Card -->
        <div class="card mb-4" style="border-left: 4px solid #10B981; background: #ffffff;">
            <div class="d-flex align-center gap-2 mb-3">
                <span style="font-size: 1.15rem; color: #10B981;">📜</span>
                <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0; color: var(--dark-950);">Agreed Policy Terms</h3>
            </div>
            <div style="font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.6rem;">
                <div class="d-flex justify-between align-center">
                    <span style="color: var(--slate-600);">Terms Accepted:</span>
                    <span class="badge badge-sm badge-success" style="font-weight: 700;">✓ Yes (Confirmed)</span>
                </div>
                <div class="d-flex justify-between align-center">
                    <span style="color: var(--slate-600);">Accepted On:</span>
                    <span style="font-weight: 700; color: var(--dark-900);">
                        {{ $booking->terms_accepted_at ? $booking->terms_accepted_at->format('d M Y, h:i A') : ($booking->created_at ? $booking->created_at->format('d M Y, h:i A') : 'Recorded at booking') }}
                    </span>
                </div>
                <div class="d-flex justify-between align-center">
                    <span style="color: var(--slate-600);">Terms Version:</span>
                    <span class="badge badge-outline" style="font-size: 0.75rem; font-weight: 700;">v{{ $booking->terms_version ?? '1.0' }}</span>
                </div>
                <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--slate-200); text-align: center;">
                    <a href="{{ route('terms-and-conditions') }}" target="_blank" style="color: var(--primary-dark); font-weight: 700; text-decoration: underline; font-size: 0.8rem;">
                        View Full Terms & Conditions ↗
                    </a>
                </div>
            </div>
        </div>

        <!-- Fare & Payment Card (Section 17: Customer Payment View) -->
        <div class="card mb-4">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem;">💳 Fare & Billing</h3>

            <div style="background: var(--slate-50); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem; border: 1px solid var(--slate-200);">
                <div class="d-flex justify-between align-center mb-2" style="font-size: 0.95rem;">
                    <span style="color: var(--slate-600); font-weight: 600;">Total Fare:</span>
                    <strong style="color: var(--dark-950); font-size: 1.15rem;">₹{{ number_format($booking->total_amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-between align-center mb-2" style="font-size: 0.95rem;">
                    <span style="color: var(--slate-600); font-weight: 600;">Paid:</span>
                    <strong style="color: #059669; font-size: 1.15rem;">₹{{ number_format($booking->paid_amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-between align-center pt-2" style="border-top: 1.5px solid var(--slate-200); font-size: 1rem;">
                    <span style="font-weight: 700; color: var(--dark-900);">Balance:</span>
                    <strong style="font-size: 1.35rem; color: {{ $booking->balance_amount > 0 ? '#dc2626' : '#16a34a' }};">
                        ₹{{ number_format($booking->balance_amount, 2) }}
                    </strong>
                </div>
            </div>

            <div class="d-flex justify-between align-center mb-3">
                <span style="font-size: 0.85rem; color: var(--slate-500); font-weight: 700; text-transform: uppercase;">Payment Status:</span>
                <span class="badge {{ $booking->payment_status === 'Paid' ? 'badge-paid' : ($booking->payment_status === 'Partial' ? 'badge-warning' : 'badge-pending') }}">
                    {{ $booking->payment_status }}
                </span>
            </div>

            @if($booking->payments->isNotEmpty())
                <div style="margin-top: 1rem; border-top: 1px solid var(--slate-100); padding-top: 0.75rem;">
                    <h5 style="font-size: 0.8rem; text-transform: uppercase; color: var(--slate-500); margin-bottom: 0.5rem;">Payment Transactions</h5>
                    @foreach($booking->payments as $payment)
                        <div class="d-flex justify-between align-center" style="font-size: 0.8rem; padding: 0.35rem 0; border-bottom: 1px dotted var(--slate-200);">
                            <div>
                                <span style="font-weight: 700;">₹{{ number_format($payment->amount, 2) }}</span>
                                <span style="color: var(--slate-500); margin-left: 4px;">({{ $payment->payment_method }})</span>
                            </div>
                            <span class="badge badge-sm badge-success">{{ $payment->status }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div style="margin-top: 1.25rem;">
                <a href="{{ route('invoice.show', $booking->id) }}" class="btn btn-outline btn-block" style="text-align: center;" target="_blank">
                    🧾 View / Print Tax Invoice
                </a>
            </div>
        </div>

        <!-- 24/7 Bilaspur Support Card -->
        <div class="card" style="background: linear-gradient(135deg, var(--dark-900) 0%, #1e293b 100%); color: #fff;">
            <h4 style="color: var(--primary); font-size: 1rem; font-weight: 800; margin-bottom: 0.5rem;">📞 Need Help with this Booking?</h4>
            <p style="font-size: 0.825rem; color: var(--slate-300); line-height: 1.5; margin-bottom: 1rem;">
                Our Bilaspur dispatch and passenger assistance team is on standby 24 hours a day.
            </p>
            <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                <span style="color: var(--slate-400);">Direct Dispatch:</span><br>
                <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: #fff; font-weight: 700; font-size: 1.1rem; text-decoration: none;">{{ config('vaishnavi.phone_primary') }}</a>
            </div>
            <div style="font-size: 0.9rem; margin-bottom: 1rem;">
                <span style="color: var(--slate-400);">24/7 Mobile / WhatsApp:</span><br>
                <a href="tel:{{ config('vaishnavi.phone_secondary_tel') }}" style="color: var(--primary); font-weight: 700; font-size: 1.1rem; text-decoration: none;">{{ config('vaishnavi.phone_secondary') }}</a>
            </div>
            <a href="https://wa.me/919244784443?text={{ urlencode('Hi Vaishnavi Tours, I need assistance regarding Booking #' . $booking->booking_id) }}" target="_blank" class="btn btn-primary btn-block btn-sm" style="text-align: center;">
                💬 Chat on WhatsApp
            </a>
        </div>
    </div>
</div>

<!-- Cancellation Modal -->
<div id="cancelModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: var(--radius-lg); max-width: 480px; width: 100%; padding: 1.75rem; box-shadow: var(--shadow-xl);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="font-size: 1.2rem; font-weight: 800; margin: 0; color: #dc2626;">Request Cancellation - #{{ $booking->booking_id }}</h3>
            <button type="button" onclick="document.getElementById('cancelModal').style.display='none'" style="background:none; border:none; font-size: 1.5rem; cursor:pointer;">✕</button>
        </div>
        <p style="font-size: 0.875rem; color: var(--slate-600); margin-bottom: 1.25rem;">
            Please state why you wish to cancel this booking. Your request will be forwarded to the central dispatch manager for approval and refund processing as per company policy.
        </p>

        <form action="{{ route('customer.bookings.cancel', $booking->id) }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label for="cancellation_reason" class="form-label">Reason for cancellation request *</label>
                <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="3" required placeholder="Change in travel plans, flight rescheduled..."></textarea>
            </div>

            <div class="d-flex justify-end gap-2">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('cancelModal').style.display='none'">Keep Booking</button>
                <button type="submit" class="btn btn-danger">Submit Cancellation Request</button>
            </div>
        </form>
    </div>
</div>

<style>
.timeline-stepper {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding: 1.5rem 0.5rem;
    min-width: 680px;
}
.timeline-stepper::before {
    content: '';
    position: absolute;
    top: 36px;
    left: 40px;
    right: 40px;
    height: 4px;
    background: var(--slate-200);
    z-index: 1;
}
.timeline-step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}
.timeline-step .step-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid var(--slate-300);
    color: var(--slate-500);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-weight: 800;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}
.timeline-step.completed .step-circle {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
}
.timeline-step.current .step-circle {
    background: var(--primary);
    border-color: var(--dark-900);
    color: var(--dark-900);
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.3);
    animation: pulseCircle 2s infinite;
}
.timeline-step .step-label {
    font-size: 0.775rem;
    font-weight: 700;
    color: var(--slate-500);
    margin-top: 8px;
}
.timeline-step.completed .step-label,
.timeline-step.current .step-label {
    color: var(--dark-900);
}
@keyframes pulseCircle {
    0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
    70% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
    100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}
</style>
@endsection
