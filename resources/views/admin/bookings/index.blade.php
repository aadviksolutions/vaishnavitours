@extends('layouts.admin')

@section('title', 'Bookings Management - Vaishnavi Tours')
@section('page_title', 'Cab Bookings Management')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-950);">Cab Bookings Management</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">
            Central dispatch desk: review reservations, allocate fleet & chauffeurs, track journey milestones.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">+ Create New Booking</a>
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
<div class="card mb-4" style="padding: 1.25rem;">
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

<!-- Bookings Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table" style="font-size: 0.875rem;">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Pickup</th>
                    <th>Destination</th>
                    <th>Travel Date</th>
                    <th>Travel Time</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Total Amount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Payment Status</th>
                    <th>Booking Status</th>
                    <th>Created At</th>
                    <th style="text-align: right; min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td>
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" style="font-weight: 800; color: var(--primary-dark); font-family: monospace;">
                                {{ $booking->booking_id }}
                            </a>
                            @if($booking->cancellation_status === 'Requested' && $booking->booking_status !== 'Cancelled')
                                <span class="badge badge-danger" style="display: block; font-size: 0.65rem; margin-top: 2px;">
                                    Cancel Req.
                                </span>
                            @endif
                        </td>
                        <td style="font-weight: 700; white-space: nowrap;">
                            {{ $booking->customer->name ?? 'Guest User' }}
                        </td>
                        <td style="white-space: nowrap; color: var(--slate-600);">
                            {{ $booking->customer->phone ?? 'N/A' }}
                        </td>
                        <td>
                            <div style="max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $booking->pickup_location }}">
                                {{ $booking->pickup_location }}
                            </div>
                        </td>
                        <td>
                            <div style="max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $booking->destination }}">
                                {{ $booking->destination }}
                            </div>
                        </td>
                        <td style="white-space: nowrap; font-weight: 600;">
                            {{ $booking->travel_date ? $booking->travel_date->format('d M, Y') : '' }}
                        </td>
                        <td style="white-space: nowrap;">
                            {{ date('h:i A', strtotime($booking->travel_time)) }}
                        </td>
                        <td style="white-space: nowrap;">
                            @if($booking->vehicle)
                                <span title="{{ $booking->vehicle->registration_number }}">{{ $booking->vehicle->name }}</span>
                            @else
                                <span style="color: #dc2626; font-size: 0.8rem; font-weight: 600;">Unassigned</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            @if($booking->driver)
                                <span>{{ $booking->driver->name }}</span>
                            @else
                                <span style="color: #dc2626; font-size: 0.8rem; font-weight: 600;">Unassigned</span>
                            @endif
                        </td>
                        <td style="font-weight: 800; white-space: nowrap;">
                            ₹{{ number_format($booking->total_amount, 2) }}
                        </td>
                        <td style="color: #059669; font-weight: 700; white-space: nowrap;">
                            ₹{{ number_format($booking->paid_amount, 2) }}
                        </td>
                        <td style="color: {{ $booking->balance_amount > 0 ? '#dc2626' : '#16a34a' }}; font-weight: 700; white-space: nowrap;">
                            ₹{{ number_format($booking->balance_amount, 2) }}
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="badge badge-sm {{ $booking->payment_status === 'Paid' ? 'badge-paid' : ($booking->payment_status === 'Partial' ? 'badge-warning' : 'badge-pending') }}">
                                {{ $booking->payment_status }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="badge badge-sm
                                {{ $booking->booking_status === 'Completed' ? 'badge-success' : '' }}
                                {{ in_array($booking->booking_status, ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned']) ? 'badge-primary' : '' }}
                                {{ in_array($booking->booking_status, ['Trip Started', 'On The Way']) ? 'badge-warning' : '' }}
                                {{ $booking->booking_status === 'Cancelled' ? 'badge-danger' : '' }}
                            ">
                                {{ $booking->booking_status }}
                            </span>
                        </td>
                        <td style="font-size: 0.75rem; color: var(--slate-500); white-space: nowrap;">
                            {{ $booking->created_at->format('d M, h:i A') }}
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div class="d-flex justify-end gap-1 flex-wrap">
                                <!-- View -->
                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="View Details">
                                    View
                                </a>

                                <!-- Edit -->
                                <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Edit Booking">
                                    Edit
                                </a>

                                <!-- Confirm (If Pending) -->
                                @if($booking->booking_status === 'Pending')
                                    <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; background: #059669; color: #fff;" title="Confirm Booking">
                                            Confirm
                                        </button>
                                    </form>
                                @endif

                                <!-- Assign Vehicle Trigger -->
                                @if(in_array($booking->booking_status, ['Confirmed', 'Vehicle Assigned']) && $availableVehicles->isNotEmpty())
                                    <button type="button" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="openModal('vehicleModal{{ $booking->id }}')" title="Assign Vehicle">
                                        <x-icon name="car-front" size="14" style="margin-right: 4px;" /> Cab
                                    </button>
                                @endif

                                <!-- Assign Driver Trigger -->
                                @if(in_array($booking->booking_status, ['Vehicle Assigned', 'Driver Assigned']) && $availableDrivers->isNotEmpty())
                                    <button type="button" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="openModal('driverModal{{ $booking->id }}')" title="Assign Driver">
                                        <x-icon name="user" size="14" style="margin-right: 4px;" /> Driver
                                    </button>
                                @endif

                                <!-- Update Amount Trigger -->
                                <button type="button" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="openModal('amountModal{{ $booking->id }}')" title="Update Fare Amount">
                                    ₹ Fare
                                </button>

                                <!-- Update Payment Trigger -->
                                @if($booking->balance_amount > 0)
                                    <button type="button" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="openModal('paymentModal{{ $booking->id }}')" title="Record Payment">
                                        <x-icon name="credit-card" size="14" style="margin-right: 4px;" /> Pay
                                    </button>
                                @endif

                                <!-- Update Status Trigger -->
                                @if(!in_array($booking->booking_status, ['Completed', 'Cancelled']))
                                    <button type="button" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="openModal('statusModal{{ $booking->id }}')" title="Update Status">
                                        <x-icon name="zap" size="14" style="margin-right: 4px;" /> Status
                                    </button>
                                @endif

                                <!-- Invoice -->
                                <a href="{{ route('invoice.show', $booking->id) }}" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Tax Invoice">
                                    <x-icon name="receipt" size="14" style="margin-right: 4px;" /> Inv
                                </a>

                                <!-- Cancel Trigger -->
                                @if(!in_array($booking->booking_status, ['Completed', 'Cancelled']))
                                    <button type="button" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="openModal('cancelModal{{ $booking->id }}')" title="Cancel Booking">
                                        ✕
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Modal: Assign Vehicle -->
                    <div id="vehicleModal{{ $booking->id }}" class="custom-modal" style="display: none;">
                        <div class="custom-modal-content">
                            <div class="d-flex justify-between align-center mb-3">
                                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Assign Vehicle - {{ $booking->booking_id }}</h3>
                                <button type="button" onclick="closeModal('vehicleModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
                            </div>
                            <form action="{{ route('admin.bookings.assign-vehicle', $booking->id) }}" method="POST">
                                @csrf
                                <p style="font-size: 0.85rem; color: var(--slate-600); margin-bottom: 1rem;">
                                    Select an Available vehicle for travel date <strong>{{ $booking->travel_date->format('d M, Y') }}</strong>:
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

                    <!-- Modal: Assign Driver -->
                    <div id="driverModal{{ $booking->id }}" class="custom-modal" style="display: none;">
                        <div class="custom-modal-content">
                            <div class="d-flex justify-between align-center mb-3">
                                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Assign Chauffeur - {{ $booking->booking_id }}</h3>
                                <button type="button" onclick="closeModal('driverModal{{ $booking->id }}')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">✕</button>
                            </div>
                            <form action="{{ route('admin.bookings.assign-driver', $booking->id) }}" method="POST">
                                @csrf
                                <p style="font-size: 0.85rem; color: var(--slate-600); margin-bottom: 1rem;">
                                    Select an Available driver for travel date <strong>{{ $booking->travel_date->format('d M, Y') }}</strong> (automatically creates/activates Trip):
                                </p>
                                <div class="form-group mb-3">
                                    <label class="form-label">Available Chauffeurs</label>
                                    <select name="driver_id" class="form-select" required>
                                        <option value="">-- Choose Available Driver --</option>
                                        @foreach($availableDrivers as $d)
                                            <option value="{{ $d->id }}" {{ $booking->driver_id == $d->id ? 'selected' : '' }}>
                                                {{ $d->name }} (📞 {{ $d->mobile }}) - Rating: {{ $d->rating }}⭐ [{{ $d->status }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex justify-end gap-2">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('driverModal{{ $booking->id }}')">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Assign Driver & Activate Trip</button>
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

                @empty
                    <tr>
                        <td colspan="16" style="text-align: center; padding: 3rem 1rem; color: var(--slate-500);">
                            <div class="icon-box icon-box-lg icon-box-primary" style="margin: 0 auto 0.75rem;"><x-icon name="car-front" size="32" /></div>
                            <strong>No cab bookings found matching criteria.</strong>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
        <div style="padding: 1.25rem; border-top: 1px solid var(--slate-200);">
            {{ $bookings->links() }}
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
    max-width: 520px;
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
