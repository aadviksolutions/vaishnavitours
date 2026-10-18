@extends('layouts.admin')

@section('title', 'Fleet & Trip Expenses - Vaishnavi Tours')
@section('page_title', 'Expense Management')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <h1 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--dark-900);">Fleet & Trip Expenses</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 12.5px;">
            Record and audit Fuel, Toll, Parking, and Maintenance expenses linked to specific bookings and vehicles.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm">
            + Add New Expense
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px;">
        ✓ {{ session('success') }}
    </div>
@endif

<!-- Expense Category KPI Cards -->
<div class="grid grid-4 gap-2 mb-3">
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Total Logged Expenses</span>
        <div style="font-size: 20px; font-weight: 700; color: var(--dark-900);">₹{{ number_format($totals['total'], 2) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #D97706;">Fuel Total</span>
        <div style="font-size: 20px; font-weight: 700; color: #D97706;">₹{{ number_format($totals['fuel'], 2) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #2563EB;">Toll & Parking</span>
        <div style="font-size: 20px; font-weight: 700; color: #2563EB;">₹{{ number_format($totals['toll'], 2) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Other Expenses</span>
        <div style="font-size: 20px; font-weight: 700; color: var(--slate-700);">₹{{ number_format($totals['other'], 2) }}</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-3" style="padding: 0.75rem 1rem; background: #fff; border: 1px solid var(--slate-200); border-radius: 6px;">
    <form method="GET" action="{{ route('admin.expenses.index') }}" class="d-flex align-center gap-2 flex-wrap">
        <div style="min-width: 170px;">
            <select name="category" class="form-select form-select-sm">
                <option value="">All Expense Categories</option>
                <option value="Fuel" {{ request('category') === 'Fuel' ? 'selected' : '' }}>Fuel</option>
                <option value="Toll" {{ request('category') === 'Toll' ? 'selected' : '' }}>Toll</option>
                <option value="Other" {{ request('category') === 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div style="min-width: 150px;">
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From date">
        </div>
        <div style="min-width: 150px;">
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To date">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter Expenses</button>
        @if(request()->hasAny(['category', 'date_from', 'date_to', 'booking_id']))
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Expenses Table -->
<div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; font-size: 12.5px;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Amount (₹)</th>
                    <th>Linked Booking / Vehicle</th>
                    <th>Description</th>
                    <th>Payment Method</th>
                    <th>Logged By</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td style="white-space: nowrap; font-weight: 500;">
                            {{ \Carbon\Carbon::parse($exp->expense_date)->format('d M Y') }}
                        </td>
                        <td>
                            @php
                                $badge = match($exp->category) {
                                    'Fuel' => 'bg-amber-100 text-amber-800',
                                    'Toll' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-slate-100 text-slate-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $badge }}">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td style="font-weight: 700; color: #DC2626;">
                            ₹{{ number_format($exp->amount, 2) }}
                        </td>
                        <td>
                            @if($exp->booking)
                                <a href="{{ route('admin.bookings.show', $exp->booking_id) }}" style="font-weight: 600; color: #2563EB;">
                                    {{ $exp->booking->booking_id }}
                                </a>
                            @endif
                            @if($exp->vehicle)
                                <div style="font-size: 11px; color: var(--slate-500);">
                                    {{ $exp->vehicle->name }} ({{ $exp->vehicle->registration_number }})
                                </div>
                            @endif
                            @if(!$exp->booking && !$exp->vehicle)
                                <span style="color: var(--slate-400);">General Fleet</span>
                            @endif
                        </td>
                        <td style="max-width: 250px;">
                            {{ $exp->description ?: 'N/A' }}
                            @if($exp->receipt_number)
                                <div style="font-size: 10.5px; color: var(--slate-500);">Ref: {{ $exp->receipt_number }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 10.5px;">{{ $exp->payment_method ?: 'Cash' }}</span>
                        </td>
                        <td style="font-size: 11.5px; color: var(--slate-600);">
                            {{ $exp->creator?->name ?? 'Admin' }}
                        </td>
                        <td style="text-align: right;">
                            <form action="{{ route('admin.expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Delete this expense record?');" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm text-red-600 border-red-300" style="padding: 2px 6px; font-size: 11px;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--slate-500);">
                            No expenses logged yet. Click <strong>+ Add New Expense</strong> to record fuel, toll or maintenance costs.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
        <div style="padding: 0.75rem 1rem; border-top: 1px solid var(--slate-200);">
            {{ $expenses->links() }}
        </div>
    @endif
</div>
@endsection
