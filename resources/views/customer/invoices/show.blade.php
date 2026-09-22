@extends('layouts.customer')

@section('title', 'Tax Invoice #' . $invoice->invoice_number)

@section('content')
<div class="d-flex align-center justify-between flex-wrap gap-2 mb-4 no-print">
    <div>
        <a href="{{ route('customer.bookings.show', $invoice->booking_id) }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
            ← Back to Booking #{{ $invoice->booking->booking_id }}
        </a>
        <h1 style="font-size: 1.75rem; font-weight: 800; margin: 4px 0 0 0;">Tax Invoice & Travel Receipt</h1>
    </div>

    <div class="d-flex align-center gap-2">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            🖨️ Print / Download PDF
        </button>
    </div>
</div>

<!-- Invoice Printable Sheet -->
<div class="card invoice-sheet" style="background: #fff; max-width: 840px; margin: 0 auto; padding: 2.5rem; border: 1px solid var(--slate-200); box-shadow: var(--shadow-lg);">
    <!-- Invoice Header -->
    <div class="d-flex justify-between align-start mb-4" style="border-bottom: 2px solid var(--dark-900); padding-bottom: 1.5rem;">
        <div>
            <div class="d-flex align-center gap-2 mb-2">
                <div style="width: 38px; height: 38px; background: var(--dark-900); color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 800;">
                    VT
                </div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--dark-900);">
                    Vaishnavi <span style="color: var(--primary);">Tours</span>
                </div>
            </div>
            <div style="font-size: 0.825rem; color: var(--slate-600); line-height: 1.5;">
                <strong>{{ config('vaishnavi.business_name') }}</strong><br>
                {{ config('vaishnavi.address') }}<br>
                📞 Phone: {{ config('vaishnavi.phone_primary') }} | {{ config('vaishnavi.phone_secondary') }}
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--slate-800); text-transform: uppercase; letter-spacing: 0.05em;">TAX INVOICE</div>
            <div style="font-size: 1.05rem; font-weight: 800; color: var(--primary); margin-top: 4px;">#{{ $invoice->invoice_number }}</div>
            <div style="font-size: 0.825rem; color: var(--slate-500); margin-top: 6px;">
                <strong>Invoice Date:</strong> {{ $invoice->issued_at ? \Carbon\Carbon::parse($invoice->issued_at)->format('d M Y') : $invoice->created_at->format('d M Y') }}
            </div>
            <div style="font-size: 0.825rem; color: var(--slate-500);">
                <strong>Booking Ref:</strong> #{{ $invoice->booking->booking_id }}
            </div>
            <div class="mt-2">
                <span class="badge {{ $invoice->status === 'Paid' ? 'badge-success' : ($invoice->status === 'Partially Paid' ? 'badge-warning' : 'badge-danger') }}" style="font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                    {{ $invoice->status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Billed To & Journey Info -->
    <div class="grid grid-2 gap-4 mb-4" style="font-size: 0.875rem;">
        <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md);">
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--slate-500); margin-bottom: 0.5rem;">Billed To (Passenger)</div>
            <div style="font-weight: 800; font-size: 1.05rem; color: var(--dark-900);">{{ $invoice->customer->name }}</div>
            <div style="color: var(--slate-600); margin-top: 2px;">📞 {{ $invoice->customer->phone }}</div>
            @if($invoice->customer->email)
                <div style="color: var(--slate-600);">✉️ {{ $invoice->customer->email }}</div>
            @endif
            @if($invoice->customer->customer && $invoice->customer->customer->address)
                <div style="color: var(--slate-600); margin-top: 4px;">
                    📍 {{ $invoice->customer->customer->address }}, {{ $invoice->customer->customer->city ?? 'Bilaspur' }} - {{ $invoice->customer->customer->pincode ?? '495001' }}
                </div>
            @endif
        </div>

        <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-md);">
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--slate-500); margin-bottom: 0.5rem;">Trip & Cab Particulars</div>
            <div><strong>Trip Type:</strong> {{ $invoice->booking->trip_type }}</div>
            <div><strong>Pickup Date:</strong> {{ $invoice->booking->travel_date ? $invoice->booking->travel_date->format('d M Y') : '' }} at {{ date('h:i A', strtotime($invoice->booking->travel_time)) }}</div>
            <div><strong>From:</strong> {{ $invoice->booking->pickup_location }}</div>
            <div><strong>To:</strong> {{ $invoice->booking->destination }}</div>
            @if($invoice->booking->vehicle)
                <div style="margin-top: 4px;"><strong>Vehicle:</strong> {{ $invoice->booking->vehicle->name }} ({{ $invoice->booking->vehicle->registration_number }})</div>
            @endif
            @if($invoice->booking->driver)
                <div><strong>Chauffeur:</strong> {{ $invoice->booking->driver->name }} ({{ $invoice->booking->driver->mobile }})</div>
            @endif
        </div>
    </div>

    @php
        $total = (float)$invoice->total_amount;
        $baseFare = round($total / 1.05, 2);
        $taxAmount = round($total - $baseFare, 2);
        $halfTax = round($taxAmount / 2, 2);
    @endphp

    <!-- Charges Breakdown Table -->
    <table class="table mb-4" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--slate-100); border-bottom: 2px solid var(--slate-300);">
                <th style="padding: 0.75rem; text-align: left; font-size: 0.85rem;">Description</th>
                <th style="padding: 0.75rem; text-align: right; font-size: 0.85rem; width: 140px;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 0.75rem; border-bottom: 1px solid var(--slate-200);">
                    <strong>Commercial Taxi Chauffeur Hire Service</strong><br>
                    <small style="color: var(--slate-500);">Route: {{ $invoice->booking->pickup_location }} to {{ $invoice->booking->destination }} ({{ $invoice->booking->trip_type }})</small>
                </td>
                <td style="padding: 0.75rem; text-align: right; border-bottom: 1px solid var(--slate-200); font-weight: 600;">
                    ₹{{ number_format($baseFare, 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 0.75rem; border-bottom: 1px solid var(--slate-200);">
                    <strong>Goods & Services Tax (GST @ 5% on Passenger Transport)</strong><br>
                    <small style="color: var(--slate-500);">CGST @ 2.5%: ₹{{ number_format($halfTax, 2) }} | SGST @ 2.5%: ₹{{ number_format($halfTax, 2) }}</small>
                </td>
                <td style="padding: 0.75rem; text-align: right; border-bottom: 1px solid var(--slate-200); font-weight: 600;">
                    ₹{{ number_format($taxAmount, 2) }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background: var(--slate-50);">
                <td style="padding: 0.85rem; font-weight: 800; font-size: 1rem;">Total Invoice Amount:</td>
                <td style="padding: 0.85rem; text-align: right; font-weight: 800; font-size: 1.15rem; color: var(--dark-900);">
                    ₹{{ number_format($invoice->total_amount, 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem 0.85rem; color: #16a34a; font-weight: 700;">Total Amount Paid:</td>
                <td style="padding: 0.5rem 0.85rem; text-align: right; color: #16a34a; font-weight: 700;">
                    - ₹{{ number_format($invoice->paid_amount, 2) }}
                </td>
            </tr>
            <tr style="border-top: 2px solid var(--slate-300); background: #fff;">
                <td style="padding: 0.75rem 0.85rem; font-weight: 800; font-size: 1.05rem;">Balance Due / Payable:</td>
                <td style="padding: 0.75rem 0.85rem; text-align: right; font-weight: 800; font-size: 1.15rem; color: {{ $invoice->balance_amount > 0 ? '#dc2626' : '#16a34a' }};">
                    ₹{{ number_format($invoice->balance_amount, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Terms & Signature Footer -->
    <div class="grid grid-2 gap-4 align-end" style="border-top: 1px dashed var(--slate-300); padding-top: 1.5rem; font-size: 0.8rem; color: var(--slate-600);">
        <div>
            <div style="font-weight: 700; color: var(--dark-900); margin-bottom: 4px;">Terms & Conditions:</div>
            <ul style="margin: 0; padding-left: 1.2rem; line-height: 1.5;">
                <li>Toll plaza taxes, state border permits & parking fees are extra if applicable.</li>
                <li>Night driving allowance applies between 10:00 PM and 06:00 AM as per agreement.</li>
                <li>This is a computer-generated tax invoice valid under Indian GST law.</li>
            </ul>
        </div>

        <div style="text-align: right;">
            <div style="margin-bottom: 2.5rem; color: var(--slate-400);">For Vaishnavi Tours</div>
            <div style="font-weight: 800; color: var(--dark-900); text-transform: uppercase;">Authorized Signatory</div>
            <div style="color: var(--slate-500); font-size: 0.75rem;">Bilaspur Head Office</div>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background: #fff !important;
    }
    header, .no-print, footer, .admin-sidebar, .admin-topbar {
        display: none !important;
    }
    .invoice-sheet {
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
}
</style>
@endsection
