@extends('layouts.admin')

@section('title', 'Booking ' . $booking->booking_id . ' - Vaishnavi Tours')
@section('page_title', 'Booking Details')

@section('content')
<!-- Header: Booking VT-XXXX -->
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('admin.bookings.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600; text-decoration: none;">
            ← Back to Bookings List
        </a>
        <div class="d-flex align-center gap-2 mt-1">
            <h1 style="font-size: 1.85rem; font-weight: 900; margin: 0; color: var(--dark-950);">
                Booking {{ $booking->booking_id }}
            </h1>
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

    <div class="d-flex align-center gap-2 flex-wrap">
        <!-- Confirm Button if Pending -->
        @if($booking->booking_status === 'Pending')
            <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" style="background: #059669; color: #fff;">
                    <x-icon name="circle-check" size="16" style="margin-right: 6px;" /> Confirm Booking
                </button>
            </form>
        @endif

        <!-- Invoice -->
        <a href="{{ route('invoice.show', $booking->id) }}" class="btn btn-outline btn-sm" target="_blank">
            <x-icon name="printer" size="16" style="margin-right: 6px;" /> Print / View Tax Invoice
        </a>

        <!-- Edit -->
        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-outline btn-sm">
            <x-icon name="edit" size="16" style="margin-right: 6px;" /> Edit Booking
        </a>

        <!-- Delete -->
        <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this booking?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash-2" size="14" style="margin-right: 4px;" /> Delete</button>
        </form>
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
        <strong style="display: flex; align-items: center; gap: 6px;"><x-icon name="alert-circle" size="18" /><span>Please address the following issues:</span></strong>
        <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Pending Cancellation Request Banner if Customer Requested -->
@if($booking->cancellation_status === 'Requested' && $booking->booking_status !== 'Cancelled')
    <div class="alert alert-danger mb-4" style="border-left: 5px solid #dc2626; background: #FFF5F5;">
        <div class="d-flex justify-between align-center flex-wrap gap-2">
            <div>
                <strong style="font-size: 1.05rem; color: #991B1B;">⚠️ Customer Cancellation Request Pending Approval</strong>
                <p style="margin: 0.25rem 0 0 0; color: #7F1D1D; font-size: 0.9rem;">
                    Reason: <strong>{{ $booking->cancellation_reason ?: 'No specific reason provided' }}</strong>
                </p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Approve customer cancellation request?');">
                    @csrf
                    <input type="hidden" name="cancellation_reason" value="{{ $booking->cancellation_reason ?: 'Customer requested cancellation approved by admin.' }}">
                    <button type="submit" class="btn btn-danger btn-sm">Approve Cancellation</button>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Main Grid -->
<div class="grid grid-3 gap-4">
    <!-- Left 2 Columns -->
    <div style="grid-column: span 2;">
        <!-- 1. Customer Information & Trip Information -->
        <div class="grid grid-2 gap-3 mb-4">
            <!-- Customer Information Card -->
            <div class="card">
                <div class="d-flex justify-between align-center mb-3">
                    <h3 style="font-size: 1rem; font-weight: 800; color: var(--dark-950); margin: 0;">
                        <x-icon name="user" size="20" class="text-primary" style="margin-right: 6px;" /> Customer Information
                    </h3>
                    <span class="badge badge-outline" style="font-size: 0.75rem;">Account</span>
                </div>
                <div style="font-size: 0.925rem; line-height: 1.6;">
                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Name:</span></div>
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--dark-950); margin-bottom: 0.5rem;">
                        {{ $booking->customer->name ?? 'Guest User' }}
                    </div>

                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Mobile:</span></div>
                    <div style="font-weight: 700; color: var(--dark-900); margin-bottom: 0.5rem;">
                        <x-icon name="phone" size="14" style="margin-right: 4px;" /> {{ $booking->customer->phone ?? 'N/A' }}
                    </div>

                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Email:</span></div>
                    <div style="font-weight: 600; color: var(--slate-600);">
                        <x-icon name="mail" size="14" style="margin-right: 4px;" /> {{ $booking->customer->email ?? 'N/A' }}
                    </div>

                    <!-- Read-Only Legal Acceptance Record -->
                    <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--slate-200); font-size: 0.825rem;">
                        <div class="d-flex justify-between align-center mb-1">
                            <span style="color: var(--slate-500); font-weight: 700; text-transform: uppercase; font-size: 0.75rem;">Terms Accepted:</span>
                            @if($booking->terms_accepted)
                                <span class="badge badge-sm badge-success"><x-icon name="circle-check" size="14" style="margin-right: 4px;" /> Yes (v{{ $booking->terms_version ?? '1.0' }})</span>
                            @else
                                <span class="badge badge-sm" style="background: var(--slate-200); color: var(--slate-600);">Recorded with Booking</span>
                            @endif
                        </div>
                        <div class="d-flex justify-between align-center mb-1">
                            <span style="color: var(--slate-500); font-size: 0.75rem;">Agreed At:</span>
                            <span style="font-weight: 600; color: var(--dark-900);">
                                {{ $booking->terms_accepted_at ? $booking->terms_accepted_at->format('d M Y, h:i A') : ($booking->created_at ? $booking->created_at->format('d M Y, h:i A') : 'N/A') }}
                            </span>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--slate-400); margin-top: 0.25rem;">
                            <x-icon name="lock" size="14" style="margin-right: 4px;" /> Read-only legal record agreed at booking creation.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trip Information Card -->
            <div class="card">
                <div class="d-flex justify-between align-center mb-3">
                    <h3 style="font-size: 1rem; font-weight: 800; color: var(--dark-950); margin: 0;">
                        <x-icon name="route" size="20" class="text-primary" style="margin-right: 6px;" /> Trip Information
                    </h3>
                    <span class="badge" style="background: var(--slate-200); color: var(--dark-800); font-size: 0.75rem;">
                        {{ $booking->trip_type }}
                    </span>
                </div>
                <div style="font-size: 0.925rem; line-height: 1.6;">
                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Pickup Location:</span></div>
                    <div style="font-weight: 800; color: var(--dark-950); margin-bottom: 0.5rem;">
                        <x-icon name="map-pin" size="16" class="text-primary" style="margin-right: 4px;" /> {{ $booking->pickup_location }}
                    </div>

                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Destination:</span></div>
                    <div style="font-weight: 800; color: var(--dark-950); margin-bottom: 0.5rem;">
                        <x-icon name="map-pinned" size="16" class="text-primary" style="margin-right: 4px;" /> {{ $booking->destination }}
                    </div>

                    <div class="grid grid-2 gap-2 mt-2 pt-2" style="border-top: 1px dashed var(--slate-200);">
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Date:</span>
                            <div style="font-weight: 700;">{{ $booking->travel_date->format('d M, Y') }}</div>
                        </div>
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Time:</span>
                            <div style="font-weight: 700;">{{ date('h:i A', strtotime($booking->travel_time)) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Vehicle Information & Driver Information -->
        <div class="grid grid-2 gap-3 mb-4">
            <!-- Vehicle Information Card -->
            <div class="card" style="border-top: 3px solid var(--primary);">
                <div class="d-flex justify-between align-center mb-3">
                    <h3 style="font-size: 1rem; font-weight: 800; color: var(--dark-950); margin: 0;">
                        <x-icon name="car-front" size="20" class="text-primary" style="margin-right: 6px;" /> Vehicle Information
                    </h3>
                    @if($booking->vehicle)
                        <span class="badge badge-success">{{ $booking->vehicle->status }}</span>
                    @else
                        <span class="badge badge-danger">Not Assigned</span>
                    @endif
                </div>

                @if($booking->vehicle)
                    <div style="font-size: 0.925rem; line-height: 1.6;">
                        <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Vehicle:</span></div>
                        <div style="font-weight: 800; font-size: 1.05rem; color: var(--dark-950); margin-bottom: 0.5rem;">
                            {{ $booking->vehicle->name }} ({{ $booking->vehicle->vehicle_type }})
                        </div>

                        <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Registration:</span></div>
                        <div style="font-weight: 800; font-family: monospace; color: var(--dark-900); margin-bottom: 0.5rem;">
                            {{ $booking->vehicle->registration_number }}
                        </div>

                        <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Seats:</span></div>
                        <div style="font-weight: 700; color: var(--slate-700);">
                            {{ $booking->vehicle->seating_capacity }} Seater ({{ $booking->vehicle->ac_non_ac }})
                        </div>
                    </div>
                @else
                    <p style="color: #dc2626; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem;">
                        ⚠️ No vehicle allocated yet. Select an available fleet car below.
                    </p>
                @endif

                <!-- Assign Vehicle Form (Available only, overlap checked) -->
                @if(!in_array($booking->booking_status, ['Trip Started', 'On The Way', 'Completed', 'Cancelled']))
                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--slate-200);">
                        <form action="{{ route('admin.bookings.assign-vehicle', $booking->id) }}" method="POST">
                            @csrf
                            <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Assign Vehicle (Available Only)</label>
                            <div class="d-flex gap-2">
                                <select name="vehicle_id" class="form-select form-select-sm" required style="font-size: 0.85rem;">
                                    <option value="">-- Select Available Vehicle --</option>
                                    @foreach($availableVehicles as $v)
                                        <option value="{{ $v->id }}" {{ $booking->vehicle_id == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }} ({{ $v->registration_number }}) - {{ $v->status }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm" style="white-space: nowrap;">
                                    Assign Vehicle
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Driver Information Card -->
            <div class="card" style="border-top: 3px solid var(--dark-900);">
                <div class="d-flex justify-between align-center mb-3">
                    <h3 style="font-size: 1rem; font-weight: 800; color: var(--dark-950); margin: 0;">
                        <x-icon name="award" size="20" class="text-primary" style="margin-right: 6px;" /> Driver Information
                    </h3>
                    @if($booking->driver)
                        <span class="badge badge-success">{{ $booking->driver->status }}</span>
                    @else
                        <span class="badge badge-danger">Not Assigned</span>
                    @endif
                </div>

                @if($booking->driver)
                    <div style="font-size: 0.925rem; line-height: 1.6;">
                        <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Driver:</span></div>
                        <div style="font-weight: 800; font-size: 1.05rem; color: var(--dark-950); margin-bottom: 0.5rem;">
                            {{ $booking->driver->name }}
                        </div>

                        <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Mobile:</span></div>
                        <div style="font-weight: 700; color: var(--dark-900); margin-bottom: 0.5rem;">
                            📞 {{ $booking->driver->mobile }}
                        </div>

                        <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">License / Rating:</span></div>
                        <div style="font-weight: 600; color: var(--slate-700);">
                            Lic: {{ $booking->driver->license_number }} (⭐ {{ $booking->driver->rating }})
                        </div>
                    </div>
                @else
                    <p style="color: #dc2626; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem;">
                        ⚠️ No chauffeur assigned yet. Select an available chauffeur below.
                    </p>
                @endif

                <!-- Assign Driver Form (Available only, activates Trip) -->
                @if(!in_array($booking->booking_status, ['Trip Started', 'On The Way', 'Completed', 'Cancelled']))
                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--slate-200);">
                        <form action="{{ route('admin.bookings.assign-driver', $booking->id) }}" method="POST">
                            @csrf
                            <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Assign Driver (Available Only)</label>
                            <div class="d-flex gap-2">
                                <select name="driver_id" class="form-select form-select-sm" required style="font-size: 0.85rem;">
                                    <option value="">-- Select Available Driver --</option>
                                    @foreach($availableDrivers as $d)
                                        <option value="{{ $d->id }}" {{ $booking->driver_id == $d->id ? 'selected' : '' }}>
                                            {{ $d->name }} ({{ $d->mobile }}) - {{ $d->status }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-dark btn-sm" style="white-space: nowrap;">
                                    Assign Driver
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- 3. Controlled Status Workflow -->
        <div class="card mb-4" style="border-top: 4px solid var(--primary);">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--dark-950); margin: 0;">
                    <x-icon name="zap" size="20" class="text-primary" style="margin-right: 6px;" /> Controlled Status Workflow
                </h3>
                <span class="badge badge-lg badge-primary">
                    Current: {{ $booking->booking_status }}
                </span>
            </div>

            @if(in_array($booking->booking_status, ['Completed', 'Cancelled']))
                <div class="alert alert-info" style="margin: 0;">
                    This booking has reached its final state: <strong>{{ $booking->booking_status }}</strong>. No further status changes are permitted.
                    @if($booking->isCancelled() && $booking->cancelled_at)
                        <div style="font-size: 0.85rem; margin-top: 0.35rem;">
                            Cancelled on {{ $booking->cancelled_at->format('d M, Y h:i A') }} by {{ $booking->cancelledBy->name ?? 'Administrator' }}. Reason: {{ $booking->cancellation_reason }}
                        </div>
                    @endif
                </div>
            @else
                <form action="{{ route('admin.bookings.status', $booking->id) }}" method="POST" class="grid grid-3 gap-2 align-end">
                    @csrf
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Allowed Next Workflow Status</label>
                        <select name="booking_status" class="form-select" required>
                            @foreach(\App\Models\Booking::WORKFLOW_TRANSITIONS[$booking->booking_status] ?? [] as $st)
                                <option value="{{ $st }}">Advance to: {{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Remarks / Operational Notes</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Audit remarks...">
                    </div>

                    <div>
                        <button type="submit" class="btn btn-dark w-100" style="height: 42px;">
                            Apply Controlled Transition
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <!-- 4. Status History Table: Section 6 -->
        <div class="card mb-4">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--dark-950); margin-bottom: 1rem;">
                <x-icon name="clipboard-list" size="20" class="text-primary" style="margin-right: 6px;" /> Booking Status History (Audit Trail)
            </h3>

            <div class="table-responsive">
                <table class="table" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Transition</th>
                            <th>Changed By</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->statusHistories as $history)
                            <tr>
                                <td style="white-space: nowrap; color: var(--slate-600); font-weight: 600;">
                                    {{ $history->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($history->old_status)
                                        <span class="badge" style="background: var(--slate-200); color: var(--slate-700);">{{ $history->old_status }}</span>
                                        ➔
                                    @endif
                                    <span class="badge badge-primary">{{ $history->new_status }}</span>
                                </td>
                                <td style="white-space: nowrap; font-weight: 700;">
                                    {{ $history->changer->name ?? 'Administrator' }}
                                </td>
                                <td>
                                    {{ $history->remarks ?: $history->comment }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--slate-500); padding: 1.5rem;">
                                    No status changes recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Special Notes if any -->
        @if($booking->notes)
            <div class="card mb-4" style="background: #FFFDF5; border-left: 4px solid var(--primary);">
                <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--primary-dark); margin-bottom: 0.25rem;">
                    Special Notes from Customer
                </div>
                <div style="color: var(--dark-900); font-size: 0.95rem;">
                    {{ $booking->notes }}
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Financial Information & Quick Actions -->
    <div>
        <!-- Financial Information Card -->
        <div class="card mb-4" style="border-top: 4px solid #10B981;">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--dark-950); margin: 0;">
                    <x-icon name="credit-card" size="20" class="text-primary" style="margin-right: 6px;" /> Financial Information
                </h3>
                <span class="badge badge-lg {{ $booking->payment_status === 'Paid' ? 'badge-paid' : ($booking->payment_status === 'Partial' ? 'badge-warning' : 'badge-pending') }}">
                    {{ $booking->payment_status }}
                </span>
            </div>

            <div style="background: var(--slate-50); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                <div class="d-flex justify-between align-center mb-2" style="font-size: 0.95rem;">
                    <span style="color: var(--slate-600);">Total Amount:</span>
                    <strong style="font-size: 1.2rem; color: var(--dark-950);">₹{{ number_format($booking->total_amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-between align-center mb-2" style="font-size: 0.95rem;">
                    <span style="color: var(--slate-600);">Paid Amount:</span>
                    <strong style="font-size: 1.15rem; color: #059669;">₹{{ number_format($booking->paid_amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-between align-center pt-2" style="border-top: 1.5px solid var(--slate-200); font-size: 1rem;">
                    <span style="font-weight: 800; color: var(--dark-950);">Balance:</span>
                    <strong style="font-size: 1.3rem; color: {{ $booking->balance_amount > 0 ? '#DC2626' : '#16A34A' }};">
                        ₹{{ number_format($booking->balance_amount, 2) }}
                    </strong>
                </div>
            </div>

            <!-- Update Amount Form (Section 16) -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px dashed var(--slate-200);">
                <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.75rem;">
                    <x-icon name="edit" size="16" style="margin-right: 6px;" /> Update Total / Paid Amount
                </h4>
                <form action="{{ route('admin.bookings.amount', $booking->id) }}" method="POST">
                    @csrf
                    <div class="form-group mb-2">
                        <label class="form-label" style="font-size: 0.775rem;">Total Amount (₹)</label>
                        <input type="number" step="0.01" min="0" name="total_amount" class="form-control form-control-sm" value="{{ $booking->total_amount }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label" style="font-size: 0.775rem;">Paid Amount (₹)</label>
                        <input type="number" step="0.01" min="0" name="paid_amount" class="form-control form-control-sm" value="{{ $booking->paid_amount }}" required>
                        <small style="color: var(--slate-500); font-size: 0.725rem;">Balance auto-calculated.</small>
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm w-100">
                        Update Amount
                    </button>
                </form>
            </div>

            <!-- Record Direct Payment Form -->
            @if($booking->balance_amount > 0)
                <div>
                    <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.75rem;">
                        <x-icon name="credit-card" size="16" style="margin-right: 6px;" /> Record Payment
                    </h4>
                    <form action="{{ route('admin.bookings.payment', $booking->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-2">
                            <label class="form-label" style="font-size: 0.775rem;">Amount to Receive (₹)</label>
                            <input type="number" step="0.01" min="1" max="{{ $booking->balance_amount }}" name="amount" class="form-control form-control-sm" value="{{ $booking->balance_amount }}" required>
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label" style="font-size: 0.775rem;">Payment Mode</label>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                <option value="Cash">Cash to Driver</option>
                                <option value="UPI / QR">UPI (PhonePe / GPay / Paytm)</option>
                                <option value="Card">Card</option>
                                <option value="Net Banking">Net Banking</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-size: 0.775rem;">Reference / UTR #</label>
                            <input type="text" name="transaction_id" class="form-control form-control-sm" placeholder="Optional txn ID">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100" style="background: #059669; color: #fff;">
                            Record Payment
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Cancel Booking Action Card -->
        @if(!in_array($booking->booking_status, ['Completed', 'Cancelled']))
            <div class="card" style="border-top: 3px solid #dc2626;">
                <h4 style="font-size: 0.95rem; font-weight: 800; color: #dc2626; margin-bottom: 0.5rem;">
                    <x-icon name="circle-x" size="16" style="margin-right: 6px;" /> Cancel Booking
                </h4>
                <p style="font-size: 0.8rem; color: var(--slate-500); margin-bottom: 1rem;">
                    Cancelling releases any assigned vehicle & driver back to Available.
                </p>
                <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                    @csrf
                    <div class="form-group mb-2">
                        <textarea name="cancellation_reason" class="form-control form-control-sm" rows="2" placeholder="Cancellation reason..." required>{{ $booking->cancellation_reason }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        Confirm Cancellation
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
