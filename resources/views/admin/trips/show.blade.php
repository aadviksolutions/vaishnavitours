@extends('layouts.admin')

@section('title', 'Trip Details #TRIP-' . str_pad($trip->id, 4, '0', STR_PAD_LEFT))
@section('page_title', 'Trip #' . str_pad($trip->id, 4, '0', STR_PAD_LEFT))

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('admin.trips.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600; text-decoration: none;">
            ← Back to All Trips
        </a>
        <div class="d-flex align-center gap-2 mt-1">
            <h1 style="font-size: 1.75rem; font-weight: 900; margin: 0; color: var(--dark-950);">
                Trip #TRIP-{{ str_pad($trip->id, 4, '0', STR_PAD_LEFT) }}
            </h1>
            <span class="badge badge-lg
                {{ $trip->status === 'Completed' ? 'badge-success' : '' }}
                {{ in_array($trip->status, ['Driver Assigned', 'Scheduled']) ? 'badge-primary' : '' }}
                {{ in_array($trip->status, ['Trip Started', 'On The Way', 'Started']) ? 'badge-warning' : '' }}
                {{ $trip->status === 'Cancelled' ? 'badge-danger' : '' }}
            ">
                {{ $trip->status }}
            </span>
        </div>
    </div>

    <div class="d-flex align-center gap-2">
        @if($trip->booking)
            <a href="{{ route('admin.bookings.show', $trip->booking_id) }}" class="btn btn-outline btn-sm">
                🚖 View Booking {{ $trip->booking->booking_id }}
            </a>
            <a href="{{ route('invoice.show', $trip->booking_id) }}" class="btn btn-outline btn-sm" target="_blank">
                🧾 Tax Invoice
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3">
        ✓ {{ session('success') }}
    </div>
@endif

<!-- Trip Timeline Section (Section 11) -->
<div class="card mb-4" style="border-top: 4px solid var(--primary); padding: 2rem;">
    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dark-950); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
        <span>⏱️</span> Live Journey Milestone Timeline
    </h3>

    @php
        $milestones = [
            'Confirmed' => 'Booking Confirmed',
            'Vehicle Assigned' => 'Vehicle Assigned',
            'Driver Assigned' => 'Driver Assigned',
            'Trip Started' => 'Trip Started',
            'On The Way' => 'On The Way',
            'Completed' => 'Completed',
        ];

        $order = array_keys($milestones);
        $currentStatus = $trip->status;
        if ($currentStatus === 'Started') $currentStatus = 'Trip Started';
        $currIdx = array_search($currentStatus, $order);
        if ($currIdx === false) $currIdx = 1;
        $isCancelled = $trip->status === 'Cancelled';
    @endphp

    @if($isCancelled)
        <div class="alert alert-danger" style="margin: 0;">
            <strong>🚫 This Trip was Cancelled.</strong>
            @if($trip->booking && $trip->booking->cancellation_reason)
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Reason: {{ $trip->booking->cancellation_reason }}</p>
            @endif
        </div>
    @else
        <div style="display: flex; justify-content: space-between; position: relative; max-width: 900px; margin: 1.5rem auto 1rem;">
            <!-- Connector Line -->
            <div style="position: absolute; top: 16px; left: 30px; right: 30px; height: 4px; background: var(--slate-200); z-index: 1;"></div>
            <div style="position: absolute; top: 16px; left: 30px; width: {{ ($currIdx / (count($order) - 1)) * 100 }}%; height: 4px; background: var(--primary); z-index: 2; transition: all 0.3s;"></div>

            @foreach($milestones as $key => $label)
                @php
                    $stepIdx = array_search($key, $order);
                    $isPast = $stepIdx < $currIdx;
                    $isCurrent = $stepIdx === $currIdx;
                @endphp
                <div style="text-align: center; position: relative; z-index: 3; width: 120px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; margin: 0 auto 0.5rem; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem;
                        background: {{ $isPast ? '#10B981' : ($isCurrent ? 'var(--primary)' : '#ffffff') }};
                        color: {{ ($isPast || $isCurrent) ? '#ffffff' : 'var(--slate-400)' }};
                        border: 3px solid {{ ($isPast || $isCurrent) ? 'transparent' : 'var(--slate-300)' }};
                        box-shadow: {{ $isCurrent ? '0 0 0 4px rgba(245, 158, 11, 0.25)' : 'none' }};">
                        @if($isPast)
                            ✓
                        @elseif($isCurrent)
                            ●
                        @else
                            {{ $stepIdx + 1 }}
                        @endif
                    </div>
                    <div style="font-size: 0.775rem; font-weight: {{ $isCurrent ? '800' : '600' }}; color: {{ $isCurrent ? 'var(--dark-950)' : 'var(--slate-600)' }};">
                        {{ $label }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="grid grid-3 gap-4">
    <!-- Left 2 Cols: Trip Operational Information -->
    <div style="grid-column: span 2;">
        <!-- Route & Schedule -->
        <div class="card mb-4">
            <h3 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1rem; color: var(--dark-950);">
                🚖 Route & Schedule Information
            </h3>
            <div class="grid grid-2 gap-3 mb-3">
                <div style="background: var(--slate-50); padding: 1rem; border-radius: 8px; border-left: 4px solid var(--primary);">
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">Pickup Location</div>
                    <div style="font-weight: 800; font-size: 1.05rem; margin-top: 4px;">{{ $trip->booking->pickup_location ?? 'Bilaspur' }}</div>
                </div>
                <div style="background: var(--slate-50); padding: 1rem; border-radius: 8px; border-left: 4px solid var(--dark-900);">
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">Drop Destination</div>
                    <div style="font-weight: 800; font-size: 1.05rem; margin-top: 4px;">{{ $trip->booking->destination ?? 'Raipur' }}</div>
                </div>
            </div>
            <div class="grid grid-3 gap-3" style="font-size: 0.9rem;">
                <div>
                    <span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Customer</span>
                    <div style="font-weight: 800; margin-top: 2px;">{{ $trip->booking->customer->name ?? 'Guest User' }}</div>
                    <div style="font-size: 0.8rem; color: var(--slate-600);">📞 {{ $trip->booking->customer->phone ?? '' }}</div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Travel Schedule</span>
                    <div style="font-weight: 800; margin-top: 2px;">
                        {{ $trip->booking && $trip->booking->travel_date ? $trip->booking->travel_date->format('d M, Y') : '' }}
                    </div>
                    <div style="font-size: 0.8rem; color: var(--slate-600);">{{ $trip->booking ? date('h:i A', strtotime($trip->booking->travel_time)) : '' }}</div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Trip Category</span>
                    <div style="font-weight: 800; margin-top: 2px;">{{ $trip->booking->trip_type ?? 'One-Way' }}</div>
                </div>
            </div>
        </div>

        <!-- Fleet & Driver -->
        <div class="grid grid-2 gap-3 mb-4">
            <div class="card">
                <span style="color: var(--slate-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Assigned Vehicle</span>
                @if($trip->vehicle)
                    <div style="font-size: 1.1rem; font-weight: 800; margin-top: 4px; color: var(--dark-950);">
                        {{ $trip->vehicle->name }}
                    </div>
                    <div style="font-size: 0.9rem; font-family: monospace; font-weight: 700; color: var(--slate-600);">
                        {{ $trip->vehicle->registration_number }}
                    </div>
                    <div style="font-size: 0.8rem; color: var(--slate-500); margin-top: 4px;">
                        {{ $trip->vehicle->vehicle_type }} • {{ $trip->vehicle->seating_capacity }} Seater ({{ $trip->vehicle->ac_non_ac }})
                    </div>
                @else
                    <div style="color: #dc2626; font-size: 0.9rem; margin-top: 4px;">No vehicle allocated</div>
                @endif
            </div>

            <div class="card">
                <span style="color: var(--slate-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Assigned Chauffeur</span>
                @if($trip->driver)
                    <div style="font-size: 1.1rem; font-weight: 800; margin-top: 4px; color: var(--dark-950);">
                        {{ $trip->driver->name }}
                    </div>
                    <div style="font-size: 0.9rem; font-weight: 700; color: var(--dark-900);">
                        📞 {{ $trip->driver->mobile }}
                    </div>
                    <div style="font-size: 0.8rem; color: var(--slate-500); margin-top: 4px;">
                        License: {{ $trip->driver->license_number }} (⭐ {{ $trip->driver->rating }})
                    </div>
                @else
                    <div style="color: #dc2626; font-size: 0.9rem; margin-top: 4px;">No driver assigned</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Col: Update Status & Odometer -->
    <div>
        <div class="card mb-4" style="border-top: 4px solid var(--dark-900);">
            <h3 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1rem; color: var(--dark-950);">
                ⚡ Update Trip Status & Odometer
            </h3>

            <form action="{{ route('admin.trips.update', $trip->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Transition Trip Status</label>
                    <select name="status" class="form-select" required>
                        @foreach(['Driver Assigned', 'Trip Started', 'On The Way', 'Completed', 'Cancelled'] as $st)
                            <option value="{{ $st }}" {{ $trip->status === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Start Odometer (KM)</label>
                    <input type="number" name="start_odometer" class="form-control" value="{{ $trip->start_odometer }}" placeholder="e.g. 45200">
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">End Odometer (KM)</label>
                    <input type="number" name="end_odometer" class="form-control" value="{{ $trip->end_odometer }}" placeholder="e.g. 45340">
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Route Notes / Checkpoints</label>
                    <textarea name="route_notes" class="form-control" rows="2" placeholder="e.g. Bilaspur toll crossed at 06:15 AM">{{ $trip->route_notes ?: $trip->notes }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100" style="font-weight: 800;">
                    Save Trip Updates
                </button>
            </form>
        </div>

        @if($trip->started_at || $trip->completed_at)
            <div class="card" style="font-size: 0.85rem; color: var(--slate-600);">
                <div style="font-weight: 700; color: var(--dark-900); margin-bottom: 0.5rem; text-transform: uppercase; font-size: 0.75rem;">
                    Trip Timestamps
                </div>
                @if($trip->started_at)
                    <div>Trip Started: <strong>{{ $trip->started_at->format('d M, h:i A') }}</strong></div>
                @endif
                @if($trip->completed_at)
                    <div style="margin-top: 4px;">Trip Completed: <strong>{{ $trip->completed_at->format('d M, h:i A') }}</strong></div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
