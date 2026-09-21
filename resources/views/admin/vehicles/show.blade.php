@extends('layouts.admin')

@section('title', 'Vehicle: ' . $vehicle->name)
@section('page_title', 'Vehicle Details')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('admin.vehicles.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
            ← Back to Fleet Management
        </a>
        <div class="d-flex align-center gap-2 mt-1">
            <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0;">{{ $vehicle->name }}</h1>
            <span class="badge {{ $vehicle->status === 'Available' ? 'badge-success' : ($vehicle->status === 'On Trip' ? 'badge-warning' : 'badge-danger') }}">
                {{ $vehicle->status }}
            </span>
        </div>
    </div>

    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-primary btn-sm">
            ✏️ Edit Vehicle
        </a>
        <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this vehicle from the fleet?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">🗑️ Remove</button>
        </form>
    </div>
</div>

<div class="grid grid-3 gap-4">
    <!-- Left 2 Cols: Specs & Trip History -->
    <div style="grid-column: span 2;">
        <div class="card mb-4">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">🚗 Fleet Specifications</h3>

            <div class="grid grid-3 gap-3 mb-3">
                <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-size: 0.75rem; color: var(--slate-500); text-transform: uppercase;">Registration No.</div>
                    <div style="font-weight: 800; font-family: monospace; font-size: 1.1rem; margin-top: 4px;">{{ $vehicle->registration_number }}</div>
                </div>

                <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-size: 0.75rem; color: var(--slate-500); text-transform: uppercase;">Category & Seating</div>
                    <div style="font-weight: 800; font-size: 1.05rem; margin-top: 4px;">{{ $vehicle->vehicle_type }} ({{ $vehicle->seating_capacity }} Seater)</div>
                </div>

                <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-size: 0.75rem; color: var(--slate-500); text-transform: uppercase;">Comfort / Aircon</div>
                    <div style="font-weight: 800; font-size: 1.05rem; margin-top: 4px;">{{ $vehicle->ac_non_ac }}</div>
                </div>
            </div>

            <div class="grid grid-2 gap-3" style="font-size: 0.9rem;">
                <div>
                    <span style="color: var(--slate-500);">Outstation Per KM:</span>
                    <strong>₹{{ number_format($vehicle->per_km_rate, 2) }} / KM</strong>
                </div>
                <div>
                    <span style="color: var(--slate-500);">Local Hourly Rate:</span>
                    <strong>₹{{ number_format($vehicle->per_hour_rate, 2) }} / Hr</strong>
                </div>
            </div>

            @if($vehicle->notes)
                <div style="margin-top: 1rem; background: #fffbeb; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid #fef3c7; font-size: 0.875rem;">
                    <strong>Fleet Notes:</strong> {{ $vehicle->notes }}
                </div>
            @endif
        </div>

        <!-- Recent Booking Assignments -->
        <div class="card">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;">📋 Booking & Trip History</h3>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicle->bookings as $b)
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
                                <td style="font-weight: 800;">₹{{ number_format($b->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4" style="color: var(--slate-500);">No trips assigned to this vehicle yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right 1 Col: Assigned Driver Card -->
    <div>
        <div class="card mb-4">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;">👨‍✈️ Default Assigned Driver</h3>

            @if($vehicle->assignedDriver)
                <div style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900);">{{ $vehicle->assignedDriver->name }}</div>
                <div style="color: var(--slate-600); font-size: 0.85rem; margin-top: 4px;">
                    📞 <a href="tel:{{ $vehicle->assignedDriver->mobile }}">{{ $vehicle->assignedDriver->mobile }}</a>
                </div>
                <div style="color: var(--slate-500); font-size: 0.8rem; margin-top: 2px;">
                    License: {{ $vehicle->assignedDriver->license_number }}
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.drivers.show', $vehicle->assignedDriver->id) }}" class="btn btn-outline btn-sm btn-block" style="text-align: center;">
                        View Chauffeur Profile →
                    </a>
                </div>
            @else
                <div style="color: var(--slate-500); font-size: 0.875rem;">
                    No primary driver attached. Drivers can be allocated dynamically during booking dispatch.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
