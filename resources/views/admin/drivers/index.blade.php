@extends('layouts.admin')

@section('title', 'Chauffeurs & Drivers')
@section('page_title', 'Chauffeur Management')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-900);">Chauffeurs & Drivers Roster</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Manage verified professional drivers, license verifications, and assigned vehicles.</p>
    </div>
    <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary btn-sm">+ Onboard New Driver</a>
</div>

<!-- Filters Bar -->
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.drivers.index') }}" class="d-flex align-center gap-3 flex-wrap">
        <div style="min-width: 200px;">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Driver Statuses --</option>
                @foreach(['Available', 'Assigned', 'On Trip', 'Inactive'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        @if(request()->filled('status'))
            <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Drivers Table (Section 17) -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Mobile</th>
                    <th>License</th>
                    <th>Assigned Vehicle</th>
                    <th>Status</th>
                    <th>Trips</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                    <tr>
                        <td>
                            <div class="d-flex align-center gap-2">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--dark-900); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem;">
                                    👨‍✈️
                                </div>
                                <div>
                                    <a href="{{ route('admin.drivers.show', $driver->id) }}" style="font-weight: 800; color: var(--dark-900);">
                                        {{ $driver->name }}
                                    </a>
                                    <div style="font-size: 0.725rem; color: #D97706;">⭐ {{ number_format($driver->rating, 1) }} Rating</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--dark-900);">📞 {{ $driver->mobile }}</div>
                            @if($driver->alternate_mobile)
                                <small style="color: var(--slate-500);">Alt: {{ $driver->alternate_mobile }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-outline" style="font-family: monospace; font-size: 0.8rem; font-weight: 700;">
                                {{ $driver->license_number }}
                            </span>
                        </td>
                        <td>
                            @if($driver->assignedVehicle)
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--dark-900);">🚗 {{ $driver->assignedVehicle->name }}</div>
                                <div style="font-size: 0.725rem; font-family: monospace; color: var(--slate-500);">{{ $driver->assignedVehicle->registration_number }}</div>
                            @else
                                <span style="color: var(--slate-400); font-size: 0.8rem;">Pool Driver (Unassigned)</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge 
                                {{ $driver->status === 'Available' ? 'badge-success' : '' }}
                                {{ $driver->status === 'Assigned' ? 'badge-primary' : '' }}
                                {{ $driver->status === 'On Trip' ? 'badge-warning' : '' }}
                                {{ $driver->status === 'Inactive' ? 'badge-secondary' : '' }}
                            ">
                                {{ $driver->status }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-outline" style="font-weight: 800; font-size: 0.85rem;">
                                {{ $driver->trips ? $driver->trips->count() : 0 }} Trips
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.drivers.edit', $driver->id) }}" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                Edit
                            </a>
                            <a href="{{ route('admin.drivers.edit', $driver->id) }}#vehicle" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: var(--primary-dark);">
                                Assign
                            </a>
                            <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                View History
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color: var(--slate-500);">
                            No chauffeurs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($drivers->hasPages())
        <div class="mt-4">
            {{ $drivers->links() }}
        </div>
    @endif
</div>
@endsection
