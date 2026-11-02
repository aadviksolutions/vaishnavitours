@extends('layouts.admin')

@section('title', 'Bookings Management - Vaishnavi Tours')
@section('page_title', 'Cab Bookings Management')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.35rem; font-weight: 600; margin: 0; color: var(--dark-900);">Cab Bookings Management</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.85rem;">
            Central dispatch desk: review reservations, allocate fleet & chauffeurs, track journey milestones.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">Create New Booking</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3">
        <x-icon name="circle-check" size="18" style="vertical-align: middle; margin-right: 4px;" /> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-3">
        <x-icon name="alert-circle" size="18" style="vertical-align: middle; margin-right: 4px;" /> {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <strong style="display: flex; align-items: center; gap: 6px;"><x-icon name="alert-circle" size="18" /><span>Please correct the following errors:</span></strong>
        <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Filters Bar -->
<div class="card mb-4" style="padding: 1rem 1.25rem;">
    <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid grid-4 gap-3 align-end">
        <div>
            <label class="form-label" style="font-size: 0.775rem;">Search Passenger / Route / ID</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ID, passenger name, phone..." value="{{ request('search') }}">
        </div>

        <div>
            <label class="form-label" style="font-size: 0.775rem;">Booking Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">-- All Statuses --</option>
                @foreach(['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way', 'Completed', 'Cancelled'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label" style="font-size: 0.775rem;">Payment Status</label>
            <select name="payment_status" class="form-select form-select-sm">
                <option value="">-- All Payment Statuses --</option>
                @foreach(['Pending', 'Partial', 'Paid', 'Refunded'] as $pst)
                    <option value="{{ $pst }}" {{ request('payment_status') === $pst ? 'selected' : '' }}>{{ $pst }}</option>
                @endforeach
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm flex-1">Apply Filters</button>
            @if(request()->hasAny(['search', 'status', 'payment_status', 'trip_type']))
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline btn-sm">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Excel Sheet KPI Summary Bar & Toolbar -->
<div class="card mb-3" style="padding: 0.75rem 1rem; background: #FFFFFF; border: 1px solid #CBD5E1;">
    <div class="d-flex justify-between align-center flex-wrap gap-2">
        <!-- Sheet KPI Metrics -->
        <div class="d-flex align-center flex-wrap gap-3" style="font-size: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #2563EB;"></span>
                <span style="color: #64748B;">Total Records:</span>
                <strong style="color: #0F172A; font-family: monospace; font-size: 13px;">{{ $bookings->total() }}</strong>
            </div>
            <div style="border-left: 1px solid #E2E8F0; padding-left: 0.75rem; display: flex; align-items: center; gap: 6px;">
                <span style="color: #64748B;">Page Items:</span>
                <strong style="color: #0F172A; font-family: monospace;">{{ $bookings->count() }}</strong>
            </div>
            <div style="border-left: 1px solid #E2E8F0; padding-left: 0.75rem; display: flex; align-items: center; gap: 6px;">
                <span style="color: #D97706;">⚠️ Unassigned Fleet/Driver:</span>
                <strong style="color: #B45309; font-family: monospace;">
                    {{ $bookings->filter(fn($b) => !$b->vehicle_id || !$b->driver_id)->count() }}
                </strong>
            </div>
            <div style="border-left: 1px solid #E2E8F0; padding-left: 0.75rem; display: flex; align-items: center; gap: 6px;">
                <span style="color: #64748B;">Balance Due:</span>
                <strong style="color: #DC2626; font-family: monospace;">
                    ₹{{ number_format($bookings->sum('balance_amount'), 2) }}
                </strong>
            </div>
        </div>

        <!-- Sheet Actions (Export CSV & Quick Print) -->
        <div class="d-flex align-center gap-2">
            <button type="button" onclick="exportBookingsToCSV()" class="btn btn-outline btn-sm" style="font-size: 11.5px; height: 30px; padding: 0 0.65rem; border-color: #CBD5E1; color: #1E293B; background: #F8FAFC;" title="Download current view as CSV spreadsheet">
                📥 Export Sheet (.csv)
            </button>
            <button type="button" onclick="window.print()" class="btn btn-outline btn-sm" style="font-size: 11.5px; height: 30px; padding: 0 0.65rem; border-color: #CBD5E1; color: #1E293B; background: #F8FAFC;" title="Print this bookings table">
                🖨️ Print
            </button>
        </div>
    </div>
</div>

<!-- Bookings Compact Excel Sheet Table -->
<div class="card" style="border: 1px solid #CBD5E1; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="table-responsive" style="max-height: 72vh; overflow-y: auto;">
        <table class="excel-sheet-table" id="bookingsExcelTable">
            <thead>
                <tr>
                    <th style="min-width: 85px; text-align: center;">Actions</th>
                    <th style="min-width: 105px;">Booking ID</th>
                    <th style="min-width: 130px;">Passenger</th>
                    <th style="min-width: 100px;">Mobile</th>
                    <th style="min-width: 120px;">Pickup Point</th>
                    <th style="min-width: 120px;">Destination</th>
                    <th style="min-width: 95px;">Date</th>
                    <th style="min-width: 75px;">Time</th>
                    <th style="min-width: 120px;">Fleet Vehicle</th>
                    <th style="min-width: 120px;">Chauffeur</th>
                    <th style="min-width: 85px; text-align: right;">Total (₹)</th>
                    <th style="min-width: 80px; text-align: right;">Paid (₹)</th>
                    <th style="min-width: 85px; text-align: right;">Balance (₹)</th>
                    <th style="min-width: 80px; text-align: center;">Pay Status</th>
                    <th style="min-width: 100px; text-align: center;">Booking Status</th>
                    <th style="min-width: 90px;">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    @php
                        $canAssign = !in_array($booking->booking_status, ['Completed', 'Cancelled']);
                    @endphp
                    <tr>
                        <!-- First Column: Action Dropdown Menu -->
                        <td style="text-align: center; position: relative;">
                            <div class="booking-action-dropdown">
                                <button type="button" class="btn-action-trigger" onclick="toggleBookingDropdown(event, 'actionMenu{{ $booking->id }}')" title="Booking Actions">
                                    <span>Actions</span>
                                    <span class="dropdown-arrow">▾</span>
                                </button>
                                <div id="actionMenu{{ $booking->id }}" class="booking-dropdown-menu">
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="dropdown-item">
                                        View Details
                                    </a>
                                    <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="dropdown-item">
                                        Edit Booking
                                    </a>

                                    @if($booking->booking_status === 'Pending')
                                        <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="dropdown-item" style="color: #059669; font-weight: 600;">
                                                Confirm Booking
                                            </button>
                                        </form>
                                    @endif

                                    @if($canAssign)
                                        <button type="button" class="dropdown-item" onclick="openAssignModal('{{ $booking->id }}', 'vehicle'); closeAllBookingDropdowns();">
                                            Dispatch / Assign
                                        </button>
                                    @endif

                                    <button type="button" class="dropdown-item" onclick="openModal('amountModal{{ $booking->id }}'); closeAllBookingDropdowns();">
                                        Update Fare (₹)
                                    </button>

                                    @if($booking->balance_amount > 0)
                                        <button type="button" class="dropdown-item" onclick="openModal('paymentModal{{ $booking->id }}'); closeAllBookingDropdowns();" style="color: #059669;">
                                            Record Payment
                                        </button>
                                    @endif

                                    @if(!in_array($booking->booking_status, ['Completed', 'Cancelled']))
                                        <button type="button" class="dropdown-item" onclick="openModal('statusModal{{ $booking->id }}'); closeAllBookingDropdowns();">
                                            Update Status
                                        </button>
                                    @endif

                                    <a href="{{ route('invoice.show', $booking->id) }}" class="dropdown-item" target="_blank">
                                        Print Tax Invoice
                                    </a>

                                    @if(!in_array($booking->booking_status, ['Completed', 'Cancelled']))
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item" onclick="openModal('cancelModal{{ $booking->id }}'); closeAllBookingDropdowns();" style="color: #DC2626;">
                                            Cancel Booking
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Booking ID -->
                        <td>
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" style="font-weight: 700; color: #2563EB; font-family: ui-monospace, monospace; text-decoration: none;">
                                {{ $booking->booking_id }}
                            </a>
                            @if($booking->cancellation_status === 'Requested' && $booking->booking_status !== 'Cancelled')
                                <span class="excel-badge excel-badge-danger" style="margin-left: 3px;">
                                    Cancel Req
                                </span>
                            @endif
                        </td>

                        <!-- Customer -->
                        <td style="font-weight: 600; color: #1E293B;">
                            <div style="max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $booking->customer->name ?? 'Guest User' }}">
                                {{ $booking->customer->name ?? 'Guest User' }}
                            </div>
                        </td>

                        <!-- Mobile -->
                        <td style="font-family: ui-monospace, monospace; color: #475569;">
                            {{ $booking->customer->phone ?? 'N/A' }}
                        </td>

                        <!-- Pickup -->
                        <td>
                            <div style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $booking->pickup_location }}">
                                {{ $booking->pickup_location }}
                            </div>
                        </td>

                        <!-- Destination -->
                        <td>
                            <div style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $booking->destination }}">
                                {{ $booking->destination }}
                            </div>
                        </td>

                        <!-- Date -->
                        <td style="font-family: ui-monospace, monospace; font-size: 11.5px;">
                            {{ $booking->travel_date ? $booking->travel_date->format('d/m/Y') : '-' }}
                        </td>

                        <!-- Time -->
                        <td style="font-family: ui-monospace, monospace; font-size: 11.5px; color: #475569;">
                            {{ $booking->travel_time ? date('h:i A', strtotime($booking->travel_time)) : '-' }}
                        </td>

                        <!-- Vehicle Cell -->
                        <td>
                            @if($canAssign)
                                <button type="button" 
                                        onclick="openAssignModal('{{ $booking->id }}', 'vehicle')" 
                                        class="btn-cell-assign {{ $booking->vehicle ? 'is-assigned' : 'is-unassigned' }}" 
                                        title="{{ $booking->vehicle ? 'Allocated: ' . $booking->vehicle->name . ' (' . $booking->vehicle->registration_number . ') — Click to change' : 'Click to allocate vehicle' }}">
                                    @if($booking->vehicle)
                                        <span class="cell-assigned-name">{{ $booking->vehicle->name }}</span>
                                        <span class="cell-edit-badge" title="Change">✎</span>
                                    @else
                                        <span class="unassigned-text">Unassigned</span>
                                    @endif
                                </button>
                            @else
                                @if($booking->vehicle)
                                    <span style="font-size: 11.5px;" title="{{ $booking->vehicle->registration_number }}">{{ $booking->vehicle->name }}</span>
                                @else
                                    <span style="color: #94A3B8; font-size: 11px;">Unassigned</span>
                                @endif
                            @endif
                        </td>

                        <!-- Driver Cell -->
                        <td>
                            @if($canAssign)
                                <button type="button" 
                                        onclick="openAssignModal('{{ $booking->id }}', 'driver')" 
                                        class="btn-cell-assign {{ $booking->driver ? 'is-assigned' : 'is-unassigned' }}" 
                                        title="{{ $booking->driver ? 'Assigned: ' . $booking->driver->name . ' (📞 ' . $booking->driver->mobile . ') — Click to change' : 'Click to assign driver' }}">
                                    @if($booking->driver)
                                        <span class="cell-assigned-name">{{ $booking->driver->name }}</span>
                                        <span class="cell-edit-badge" title="Change">✎</span>
                                    @else
                                        <span class="unassigned-text">Unassigned</span>
                                    @endif
                                </button>
                            @else
                                @if($booking->driver)
                                    <span style="font-size: 11.5px;">{{ $booking->driver->name }}</span>
                                @else
                                    <span style="color: #94A3B8; font-size: 11px;">Unassigned</span>
                                @endif
                            @endif
                        </td>

                        <!-- Total Amount -->
                        <td style="text-align: right; font-weight: 700; font-family: ui-monospace, monospace; color: #0F172A;">
                            ₹{{ number_format($booking->total_amount, 2) }}
                        </td>

                        <!-- Paid Amount -->
                        <td style="text-align: right; font-weight: 600; font-family: ui-monospace, monospace; color: #059669;">
                            ₹{{ number_format($booking->paid_amount, 2) }}
                        </td>

                        <!-- Balance Amount -->
                        <td style="text-align: right; font-weight: 700; font-family: ui-monospace, monospace; color: {{ $booking->balance_amount > 0 ? '#DC2626' : '#059669' }};">
                            ₹{{ number_format($booking->balance_amount, 2) }}
                        </td>

                        <!-- Payment Status -->
                        <td style="text-align: center;">
                            <span class="excel-badge {{ $booking->payment_status === 'Paid' ? 'excel-badge-success' : ($booking->payment_status === 'Partial' ? 'excel-badge-warning' : 'excel-badge-secondary') }}">
                                {{ $booking->payment_status }}
                            </span>
                        </td>

                        <!-- Booking Status -->
                        <td style="text-align: center;">
                            <span class="excel-badge 
                                {{ $booking->booking_status === 'Completed' ? 'excel-badge-success' : '' }}
                                {{ in_array($booking->booking_status, ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned']) ? 'excel-badge-primary' : '' }}
                                {{ in_array($booking->booking_status, ['Trip Started', 'On The Way']) ? 'excel-badge-warning' : '' }}
                                {{ $booking->booking_status === 'Cancelled' ? 'excel-badge-danger' : '' }}
                            ">
                                {{ $booking->booking_status }}
                            </span>
                        </td>

                        <!-- Created At -->
                        <td style="font-size: 10.5px; color: #64748B; font-family: ui-monospace, monospace;">
                            {{ $booking->created_at->format('d/m H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" style="text-align: center; padding: 2.5rem 1rem; color: #64748B;">
                            <strong>No bookings found matching current filters.</strong>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
        <div style="padding: 0.85rem 1.25rem; border-top: 1px solid #CBD5E1; background: #F8FAFC;">
            {{ $bookings->links() }}
        </div>
    @endif
</div>

<!-- =========================================================================
     MODALS CONTAINER (Clean W3C Valid placement outside the table)
     ========================================================================= -->
@foreach($bookings as $booking)
    <!-- Modal: Allocate Vehicle & Chauffeur Directly -->
    <div id="assignModal{{ $booking->id }}" class="custom-modal" style="display: none;">
        <div class="custom-modal-content" style="max-width: 560px;">
            <div class="d-flex justify-between align-center mb-3 pb-2" style="border-bottom: 1px solid var(--slate-200);">
                <div class="d-flex align-center gap-2">
                    <div style="background: #EFF6FF; color: #1D4ED8; width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 18px;">
                        🚖
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0; color: var(--dark-900);">
                            Allocate Fleet Vehicle &amp; Chauffeur
                        </h3>
                        <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 1px;">
                            Booking <strong style="font-family: monospace; color: var(--primary);">#{{ $booking->booking_id }}</strong> &bull; Status: <span class="badge badge-sm badge-primary">{{ $booking->booking_status }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeModal('assignModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--slate-400);">✕</button>
            </div>

            <!-- Trip Summary Info Box -->
            <div style="background: #F8FAFC; border: 1px solid var(--slate-200); border-radius: 6px; padding: 0.75rem; margin-bottom: 1rem; font-size: 0.8rem; line-height: 1.45;">
                <div class="grid grid-2 gap-2 mb-1">
                    <div>
                        <span style="color: var(--slate-500);">Passenger:</span> 
                        <strong>{{ $booking->customer->name ?? 'Guest User' }}</strong>
                        @if(!empty($booking->customer?->phone))
                            <span style="color: var(--slate-600); font-size: 0.75rem;">(📞 {{ $booking->customer->phone }})</span>
                        @endif
                    </div>
                    <div>
                        <span style="color: var(--slate-500);">Vehicle Category:</span> 
                        <strong>{{ $booking->effective_vehicle_category ?: 'Standard' }}</strong>
                    </div>
                </div>
                <div class="grid grid-2 gap-2">
                    <div>
                        <span style="color: var(--slate-500);">Route:</span> 
                        <strong>{{ $booking->pickup_location }} ➔ {{ $booking->destination }}</strong>
                    </div>
                    <div>
                        <span style="color: var(--slate-500);">Travel Schedule:</span> 
                        <strong>{{ $booking->travel_date ? $booking->travel_date->format('d M, Y') : '' }} ({{ date('h:i A', strtotime($booking->travel_time)) }})</strong>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.bookings.assign', $booking->id) }}" method="POST">
                @csrf

                <!-- 1. Vehicle Selection -->
                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem; display: flex; align-items: center; justify-content: space-between;">
                        <span style="display: flex; align-items: center; gap: 5px;">
                            <x-icon name="car-front" size="14" class="text-primary" />
                            <span>1. Fleet Vehicle Allocation</span>
                        </span>
                        @if($booking->vehicle)
                            <span style="color: #059669; font-weight: 600; font-size: 0.75rem;">
                                ✓ Assigned: {{ $booking->vehicle->name }}
                            </span>
                        @else
                            <span style="color: #dc2626; font-weight: 600; font-size: 0.75rem;">
                                ⚠️ Currently Unassigned
                            </span>
                        @endif
                    </label>
                    <select name="vehicle_id" id="assignVehicleSelect{{ $booking->id }}" class="form-select" style="font-size: 0.85rem;">
                        <option value="">-- {{ $booking->vehicle ? 'Keep Assigned: ' . $booking->vehicle->name . ' (' . $booking->vehicle->registration_number . ')' : 'Choose Available Vehicle' }} --</option>
                        @if($booking->vehicle)
                            <option value="{{ $booking->vehicle->id }}" selected>
                                ★ Current: {{ $booking->vehicle->name }} ({{ $booking->vehicle->registration_number }}) - [{{ $booking->vehicle->status }}]
                            </option>
                        @endif
                        <optgroup label="🏢 Available Own Fleet">
                            @foreach($availableVehicles->where('is_third_party', false) as $v)
                                @if(!$booking->vehicle || $v->id !== $booking->vehicle_id)
                                    <option value="{{ $v->id }}">
                                        {{ $v->name }} ({{ $v->registration_number }}) - {{ $v->vehicle_type }} ({{ $v->seating_capacity }} Seater) [{{ $v->status }}]
                                    </option>
                                @endif
                            @endforeach
                        </optgroup>
                        @if($availableVehicles->where('is_third_party', true)->isNotEmpty())
                            <optgroup label="🤝 Available Third-Party Partner Vehicles (No docs required)">
                                @foreach($availableVehicles->where('is_third_party', true) as $v)
                                    @if(!$booking->vehicle || $v->id !== $booking->vehicle_id)
                                        <option value="{{ $v->id }}">
                                            {{ $v->registration_number }} - {{ $v->name }} (Partner: {{ $v->vendor_name ?: 'Partner' }})
                                        </option>
                                    @endif
                                @endforeach
                            </optgroup>
                        @endif
                        @if($allVehicles->where('status', '!=', 'Available')->where('id', '!=', $booking->vehicle_id)->isNotEmpty())
                            <optgroup label="Other Fleet (Busy / Inactive / In Maintenance)">
                                @foreach($allVehicles->where('status', '!=', 'Available')->where('id', '!=', $booking->vehicle_id) as $v)
                                    <option value="{{ $v->id }}" disabled>
                                        {{ $v->name }} ({{ $v->registration_number }}) - [{{ $v->status }}]
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <div style="font-size: 0.725rem; color: var(--slate-500); margin-top: 3px;">
                        Own fleet cars require valid documents; Third-party travel partner vehicles require only vehicle number.
                    </div>
                </div>

                <!-- 2. Driver Selection -->
                <div class="form-group mb-3">
                    <div class="d-flex justify-between align-center mb-1">
                        <label class="form-label mb-0" style="font-weight: 700; font-size: 0.825rem; display: flex; align-items: center; gap: 5px;">
                            <x-icon name="user" size="14" class="text-primary" />
                            <span>2. Chauffeur / Driver Assignment</span>
                        </label>
                        <div class="d-flex align-center gap-2">
                            @if($booking->driver)
                                <span style="color: #059669; font-weight: 600; font-size: 0.75rem;">
                                    ✓ Assigned: {{ $booking->driver->name }}
                                </span>
                            @else
                                <span style="color: #dc2626; font-weight: 600; font-size: 0.75rem;">
                                    ⚠️ Currently Unassigned
                                </span>
                            @endif
                            <button type="button" onclick="openIndexQuickDriverModal('assignDriverSelect{{ $booking->id }}')" style="background: none; border: none; color: #059669; font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: underline; padding: 0;">
                                + Add New Driver
                            </button>
                        </div>
                    </div>
                    <select name="driver_id" id="assignDriverSelect{{ $booking->id }}" class="form-select driver-select-field" onchange="onIndexDriverSelectChange(this)" style="font-size: 0.85rem;">
                        <option value="">-- {{ $booking->driver ? 'Keep Assigned: ' . $booking->driver->name : 'Choose Available Chauffeur' }} --</option>
                        <option value="new_driver" style="font-weight: 700; color: #059669; background: #ECFDF5;">+ Add New Driver</option>
                        @if($booking->driver)
                            <option value="{{ $booking->driver->id }}" selected>
                                ★ Current: {{ $booking->driver->name }} (📞 {{ $booking->driver->mobile }}) - [{{ $booking->driver->status }}]
                            </option>
                        @endif
                        <optgroup label="Available Chauffeurs">
                            @foreach($availableDrivers as $d)
                                @if(!$booking->driver || $d->id !== $booking->driver_id)
                                    <option value="{{ $d->id }}">
                                        {{ $d->name }} (📞 {{ $d->mobile }}) - Rating: {{ $d->rating }}⭐ [{{ $d->status }}]
                                    </option>
                                @endif
                            @endforeach
                        </optgroup>
                        @if($allDrivers->where('status', '!=', 'Available')->where('id', '!=', $booking->driver_id)->isNotEmpty())
                            <optgroup label="Other Drivers (On Trip / Off Duty)">
                                @foreach($allDrivers->where('status', '!=', 'Available')->where('id', '!=', $booking->driver_id) as $d)
                                    <option value="{{ $d->id }}" disabled>
                                        {{ $d->name }} (📞 {{ $d->mobile }}) - [{{ $d->status }}]
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <div style="font-size: 0.725rem; color: var(--slate-500); margin-top: 3px;">
                        Assigning chauffeur automatically activates the live Trip tracking and journey dispatch updates.
                    </div>
                </div>

                <div class="d-flex justify-between align-center mt-3 pt-3" style="border-top: 1px solid var(--slate-200);">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('assignModal{{ $booking->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 0.45rem 1.4rem;">
                        Confirm Allocation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Assign Vehicle (Separate action fallback) -->
    <div id="vehicleModal{{ $booking->id }}" class="custom-modal" style="display: none;">
        <div class="custom-modal-content">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Assign Vehicle - {{ $booking->booking_id }}</h3>
                <button type="button" onclick="closeModal('vehicleModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('admin.bookings.assign-vehicle', $booking->id) }}" method="POST">
                @csrf
                <p style="font-size: 0.85rem; color: var(--slate-600); margin-bottom: 1rem;">
                    Select an Available vehicle for travel date <strong>{{ $booking->travel_date ? $booking->travel_date->format('d M, Y') : '' }}</strong>:
                </p>
                <div class="form-group mb-3">
                    <label class="form-label">Available Vehicles (No Maintenance / Inactive)</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">-- Choose Available Vehicle --</option>
                        @foreach($availableVehicles as $v)
                            <option value="{{ $v->id }}" {{ $booking->vehicle_id == $v->id ? 'selected' : '' }}>
                                {{ $v->name }} ({{ $v->registration_number }}) - {{ $v->vehicle_type }} [{{ $v->status }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('vehicleModal{{ $booking->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Confirm Vehicle Allocation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Assign Driver (Separate action fallback) -->
    <div id="driverModal{{ $booking->id }}" class="custom-modal" style="display: none;">
        <div class="custom-modal-content">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Assign Chauffeur - {{ $booking->booking_id }}</h3>
                <button type="button" onclick="closeModal('driverModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('admin.bookings.assign-driver', $booking->id) }}" method="POST">
                @csrf
                <p style="font-size: 0.85rem; color: var(--slate-600); margin-bottom: 1rem;">
                    Select an Available driver for travel date <strong>{{ $booking->travel_date ? $booking->travel_date->format('d M, Y') : '' }}</strong> (automatically creates/activates Trip):
                </p>
                <div class="form-group mb-3">
                    <div class="d-flex justify-between align-center mb-1">
                        <label class="form-label mb-0">Available Chauffeurs</label>
                        <button type="button" onclick="openIndexQuickDriverModal('driverSelectSolo{{ $booking->id }}')" style="background: none; border: none; color: #059669; font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: underline; padding: 0;">
                            + Add New Driver
                        </button>
                    </div>
                    <select name="driver_id" id="driverSelectSolo{{ $booking->id }}" class="form-select driver-select-field" onchange="onIndexDriverSelectChange(this)" required>
                        <option value="">-- Choose Available Driver --</option>
                        <option value="new_driver" style="font-weight: 700; color: #059669; background: #ECFDF5;">+ Add New Driver</option>
                        @foreach($availableDrivers as $d)
                            <option value="{{ $d->id }}" {{ $booking->driver_id == $d->id ? 'selected' : '' }}>
                                {{ $d->name }} (📞 {{ $d->mobile }}) - Rating: {{ $d->rating }}⭐ [{{ $d->status }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('driverModal{{ $booking->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Assign Driver &amp; Activate Trip</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Update Amount -->
    <div id="amountModal{{ $booking->id }}" class="custom-modal" style="display: none;">
        <div class="custom-modal-content">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Update Fare Amount - {{ $booking->booking_id }}</h3>
                <button type="button" onclick="closeModal('amountModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('admin.bookings.amount', $booking->id) }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">Total Fare Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="total_amount" class="form-control" value="{{ $booking->total_amount }}" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Paid Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="{{ $booking->paid_amount }}" required>
                    <small style="color: var(--slate-500); font-size: 0.775rem;">Cannot exceed total amount. Balance will automatically be calculated.</small>
                </div>
                <div class="d-flex justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('amountModal{{ $booking->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm">Save Amounts</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Update Payment -->
    <div id="paymentModal{{ $booking->id }}" class="custom-modal" style="display: none;">
        <div class="custom-modal-content">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Record Payment - {{ $booking->booking_id }}</h3>
                <button type="button" onclick="closeModal('paymentModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('admin.bookings.payment', $booking->id) }}" method="POST">
                @csrf
                <div class="mb-2" style="background: var(--slate-100); padding: 0.75rem; border-radius: 6px; font-size: 0.85rem;">
                    Total Fare: <strong>₹{{ number_format($booking->total_amount, 2) }}</strong> |
                    Already Paid: <strong>₹{{ number_format($booking->paid_amount, 2) }}</strong> |
                    Remaining Balance: <strong style="color: #dc2626;">₹{{ number_format($booking->balance_amount, 2) }}</strong>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Payment Amount (₹)</label>
                    <input type="number" step="0.01" min="1" max="{{ $booking->balance_amount }}" name="amount" class="form-control" value="{{ $booking->balance_amount }}" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="Cash">Cash to Driver</option>
                        <option value="UPI / QR">UPI (Google Pay / PhonePe / Paytm)</option>
                        <option value="Card">Credit / Debit Card</option>
                        <option value="Net Banking">Net Banking / NEFT</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Transaction Reference / Receipt #</label>
                    <input type="text" name="transaction_id" class="form-control" placeholder="e.g. UPI Ref # or Cash Receipt">
                </div>
                <div class="d-flex justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('paymentModal{{ $booking->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Log Payment Receipt</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Update Status -->
    <div id="statusModal{{ $booking->id }}" class="custom-modal" style="display: none;">
        <div class="custom-modal-content">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Transition Status - {{ $booking->booking_id }}</h3>
                <button type="button" onclick="closeModal('statusModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('admin.bookings.status', $booking->id) }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">Target Workflow Status</label>
                    <select name="booking_status" class="form-select" required>
                        @foreach(\App\Models\Booking::WORKFLOW_TRANSITIONS[$booking->booking_status] ?? [] as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Remarks / Audit Note</label>
                    <input type="text" name="remarks" class="form-control" placeholder="Reason or milestone notes...">
                </div>
                <div class="d-flex justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('statusModal{{ $booking->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Cancel Booking -->
    <div id="cancelModal{{ $booking->id }}" class="custom-modal" style="display: none;">
        <div class="custom-modal-content">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: #dc2626;">Cancel Booking - {{ $booking->booking_id }}</h3>
                <button type="button" onclick="closeModal('cancelModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST">
                @csrf
                @if($booking->cancellation_reason)
                    <div class="mb-3" style="background: #FEE2E2; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; color: #991B1B;">
                        <strong>Customer's Cancellation Reason:</strong><br>
                        {{ $booking->cancellation_reason }}
                    </div>
                @endif
                <div class="form-group mb-3">
                    <label class="form-label">Cancellation Reason</label>
                    <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="State reason for cancelling booking...">{{ $booking->cancellation_reason }}</textarea>
                </div>
                <p style="font-size: 0.8rem; color: var(--slate-500); margin-bottom: 1rem;">
                    Cancelling this booking will release the allocated vehicle and chauffeur back to Available status. History will be permanently retained.
                </p>
                <div class="d-flex justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('cancelModal{{ $booking->id }}')">Back</button>
                    <button type="submit" class="btn btn-danger btn-sm">Approve / Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<!-- Quick Add Driver Modal (Shared across Index Assign modals) -->
<div id="indexQuickDriverModal" class="custom-modal" style="display: none; z-index: 10005;">
    <div class="custom-modal-content" style="max-width: 480px; padding: 1.5rem;">
        <div class="d-flex justify-between align-center mb-3 pb-2" style="border-bottom: 1px solid #E2E8F0;">
            <div class="d-flex align-center gap-2">
                <div style="background: #ECFDF5; color: #059669; width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
                    <x-icon name="user-check" :size="16" />
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0; color: #0F172A;">
                        Quick Register Driver / Chauffeur
                    </h3>
                    <div style="font-size: 0.75rem; color: #64748B;">Instantly add and assign without leaving this page</div>
                </div>
            </div>
            <button type="button" onclick="closeIndexQuickDriverModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748B;">✕</button>
        </div>

        <form id="indexQuickDriverForm" onsubmit="submitIndexQuickDriver(event)">
            <div class="form-group mb-2">
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Driver Full Name *</label>
                <input type="text" id="idx_quick_driver_name" class="form-control form-control-sm" placeholder="e.g. Ramesh Kumar Verma" required>
            </div>

            <div class="form-group mb-2">
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Mobile Number (10 Digits) *</label>
                <input type="tel" id="idx_quick_driver_mobile" class="form-control form-control-sm phone-input" maxlength="10" placeholder="e.g. 9876543210" required>
            </div>

            <div class="form-group mb-2">
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Driving License # <span style="font-weight: 400; color: #64748B;">(Optional)</span></label>
                <input type="text" id="idx_quick_driver_license" class="form-control form-control-sm" placeholder="e.g. CG-04-2022-1234567 (Auto-generated if empty)">
            </div>

            <div class="form-group mb-3">
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Preferred Car / Notes <span style="font-weight: 400; color: #64748B;">(Optional)</span></label>
                <input type="text" id="idx_quick_driver_notes" class="form-control form-control-sm" placeholder="e.g. Innova Specialist, Raipur Outstation">
            </div>

            <div id="idxQuickDriverError" style="display: none; background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626; padding: 0.5rem; border-radius: 6px; font-size: 0.8rem; margin-bottom: 0.75rem;"></div>

            <div class="d-flex justify-end gap-2 pt-2" style="border-top: 1px solid #E2E8F0;">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeIndexQuickDriverModal()">Cancel</button>
                <button type="submit" id="idxQuickDriverSubmitBtn" class="btn btn-success btn-sm" style="background: #059669; border-color: #059669; font-weight: 700;">
                    ✓ Add &amp; Select Driver
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* -------------------------------------------------------------------------
   EXCEL SHEET STYLING FOR BOOKINGS MANAGEMENT
   Crisp borders, dense rows, monospace numeric alignment, sticky header
   ------------------------------------------------------------------------- */
.excel-sheet-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
}

.excel-sheet-table th {
    background: #F1F5F9;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 6px 8px;
    border: 1px solid #CBD5E1;
    position: sticky;
    top: 0;
    z-index: 5;
    white-space: nowrap;
    user-select: none;
}

.excel-sheet-table td {
    padding: 5px 8px;
    border: 1px solid #E2E8F0;
    vertical-align: middle;
    white-space: nowrap;
    height: 34px;
    font-size: 12px;
}

.excel-sheet-table tbody tr:nth-child(even) {
    background: #F8FAFC;
}

.excel-sheet-table tbody tr:hover {
    background: #EFF6FF !important;
}

/* Excel Badges */
.excel-badge {
    display: inline-block;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: 700;
    border-radius: 3px;
    line-height: 1.25;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.excel-badge-primary { background: #DBEAFE; color: #1D4ED8; border: 1px solid #BFDBFE; }
.excel-badge-success { background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0; }
.excel-badge-warning { background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }
.excel-badge-danger  { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
.excel-badge-secondary { background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; }

/* Booking Action Dropdown (First Column) */
.booking-action-dropdown {
    position: relative;
    display: inline-block;
}
.btn-action-trigger {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 4px;
    height: 25px;
    padding: 0 8px;
    font-size: 11px;
    font-weight: 600;
    color: #1E293B;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.btn-action-trigger:hover {
    background: #EFF6FF;
    border-color: #2563EB;
    color: #1D4ED8;
}
.dropdown-arrow {
    font-size: 9px;
    color: #64748B;
}
.booking-dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 3px;
    min-width: 165px;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 6px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05);
    z-index: 1000;
    padding: 4px 0;
    text-align: left;
}
.booking-dropdown-menu.show {
    display: block;
}
.booking-dropdown-menu .dropdown-item {
    display: block;
    width: 100%;
    padding: 6px 12px;
    font-size: 11.5px;
    font-weight: 500;
    color: #1E293B;
    text-decoration: none;
    background: transparent;
    border: none;
    text-align: left;
    cursor: pointer;
    transition: background 0.1s ease;
    white-space: nowrap;
}
.booking-dropdown-menu .dropdown-item:hover {
    background: #F1F5F9;
    color: #0F172A;
}
.booking-dropdown-menu .dropdown-divider {
    height: 1px;
    background: #E2E8F0;
    margin: 4px 0;
}

/* Quick Assign Cell Buttons */
.btn-cell-assign {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: transparent;
    border: 1px dashed #CBD5E1;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 11px;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s ease-in-out;
    line-height: 1.25;
}
.btn-cell-assign.is-unassigned {
    color: #DC2626;
    background: #FEF2F2;
    border-color: #FCA5A5;
    font-weight: 600;
}
.btn-cell-assign.is-unassigned:hover {
    background: #FEE2E2;
}
.btn-cell-assign.is-assigned {
    color: #0F172A;
    font-weight: 500;
    border-style: solid;
    border-color: #E2E8F0;
    background: #FFFFFF;
}
.btn-cell-assign.is-assigned:hover {
    border-color: #2563EB;
    color: #2563EB;
    background: #EFF6FF;
}
.cell-assigned-name {
    max-width: 105px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cell-edit-badge {
    font-size: 10px;
    color: #64748B;
    margin-left: 2px;
}

.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.65);
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
    max-width: 520px;
    width: 100%;
    padding: 1.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    max-height: 90vh;
    overflow-y: auto;
}
</style>

<script>
// -------------------------------------------------------------------------
// 1. Modal open & close handlers
// -------------------------------------------------------------------------
function openAssignModal(id, target) {
    var modal = document.getElementById('assignModal' + id);
    if (!modal) {
        modal = document.getElementById(target + 'Modal' + id);
    }
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(function() {
            if (target === 'driver') {
                var d = document.getElementById('assignDriverSelect' + id);
                if (d) d.focus();
            } else {
                var v = document.getElementById('assignVehicleSelect' + id);
                if (v) v.focus();
            }
        }, 100);
    }
}
function openModal(id) {
    if (id.startsWith('vehicleModal')) {
        var bId = id.replace('vehicleModal', '');
        openAssignModal(bId, 'vehicle');
        return;
    }
    if (id.startsWith('driverModal')) {
        var bId = id.replace('driverModal', '');
        openAssignModal(bId, 'driver');
        return;
    }
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

// -------------------------------------------------------------------------
// Action Dropdown Toggle Handlers
// -------------------------------------------------------------------------
function toggleBookingDropdown(e, menuId) {
    e.stopPropagation();
    const menu = document.getElementById(menuId);
    if (!menu) return;
    const isShown = menu.classList.contains('show');
    closeAllBookingDropdowns();
    if (!isShown) {
        menu.classList.add('show');
        const rect = menu.getBoundingClientRect();
        if (rect.bottom > window.innerHeight - 20) {
            menu.style.top = 'auto';
            menu.style.bottom = '100%';
            menu.style.marginTop = '0';
            menu.style.marginBottom = '3px';
        } else {
            menu.style.top = '100%';
            menu.style.bottom = 'auto';
            menu.style.marginTop = '3px';
            menu.style.marginBottom = '0';
        }
    }
}

function closeAllBookingDropdowns() {
    document.querySelectorAll('.booking-dropdown-menu.show').forEach(m => m.classList.remove('show'));
}

document.addEventListener('click', closeAllBookingDropdowns);

// -------------------------------------------------------------------------
// 2. Quick Add Driver Modal Handlers (AJAX)
// -------------------------------------------------------------------------
let currentTargetDriverSelectId = null;

function openIndexQuickDriverModal(selectId) {
    currentTargetDriverSelectId = selectId || null;
    const modal = document.getElementById('indexQuickDriverModal');
    const form = document.getElementById('indexQuickDriverForm');
    const errBox = document.getElementById('idxQuickDriverError');
    if (errBox) errBox.style.display = 'none';
    if (form) form.reset();
    if (modal) modal.style.display = 'flex';
    setTimeout(() => {
        const nameInput = document.getElementById('idx_quick_driver_name');
        if (nameInput) nameInput.focus();
    }, 100);
}

function closeIndexQuickDriverModal() {
    const modal = document.getElementById('indexQuickDriverModal');
    if (modal) modal.style.display = 'none';
    // If user cancelled, reset target select if it had 'new_driver' selected
    if (currentTargetDriverSelectId) {
        const select = document.getElementById(currentTargetDriverSelectId);
        if (select && select.value === 'new_driver') {
            select.value = '';
        }
    }
}

function onIndexDriverSelectChange(selectEl) {
    if (!selectEl) return;
    if (selectEl.value === 'new_driver') {
        openIndexQuickDriverModal(selectEl.id);
    }
}

async function submitIndexQuickDriver(e) {
    e.preventDefault();
    const nameInput = document.getElementById('idx_quick_driver_name');
    const mobileInput = document.getElementById('idx_quick_driver_mobile');
    const licenseInput = document.getElementById('idx_quick_driver_license');
    const notesInput = document.getElementById('idx_quick_driver_notes');
    const btn = document.getElementById('idxQuickDriverSubmitBtn');
    const errBox = document.getElementById('idxQuickDriverError');

    const name = nameInput.value.trim();
    const mobile = mobileInput.value.trim().replace(/[^0-9]/g, '');
    const license = licenseInput.value.trim();
    const notes = notesInput ? notesInput.value.trim() : '';

    if (!name || mobile.length !== 10) {
        if (errBox) {
            errBox.textContent = 'Please enter a valid driver name and 10-digit mobile number.';
            errBox.style.display = 'block';
        }
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Saving Driver...';
    }
    if (errBox) errBox.style.display = 'none';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('{{ route("admin.drivers.quick") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({
                name: name,
                mobile: mobile,
                license_number: license,
                notes: notes
            })
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Error saving new driver. Please check inputs.');
        }

        const driver = data.driver;

        // Append this new driver option to ALL driver selects on this page
        document.querySelectorAll('select.driver-select-field, select[name="driver_id"]').forEach(sel => {
            const opt = document.createElement('option');
            opt.value = driver.id;
            opt.textContent = `${driver.name} (📞 ${driver.mobile}) - ★ Available`;
            // Insert right after the 'new_driver' option if present
            const newDriverOpt = sel.querySelector('option[value="new_driver"]');
            if (newDriverOpt && newDriverOpt.nextSibling) {
                sel.insertBefore(opt, newDriverOpt.nextSibling);
            } else {
                sel.appendChild(opt);
            }
        });

        // Set the newly created driver on the active target select
        if (currentTargetDriverSelectId) {
            const targetSelect = document.getElementById(currentTargetDriverSelectId);
            if (targetSelect) {
                targetSelect.value = driver.id;
            }
        }

        closeIndexQuickDriverModal();
        alert(`✓ Driver "${driver.name}" successfully registered and selected!`);
    } catch (err) {
        if (errBox) {
            errBox.textContent = err.message;
            errBox.style.display = 'block';
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = '✓ Add & Select Driver';
        }
    }
}

// -------------------------------------------------------------------------
// 3. Export Excel Sheet Table to CSV (Client-side, Instant)
// -------------------------------------------------------------------------
function exportBookingsToCSV() {
    const table = document.getElementById('bookingsExcelTable');
    if (!table) return;

    const rows = [];
    // Process Table Header (Skip Column 0: Actions)
    const headers = [];
    table.querySelectorAll('thead th').forEach((th, idx) => {
        if (idx >= 1) {
            headers.push('"' + th.innerText.replace(/"/g, '""').trim() + '"');
        }
    });
    rows.push(headers.join(','));

    // Process Table Rows (Skip Column 0: Actions)
    table.querySelectorAll('tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length > 1) {
            const rowData = [];
            for (let i = 1; i < cells.length; i++) {
                let cellText = cells[i].innerText.replace(/"/g, '""').replace(/\r?\n/g, ' ').trim();
                rowData.push('"' + cellText + '"');
            }
            rows.push(rowData.join(','));
        }
    });

    const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + rows.join('\r\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    const today = new Date().toISOString().slice(0, 10);
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `Vaishnavi_Tours_Cab_Bookings_${today}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
