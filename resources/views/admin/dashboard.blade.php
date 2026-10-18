@extends('layouts.admin')

@section('title', 'Operations Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .kpi-grid-11 {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 0.85rem;
    }
    .quick-actions-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        background: #fff;
        padding: 0.85rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--slate-200);
        margin-bottom: 1.25rem;
    }
    .quick-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.85rem;
        font-size: 12.5px;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s ease-in-out;
    }
    .dash-kpi-card {
        background: #fff;
        border: 1px solid var(--slate-200);
        border-radius: 8px;
        padding: 0.85rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .dash-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .dash-kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 3.5px;
    }
    .dash-kpi-card.color-blue::before { background: #2563EB; }
    .dash-kpi-card.color-indigo::before { background: #4F46E5; }
    .dash-kpi-card.color-amber::before { background: #D97706; }
    .dash-kpi-card.color-rose::before { background: #E11D48; }
    .dash-kpi-card.color-emerald::before { background: #059669; }
    .dash-kpi-card.color-teal::before { background: #0D9488; }
    .dash-kpi-card.color-purple::before { background: #9333EA; }
    .dash-kpi-card.color-orange::before { background: #EA580C; }
    .dash-kpi-card.color-red::before { background: #DC2626; }
    .dash-kpi-card.color-slate::before { background: #475569; }
    .dash-kpi-card.color-cyan::before { background: #0891B2; }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="mb-3 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--dark-900);">Vaishnavi Tour — Operations Dispatch & Command</h1>
        <p style="color: var(--slate-500); margin: 3px 0 0 0; font-size: 12.5px;">
            Real-time fleet telemetry, instant driver assignments, booking tracking & GST billing.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="d-flex align-center gap-1" style="background: #fff; padding: 2px 8px; border-radius: 6px; border: 1px solid var(--slate-200);">
            <x-icon name="search" size="14" class="text-slate-400" />
            <input type="text" name="search" placeholder="Search Booking / Phone..." style="border: none; outline: none; font-size: 12px; width: 170px; padding: 4px;" />
            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 2px 8px; font-size: 11px;">Find</button>
        </form>
    </div>
</div>

<!-- Section 2: 10 Quick-Action Buttons (Clear buttons, minimal clicks) -->
<div class="quick-actions-bar">
    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: var(--slate-500); display: flex; align-items: center; margin-right: 0.5rem;">
        <x-icon name="zap" size="13" class="text-amber-500 mr-1" /> Quick Actions:
    </span>
    <a href="{{ route('admin.bookings.create') }}" class="quick-action-btn bg-emerald-600 hover:bg-emerald-700 text-white">
        <x-icon name="calendar-check" size="14" /> + Create Booking
    </a>
    <a href="{{ route('admin.bookings.index', ['filter' => 'unassigned_driver']) }}" class="quick-action-btn bg-indigo-600 hover:bg-indigo-700 text-white">
        <x-icon name="user-check" size="14" /> Assign Driver
    </a>
    <a href="{{ route('admin.bookings.index', ['filter' => 'unassigned_vehicle']) }}" class="quick-action-btn bg-blue-600 hover:bg-blue-700 text-white">
        <x-icon name="car-front" size="14" /> Assign Vehicle
    </a>
    <a href="{{ route('admin.drivers.create') }}" class="quick-action-btn bg-slate-700 hover:bg-slate-800 text-white">
        <x-icon name="user-round" size="14" /> + Add Driver
    </a>
    <a href="{{ route('admin.vehicles.create') }}" class="quick-action-btn bg-slate-700 hover:bg-slate-800 text-white">
        <x-icon name="truck" size="14" /> + Add Vehicle
    </a>
    <a href="{{ route('admin.locations.create') }}" class="quick-action-btn bg-amber-600 hover:bg-amber-700 text-white">
        <x-icon name="map-pin" size="14" /> + Add Pickup Location
    </a>
    <a href="{{ route('admin.tour-packages.create') }}" class="quick-action-btn bg-teal-600 hover:bg-teal-700 text-white">
        <x-icon name="compass" size="14" /> + Add Tour Package
    </a>
    <a href="{{ route('admin.expenses.create') }}" class="quick-action-btn bg-rose-600 hover:bg-rose-700 text-white">
        <x-icon name="dollar-sign" size="14" /> + Add Expense
    </a>
    <a href="{{ route('admin.invoices.index') }}" class="quick-action-btn bg-cyan-700 hover:bg-cyan-800 text-white">
        <x-icon name="receipt" size="14" /> Create / View Invoice
    </a>
    <a href="{{ route('admin.enquiries.index') }}" class="quick-action-btn bg-purple-600 hover:bg-purple-700 text-white">
        <x-icon name="mail" size="14" /> View Enquiries
    </a>
</div>

<!-- Section 2: 11 Functional Summary Cards -->
<div class="kpi-grid-11 mb-3">
    <!-- 1. Today's bookings -->
    <a href="{{ route('admin.bookings.index', ['date' => now()->toDateString()]) }}" class="dash-kpi-card color-blue">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Today's Bookings</span>
            <x-icon name="calendar" size="15" class="text-blue-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: var(--dark-900); margin: 0.25rem 0;">
            {{ number_format($todaysBookings) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Scheduled for today</span>
    </a>

    <!-- 2. Upcoming bookings -->
    <a href="{{ route('admin.bookings.index', ['filter' => 'upcoming']) }}" class="dash-kpi-card color-indigo">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Upcoming Bookings</span>
            <x-icon name="clock" size="15" class="text-indigo-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: var(--dark-900); margin: 0.25rem 0;">
            {{ number_format($upcomingBookings) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Future reserved trips</span>
    </a>

    <!-- 3. Pending enquiries -->
    <a href="{{ route('admin.enquiries.index', ['status' => 'New']) }}" class="dash-kpi-card color-amber">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Pending Enquiries</span>
            <x-icon name="mail" size="15" class="text-amber-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: var(--dark-900); margin: 0.25rem 0;">
            {{ number_format($pendingEnquiries) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Awaiting quotation/call</span>
    </a>

    <!-- 4. Unassigned bookings -->
    <a href="{{ route('admin.bookings.index', ['filter' => 'unassigned']) }}" class="dash-kpi-card color-rose">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #E11D48;">Unassigned Bookings</span>
            <x-icon name="alert-triangle" size="15" class="text-rose-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: #E11D48; margin: 0.25rem 0;">
            {{ number_format($unassignedBookings) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Needs vehicle or driver</span>
    </a>

    <!-- 5. Available vehicles -->
    <a href="{{ route('admin.vehicles.index', ['status' => 'Available']) }}" class="dash-kpi-card color-emerald">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Available Vehicles</span>
            <x-icon name="car-front" size="15" class="text-emerald-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: var(--dark-900); margin: 0.25rem 0;">
            {{ number_format($availableVehicles) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Ready for dispatch</span>
    </a>

    <!-- 6. Available drivers -->
    <a href="{{ route('admin.drivers.index', ['filter' => 'unassigned']) }}" class="dash-kpi-card color-teal">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Available Drivers</span>
            <x-icon name="user-check" size="15" class="text-teal-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: var(--dark-900); margin: 0.25rem 0;">
            {{ number_format($availableDrivers) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Unassigned & active</span>
    </a>

    <!-- 7. Ongoing trips -->
    <a href="{{ route('admin.trips.index', ['status' => 'Trip Started']) }}" class="dash-kpi-card color-purple">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Ongoing Trips</span>
            <x-icon name="navigation" size="15" class="text-purple-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: var(--dark-900); margin: 0.25rem 0;">
            {{ number_format($ongoingTrips) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">On the road right now</span>
    </a>

    <!-- 8. Incomplete trips -->
    <a href="{{ route('admin.trips.index', ['trip_status' => 'Open', 'payment_status' => 'pending_payment']) }}" class="dash-kpi-card color-orange">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #EA580C;">Incomplete Trips</span>
            <x-icon name="alert-circle" size="15" class="text-orange-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: #EA580C; margin: 0.25rem 0;">
            {{ number_format($incompleteTrips) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Open / Pending payment</span>
    </a>

    <!-- 9. Pending payments -->
    <a href="{{ route('admin.bookings.index', ['payment_status' => 'Pending']) }}" class="dash-kpi-card color-red">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #DC2626;">Pending Payments</span>
            <x-icon name="credit-card" size="15" class="text-red-600" />
        </div>
        <div style="font-size: 20px; font-weight: 700; color: #DC2626; margin: 0.25rem 0;">
            ₹{{ number_format($pendingPayments, 2) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Uncollected balances</span>
    </a>

    <!-- 10. Outstanding expenses -->
    <a href="{{ route('admin.expenses.index') }}" class="dash-kpi-card color-slate">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Trip Expenses</span>
            <x-icon name="dollar-sign" size="15" class="text-slate-600" />
        </div>
        <div style="font-size: 20px; font-weight: 700; color: var(--dark-900); margin: 0.25rem 0;">
            ₹{{ number_format($outstandingExpenses, 2) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">Fuel, toll, parking recorded</span>
    </a>

    <!-- 11. GST invoices -->
    <a href="{{ route('admin.invoices.index') }}" class="dash-kpi-card color-cyan">
        <div class="d-flex justify-between align-center">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">GST Invoices</span>
            <x-icon name="receipt" size="15" class="text-cyan-600" />
        </div>
        <div style="font-size: 24px; font-weight: 700; color: var(--dark-900); margin: 0.25rem 0;">
            {{ number_format($gstInvoicesCount) }}
        </div>
        <span style="font-size: 11px; color: var(--slate-500);">5% GST compliant tax bills</span>
    </a>
</div>

<!-- Active Bookings & Operational Dispatch Stream -->
<div class="card mb-3" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
    <div class="d-flex justify-between align-center mb-3 flex-wrap gap-2">
        <div>
            <h3 style="font-size: 15px; font-weight: 700; color: var(--dark-900); margin: 0; display: flex; align-items: center; gap: 6px;">
                <x-icon name="calendar-check" size="16" class="text-emerald-600" /> Latest Reservations & Dispatch Status
            </h3>
            <span style="font-size: 12px; color: var(--slate-500);">Shows category prefix (CORP, WEB, MAN, TP) and instant assignment status.</span>
        </div>
        <div class="d-flex align-center gap-1">
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline btn-sm">View All Bookings →</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" style="width: 100%; font-size: 12.5px;">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Category</th>
                    <th>Customer</th>
                    <th>Trip Details</th>
                    <th>Travel Date</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Fare / Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $b)
                <tr>
                    <td>
                        <a href="{{ route('admin.bookings.show', $b) }}" style="font-weight: 700; color: #2563EB;">
                            {{ $b->booking_id }}
                        </a>
                    </td>
                    <td>
                        @php
                            $catColors = [
                                'CORP' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'WEB' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'MAN' => 'bg-slate-100 text-slate-800 border-slate-200',
                                'TP' => 'bg-amber-100 text-amber-800 border-amber-200',
                            ];
                            $cClass = $catColors[$b->booking_category] ?? 'bg-slate-100 text-slate-800';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $cClass }}">
                            {{ $b->booking_category }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--dark-900);">{{ $b->customer?->name ?? 'Guest' }}</div>
                        <div style="font-size: 11px; color: var(--slate-500);">{{ $b->customer?->phone }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 500;">
                            {{ $b->pickup_location }}
                            @if($b->via_location)
                                <span style="color: var(--slate-400); font-size: 11px;">via {{ $b->via_location }}</span>
                            @endif
                            → {{ $b->destination }}
                        </div>
                        <div style="font-size: 11px; color: var(--slate-500);">
                            {{ $b->trip_type }} ({{ $b->vehicle_category }})
                        </div>
                    </td>
                    <td>
                        <div>{{ \Carbon\Carbon::parse($b->travel_date)->format('d M Y') }}</div>
                        <div style="font-size: 11px; color: var(--slate-500);">{{ $b->travel_time }}</div>
                    </td>
                    <td>
                        @if($b->vehicle)
                            <span class="badge badge-success">{{ $b->vehicle->name }}</span>
                            <div style="font-size: 10.5px; color: var(--slate-500);">{{ $b->vehicle->registration_number }}</div>
                        @else
                            <a href="{{ route('admin.bookings.show', $b) }}" class="badge badge-danger text-xs">+ Assign Vehicle</a>
                        @endif
                    </td>
                    <td>
                        @if($b->driver)
                            <span class="badge badge-success">{{ $b->driver->name }}</span>
                            <div style="font-size: 10.5px; color: var(--slate-500);">{{ $b->driver->mobile }}</div>
                        @else
                            <a href="{{ route('admin.bookings.show', $b) }}" class="badge badge-danger text-xs">+ Assign Driver</a>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 600;">₹{{ number_format($b->total_amount, 2) }}</div>
                        @if((float) $b->balance_amount > 0)
                            <div style="font-size: 11px; color: #DC2626; font-weight: 600;">Due: ₹{{ number_format($b->balance_amount, 2) }}</div>
                        @else
                            <div style="font-size: 11px; color: #059669; font-weight: 600;">Paid</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $b->booking_status }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.bookings.show', $b) }}" class="btn btn-primary btn-sm" style="padding: 2px 8px; font-size: 11.5px;">Manage</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 2rem; color: var(--slate-500);">
                        No reservations recorded yet. Click <strong>+ Create Booking</strong> above to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Revenue Trends & Financial Tracking -->
<div class="grid grid-2 gap-2 mb-3">
    <!-- Chart: 6 Months Revenue -->
    <div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
        <h4 style="font-size: 13.5px; font-weight: 700; margin: 0 0 0.75rem 0; color: var(--dark-900); display: flex; align-items: center; gap: 6px;">
            <x-icon name="trending-up" size="15" class="text-blue-600" /> Revenue Collection Trend (Last 6 Months)
        </h4>
        <canvas id="revenueChart" height="150"></canvas>
    </div>

    <!-- Recent Payments Collected -->
    <div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
        <div class="d-flex justify-between align-center mb-2">
            <h4 style="font-size: 13.5px; font-weight: 700; margin: 0; color: var(--dark-900); display: flex; align-items: center; gap: 6px;">
                <x-icon name="credit-card" size="15" class="text-emerald-600" /> Recent Collected Payments
            </h4>
            <a href="{{ route('admin.payments.index') }}" style="font-size: 11.5px; color: #2563EB;">All Payments →</a>
        </div>
        <div class="table-responsive">
            <table class="data-table" style="width: 100%; font-size: 12px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $p)
                    <tr>
                        <td>{{ $p->created_at->format('d M, h:i A') }}</td>
                        <td>
                            @if($p->booking)
                                <a href="{{ route('admin.bookings.show', $p->booking) }}" style="font-weight: 600; color: #2563EB;">
                                    {{ $p->booking->booking_id }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $p->customer?->name ?? 'Guest' }}</td>
                        <td style="font-weight: 700; color: #059669;">₹{{ number_format($p->amount, 2) }}</td>
                        <td><span class="badge badge-secondary">{{ $p->payment_method ?? 'Cash/UPI' }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--slate-500); padding: 1rem;">No payment transactions recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($revenueMonths),
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: @json($revenueData),
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#2563EB',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return '₹' + value.toLocaleString(); }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });
</script>
@endpush
