@extends('layouts.admin')

@section('title', 'Trips Management - Vaishnavi Tours')
@section('page_title', 'Trips Operations')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-950);">Active Trips Management</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">
            Real-time journey control, odometer kilometers tracking, and driver dispatch status.
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3">
        ✓ {{ session('success') }}
    </div>
@endif

<!-- Filters Bar -->
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.trips.index') }}" class="d-flex align-center gap-3 flex-wrap">
        <div style="min-width: 220px;">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Trip Statuses --</option>
                @foreach(['Scheduled', 'Driver Assigned', 'Started', 'Trip Started', 'On The Way', 'Completed', 'Cancelled'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        @if(request()->filled('status'))
            <a href="{{ route('admin.trips.index') }}" class="btn btn-outline btn-sm">Reset Filter</a>
        @endif
    </form>
</div>

<!-- Trips Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table" style="font-size: 0.875rem;">
            <thead>
                <tr>
                    <th>Trip ID</th>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Route</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Travel Date</th>
                    <th>Travel Time</th>
                    <th>Status</th>
                    <th style="text-align: right; min-width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trips as $trip)
                    <tr>
                        <td style="font-weight: 800; font-family: monospace;">
                            <a href="{{ route('admin.trips.show', $trip->id) }}" style="color: var(--dark-950);">
                                #TRIP-{{ str_pad($trip->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td>
                            @if($trip->booking)
                                <a href="{{ route('admin.bookings.show', $trip->booking_id) }}" style="font-weight: 800; color: var(--primary-dark); font-family: monospace;">
                                    {{ $trip->booking->booking_id }}
                                </a>
                            @else
                                <span style="color: var(--slate-400);">N/A</span>
                            @endif
                        </td>
                        <td style="font-weight: 700;">
                            {{ $trip->booking->customer->name ?? 'Guest Traveler' }}
                        </td>
                        <td>
                            <div style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $trip->booking->pickup_location ?? '' }} to {{ $trip->booking->destination ?? '' }}">
                                {{ $trip->booking->pickup_location ?? 'Bilaspur' }} ➔ {{ $trip->booking->destination ?? 'Raipur' }}
                            </div>
                        </td>
                        <td style="white-space: nowrap;">
                            @if($trip->vehicle)
                                <span title="{{ $trip->vehicle->registration_number }}">{{ $trip->vehicle->name }}</span>
                            @else
                                <span style="color: #dc2626; font-size: 0.8rem;">Unassigned</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            @if($trip->driver)
                                <span>{{ $trip->driver->name }}</span>
                            @else
                                <span style="color: #dc2626; font-size: 0.8rem;">Unassigned</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap; font-weight: 600;">
                            {{ $trip->booking && $trip->booking->travel_date ? $trip->booking->travel_date->format('d M, Y') : '' }}
                        </td>
                        <td style="white-space: nowrap;">
                            {{ $trip->booking ? date('h:i A', strtotime($trip->booking->travel_time)) : '' }}
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="badge badge-sm
                                {{ $trip->status === 'Completed' ? 'badge-success' : '' }}
                                {{ in_array($trip->status, ['Driver Assigned', 'Scheduled']) ? 'badge-primary' : '' }}
                                {{ in_array($trip->status, ['Started', 'Trip Started', 'On The Way']) ? 'badge-warning' : '' }}
                                {{ $trip->status === 'Cancelled' ? 'badge-danger' : '' }}
                            ">
                                {{ $trip->status }}
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div class="d-flex justify-end gap-1">
                                <!-- Action: View -->
                                <a href="{{ route('admin.trips.show', $trip->id) }}" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                    View
                                </a>

                                <!-- Action: Update Status Modal -->
                                <button type="button" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="openModal('tripModal{{ $trip->id }}')">
                                    Update Status
                                </button>

                                <!-- Action: View Booking -->
                                @if($trip->booking_id)
                                    <a href="{{ route('admin.bookings.show', $trip->booking_id) }}" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                        View Booking
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Modal: Update Trip Status -->
                    <div id="tripModal{{ $trip->id }}" class="custom-modal" style="display: none;">
                        <div class="custom-modal-content">
                            <div class="d-flex justify-between align-center mb-3">
                                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Update Trip #TRIP-{{ str_pad($trip->id, 4, '0', STR_PAD_LEFT) }}</h3>
                                <button type="button" onclick="closeModal('tripModal{{ $trip->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
                            </div>
                            <form action="{{ route('admin.trips.update', $trip->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group mb-3">
                                    <label class="form-label">Trip Status</label>
                                    <select name="status" class="form-select" required>
                                        @foreach(['Driver Assigned', 'Trip Started', 'On The Way', 'Completed', 'Cancelled'] as $st)
                                            <option value="{{ $st }}" {{ $trip->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-2 gap-2 mb-3">
                                    <div>
                                        <label class="form-label">Start Odometer (KM)</label>
                                        <input type="number" name="start_odometer" class="form-control" value="{{ $trip->start_odometer }}" placeholder="e.g. 45200">
                                    </div>
                                    <div>
                                        <label class="form-label">End Odometer (KM)</label>
                                        <input type="number" name="end_odometer" class="form-control" value="{{ $trip->end_odometer }}" placeholder="e.g. 45340">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Route Notes / Checkpoint</label>
                                    <input type="text" name="route_notes" class="form-control" value="{{ $trip->route_notes }}" placeholder="e.g. Mangla Bypass toll crossed">
                                </div>
                                <div class="d-flex justify-end gap-2">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('tripModal{{ $trip->id }}')">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Update Trip</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: var(--slate-500);">
                            No active trips recorded. Trips are automatically created once a chauffeur is assigned.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($trips->hasPages())
        <div style="padding: 1.25rem; border-top: 1px solid var(--slate-200);">
            {{ $trips->links() }}
        </div>
    @endif
</div>

<style>
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(2px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.custom-modal-content {
    background: #ffffff;
    border-radius: 10px;
    max-width: 500px;
    width: 100%;
    padding: 1.75rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
}
</style>

<script>
function openModal(id) {
    var m = document.getElementById(id);
    if (m) m.style.display = 'flex';
}
function closeModal(id) {
    var m = document.getElementById(id);
    if (m) m.style.display = 'none';
}
window.onclick = function(event) {
    if (event.target.classList.contains('custom-modal')) {
        event.target.style.display = 'none';
    }
}
</script>
@endsection
