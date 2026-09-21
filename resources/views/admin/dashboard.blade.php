@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="mb-4 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-900);">Admin Dashboard</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Vaishnavi Tours operations, bookings, fleet status, and revenue analytics.</p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">+ New Booking</a>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-dark btn-sm">+ Record Payment</a>
    </div>
</div>

<!-- 4 Admin Dashboard Cards (Section 14) -->
<div class="grid grid-4 gap-3 mb-4">
    <div class="kpi-card" style="border-left: 4px solid var(--dark-900);">
        <div class="kpi-icon" style="background: var(--slate-100); color: var(--dark-900);">🚖</div>
        <div class="kpi-label">Total Bookings</div>
        <div class="kpi-value">{{ number_format($totalBookings) }}</div>
        <div class="kpi-sub">All-time reservations</div>
    </div>

    <div class="kpi-card" style="border-left: 4px solid var(--primary);">
        <div class="kpi-icon" style="background: var(--primary-light); color: var(--primary-dark);">🗓️</div>
        <div class="kpi-label">Today's Trips</div>
        <div class="kpi-value">{{ number_format($todaysTrips) }}</div>
        <div class="kpi-sub">{{ $upcomingTrips }} upcoming queued</div>
    </div>

    <div class="kpi-card" style="border-left: 4px solid #10B981;">
        <div class="kpi-icon" style="background: #ECFDF5; color: #059669;">💰</div>
        <div class="kpi-label">Revenue</div>
        <div class="kpi-value">₹{{ number_format($collectedAmount, 0) }}</div>
        <div class="kpi-sub">Total ₹{{ number_format($totalRevenue, 0) }} booked</div>
    </div>

    <div class="kpi-card" style="border-left: 4px solid #EF4444;">
        <div class="kpi-icon" style="background: #FEF2F2; color: #DC2626;">⚠️</div>
        <div class="kpi-label">Pending Payments</div>
        <div class="kpi-value">₹{{ number_format($pendingPayments, 0) }}</div>
        <div class="kpi-sub">Outstanding customer balance</div>
    </div>
</div>

<!-- Visual Analytics Row: Revenue Chart & Booking Status Chart (Section 14) -->
<div class="grid grid-3 gap-4 mb-4">
    <!-- Revenue Chart (2 Columns) -->
    <div style="grid-column: span 2;">
        <div class="card" style="padding: 1.5rem;">
            <div class="d-flex justify-between align-center mb-3">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dark-900); margin: 0;">📈 Revenue Chart</h3>
                    <p style="font-size: 0.8rem; color: var(--slate-500); margin: 2px 0 0 0;">Monthly collected revenue trend (INR)</p>
                </div>
                <span class="badge badge-available">Live Trend</span>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Booking Status Chart (1 Column) -->
    <div>
        <div class="card" style="padding: 1.5rem; height: 100%;">
            <div class="d-flex justify-between align-center mb-3">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dark-900); margin: 0;">📊 Booking Status</h3>
                    <p style="font-size: 0.8rem; color: var(--slate-500); margin: 2px 0 0 0;">Distribution by trip status</p>
                </div>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Operational Highlights: Fleet, Drivers, Notifications -->
<div class="grid grid-3 gap-3 mb-4">
    <div class="card d-flex align-center gap-3" style="padding: 1.25rem;">
        <div style="font-size: 2rem;">🚗</div>
        <div>
            <div style="font-size: 0.775rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase;">Fleet Availability</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--dark-900);">
                <span style="color: #10B981;">{{ $availableVehicles }} Available</span> / {{ $activeVehicles }} Total
            </div>
            <a href="{{ route('admin.vehicles.index') }}" style="font-size: 0.8rem; color: var(--primary-dark); font-weight: 700;">Manage Fleet →</a>
        </div>
    </div>

    <div class="card d-flex align-center gap-3" style="padding: 1.25rem;">
        <div style="font-size: 2rem;">👨‍✈️</div>
        <div>
            <div style="font-size: 0.775rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase;">Chauffeurs</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--dark-900);">
                {{ $activeDrivers }} Active Drivers
            </div>
            <a href="{{ route('admin.drivers.index') }}" style="font-size: 0.8rem; color: var(--primary-dark); font-weight: 700;">Manage Drivers →</a>
        </div>
    </div>

    <div class="card d-flex align-center gap-3" style="padding: 1.25rem;">
        <div style="font-size: 2rem;">🔔</div>
        <div>
            <div style="font-size: 0.775rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase;">Notifications</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: {{ $unreadNotificationsCount > 0 ? '#EF4444' : '#10B981' }};">
                {{ $unreadNotificationsCount }} Unread Alerts
            </div>
            <a href="{{ route('admin.notifications.index') }}" style="font-size: 0.8rem; color: var(--primary-dark); font-weight: 700;">Notifications Desk →</a>
        </div>
    </div>
</div>

<!-- Recent Bookings & Recent Payments (Section 14) -->
<div class="grid grid-3 gap-4">
    <!-- Left 2 Cols: Recent Bookings -->
    <div style="grid-column: span 2;">
        <div class="card">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: var(--dark-900);">🚖 Recent Bookings</h3>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline btn-sm">All Bookings →</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Route</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Fare</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" style="font-weight: 800; color: var(--dark-900);">
                                        #{{ $b->booking_id }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-size: 0.85rem;">{{ $b->customer->name ?? 'Guest' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--slate-500);">📞 {{ $b->customer->phone ?? '' }}</div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--dark-900);">
                                        {{ Str::limit($b->pickup_location, 14) }} ➔ {{ Str::limit($b->destination, 14) }}
                                    </div>
                                    <small style="color: var(--slate-500);">{{ $b->trip_type }}</small>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; font-weight: 600;">{{ $b->travel_date ? $b->travel_date->format('d M') : '' }}</div>
                                    <small style="color: var(--slate-500);">{{ date('h:i A', strtotime($b->travel_time)) }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-sm {{ $b->booking_status === 'Completed' ? 'badge-available' : ($b->booking_status === 'Cancelled' ? 'badge-cancelled' : 'badge-pending') }}">
                                        {{ $b->booking_status }}
                                    </span>
                                </td>
                                <td style="font-weight: 800; font-size: 0.9rem;">
                                    ₹{{ number_format($b->total_amount, 0) }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4" style="color: var(--slate-500);">No bookings recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right 1 Col: Recent Payments Ledger -->
    <div>
        <div class="card">
            <div class="d-flex justify-between align-center mb-3">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: var(--dark-900);">💳 Recent Payments</h3>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline btn-sm">Ledger →</a>
            </div>

            @forelse($recentPayments as $pay)
                <div style="border-bottom: 1px solid var(--slate-100); padding: 0.75rem 0;">
                    <div class="d-flex justify-between align-center">
                        <span style="font-weight: 700; font-size: 0.9rem; color: var(--dark-900);">₹{{ number_format($pay->amount, 2) }}</span>
                        <span class="badge {{ $pay->status === 'Success' ? 'badge-available' : 'badge-pending' }}" style="font-size: 0.7rem;">
                            {{ $pay->status }}
                        </span>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--slate-500); margin-top: 2px;">
                        #{{ $pay->booking->booking_id ?? 'Direct' }} • {{ $pay->payment_method }}
                    </div>
                    <div style="font-size: 0.725rem; color: var(--slate-400); margin-top: 1px;">
                        {{ $pay->created_at->format('d M Y, h:i A') }}
                    </div>
                </div>
            @empty
                <p style="color: var(--slate-500); font-size: 0.85rem; padding: 1.5rem 0; text-align: center;">No payments recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Revenue Chart
        const revCtx = document.getElementById('revenueChart');
        if (revCtx) {
            new Chart(revCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($revenueMonths ?? ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']) !!},
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: {!! json_encode($revenueData ?? [54000, 68000, 82000, 75000, 91000, 115000]) !!},
                        backgroundColor: '#F59E0B',
                        borderRadius: 6,
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: {
                                callback: function(value) { return '₹' + value.toLocaleString(); }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Booking Status Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const statusLabels = {!! json_encode(array_keys($statusCounts ?? [])) !!};
            const statusValues = {!! json_encode(array_values($statusCounts ?? [])) !!};

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels.length ? statusLabels : ['Confirmed', 'Pending', 'Completed'],
                    datasets: [{
                        data: statusValues.length ? statusValues : [12, 4, 25],
                        backgroundColor: ['#F59E0B', '#1E293B', '#10B981', '#0284C7', '#EF4444', '#64748B'],
                        borderWidth: 2,
                        borderColor: '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, font: { size: 10 } }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
