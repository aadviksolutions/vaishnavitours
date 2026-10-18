@extends('layouts.admin')

@section('title', 'Enquiries & Lead Inbox')
@section('page_title', 'Customer Enquiries')

@section('content')
<div class="mb-3 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--dark-900);">Customer & Travel Partner Enquiries</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 12.5px;">
            Track website queries, manual phone inquiries, corporate RFQs, and convert them directly to bookings.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <button type="button" onclick="document.getElementById('manualEnquiryModal').style.display='flex'" class="btn btn-primary btn-sm">
            + Record Manual Enquiry
        </button>
    </div>
</div>

<!-- Tabs for Sources: Website, Manual, Corporate, Third-Party, All -->
<div class="d-flex align-center gap-1 mb-3" style="border-bottom: 2px solid var(--slate-200); padding-bottom: 0;">
    <a href="{{ route('admin.enquiries.index', ['source' => 'website']) }}" 
       style="padding: 0.5rem 1rem; font-size: 13px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $currentSource === 'website' ? '#2563EB' : 'transparent' }}; color: {{ $currentSource === 'website' ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       Website Enquiries ({{ $sourceCounts['website'] }})
    </a>
    <a href="{{ route('admin.enquiries.index', ['source' => 'manual']) }}" 
       style="padding: 0.5rem 1rem; font-size: 13px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $currentSource === 'manual' ? '#2563EB' : 'transparent' }}; color: {{ $currentSource === 'manual' ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       Manual Enquiries ({{ $sourceCounts['manual'] }})
    </a>
    <a href="{{ route('admin.enquiries.index', ['source' => 'corporate']) }}" 
       style="padding: 0.5rem 1rem; font-size: 13px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $currentSource === 'corporate' ? '#2563EB' : 'transparent' }}; color: {{ $currentSource === 'corporate' ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       Corporate RFQs ({{ $sourceCounts['corporate'] }})
    </a>
    <a href="{{ route('admin.enquiries.index', ['source' => 'third_party']) }}" 
       style="padding: 0.5rem 1rem; font-size: 13px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $currentSource === 'third_party' ? '#2563EB' : 'transparent' }}; color: {{ $currentSource === 'third_party' ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       Travel Partner Enquiries ({{ $sourceCounts['third_party'] }})
    </a>
    <a href="{{ route('admin.enquiries.index', ['source' => 'all']) }}" 
       style="padding: 0.5rem 1rem; font-size: 13px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $currentSource === 'all' ? '#2563EB' : 'transparent' }}; color: {{ $currentSource === 'all' ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       All Enquiries ({{ $sourceCounts['all'] }})
    </a>
</div>

<!-- Search & Status Filter -->
<div class="card mb-3" style="padding: 0.75rem 1rem; background: #fff; border: 1px solid var(--slate-200); border-radius: 6px;">
    <form method="GET" action="{{ route('admin.enquiries.index') }}" class="d-flex align-center gap-2 flex-wrap">
        <input type="hidden" name="source" value="{{ $currentSource }}">
        <div style="flex: 1; min-width: 220px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by enquiry ID, customer, phone, location..." value="{{ request('search') }}">
        </div>
        <div>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                @foreach(['New', 'Contacted', 'Quotation Sent', 'Converted', 'Lost', 'In Progress', 'Resolved', 'Closed'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.enquiries.index', ['source' => $currentSource]) }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Enquiries Table -->
<div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; font-size: 12.5px;">
            <thead>
                <tr>
                    <th>Enquiry ID</th>
                    <th>Source</th>
                    <th>Customer Name & Contact</th>
                    <th>Booking Type & Route</th>
                    <th>Travel Dates</th>
                    <th>Vehicle Pref</th>
                    <th>Assigned Staff</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enquiries as $item)
                <tr>
                    <td>
                        <span style="font-weight: 700; color: #2563EB;">{{ $item->enquiry_id }}</span>
                        <div style="font-size: 10.5px; color: var(--slate-400);">{{ $item->created_at->format('d M, h:i A') }}</div>
                    </td>
                    <td>
                        @php
                            $sColors = [
                                'website' => 'bg-blue-100 text-blue-800',
                                'manual' => 'bg-slate-100 text-slate-800',
                                'corporate' => 'bg-purple-100 text-purple-800',
                                'third_party' => 'bg-amber-100 text-amber-800',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sColors[$item->source] ?? 'bg-slate-100 text-slate-800' }}">
                            {{ strtoupper(str_replace('_', ' ', $item->source)) }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--dark-900);">{{ $item->name }}</div>
                        <div style="font-size: 11px;">
                            <a href="tel:{{ $item->phone }}" style="color: #2563EB;">📞 {{ $item->phone }}</a>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-secondary" style="font-size: 10.5px;">{{ $item->booking_type ?: 'Taxi Service' }}</span>
                        <div style="font-size: 11.5px; margin-top: 2px;">
                            {{ $item->pickup_location ?: 'N/A' }}
                            @if($item->via_location)
                                <span style="color: var(--slate-400);">via {{ $item->via_location }}</span>
                            @endif
                            → {{ $item->destination ?: 'N/A' }}
                        </div>
                    </td>
                    <td>
                        @if($item->travel_date)
                            <div>{{ \Carbon\Carbon::parse($item->travel_date)->format('d M Y') }}</div>
                        @else
                            <span style="color: var(--slate-400);">-</span>
                        @endif
                        @if($item->duration_days)
                            <div style="font-size: 10.5px; color: var(--slate-500);">{{ $item->duration_days }} Days</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size: 11px; font-weight: 600;">{{ $item->vehicle_preference ?: 'Any Available' }}</span>
                    </td>
                    <td>
                        <span style="font-size: 11px; color: var(--slate-600);">
                            {{ $item->assignedUser?->name ?? 'Unassigned' }}
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.enquiries.update', $item->id) }}" style="display: inline-block;">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="font-size: 11px; font-weight: 600; padding: 2px 6px;">
                                @foreach(['New', 'Contacted', 'Quotation Sent', 'Converted', 'Lost'] as $st)
                                    <option value="{{ $st }}" {{ ($item->status ?? 'New') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        @if($item->status === 'Converted' && $item->converted_booking_id)
                            <a href="{{ route('admin.bookings.show', $item->converted_booking_id) }}" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 2px 7px;">
                                View Booking #{{ $item->converted_booking_id }}
                            </a>
                        @else
                            <a href="{{ route('admin.enquiries.convert', $item) }}" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 2px 7px;" title="Convert to confirmed booking without re-entering data">
                                + Convert to Booking
                            </a>
                        @endif
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->phone) }}" target="_blank" class="btn btn-outline btn-sm" style="font-size: 11px; padding: 2px 6px; color: #16A34A; border-color: #86EFAC;">
                            WA
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2rem; color: var(--slate-500);">
                        No enquiries found in this category.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding: 0.75rem 1rem;">
        {{ $enquiries->links() }}
    </div>
</div>

<!-- Modal: Record Manual Enquiry -->
<div id="manualEnquiryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; overflow-y: auto; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 580px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--dark-900);">Record Manual / Corporate Enquiry</h3>
            <button type="button" onclick="document.getElementById('manualEnquiryModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form action="{{ route('admin.enquiries.store-manual') }}" method="POST">
            @csrf
            <div class="grid grid-2 gap-2 mb-2">
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Enquiry Source *</label>
                    <select name="source" class="form-select form-select-sm" required>
                        <option value="manual">Manual (Phone / Walk-in)</option>
                        <option value="corporate">Corporate Client (RFQ)</option>
                        <option value="third_party">Travel Partner (Agent)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Booking Type *</label>
                    <select name="booking_type" class="form-select form-select-sm" required>
                        <option value="Round-Trip">Round-Trip</option>
                        <option value="One-Way">One-Way</option>
                        <option value="Local Hourly">Local Rental</option>
                        <option value="Tour Package">Tour Package</option>
                        <option value="Ambulance">Ambulance Service</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-2 gap-2 mb-2">
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Customer / Company Name *</label>
                    <input type="text" name="name" class="form-control form-control-sm" required>
                </div>
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Mobile Number *</label>
                    <input type="tel" name="phone" class="form-control form-control-sm" required>
                </div>
            </div>
            <div class="grid grid-3 gap-2 mb-2">
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Pickup Location *</label>
                    <input type="text" name="pickup_location" class="form-control form-control-sm" required>
                </div>
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Via Location</label>
                    <input type="text" name="via_location" class="form-control form-control-sm" placeholder="Optional">
                </div>
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Destination *</label>
                    <input type="text" name="destination" class="form-control form-control-sm" required>
                </div>
            </div>
            <div class="grid grid-3 gap-2 mb-2">
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Travel Date</label>
                    <input type="date" name="travel_date" class="form-control form-control-sm">
                </div>
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Duration (Days)</label>
                    <input type="number" name="duration_days" class="form-control form-control-sm" min="1" value="1">
                </div>
                <div>
                    <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Vehicle Preference</label>
                    <select name="vehicle_preference" class="form-select form-select-sm">
                        <option value="Sedan">Sedan (Dzire / Etios)</option>
                        <option value="SUV">SUV (Ertiga / Innova)</option>
                        <option value="Luxury SUV">Innova Crysta</option>
                        <option value="Tempo Traveller">Tempo Traveller</option>
                        <option value="Ambulance">Ambulance</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label style="font-size: 11.5px; font-weight: 600; display: block; margin-bottom: 2px;">Follow-up Notes / Agreed Terms</label>
                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Quotation offered, corporate PO reference, client preferences..."></textarea>
            </div>
            <div class="d-flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('manualEnquiryModal').style.display='none'" class="btn btn-secondary btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Manual Enquiry</button>
            </div>
        </form>
    </div>
</div>
@endsection
