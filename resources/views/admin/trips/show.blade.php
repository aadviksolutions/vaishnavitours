@extends('layouts.admin')

@section('title', 'Trip Details #TRIP-' . str_pad($trip->id, 4, '0', STR_PAD_LEFT))
@section('page_title', 'Trip #' . str_pad($trip->id, 4, '0', STR_PAD_LEFT))

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('admin.trips.index') }}" style="font-size: 0.85rem; color: var(--slate-600); font-weight: 600; text-decoration: none;">
            ← Back to All Trips
        </a>
        <div class="d-flex align-center gap-2 mt-1">
            <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--dark-950);">
                Trip #TRIP-{{ str_pad($trip->id, 4, '0', STR_PAD_LEFT) }}
            </h1>
            <span class="badge {{ $trip->journey_status === 'Journey Completed' ? 'badge-success' : 'badge-warning' }}">
                Journey: {{ $trip->journey_status ?: $trip->status }}
            </span>
            <span class="badge {{ $trip->trip_status === 'Closed' ? 'badge-dark' : 'badge-danger' }}">
                Trip Status: {{ $trip->trip_status ?: 'Open' }}
            </span>
        </div>
    </div>

    <div class="d-flex align-center gap-2">
        @if($trip->booking)
            <a href="{{ route('admin.bookings.show', $trip->booking_id) }}" class="btn btn-outline btn-sm">
                🚖 View Booking {{ $trip->booking->booking_id }}
            </a>
            <a href="{{ route('admin.invoices.show', $trip->booking->invoice?->id ?? 1) }}" class="btn btn-outline btn-sm" target="_blank">
                🧾 Tax Invoice
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px;">
        ✓ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-3" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px;">
        ✕ {{ session('error') }}
    </div>
@endif

<!-- Trip Lifecycle & Financial Closure Card (Requirement: Section 8) -->
<div class="card mb-3" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-2">
        <div>
            <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--dark-900); display: flex; align-items: center; gap: 6px;">
                <x-icon name="shield-check" size="16" class="text-blue-600" /> Trip Lifecycle & Financial Closure Rule
            </h3>
            <span style="font-size: 12px; color: var(--slate-500);">
                A trip remains open until full payment is received. Journey completion and financial closure are kept separate.
            </span>
        </div>
        <div>
            @if($trip->trip_status === 'Closed')
                <span class="badge badge-success" style="font-size: 12px; padding: 4px 10px;">
                    ✓ Closed on {{ $trip->closed_at?->format('d M Y, h:i A') }}
                </span>
            @else
                <span class="badge badge-warning" style="font-size: 12px; padding: 4px 10px;">
                    ● Open / Incomplete
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-4 gap-2 mb-3" style="background: var(--slate-50); padding: 0.85rem; border-radius: 6px;">
        <div>
            <span style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 600;">Total Booking Fare</span>
            <div style="font-size: 16px; font-weight: 700; color: var(--dark-900);">₹{{ number_format($trip->booking?->total_amount ?? 0, 2) }}</div>
        </div>
        <div>
            <span style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 600;">Amount Collected</span>
            <div style="font-size: 16px; font-weight: 700; color: #059669;">₹{{ number_format($trip->booking?->paid_amount ?? 0, 2) }}</div>
        </div>
        <div>
            <span style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 600;">Outstanding Balance</span>
            <div style="font-size: 16px; font-weight: 700; color: {{ ($trip->booking?->balance_amount ?? 0) > 0 ? '#DC2626' : '#059669' }};">
                ₹{{ number_format($trip->booking?->balance_amount ?? 0, 2) }}
            </div>
        </div>
        <div>
            <span style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 600;">Journey Progress</span>
            <div style="font-size: 14px; font-weight: 700; color: var(--dark-900);">{{ $trip->journey_status ?: $trip->status }}</div>
        </div>
    </div>

    @if($trip->trip_status !== 'Closed')
        <form action="{{ route('admin.trips.close', $trip->id) }}" method="POST" style="background: #F8FAFC; border: 1px dashed var(--slate-300); padding: 0.85rem; border-radius: 6px;">
            @csrf
            <div class="d-flex justify-between align-center flex-wrap gap-2">
                <div>
                    <div style="font-weight: 600; font-size: 13px; color: var(--dark-900);">Formally Close Trip</div>
                    <div style="font-size: 11.5px; color: var(--slate-500);">
                        @if(($trip->booking?->balance_amount ?? 0) > 0)
                            <span style="color: #DC2626; font-weight: 600;">Balance remains pending!</span> Check the exception box below if closing under corporate credit terms.
                        @else
                            Full payment received. Trip can be marked closed.
                        @endif
                    </div>
                </div>

                <div class="d-flex align-center gap-2">
                    @if(($trip->booking?->balance_amount ?? 0) > 0)
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="credit_exception" value="1" id="chkCreditException">
                            <span>Authorized Credit Exception</span>
                        </label>
                        <input type="text" name="exception_reason" id="txtExceptionReason" placeholder="Reason (e.g. 30-day corporate billing)" style="font-size: 11px; padding: 4px 8px; border: 1px solid var(--slate-300); border-radius: 4px; width: 200px;">
                    @endif
                    <button type="submit" class="btn btn-dark btn-sm">Formal Trip Closure</button>
                </div>
            </div>
        </form>
    @else
        <div style="font-size: 12px; color: var(--slate-600); background: #F1F5F9; padding: 0.5rem 0.75rem; border-radius: 4px;">
            Closed By: <strong>{{ $trip->closedByUser?->name ?? 'Admin Staff' }}</strong> · Reason: <em>{{ $trip->closure_reason }}</em>
        </div>
    @endif
</div>

<div class="grid grid-3 gap-3">
    <!-- Left 2 Cols: Trip Operational Information -->
    <div style="grid-column: span 2;">
        <!-- Route & Schedule -->
        <div class="card mb-3" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 0.75rem; color: var(--dark-950);">
                Route & Schedule Information
            </h3>
            <div class="grid grid-3 gap-2 mb-3">
                <div style="background: var(--slate-50); padding: 0.75rem; border-radius: 6px; border-left: 3px solid #2563EB;">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 600;">Pickup Location</div>
                    <div style="font-weight: 700; font-size: 14px; margin-top: 2px;">{{ $trip->booking?->pickup_location ?? 'Bilaspur' }}</div>
                </div>
                <div style="background: var(--slate-50); padding: 0.75rem; border-radius: 6px; border-left: 3px solid #D97706;">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 600;">Via Location</div>
                    <div style="font-weight: 700; font-size: 14px; margin-top: 2px;">{{ $trip->booking?->via_location ?: 'Direct (No via)' }}</div>
                </div>
                <div style="background: var(--slate-50); padding: 0.75rem; border-radius: 6px; border-left: 3px solid #059669;">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 600;">Final Drop Destination</div>
                    <div style="font-weight: 700; font-size: 14px; margin-top: 2px;">{{ $trip->booking?->destination ?? 'Raipur' }}</div>
                </div>
            </div>
            <div class="grid grid-3 gap-2" style="font-size: 12.5px;">
                <div>
                    <span style="color: var(--slate-500); font-size: 11px; text-transform: uppercase; font-weight: 600;">Customer</span>
                    <div style="font-weight: 700; margin-top: 2px;">{{ $trip->booking?->customer?->name ?? 'Guest User' }}</div>
                    <div style="font-size: 11px; color: var(--slate-600);">📞 {{ $trip->booking?->customer?->phone ?? '' }}</div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 11px; text-transform: uppercase; font-weight: 600;">Travel Schedule</span>
                    <div style="font-weight: 700; margin-top: 2px;">
                        {{ $trip->booking && $trip->booking->travel_date ? $trip->booking->travel_date->format('d M, Y') : '' }}
                    </div>
                    <div style="font-size: 11px; color: var(--slate-600);">{{ $trip->booking && $trip->booking->travel_time ? (is_numeric(strtotime($trip->booking->travel_time)) ? date('h:i A', strtotime($trip->booking->travel_time)) : $trip->booking->travel_time) : '' }}</div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 11px; text-transform: uppercase; font-weight: 600;">Category & Prefix</span>
                    <div style="font-weight: 700; margin-top: 2px;">
                        <span class="badge badge-primary">{{ $trip->booking?->booking_category ?? 'WEB' }}</span>
                        {{ $trip->booking?->trip_type ?? 'One-Way' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Fleet & Driver -->
        <div class="grid grid-2 gap-2 mb-3">
            <div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
                <span style="color: var(--slate-500); font-size: 11px; font-weight: 700; text-transform: uppercase;">Assigned Vehicle</span>
                @if($trip->vehicle)
                    <div style="font-size: 14px; font-weight: 700; margin-top: 4px; color: var(--dark-950);">
                        {{ $trip->vehicle->name }}
                    </div>
                    <div style="font-size: 12px; font-family: monospace; font-weight: 700; color: #2563EB;">
                        {{ $trip->vehicle->registration_number }}
                    </div>
                    <div style="font-size: 11px; color: var(--slate-500); margin-top: 4px;">
                        {{ $trip->vehicle->vehicle_type }} • {{ $trip->vehicle->ownership_type === 'third_party' ? 'Third-Party' : 'Fleet Owned' }}
                    </div>
                @else
                    <div style="color: #dc2626; font-size: 12px; margin-top: 4px;">No vehicle allocated</div>
                @endif
            </div>

            <div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
                <span style="color: var(--slate-500); font-size: 11px; font-weight: 700; text-transform: uppercase;">Assigned Chauffeur</span>
                @if($trip->driver)
                    <div style="font-size: 14px; font-weight: 700; margin-top: 4px; color: var(--dark-950);">
                        {{ $trip->driver->name }}
                    </div>
                    <div style="font-size: 12px; font-weight: 600; color: var(--dark-900);">
                        📞 {{ $trip->driver->mobile }}
                    </div>
                    <div style="font-size: 11px; color: var(--slate-500); margin-top: 4px;">
                        License: {{ $trip->driver->license_number }} (⭐ {{ $trip->driver->rating }})
                    </div>
                @else
                    <div style="color: #dc2626; font-size: 12px; margin-top: 4px;">No driver assigned</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Col: Update Status & Odometer -->
    <div>
        <div class="card mb-3" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 0.75rem; color: var(--dark-950);">
                ⚡ Update Journey & Odometer
            </h3>

            <form action="{{ route('admin.trips.update', $trip->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-2">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Journey Status</label>
                    <select name="status" class="form-select form-select-sm" required>
                        @foreach(['Driver Assigned', 'Trip Started', 'On The Way', 'Completed', 'Cancelled'] as $st)
                            <option value="{{ $st }}" {{ $trip->status === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-2 gap-2 mb-2">
                    <div>
                        <label class="form-label" style="font-size: 11px; font-weight: 600;">Start Odometer</label>
                        <input type="number" name="start_odometer" class="form-control form-control-sm" value="{{ $trip->start_odometer }}" placeholder="e.g. 45200">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px; font-weight: 600;">End Odometer</label>
                        <input type="number" name="end_odometer" class="form-control form-control-sm" value="{{ $trip->end_odometer }}" placeholder="e.g. 45340">
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label class="form-label" style="font-size: 11px; font-weight: 600;">Route Notes / Checkpoints</label>
                    <textarea name="route_notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Bilaspur toll crossed at 06:15 AM">{{ $trip->route_notes ?: $trip->notes }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-sm" style="font-weight: 700;">
                    Save Journey Updates
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
