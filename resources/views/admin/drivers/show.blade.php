@extends('layouts.admin')

@section('title', 'Chauffeur: ' . $driver->name)
@section('page_title', 'Chauffeur Details')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('admin.drivers.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
            ← Back to Chauffeurs Roster
        </a>
        <div class="d-flex align-center gap-2 mt-1">
            <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0;">{{ $driver->name }}</h1>
            <span class="badge {{ $driver->status === 'Available' ? 'badge-success' : ($driver->status === 'Assigned' ? 'badge-primary' : ($driver->status === 'On Trip' ? 'badge-warning' : 'badge-danger')) }}">
                {{ $driver->status }}
            </span>
        </div>
    </div>

    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.drivers.edit', $driver->id) }}" class="btn btn-primary btn-sm">
            ✏️ Edit Chauffeur
        </a>
        <form action="{{ route('admin.drivers.destroy', $driver->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this driver record?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete</button>
        </form>
    </div>
</div>

<div class="grid grid-3 gap-4">
    <!-- Left 2 Cols: Driver Details & Trips -->
    <div style="grid-column: span 2;">
        <div class="card mb-4">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">👨‍✈️ Chauffeur Credentials</h3>

            <div class="grid grid-3 gap-3 mb-3">
                <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-size: 0.75rem; color: var(--slate-500); text-transform: uppercase;">Mobile Number</div>
                    <div style="font-weight: 800; font-size: 1.05rem; margin-top: 4px;">
                        <a href="tel:{{ $driver->mobile }}" style="color: var(--dark-900);">{{ $driver->mobile }}</a>
                    </div>
                    @if($driver->alternate_mobile)
                        <small style="color: var(--slate-500);">Alt: {{ $driver->alternate_mobile }}</small>
                    @endif
                </div>

                <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-size: 0.75rem; color: var(--slate-500); text-transform: uppercase;">Commercial License</div>
                    <div style="font-weight: 800; font-family: monospace; font-size: 1rem; margin-top: 4px;">{{ $driver->license_number }}</div>
                    <small style="color: var(--slate-500);">Valid till: {{ $driver->license_expiry ? \Carbon\Carbon::parse($driver->license_expiry)->format('d M Y') : 'N/A' }}</small>
                </div>

                <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-size: 0.75rem; color: var(--slate-500); text-transform: uppercase;">Passenger Rating</div>
                    <div style="font-weight: 800; font-size: 1.25rem; color: #d97706; margin-top: 4px;">⭐ {{ number_format($driver->rating, 1) }} / 5.0</div>
                </div>
            </div>

            @if($driver->address)
                <div style="margin-bottom: 0.75rem; font-size: 0.875rem;">
                    <strong>Address:</strong> {{ $driver->address }}
                </div>
            @endif

            @if($driver->notes)
                <div style="background: #fffbeb; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid #fef3c7; font-size: 0.875rem;">
                    <strong>Background & Verification Notes:</strong> {{ $driver->notes }}
                </div>
            @endif
        </div>

        <!-- Recent Booking Assignments -->
        <div class="card">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;">📋 Completed & Assigned Trips</h3>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Passenger</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driver->bookings as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" style="font-weight: 800; color: var(--dark-900);">
                                        #{{ $b->booking_id }}
                                    </a>
                                </td>
                                <td>{{ $b->customer->name ?? 'Customer' }}</td>
                                <td>{{ $b->pickup_location }} ➔ {{ $b->destination }}</td>
                                <td>{{ $b->travel_date ? $b->travel_date->format('d M Y') : '' }}</td>
                                <td>
                                    <span class="badge badge-sm badge-secondary">{{ $b->booking_status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4" style="color: var(--slate-500);">No trips assigned to this chauffeur yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right 1 Col: Assigned Vehicle Card -->
    <div>
        <div class="card mb-4">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;">🚗 Default Assigned Vehicle</h3>

            @if($driver->assignedVehicle)
                <div style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900);">{{ $driver->assignedVehicle->name }}</div>
                <div style="font-family: monospace; color: var(--slate-600); font-size: 0.9rem; margin-top: 4px;">
                    {{ $driver->assignedVehicle->registration_number }}
                </div>
                <div style="color: var(--slate-500); font-size: 0.8rem; margin-top: 2px;">
                    {{ $driver->assignedVehicle->vehicle_type }} • {{ $driver->assignedVehicle->seating_capacity }} Seater
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.vehicles.show', $driver->assignedVehicle->id) }}" class="btn btn-outline btn-sm btn-block" style="text-align: center;">
                        View Vehicle Fleet Profile →
                    </a>
                </div>
            @else
                <div style="color: var(--slate-500); font-size: 0.875rem;">
                    Pool Chauffeur (Allocated to any available fleet vehicle dynamically per booking).
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
