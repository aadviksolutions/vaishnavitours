@extends('layouts.admin')

@section('title', 'Chauffeurs & Drivers - Vaishnavi Tours')
@section('page_title', 'Chauffeur Roster')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <h1 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--dark-900);">Chauffeurs & Professional Drivers Roster</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 12.5px;">
            Driver availability, overlapping trip conflict prevention, and independent driver-specific quality ratings.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary btn-sm">+ Onboard New Driver</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px;">
        ✓ {{ session('success') }}
    </div>
@endif

<!-- Driver Metrics -->
<div class="grid grid-4 gap-2 mb-3">
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Total Registered Drivers</span>
        <div style="font-size: 20px; font-weight: 700; color: var(--dark-900);">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #059669;">Available & Unassigned</span>
        <div style="font-size: 20px; font-weight: 700; color: #059669;">{{ number_format($stats['available_unassigned']) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #2563EB;">Currently Assigned</span>
        <div style="font-size: 20px; font-weight: 700; color: #2563EB;">{{ number_format($stats['assigned']) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Active On Roster</span>
        <div style="font-size: 20px; font-weight: 700; color: var(--dark-900);">{{ number_format($stats['active']) }}</div>
    </div>
</div>

<!-- Assigned vs Unassigned Tabs (Section 3 requirement) -->
<div class="d-flex align-center gap-1 mb-3" style="border-bottom: 2px solid var(--slate-200);">
    <a href="{{ route('admin.drivers.index') }}" 
       style="padding: 0.45rem 0.85rem; font-size: 12.5px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ !request()->has('filter') ? '#2563EB' : 'transparent' }}; color: {{ !request()->has('filter') ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       All Drivers ({{ $stats['total'] }})
    </a>
    <a href="{{ route('admin.drivers.index', ['filter' => 'unassigned']) }}" 
       style="padding: 0.45rem 0.85rem; font-size: 12.5px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ request('filter') === 'unassigned' ? '#059669' : 'transparent' }}; color: {{ request('filter') === 'unassigned' ? '#059669' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       Unassigned / Pool Drivers ({{ $stats['available_unassigned'] }})
    </a>
    <a href="{{ route('admin.drivers.index', ['filter' => 'assigned']) }}" 
       style="padding: 0.45rem 0.85rem; font-size: 12.5px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ request('filter') === 'assigned' ? '#2563EB' : 'transparent' }}; color: {{ request('filter') === 'assigned' ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       Assigned Drivers ({{ $stats['assigned'] }})
    </a>
</div>

<!-- Filters Bar with Sorting for Unassigned Drivers -->
<div class="card mb-3" style="padding: 0.75rem 1rem; background: #fff; border: 1px solid var(--slate-200); border-radius: 6px;">
    <form method="GET" action="{{ route('admin.drivers.index') }}" class="d-flex align-center gap-2 flex-wrap justify-between">
        <input type="hidden" name="filter" value="{{ request('filter') }}">
        <div class="d-flex align-center gap-2 flex-wrap">
            <select name="status" class="form-select form-select-sm" style="min-width: 140px;">
                <option value="">All Statuses</option>
                @foreach(['Available', 'Assigned', 'On Trip', 'Inactive'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>

            <select name="sort" class="form-select form-select-sm" style="min-width: 160px;">
                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Sort: Recently Added</option>
                <option value="rating_desc" {{ request('sort') === 'rating_desc' ? 'selected' : '' }}>Sort: Highest Rating (⭐)</option>
                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Sort: Name (A-Z)</option>
                <option value="experience" {{ request('sort') === 'experience' ? 'selected' : '' }}>Sort: Experience</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Filter & Sort</button>
        </div>

        @if(request()->hasAny(['status', 'sort']))
            <a href="{{ route('admin.drivers.index', ['filter' => request('filter')]) }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Drivers Table -->
<div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; font-size: 12.5px;">
            <thead>
                <tr>
                    <th>Chauffeur</th>
                    <th>Mobile</th>
                    <th>License & Validity</th>
                    <th>Vehicle Assignment</th>
                    <th>Driver Rating</th>
                    <th>Complaints Log</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                    <tr>
                        <td>
                            <div class="d-flex align-center gap-2">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #0F172A; color: #F59E0B; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                    👨‍✈️
                                </div>
                                <div>
                                    <a href="{{ route('admin.drivers.show', $driver->id) }}" style="font-weight: 700; color: var(--dark-900);">
                                        {{ $driver->name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #2563EB;">📞 {{ $driver->mobile }}</div>
                            @if($driver->alternate_mobile)
                                <div style="font-size: 10.5px; color: var(--slate-500);">Alt: {{ $driver->alternate_mobile }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-outline" style="font-family: monospace; font-size: 11px; font-weight: 700;">
                                {{ $driver->license_number }}
                            </span>
                            @if($driver->license_expiry)
                                <div style="font-size: 10.5px; color: var(--slate-500); margin-top: 2px;">
                                    Exp: {{ \Carbon\Carbon::parse($driver->license_expiry)->format('d M Y') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($driver->assignedVehicle)
                                <div style="font-size: 12px; font-weight: 600; color: var(--dark-900);">🚗 {{ $driver->assignedVehicle->name }}</div>
                                <div style="font-size: 10.5px; font-family: monospace; color: #2563EB;">{{ $driver->assignedVehicle->registration_number }}</div>
                            @else
                                <span class="badge badge-secondary" style="font-size: 10.5px;">Pool Driver (Unassigned)</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight: 700; color: #D97706;">
                                ⭐ {{ number_format($driver->rating ?: 5.0, 1) }}/5
                            </span>
                        </td>
                        <td>
                            @php
                                $cCount = $driver->complaints->count();
                            @endphp
                            @if($cCount > 0)
                                <a href="{{ route('admin.complaints.index', ['type' => 'driver']) }}" class="badge badge-danger" style="font-size: 10px;">
                                    {{ $cCount }} {{ Str::plural('Issue', $cCount) }}
                                </a>
                            @else
                                <span style="font-size: 11px; color: #059669;">✓ Clean</span>
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
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.drivers.edit', $driver->id) }}" class="btn btn-outline btn-sm" style="padding: 2px 7px; font-size: 11px;">
                                Edit
                            </a>
                            <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-primary btn-sm" style="padding: 2px 7px; font-size: 11px;">
                                Profile
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--slate-500);">
                            No drivers found in this view.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($drivers->hasPages())
        <div style="padding: 0.75rem 1rem; border-top: 1px solid var(--slate-200);">
            {{ $drivers->links() }}
        </div>
    @endif
</div>
@endsection
