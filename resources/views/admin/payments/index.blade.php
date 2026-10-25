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

<!-- High-Contrast Financial Overview Cards -->
<div class="grid grid-3 gap-3 mb-4">
    <!-- Card 1: Total Collections Recorded (High-Contrast Slate Navy & Pure White/Emerald) -->
    <div class="card" style="background: #0F172A; border-left: 4px solid #10B981; padding: 1.25rem 1.5rem; border-radius: 8px;">
        <div class="d-flex justify-between align-center mb-1">
            <span style="color: #94A3B8; font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                Total Collections
            </span>
            <span style="background: rgba(16, 185, 129, 0.2); color: #34D399; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 20px;">
                Verified
            </span>
        </div>
        <div style="font-size: 2.15rem; font-weight: 900; color: #FFFFFF; letter-spacing: -0.5px; line-height: 1.2;">
            ₹{{ number_format($totalCollected, 2) }}
        </div>
        <div style="font-size: 0.8rem; color: #CBD5E1; margin-top: 6px; font-weight: 500;">
            <x-icon name="circle-check" :size="13" style="color: #10B981; vertical-align: middle; margin-right: 4px;" /> {{ $successfulCount ?? 0 }} verified payments recorded
        </div>
    </div>

    <!-- Card 2: Pending Balance To Collect -->
    <div class="card" style="background: #FFFFFF; border-left: 4px solid #F59E0B; padding: 1.25rem 1.5rem; border-radius: 8px; border: 1px solid var(--slate-200);">
        <div class="d-flex justify-between align-center mb-1">
            <span style="color: var(--slate-600); font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                Pending Balance Due
            </span>
            <span style="background: #FEF3C7; color: #92400E; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 20px;">
                To Collect
            </span>
        </div>
        <div style="font-size: 2.15rem; font-weight: 900; color: #B45309; letter-spacing: -0.5px; line-height: 1.2;">
            ₹{{ number_format($totalPendingBalance ?? 0, 2) }}
        </div>
        <div style="font-size: 0.8rem; color: var(--slate-500); margin-top: 6px; font-weight: 500;">
            <x-icon name="clock" :size="13" style="color: #F59E0B; vertical-align: middle; margin-right: 4px;" /> Across {{ $pendingBookingsCount ?? 0 }} bookings with outstanding due
        </div>
    </div>

    <!-- Card 3: Tax Invoices & Quick Record -->
    <div class="card" style="background: #FFFFFF; border-left: 4px solid #2563EB; padding: 1.25rem 1.5rem; border-radius: 8px; border: 1px solid var(--slate-200); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="color: var(--slate-600); font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                Billing & Accounts Desk
            </div>
            <div style="font-size: 0.85rem; color: var(--slate-600); margin-top: 6px; line-height: 1.4;">
                Generate official GST invoices, track cash advances, and issue payment receipts.
            </div>
        </div>
        <div class="d-flex gap-2 mt-3 flex-wrap">
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline btn-sm" style="font-weight: 700; font-size: 0.8rem; color: #1E40AF; border-color: #93C5FD; background: #EFF6FF;">
                <x-icon name="receipt" :size="13" class="me-1 inline" /> Tax Invoices
            </a>
            <a href="{{ route('admin.payments.create') }}" class="btn btn-primary btn-sm" style="font-weight: 700; font-size: 0.8rem;">
                + Record Payment
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
