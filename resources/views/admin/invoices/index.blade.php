@extends('layouts.admin')

@section('title', 'GST Invoices Search & Billing - Vaishnavi Tours')
@section('page_title', 'GST Invoices')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <h1 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--dark-900);">GST Invoices & Official Tax Bills</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 12.5px;">
            5% GST compliant tax invoices with CGST/SGST split, sequential numbering, and customer GSTIN tracking.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline btn-sm">
            <x-icon name="file-text" size="14" class="mr-1 inline" /> Billing Reports
        </a>
    </div>
</div>

<!-- Financial Summary KPIs -->
<div class="grid grid-4 gap-2 mb-3">
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Total Issued Invoices</span>
        <div style="font-size: 20px; font-weight: 700; color: var(--dark-900);">{{ number_format($stats['total_invoices']) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Total Taxable Value</span>
        <div style="font-size: 20px; font-weight: 700; color: var(--dark-900);">₹{{ number_format($stats['total_taxable'], 2) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Total 5% GST Collected</span>
        <div style="font-size: 20px; font-weight: 700; color: #2563EB;">₹{{ number_format($stats['total_gst'], 2) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Gross Invoiced Amount</span>
        <div style="font-size: 20px; font-weight: 700; color: #059669;">₹{{ number_format($stats['total_billed'], 2) }}</div>
    </div>
</div>

<!-- Fast Search & Multi-Filter Form (Requirement: Section 11) -->
<div class="card mb-3" style="padding: 1rem; background: #fff; border: 1px solid var(--slate-200); border-radius: 8px;">
    <form method="GET" action="{{ route('admin.invoices.index') }}">
        <div class="grid grid-4 gap-2 mb-2">
            <div>
                <label style="font-size: 11px; font-weight: 600; color: var(--slate-600); display: block; margin-bottom: 2px;">Search Text</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice #, Booking ID, Customer, Phone..." value="{{ request('search') }}">
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 600; color: var(--slate-600); display: block; margin-bottom: 2px;">Booking Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <option value="CORP" {{ request('category') === 'CORP' ? 'selected' : '' }}>Corporate (CORP)</option>
                    <option value="WEB" {{ request('category') === 'WEB' ? 'selected' : '' }}>Website (WEB)</option>
                    <option value="MAN" {{ request('category') === 'MAN' ? 'selected' : '' }}>Manual (MAN)</option>
                    <option value="TP" {{ request('category') === 'TP' ? 'selected' : '' }}>Third-Party (TP)</option>
                </select>
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 600; color: var(--slate-600); display: block; margin-bottom: 2px;">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 600; color: var(--slate-600); display: block; margin-bottom: 2px;">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
        </div>

        <div class="d-flex justify-between align-center flex-wrap gap-2">
            <div class="d-flex align-center gap-2 flex-wrap">
                <select name="booking_type" class="form-select form-select-sm" style="width: 150px;">
                    <option value="">All Trip Types</option>
                    <option value="One-Way" {{ request('booking_type') === 'One-Way' ? 'selected' : '' }}>One-Way</option>
                    <option value="Round-Trip" {{ request('booking_type') === 'Round-Trip' ? 'selected' : '' }}>Round-Trip</option>
                    <option value="Local Hourly" {{ request('booking_type') === 'Local Hourly' ? 'selected' : '' }}>Local Rental</option>
                    <option value="Airport Transfer" {{ request('booking_type') === 'Airport Transfer' ? 'selected' : '' }}>Airport Transfer</option>
                    <option value="Ambulance" {{ request('booking_type') === 'Ambulance' ? 'selected' : '' }}>Ambulance</option>
                </select>

                <select name="payment_status" class="form-select form-select-sm" style="width: 140px;">
                    <option value="">Payment Status</option>
                    <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Partially Paid" {{ request('payment_status') === 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="Pending" {{ request('payment_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                </select>

                <select name="status" class="form-select form-select-sm" style="width: 130px;">
                    <option value="">Invoice Status</option>
                    <option value="Paid" {{ request('status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Unpaid" {{ request('status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>

            <div class="d-flex align-center gap-1">
                <button type="submit" class="btn btn-primary btn-sm">Search Invoices</button>
                @if(request()->hasAny(['search', 'category', 'booking_type', 'payment_status', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline btn-sm">Reset</a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Invoices Table -->
<div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; font-size: 12.5px;">
            <thead>
                <tr>
                    <th>Invoice No. & Date</th>
                    <th>Booking ID & Cat</th>
                    <th>Customer Name & Contact</th>
                    <th>Journey Details</th>
                    <th>Taxable (₹)</th>
                    <th>5% GST</th>
                    <th>Total (₹)</th>
                    <th>Paid / Due</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <a href="{{ route('admin.invoices.show', $inv->id) }}" style="font-weight: 700; color: #2563EB;">
                                {{ $inv->invoice_number }}
                            </a>
                            <div style="font-size: 10.5px; color: var(--slate-500);">
                                {{ $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') : $inv->created_at->format('d M Y') }}
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.bookings.show', $inv->booking_id) }}" style="font-weight: 700; color: var(--dark-900);">
                                {{ $inv->booking->booking_id ?? $inv->booking_id }}
                            </a>
                            @php
                                $bCat = $inv->booking_category ?: ($inv->booking->booking_category ?? 'WEB');
                                $catBadge = match($bCat) {
                                    'CORP' => 'bg-purple-100 text-purple-800',
                                    'WEB' => 'bg-blue-100 text-blue-800',
                                    'TP' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-slate-100 text-slate-800',
                                };
                            @endphp
                            <div style="margin-top: 2px;">
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-xs font-semibold {{ $catBadge }}">
                                    {{ $bCat }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--dark-900);">{{ $inv->customer->name ?? 'Customer' }}</div>
                            <div style="font-size: 11px; color: var(--slate-500);">{{ $inv->customer->phone ?? '' }}</div>
                            @if($inv->customer_gstin)
                                <div style="font-size: 10px; font-family: monospace; color: #4F46E5;">GSTIN: {{ $inv->customer_gstin }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 500;">
                                {{ $inv->booking->pickup_location ?? 'N/A' }} 
                                @if($inv->booking?->via_location)
                                    <span style="color: var(--slate-400);">via {{ $inv->booking->via_location }}</span>
                                @endif
                                ➔ {{ $inv->booking->destination ?? 'N/A' }}
                            </div>
                            <div style="font-size: 11px; color: var(--slate-500);">{{ $inv->booking->trip_type ?? '' }}</div>
                        </td>
                        <td style="font-weight: 600;">
                            ₹{{ number_format($inv->taxable_amount ?: ($inv->total_amount / 1.05), 2) }}
                        </td>
                        <td style="color: #2563EB; font-weight: 600;">
                            ₹{{ number_format($inv->gst_amount ?: ($inv->total_amount - ($inv->total_amount / 1.05)), 2) }}
                        </td>
                        <td style="font-weight: 700; color: var(--dark-900);">
                            ₹{{ number_format($inv->total_amount, 2) }}
                        </td>
                        <td>
                            <div style="color: #059669; font-weight: 600;">₹{{ number_format($inv->paid_amount, 2) }}</div>
                            @if($inv->balance_amount > 0)
                                <div style="color: #DC2626; font-size: 11px; font-weight: 700;">Due: ₹{{ number_format($inv->balance_amount, 2) }}</div>
                            @else
                                <div style="color: #059669; font-size: 10.5px;">Settled</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-sm
                                {{ $inv->status === 'Paid' ? 'badge-success' : '' }}
                                {{ $inv->status === 'Partially Paid' ? 'badge-warning' : '' }}
                                {{ $inv->status === 'Unpaid' ? 'badge-danger' : '' }}
                            ">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.invoices.show', $inv->id) }}" class="btn btn-outline btn-sm" style="padding: 2px 7px; font-size: 11px;" target="_blank">
                                📄 View / Print PDF
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2.5rem 1rem; color: var(--slate-500);">
                            No tax invoices matching your filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        <div style="padding: 0.75rem 1rem; border-top: 1px solid var(--slate-200);">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
