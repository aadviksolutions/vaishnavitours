@extends('layouts.admin')

@section('title', 'Enquiry ' . $enquiry->enquiry_id . ' - Vaishnavi Tours')
@section('page_title', 'Enquiry Details')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('admin.enquiries.index', ['source' => $enquiry->source]) }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600; text-decoration: none;">
            &larr; Back to Enquiries Inbox
        </a>
        <div class="d-flex align-center gap-2 mt-1">
            <h1 style="font-size: 1.85rem; font-weight: 900; margin: 0; color: var(--dark-950);">
                Enquiry {{ $enquiry->enquiry_id }}
            </h1>
            <span class="badge badge-lg
                {{ in_array($enquiry->status, ['Converted']) ? 'badge-success' : '' }}
                {{ in_array($enquiry->status, ['New']) ? 'badge-primary' : '' }}
                {{ in_array($enquiry->status, ['Contacted', 'Quotation Sent', 'In Progress']) ? 'badge-warning' : '' }}
                {{ in_array($enquiry->status, ['Lost', 'Closed']) ? 'badge-danger' : '' }}
            ">
                {{ $enquiry->status }}
            </span>
            <span class="badge" style="background: #EEF2F6; color: #475569; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                {{ $enquiry->source }}
            </span>
        </div>
    </div>

    <div class="d-flex align-center gap-2 flex-wrap">
        @if($enquiry->status !== 'Converted')
            <a href="{{ route('admin.enquiries.convert', $enquiry->id) }}" class="btn btn-success btn-sm" style="background: #059669; color: #fff;">
                <x-icon name="circle-check" size="16" style="margin-right: 6px;" /> Convert to Confirmed Booking
            </a>
        @else
            @if($enquiry->converted_booking_id)
                <a href="{{ route('admin.bookings.show', $enquiry->converted_booking_id) }}" class="btn btn-primary btn-sm">
                    <x-icon name="calendar-check" size="16" style="margin-right: 6px;" /> View Converted Booking #{{ $enquiry->convertedBooking?->booking_id ?? $enquiry->converted_booking_id }}
                </a>
            @endif
        @endif

        <form action="{{ route('admin.enquiries.destroy', $enquiry->id) }}" method="POST" onsubmit="return confirm('Permanently delete this enquiry?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <x-icon name="trash-2" size="14" style="margin-right: 4px;" /> Delete
            </button>
        </form>
    </div>
</div>

<div class="grid grid-3 gap-4">
    <!-- Left 2 Cols: Details & Route Map -->
    <div style="grid-column: span 2;">
        <!-- Customer & Trip Summary Grid -->
        <div class="grid grid-2 gap-3 mb-4">
            <!-- Customer Card -->
            <div class="card">
                <div class="d-flex justify-between align-center mb-3">
                    <h3 style="font-size: 1rem; font-weight: 800; color: var(--dark-950); margin: 0;">
                        <x-icon name="user" size="18" class="text-primary" style="margin-right: 6px;" /> Customer Information
                    </h3>
                    <span class="badge badge-outline" style="font-size: 0.75rem;">Lead</span>
                </div>
                <div style="font-size: 0.925rem; line-height: 1.6;">
                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Customer Name:</span></div>
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--dark-950); margin-bottom: 0.5rem;">
                        {{ $enquiry->name }}
                    </div>

                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Phone / Mobile:</span></div>
                    <div style="font-weight: 700; color: var(--dark-900); margin-bottom: 0.5rem;">
                        <a href="tel:{{ $enquiry->phone }}" style="color: #2563EB; text-decoration: none;">
                            📞 {{ $enquiry->phone }}
                        </a>
                    </div>

                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Email Address:</span></div>
                    <div style="font-weight: 600; color: var(--slate-600); margin-bottom: 0.5rem;">
                        {{ $enquiry->email ?: 'Not provided' }}
                    </div>

                    <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--slate-200);">
                        Received: <strong>{{ $enquiry->created_at->format('d M Y, h:i A') }}</strong> ({{ $enquiry->created_at->diffForHumans() }})
                    </div>
                </div>
            </div>

            <!-- Trip Request Card -->
            <div class="card">
                <div class="d-flex justify-between align-center mb-3">
                    <h3 style="font-size: 1rem; font-weight: 800; color: var(--dark-950); margin: 0;">
                        <x-icon name="route" size="18" class="text-primary" style="margin-right: 6px;" /> Requested Itinerary
                    </h3>
                    <span class="badge" style="background: var(--slate-200); color: var(--dark-800); font-size: 0.75rem;">
                        {{ $enquiry->booking_type ?: 'Cab Enquiry' }}
                    </span>
                </div>
                <div style="font-size: 0.925rem; line-height: 1.6;">
                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Pickup:</span></div>
                    <div style="font-weight: 800; color: var(--dark-950); margin-bottom: 0.4rem;">
                        🟢 {{ $enquiry->pickup_location }}
                    </div>

                    @if($enquiry->via_location)
                        <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Via Halt:</span></div>
                        <div style="font-weight: 700; color: #D97706; margin-bottom: 0.4rem;">
                            🟡 {{ $enquiry->via_location }}
                        </div>
                    @endif

                    <div><span style="color: var(--slate-500); font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Destination:</span></div>
                    <div style="font-weight: 800; color: var(--dark-950); margin-bottom: 0.4rem;">
                        🔴 {{ $enquiry->destination }}
                    </div>

                    <div class="grid grid-2 gap-2 mt-2 pt-2" style="border-top: 1px dashed var(--slate-200); font-size: 0.85rem;">
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Travel Date:</span>
                            <div style="font-weight: 700;">{{ $enquiry->travel_date ? \Carbon\Carbon::parse($enquiry->travel_date)->format('d M, Y') : 'Not fixed' }}</div>
                        </div>
                        <div>
                            <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Vehicle:</span>
                            <div style="font-weight: 700;">{{ $enquiry->vehicle_preference ?: 'Standard Sedan / SUV' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculated Road Route & Distance Map Card -->
        <x-route-map-card :enquiry="$enquiry" title="Enquiry Road Route & Distance (For Custom Quotation)" />

        @if($enquiry->message)
            <!-- Customer Message Card -->
            <div class="card mb-4" style="background: #F8FAFC; border-left: 4px solid #3B82F6;">
                <h4 style="font-size: 0.95rem; font-weight: 800; margin: 0 0 0.5rem 0; color: #1E3A8A;">
                    💬 Customer Inquiry Message / Requirements
                </h4>
                <div style="font-size: 0.9rem; color: #334155; white-space: pre-line; line-height: 1.5;">
                    {{ $enquiry->message }}
                </div>
            </div>
        @endif
    </div>

    <!-- Right 1 Col: Management & Follow-up Notes -->
    <div>
        <div class="card mb-4">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.25rem;">📝 Enquiry Management</h3>

            <form action="{{ route('admin.enquiries.update', $enquiry->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="status" class="form-label" style="font-weight: 700;">Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        @foreach(['New', 'Contacted', 'Quotation Sent', 'In Progress', 'Converted', 'Lost', 'Closed'] as $st)
                            <option value="{{ $st }}" {{ $enquiry->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="assigned_to" class="form-label" style="font-weight: 700;">Assign Staff / Agent</label>
                    <select name="assigned_to" id="assigned_to" class="form-select">
                        <option value="">-- Unassigned --</option>
                        @foreach($staffUsers as $staff)
                            <option value="{{ $staff->id }}" {{ $enquiry->assigned_to == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ ucfirst($staff->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="new_followup_note" class="form-label" style="font-weight: 700;">Add Follow-up Note</label>
                    <textarea name="new_followup_note" id="new_followup_note" class="form-control" rows="3" placeholder="Enter quotation given, customer response, discount offered, etc..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="width: 100%;">
                    💾 Update Enquiry Status & Notes
                </button>
            </form>
        </div>

        @if($enquiry->notes)
            <div class="card mb-4" style="background: #FFFBEB; border: 1px solid #FDE68A;">
                <h4 style="font-size: 0.95rem; font-weight: 800; margin: 0 0 0.5rem 0; color: #92400E;">
                    📋 History & Staff Follow-up Logs
                </h4>
                <div style="font-size: 0.85rem; color: #78350F; white-space: pre-line; line-height: 1.5; font-family: monospace;">
                    {{ $enquiry->notes }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
