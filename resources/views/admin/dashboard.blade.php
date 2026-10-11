@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="mb-3 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 18px; font-weight: 600; margin: 0; color: var(--dark-900);">Admin Dashboard</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 12px;">Vaishnavi Tours operations, reservations dispatch, fleet telemetry, and revenue.</p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">+ New Booking</a>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-dark btn-sm">+ Record Payment</a>
    </div>
</div>

<!-- 4 Admin Dashboard Cards -->
<div class="grid grid-4 gap-2 mb-3">
    <div class="kpi-card" style="border-left: 3px solid var(--dark-900);">
        <div class="d-flex justify-between align-center">
            <span class="kpi-label">Total Bookings</span>
            <div class="kpi-icon" style="background: var(--slate-100); color: var(--dark-900);">
                <x-icon name="calendar-check" size="14" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($totalBookings) }}</div>
        <div class="kpi-sub">All-time reservations</div>
    </div>

    <div class="kpi-card" style="border-left: 3px solid var(--primary);">
        <div class="d-flex justify-between align-center">
            <span class="kpi-label">Today's Trips</span>
            <div class="kpi-icon" style="background: var(--primary-light); color: var(--primary);">
                <x-icon name="calendar" size="14" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($todaysTrips) }}</div>
        <div class="kpi-sub">{{ $upcomingTrips }} upcoming queued</div>
    </div>

    <div class="kpi-card" style="border-left: 3px solid var(--success);">
        <div class="d-flex justify-between align-center">
            <span class="kpi-label">Revenue Collected</span>
            <div class="kpi-icon" style="background: var(--success-bg); color: var(--success-text);">
                <x-icon name="indian-rupee" size="14" />
            </div>
        </div>
        <div class="kpi-value">₹{{ number_format($collectedAmount, 0) }}</div>
        <div class="kpi-sub">Total ₹{{ number_format($totalRevenue, 0) }} booked</div>
    </div>

    <div class="kpi-card" style="border-left: 3px solid var(--danger);">
        <div class="d-flex justify-between align-center">
            <span class="kpi-label">Pending Payments</span>
            <div class="kpi-icon" style="background: var(--danger-bg); color: var(--danger-text);">
                <x-icon name="alert-circle" size="14" />
            </div>
        </div>
        <div class="kpi-value">₹{{ number_format($pendingPayments, 0) }}</div>
        <div class="kpi-sub">Outstanding customer balance</div>
    </div>
</div>

<!-- Operations Performance Matrix -->
<div class="card mb-3" style="padding: 1rem; background: #fff;">
    <div class="d-flex justify-between align-center mb-2">
        <h3 style="font-size: 13.5px; font-weight: 600; color: var(--dark-900); margin: 0; display: flex; align-items: center; gap: 6px;">
            <x-icon name="trending-up" size="14" /> Operations Performance Matrix (Daily / Weekly / Monthly)
        </h3>
        <span class="badge badge-secondary" style="font-size: 10.5px;">Real-time Telemetry</span>
    </div>

    <div class="grid grid-3 gap-2">
        <!-- Daily -->
        <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 6px; padding: 0.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-600);">Daily (Today)</span>
                <span class="badge badge-primary" style="font-size: 10px;">Today</span>
            </div>
            <div style="font-size: 18px; font-weight: 600; color: var(--dark-900);">
                {{ $dailyStats['bookings'] }} <span style="font-size: 12px; font-weight: 400; color: var(--slate-500);">New Bookings</span>
            </div>
            <div style="font-size: 11.5px; color: var(--slate-600); margin-top: 2px;">
                Trips Today: <strong>{{ $dailyStats['travels_today'] }}</strong> ({{ $dailyStats['completed'] }} completed)
            </div>
            <div style="font-size: 12px; font-weight: 600; color: var(--success-text); margin-top: 4px;">
                Collected Today: ₹{{ number_format($dailyStats['revenue'], 2) }}
            </div>
        </div>

        <!-- Weekly -->
        <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 6px; padding: 0.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-600);">Weekly (This Week)</span>
                <span class="badge badge-success" style="font-size: 10px;">Mon – Sun</span>
            </div>
            <div style="font-size: 18px; font-weight: 600; color: var(--dark-900);">
                {{ $weeklyStats['bookings'] }} <span style="font-size: 12px; font-weight: 400; color: var(--slate-500);">Bookings</span>
            </div>
            <div style="font-size: 11.5px; color: var(--slate-600); margin-top: 2px;">
                Completed Trips: <strong>{{ $weeklyStats['completed'] }}</strong>
            </div>
            <div style="font-size: 12px; font-weight: 600; color: var(--success-text); margin-top: 4px;">
                Collected This Week: ₹{{ number_format($weeklyStats['revenue'], 2) }}
            </div>
        </div>

        <!-- Monthly -->
        <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 6px; padding: 0.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-600);">Monthly ({{ now()->format('M Y') }})</span>
                <span class="badge badge-dark" style="font-size: 10px;">MTD</span>
            </div>
            <div style="font-size: 18px; font-weight: 600; color: var(--dark-900);">
                {{ $monthlyStats['bookings'] }} <span style="font-size: 12px; font-weight: 400; color: var(--slate-500);">Bookings</span>
            </div>
            <div style="font-size: 11.5px; color: var(--slate-600); margin-top: 2px;">
                Completed Trips: <strong>{{ $monthlyStats['completed'] }}</strong>
            </div>
            <div style="font-size: 12px; font-weight: 600; color: var(--success-text); margin-top: 4px;">
                Collected This Month: ₹{{ number_format($monthlyStats['revenue'], 2) }}
            </div>
        </div>
    </div>
</div>

<!-- Visual Analytics Row -->
<div class="grid grid-3 gap-2 mb-3">
    <!-- Revenue Chart (2 Columns) -->
    <div style="grid-column: span 2;">
        <div class="card" style="padding: 1rem;">
            <div class="d-flex justify-between align-center mb-2">
                <div>
                    <h3 style="font-size: 13.5px; font-weight: 600; color: var(--dark-900); margin: 0; display: flex; align-items: center; gap: 5px;">
                        <x-icon name="trending-up" size="14" /> Revenue Trend
                    </h3>
                    <p style="font-size: 11px; color: var(--slate-500); margin: 1px 0 0 0;">Monthly collected revenue trend (INR)</p>
                </div>
                <span class="badge badge-available">Live Trend</span>
            </div>
            <div style="height: 200px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Booking Status Chart (1 Column) -->
    <div>
        <div class="card" style="padding: 1rem; height: 100%;">
            <div class="d-flex justify-between align-center mb-2">
                <div>
                    <h3 style="font-size: 13.5px; font-weight: 600; color: var(--dark-900); margin: 0; display: flex; align-items: center; gap: 5px;">
                        <x-icon name="calendar" size="14" /> Booking Status
                    </h3>
                    <p style="font-size: 11px; color: var(--slate-500); margin: 1px 0 0 0;">Distribution by trip status</p>
                </div>
            </div>
            <div style="height: 180px; position: relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Operational Highlights: Fleet, Drivers, Notifications -->
<div class="grid grid-3 gap-2 mb-3">
    <div class="card d-flex align-center gap-2" style="padding: 0.875rem;">
        <div class="icon-box icon-box-sm icon-box-primary">
            <x-icon name="car-front" size="16" />
        </div>
        <div>
            <div style="font-size: 10.5px; font-weight: 600; color: var(--slate-500); text-transform: uppercase;">Fleet Availability</div>
            <div style="font-size: 14px; font-weight: 600; color: var(--dark-900);">
                <span style="color: var(--success-text);">{{ $availableVehicles }} Available</span> / {{ $activeVehicles }} Total
            </div>
            <a href="{{ route('admin.vehicles.index') }}" style="font-size: 11.5px; color: var(--primary); font-weight: 500;">Manage Fleet →</a>
        </div>
    </div>

    <div class="card d-flex align-center gap-2" style="padding: 0.875rem;">
        <div class="icon-box icon-box-sm icon-box-primary">
            <x-icon name="user-check" size="16" />
        </div>
        <div>
            <div style="font-size: 10.5px; font-weight: 600; color: var(--slate-500); text-transform: uppercase;">Chauffeurs</div>
            <div style="font-size: 14px; font-weight: 600; color: var(--dark-900);">
                {{ $activeDrivers }} Active Drivers
            </div>
            <a href="{{ route('admin.drivers.index') }}" style="font-size: 11.5px; color: var(--primary); font-weight: 500;">Manage Drivers →</a>
        </div>
    </div>

    <div class="card d-flex align-center gap-2" style="padding: 0.875rem;">
        <div class="icon-box icon-box-sm {{ $unreadNotificationsCount > 0 ? 'icon-box-maroon' : 'icon-box-primary' }}">
            <x-icon name="bell" size="16" />
        </div>
        <div>
            <div style="font-size: 10.5px; font-weight: 600; color: var(--slate-500); text-transform: uppercase;">Notifications</div>
            <div style="font-size: 14px; font-weight: 600; color: {{ $unreadNotificationsCount > 0 ? 'var(--danger)' : 'var(--success-text)' }};">
                {{ $unreadNotificationsCount }} Unread Alerts
            </div>
            <a href="{{ route('admin.notifications.index') }}" style="font-size: 11.5px; color: var(--primary); font-weight: 500;">Notifications Desk →</a>
        </div>
    </div>
</div>

<!-- Recent Bookings & Recent Payments -->
<div class="grid grid-3 gap-3">
    <!-- Left 2 Cols: Recent Bookings -->
    <div style="grid-column: span 2;">
        <div class="card">
            <div class="d-flex justify-between align-center mb-2">
                <h3 style="font-size: 13.5px; font-weight: 600; margin: 0; color: var(--dark-900); display: flex; align-items: center; gap: 5px;">
                    <x-icon name="calendar-check" size="14" /> Recent Bookings
                </h3>
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
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" style="font-weight: 600; color: var(--dark-900);">
                                        #{{ $b->booking_id }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight: 500; font-size: 12.5px;">{{ $b->customer->name ?? 'Guest' }}</div>
                                    <div style="font-size: 11px; color: var(--slate-500);">{{ $b->customer->phone ?? '' }}</div>
                                </td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 500; color: var(--dark-900);">
                                        {{ Str::limit($b->pickup_location, 14) }} → {{ Str::limit($b->destination, 14) }}
                                    </div>
                                    <small style="color: var(--slate-500);">{{ $b->trip_type }}</small>
                                </td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 500;">{{ $b->travel_date ? $b->travel_date->format('d M') : '' }}</div>
                                    <small style="color: var(--slate-500);">{{ date('h:i A', strtotime($b->travel_time)) }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-sm {{ $b->booking_status === 'Completed' ? 'badge-available' : ($b->booking_status === 'Cancelled' ? 'badge-cancelled' : 'badge-pending') }}">
                                        {{ $b->booking_status }}
                                    </span>
                                </td>
                                <td style="font-weight: 600; font-size: 12.5px;">
                                    ₹{{ number_format($b->total_amount, 0) }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-outline btn-sm" style="height: 26px; padding: 0 6px; font-size: 11px;">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3" style="color: var(--slate-500);">No bookings recorded yet.</td>
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
            <div class="d-flex justify-between align-center mb-2">
                <h3 style="font-size: 13.5px; font-weight: 600; margin: 0; color: var(--dark-900); display: flex; align-items: center; gap: 5px;">
                    <x-icon name="credit-card" size="14" /> Recent Payments
                </h3>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline btn-sm">Ledger →</a>
            </div>

            @forelse($recentPayments as $pay)
                <div style="border-bottom: 1px solid var(--slate-100); padding: 0.5rem 0;">
                    <div class="d-flex justify-between align-center">
                        <span style="font-weight: 600; font-size: 12.5px; color: var(--dark-900);">₹{{ number_format($pay->amount, 2) }}</span>
                        <span class="badge {{ $pay->status === 'Success' ? 'badge-available' : 'badge-pending' }}" style="font-size: 10px;">
                            {{ $pay->status }}
                        </span>
                    </div>
                    <div style="font-size: 11px; color: var(--slate-500); margin-top: 1px;">
                        #{{ $pay->booking->booking_id ?? 'Direct' }} • {{ $pay->payment_method }}
                    </div>
                    <div style="font-size: 10.5px; color: var(--slate-400); margin-top: 1px;">
                        {{ $pay->created_at->format('d M Y, h:i A') }}
                    </div>
                </div>
            @empty
                <p style="color: var(--slate-500); font-size: 12px; padding: 1rem 0; text-align: center;">No payments recorded yet.</p>
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
                        backgroundColor: '#0F2744',
                        borderRadius: 4,
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
