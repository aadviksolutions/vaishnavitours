@extends('layouts.admin')

@section('title', 'Payments Ledger')
@section('page_title', 'Payments & Collections')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0;">Financial Payments Ledger</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Comprehensive record of cash collections, UPI transfers, card payments, and receipts.</p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary btn-sm">+ Record Payment</a>
    </div>
</div>

<!-- KPI Banner -->
<div class="card mb-4" style="background: linear-gradient(135deg, var(--dark-900) 0%, #1e293b 100%); color: #fff; padding: 1.5rem 2rem;">
    <div class="d-flex justify-between align-center flex-wrap gap-3">
        <div>
            <div style="color: var(--slate-400); font-size: 0.85rem; text-transform: uppercase; font-weight: 700;">Total Collections Recorded</div>
            <div style="font-size: 2.25rem; font-weight: 800; color: var(--primary); margin-top: 4px;">
                ₹{{ number_format($totalCollected, 2) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--slate-300); margin-top: 2px;">Verified successful payments across all travel bookings</div>
        </div>
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline" style="color: #fff; border-color: rgba(255,255,255,0.3);">
                View Tax Invoices →
            </a>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.payments.index') }}" class="d-flex align-center gap-3 flex-wrap">
        <div style="min-width: 200px;">
            <select name="method" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Payment Methods --</option>
                @foreach(['Cash', 'UPI / QR', 'Bank Transfer', 'Card', 'Online Gateway'] as $m)
                    <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <div style="min-width: 180px;">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Statuses --</option>
                @foreach(['Success', 'Pending', 'Failed', 'Refunded'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        @if(request()->hasAny(['method', 'status']))
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Booking Ref</th>
                    <th>Customer</th>
                    <th>Amount Paid</th>
                    <th>Method</th>
                    <th>Transaction Ref</th>
                    <th>Status</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td style="font-family: monospace; font-size: 0.85rem; font-weight: 700;">
                            {{ $p->payment_id }}
                        </td>
                        <td>
                            <a href="{{ route('admin.bookings.show', $p->booking_id) }}" style="font-weight: 800; color: var(--primary-dark);">
                                #{{ $p->booking->booking_id ?? $p->booking_id }}
                            </a>
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 0.85rem;">{{ $p->customer->name ?? 'Customer' }}</div>
                            <div style="font-size: 0.75rem; color: var(--slate-500);">{{ $p->customer->phone ?? '' }}</div>
                        </td>
                        <td style="font-weight: 800; font-size: 1rem; color: #16a34a;">
                            ₹{{ number_format($p->amount, 2) }}
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $p->payment_method }}</span>
                        </td>
                        <td style="font-family: monospace; font-size: 0.8rem; color: var(--slate-600);">
                            {{ $p->transaction_id ?? 'N/A' }}
                        </td>
                        <td>
                            <span class="badge 
                                {{ $p->status === 'Success' ? 'badge-success' : '' }}
                                {{ $p->status === 'Pending' ? 'badge-warning' : '' }}
                                {{ $p->status === 'Failed' ? 'badge-danger' : '' }}
                                {{ $p->status === 'Refunded' ? 'badge-secondary' : '' }}
                            ">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--slate-500);">
                            {{ $p->created_at->format('d M Y, h:i A') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color: var(--slate-500);">
                            No payment transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
