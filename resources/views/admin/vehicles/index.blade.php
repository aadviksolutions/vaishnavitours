@extends('layouts.admin')

@section('title', 'Fleet Management')
@section('page_title', 'Vehicle Fleet')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-900);">Vehicle Fleet Management</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Manage commercial taxis, seating capacities, per-KM rates, and availability status.</p>
    </div>
    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary btn-sm">+ Add New Vehicle</a>
</div>

<!-- Filters Bar -->
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.vehicles.index') }}" class="d-flex align-center gap-3 flex-wrap">
        <div style="min-width: 200px;">
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Categories --</option>
                @foreach(['Hatchback', 'Sedan', 'SUV', 'Innova Crysta', 'Tempo Traveller', 'Luxury'] as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div style="min-width: 180px;">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Statuses --</option>
                @foreach(['Available', 'On Trip', 'Maintenance', 'Inactive'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        @if(request()->hasAny(['type', 'status']))
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Vehicles Table (Section 16) -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Registration</th>
                    <th>Type</th>
                    <th>Seats</th>
                    <th>Status</th>
                    <th>Assigned Trip</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $veh)
                    @php
                        $activeBooking = $veh->bookings()->whereIn('booking_status', ['Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way'])->latest()->first();
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-center gap-2">
                                <div style="width: 44px; height: 32px; border-radius: 4px; overflow: hidden; background: #fff; border: 1px solid var(--slate-200); display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ $veh->icon_url }}" alt="{{ $veh->name }}" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                </div>
                                <div>
                                    <a href="{{ route('admin.vehicles.show', $veh->id) }}" style="font-weight: 800; color: var(--dark-900);">
                                        {{ $veh->name }}
                                    </a>
                                    <div style="font-size: 0.75rem; color: var(--slate-500);">₹{{ number_format($veh->per_km_rate, 2) }}/km</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-outline" style="font-family: monospace; font-size: 0.85rem; font-weight: 700;">
                                {{ $veh->registration_number }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-weight: 600;">{{ $veh->vehicle_type }}</span>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: var(--dark-800);">{{ $veh->seating_capacity }}</span>
                        </td>
                        <td>
                            <span class="badge 
                                {{ $veh->status === 'Available' ? 'badge-success' : '' }}
                                {{ $veh->status === 'On Trip' ? 'badge-warning' : '' }}
                                {{ $veh->status === 'Maintenance' ? 'badge-danger' : '' }}
                                {{ $veh->status === 'Inactive' ? 'badge-secondary' : '' }}
                            ">
                                {{ $veh->status }}
                            </span>
                        </td>
                        <td>
                            @if($activeBooking)
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--dark-900);">
                                    #{{ $activeBooking->booking_id }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--slate-500);">
                                    {{ Str::limit($activeBooking->pickup_location, 12) }} ➔ {{ Str::limit($activeBooking->destination, 12) }}
                                </div>
                            @else
                                <span style="color: var(--slate-400); font-size: 0.8rem;">None (Ready for dispatch)</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.vehicles.edit', $veh->id) }}" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                Edit
                            </a>
                            <a href="{{ route('admin.vehicles.edit', $veh->id) }}#status" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: var(--primary-dark);">
                                Change Status
                            </a>
                            <a href="{{ route('admin.vehicles.show', $veh->id) }}" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                View History
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color: var(--slate-500);">
                            No vehicles found in fleet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($vehicles->hasPages())
        <div class="mt-4">
            {{ $vehicles->links() }}
        </div>
    @endif
</div>
@endsection
