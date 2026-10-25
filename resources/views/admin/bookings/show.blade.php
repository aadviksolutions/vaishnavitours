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

        <!-- WhatsApp Direct Message -->
        <button type="button" class="btn btn-sm d-inline-flex align-center"
            style="background:#25D366;color:#fff;border:none;"
            onclick="document.getElementById('wa-direct-modal').style.display='flex'">
            <x-icon name="message-circle" :size="14" style="margin-right: 5px;" /> WhatsApp
        </button>
    </div>
</div>

{{-- WhatsApp Direct Message Modal --}}
<div id="wa-direct-modal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 style="font-weight:800;margin:0;display:flex;align-items:center;gap:6px;">
                <x-icon name="message-circle" :size="16" style="color:#25D366;" /> Send WhatsApp to Customer
            </h5>
            <button onclick="document.getElementById('wa-direct-modal').style.display='none'"
                style="background:none;border:none;font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <p style="font-size:0.83rem;color:var(--slate-500);margin-bottom:12px;">
            Booking: <strong>{{ $booking->booking_id }}</strong> |
            Customer: <strong>{{ $booking->customer?->name }}</strong>
        </p>
        <form method="POST" action="{{ route('admin.whatsapp.send-to-booking', $booking->id) }}">
            @csrf
            <div class="form-group mb-3">
                <label class="form-label fw-semibold">Message</label>
                <textarea name="message" class="form-control" rows="6" required
                    placeholder="Type your WhatsApp message to the customer...">Hello *{{ $booking->customer?->name }}*,

Regarding your booking *{{ $booking->booking_id }}* from {{ $booking->pickup_location }} to {{ $booking->destination }} on {{ \Carbon\Carbon::parse($booking->travel_date)->format('d M Y') }}.

</textarea>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary btn-sm"
                    onclick="document.getElementById('wa-direct-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-sm" style="background:#25D366;color:#fff;border:none;">
                    📤 Send WhatsApp
                </button>
            </div>
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

        <!-- Calculated Road Route & Distance Map Card -->
        <x-route-map-card :booking="$booking" :editable="true" />

        @if($booking->booking_category === 'TP' || $booking->travel_partner_name)
            <!-- Travel Partner / Agent Details Card -->
            <div class="card mb-4" style="border-left: 4px solid #D97706; background: #FFFDF5;">
                <div class="d-flex justify-between align-center mb-2">
                    <h3 style="font-size: 1rem; font-weight: 800; color: #92400E; margin: 0; display: flex; align-items: center; gap: 6px;">
                        🤝 Travel Partner / Agent Details
                    </h3>
                    <span class="badge" style="background: #FEF3C7; color: #92400E; font-weight: 700;">
                        TP Category [{{ $booking->booking_id }}]
                    </span>
                </div>
                <div class="grid grid-3 gap-3" style="font-size: 0.9rem;">
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Partner Name:</span>
                        <strong style="color: var(--dark-900);">{{ $booking->travel_partner_name ?: 'N/A' }}</strong>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Phone / Mobile:</span>
                        @if($booking->travel_partner_phone)
                            <a href="tel:{{ $booking->travel_partner_phone }}" style="color: #D97706; font-weight: 700; text-decoration: none;">
                                {{ $booking->travel_partner_phone }}
                            </a>
                        @else
                            <span style="color: var(--slate-400);">N/A</span>
                        @endif
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Email:</span>
                        <span style="color: var(--slate-700);">{{ $booking->travel_partner_email ?: 'N/A' }}</span>
                    </div>
                </div>
                @if($booking->travel_partner_address || $booking->travel_partner_commission || $booking->travel_partner_notes)
                    <div class="grid grid-3 gap-3 mt-2 pt-2" style="border-top: 1px dashed #FDE68A; font-size: 0.85rem;">
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Partner Office Address:</span>
                            <span style="color: var(--slate-700);">{{ $booking->travel_partner_address ?: 'N/A' }}</span>
                        </div>
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Commission / Payout:</span>
                            <strong style="color: #059669;">{{ $booking->travel_partner_commission ? '₹' . number_format($booking->travel_partner_commission, 2) : 'Not specified' }}</strong>
                        </div>
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Partner Notes:</span>
                            <span style="color: var(--slate-600);">{{ $booking->travel_partner_notes ?: 'None' }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if($booking->booking_category === 'CORP' || $booking->corporate_company_name)
            <!-- Corporate Account Card -->
            <div class="card mb-4" style="border-left: 4px solid #7C3AED; background: #FDFCFF;">
                <div class="d-flex justify-between align-center mb-2">
                    <h3 style="font-size: 1rem; font-weight: 800; color: #5B21B6; margin: 0; display: flex; align-items: center; gap: 6px;">
                        <x-icon name="building-2" :size="15" /> Corporate Client Account
                    </h3>
                    <span class="badge" style="background: #EDE9FE; color: #5B21B6; font-weight: 700;">
                        CORP Category
                    </span>
                </div>
                <div class="grid grid-3 gap-3" style="font-size: 0.9rem;">
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Company Name:</span>
                        <strong style="color: var(--dark-900);">{{ $booking->corporate_company_name ?: 'N/A' }}</strong>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">GSTIN:</span>
                        <span style="font-family: monospace; font-weight: 700; color: #6D28D9;">{{ $booking->corporate_gstin ?: 'N/A' }}</span>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Contact Person:</span>
                        <span style="color: var(--slate-700);">{{ $booking->corporate_contact_person ?: 'N/A' }}</span>
                    </div>
                </div>
            </div>
        @endif

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

                <div style="background: var(--slate-50); border-radius: 6px; padding: 0.75rem; margin-bottom: 0.75rem; border: 1px solid var(--slate-200);">
                    <div class="grid grid-2 gap-2">
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Booked Category</span>
                            <strong style="color: var(--dark-950); font-size: 0.95rem;">
                                {{ $booking->effective_vehicle_category ?: 'Standard' }}
                            </strong>
                        </div>
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Model Preference</span>
                            <span style="color: {{ $booking->effective_vehicle_model ? '#4F46E5' : 'var(--slate-600)' }}; font-weight: 600; font-size: 0.9rem;">
                                {{ $booking->effective_vehicle_model ?: 'Any in category' }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($booking->vehicle)
                    <div style="font-size: 0.925rem; line-height: 1.6;">
                        <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Allocated Fleet Vehicle:</span></div>
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
                            {{ $booking->driver->mobile }}
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

            @if($booking->pricing_details)
                <div style="border: 1px solid var(--slate-200); padding: 0.85rem; margin-bottom: 1.25rem; font-size: 0.825rem;">
                    <strong>Configured fare details</strong>
                    <div style="color: var(--slate-600); margin-top: 0.35rem;">
                        @if($booking->pricing_details['included_km'] ?? null){{ $booking->pricing_details['included_km'] }} KM included · @endif
                        @if($booking->pricing_details['included_hours'] ?? null){{ $booking->pricing_details['included_hours'] }} hours included · @endif
                        @if($booking->pricing_details['extra_km_rate'] ?? null)Extra KM ₹{{ number_format($booking->pricing_details['extra_km_rate'], 2) }} · @endif
                        @if($booking->pricing_details['extra_hour_rate'] ?? null)Extra hour ₹{{ number_format($booking->pricing_details['extra_hour_rate'], 2) }} · @endif
                        @if($booking->pricing_details['toll_type'] ?? null)Toll {{ $booking->pricing_details['toll_type'] }} · @endif
                        @if($booking->pricing_details['parking_type'] ?? null)Parking {{ $booking->pricing_details['parking_type'] }} · @endif
                        @if($booking->pricing_details['border_tax_type'] ?? null)Border tax {{ $booking->pricing_details['border_tax_type'] }} · @endif
                        @if($booking->pricing_details['night_charge'] ?? null)Night charge ₹{{ number_format($booking->pricing_details['night_charge'], 2) }} · @endif
                        @if($booking->pricing_details['driver_food_type'] ?? null)Driver food {{ $booking->pricing_details['driver_food_type'] }} · @endif
                        @if($booking->pricing_details['gst_applicable'] ?? false)GST applicable @endif
                    </div>
                </div>
            @endif

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

            <!-- Process Refund Form (if paid > 0) -->
            @if($booking->paid_amount > 0)
                <div class="mt-3 pt-3" style="border-top: 1px dashed var(--slate-200);">
                    <h4 style="font-size: 0.9rem; font-weight: 800; color: #DC2626; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                        <x-icon name="rotate-ccw" :size="14" /> Process Refund
                    </h4>
                    <form action="{{ route('admin.payments.refund', $booking->id) }}" method="POST" onsubmit="return confirm('Process refund of this amount to customer?');">
                        @csrf
                        <div class="form-group mb-2">
                            <label class="form-label" style="font-size: 0.775rem;">Refund Amount (Max ₹{{ number_format($booking->paid_amount, 2) }})</label>
                            <input type="number" step="0.01" min="1" max="{{ $booking->paid_amount }}" name="amount" class="form-control form-control-sm" placeholder="Amount to refund" required>
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label" style="font-size: 0.775rem;">Reason for Refund</label>
                            <input type="text" name="reason" class="form-control form-control-sm" placeholder="e.g. Trip cancelled, excess paid" required>
                        </div>
                        <button type="submit" class="btn btn-outline btn-sm w-100" style="color: #DC2626; border-color: #F87171;">
                            Confirm Refund
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- WhatsApp Messaging Center (Section 12 & 14) -->
        @php
            $cleanPhone = preg_replace('/[^0-9]/', '', $booking->customer->phone ?? '');
            if (strlen($cleanPhone) === 10) {
                $cleanPhone = '91' . $cleanPhone;
            }

            $confirmMsg = "Namaste " . ($booking->customer->name ?? 'Customer') . ", your taxi booking with Vaishnavi Tours is CONFIRMED!\n\nBooking ID: #" . $booking->booking_id . "\nRoute: " . $booking->pickup_location . " to " . $booking->destination . "\nDate: " . ($booking->travel_date ? $booking->travel_date->format('d M Y') : '') . " at " . $booking->travel_time . "\nTotal Fare: ₹" . number_format($booking->total_amount, 2) . "\nAdvance Paid: ₹" . number_format($booking->paid_amount, 2) . "\nBalance: ₹" . number_format($booking->balance_amount, 2) . "\n\nHelpline: +91 92447 84443";

            $driverMsg = "Namaste " . ($booking->customer->name ?? 'Customer') . ", your chauffeur & vehicle details for booking #" . $booking->booking_id . ":\n\nVehicle: " . ($booking->vehicle ? $booking->vehicle->name . ' (' . $booking->vehicle->registration_number . ')' : 'Assigned') . "\nDriver: " . ($booking->driver->name ?? 'Assigned') . "\nDriver Mobile: " . ($booking->driver->mobile ?? 'Contact Dispatch') . "\n\nHave a safe journey!";

            $remindMsg = "Namaste " . ($booking->customer->name ?? 'Customer') . ", friendly reminder regarding your booking #" . $booking->booking_id . ".\n\nPending balance amount: ₹" . number_format($booking->balance_amount, 2) . ".\nKindly keep the payment ready. Thank you, Vaishnavi Tours!";

            $reviewMsg = "Namaste " . ($booking->customer->name ?? 'Customer') . ", thank you for traveling with Vaishnavi Tours!\n\nWe hope you had a pleasant trip. Please share your valuable experience with a 5-star Google Review:\nhttps://maps.google.com/?q=Vaishnavi+Tours+Bilaspur\n\nThank you!";
        @endphp
        <div class="card mb-4" style="border-top: 4px solid #25D366;">
            <div class="d-flex justify-between align-center mb-2">
                <h3 style="font-size: 1rem; font-weight: 800; color: #15803D; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <x-icon name="message-square" :size="16" /> WhatsApp Dispatch Actions
                </h3>
                <span class="badge" style="background: #DCFCE7; color: #166534; font-size: 0.7rem;">Quick Send</span>
            </div>
            <p style="font-size: 0.78rem; color: var(--slate-500); margin-bottom: 0.75rem;">
                Send automated pre-filled WhatsApp notifications directly to <strong>{{ $cleanPhone ? '+' . $cleanPhone : 'Customer' }}</strong>.
            </p>

            <div class="d-flex flex-column gap-2">
                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($confirmMsg) }}" target="_blank" class="btn btn-outline btn-sm d-flex align-center justify-between" style="border-color: #25D366; color: #166534; font-weight: 700; font-size: 0.8rem;">
                    <span style="display: flex; align-items: center; gap: 6px;"><x-icon name="send" :size="13" /> Send Booking Confirmation</span>
                    <span>➔</span>
                </a>

                @if($booking->driver)
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($driverMsg) }}" target="_blank" class="btn btn-outline btn-sm d-flex align-center justify-between" style="border-color: #3B82F6; color: #1D4ED8; font-weight: 700; font-size: 0.8rem;">
                        <span style="display: flex; align-items: center; gap: 6px;"><x-icon name="car" :size="13" /> Send Driver & Vehicle Details</span>
                        <span>➔</span>
                    </a>
                @endif

                @if($booking->balance_amount > 0)
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($remindMsg) }}" target="_blank" class="btn btn-outline btn-sm d-flex align-center justify-between" style="border-color: #F59E0B; color: #B45309; font-weight: 700; font-size: 0.8rem;">
                        <span style="display: flex; align-items: center; gap: 6px;"><x-icon name="bell" :size="13" /> Send Payment Reminder (₹{{ number_format($booking->balance_amount) }})</span>
                        <span>➔</span>
                    </a>
                @endif

                @if($booking->booking_status === 'Completed')
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($reviewMsg) }}" target="_blank" class="btn btn-outline btn-sm d-flex align-center justify-between" style="border-color: #8B5CF6; color: #6D28D9; font-weight: 700; font-size: 0.8rem;">
                        <span style="display: flex; align-items: center; gap: 6px;"><x-icon name="star" :size="13" /> Request Google Review</span>
                        <span>➔</span>
                    </a>
                @endif
            </div>
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
