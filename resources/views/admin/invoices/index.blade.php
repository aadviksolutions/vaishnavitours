@extends('layouts.admin')

@section('title', 'Tax Invoices')
@section('page_title', 'Invoices & Billing')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0;">GST Invoices & Travel Receipts</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Official computer-generated tax invoices for corporate and retail passenger trips.</p>
    </div>
</div>

<!-- Filters Bar -->
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.invoices.index') }}" class="d-flex align-center gap-3 flex-wrap">
        <div style="min-width: 200px;">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Invoice Statuses --</option>
                @foreach(['Paid', 'Partially Paid', 'Unpaid'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        @if(request()->filled('status'))
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Booking Ref</th>
                    <th>Customer</th>
                    <th>Trip Route</th>
                    <th>Invoice Total</th>
                    <th>Paid Amount</th>
                    <th>Balance Due</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <a href="{{ route('admin.invoices.show', $inv->id) }}" style="font-weight: 800; color: var(--primary-dark);">
                                #{{ $inv->invoice_number }}
                            </a>
                            <div style="font-size: 0.725rem; color: var(--slate-400);">{{ $inv->issued_at ? \Carbon\Carbon::parse($inv->issued_at)->format('d M Y') : $inv->created_at->format('d M Y') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('admin.bookings.show', $inv->booking_id) }}" style="font-weight: 700; color: var(--dark-900);">
                                #{{ $inv->booking->booking_id ?? $inv->booking_id }}
                            </a>
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 0.85rem;">{{ $inv->customer->name ?? 'Customer' }}</div>
                            <div style="font-size: 0.75rem; color: var(--slate-500);">{{ $inv->customer->phone ?? '' }}</div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; font-weight: 600;">{{ $inv->booking->pickup_location ?? 'N/A' }} ➔ {{ $inv->booking->destination ?? 'N/A' }}</div>
                            <small style="color: var(--slate-500);">{{ $inv->booking->trip_type ?? '' }}</small>
                        </td>
                        <td style="font-weight: 800; font-size: 0.95rem;">
                            ₹{{ number_format($inv->total_amount, 2) }}
                        </td>
                        <td style="color: #16a34a; font-weight: 700;">
                            ₹{{ number_format($inv->paid_amount, 2) }}
                        </td>
                        <td style="color: {{ $inv->balance_amount > 0 ? '#dc2626' : '#16a34a' }}; font-weight: 800;">
                            ₹{{ number_format($inv->balance_amount, 2) }}
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
                        <td style="text-align: right;">
                            <a href="{{ route('admin.invoices.show', $inv->id) }}" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                📄 View / Print
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5" style="color: var(--slate-500);">
                            No invoices generated yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
