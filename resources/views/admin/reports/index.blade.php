@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('page_title', 'Analytics & Revenue Reports')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0;">Business Reports & Trip Analytics</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Analyze revenue streams, fleet utilization, and driver performance over custom date ranges.</p>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="d-flex align-center gap-3 flex-wrap">
        <div>
            <label class="form-label" style="font-size: 0.775rem;">From Date</label>
            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
        </div>

        <div>
            <label class="form-label" style="font-size: 0.775rem;">To Date</label>
            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
        </div>

        <div style="align-self: flex-end;">
            <button type="submit" class="btn btn-dark btn-sm">📊 Generate Analytics</button>
        </div>
    </form>
</div>

<!-- KPI Cards -->
<div class="grid grid-3 gap-3 mb-4">
    <div class="kpi-card">
        <div class="kpi-icon" style="background: #eff6ff; color: #2563eb;">🚖</div>
        <div class="kpi-label">Bookings in Range</div>
        <div class="kpi-value">{{ $totalBookings }}</div>
        <div class="kpi-sub">{{ $completedBookings }} fulfilled • {{ $cancelledBookings }} cancelled</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: #f0fdf4; color: #16a34a;">💰</div>
        <div class="kpi-label">Total Revenue Booked</div>
        <div class="kpi-value">₹{{ number_format($totalRevenue, 2) }}</div>
        <div class="kpi-sub">₹{{ number_format($collectedRevenue, 2) }} successfully collected</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: #fef2f2; color: #dc2626;">⚠️</div>
        <div class="kpi-label">Outstanding Balances</div>
        <div class="kpi-value">₹{{ number_format($outstandingAmount, 2) }}</div>
        <div class="kpi-sub">Receivable from passengers</div>
    </div>
</div>

<!-- Vehicle Utilization & Driver Performance Tables -->
<div class="grid grid-2 gap-4 mb-4">
    <!-- Vehicle Utilization -->
    <div class="card">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem;">🚗 Fleet Vehicle Utilization</h3>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Category</th>
                        <th>Reg. No</th>
                        <th>Trips in Range</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicleStats as $v)
                        <tr>
                            <td><strong>{{ $v->name }}</strong></td>
                            <td><span class="badge badge-secondary">{{ $v->vehicle_type }}</span></td>
                            <td style="font-family: monospace;">{{ $v->registration_number }}</td>
                            <td>
                                <span class="badge {{ $v->bookings_count > 0 ? 'badge-primary' : 'badge-outline' }}">
                                    {{ $v->bookings_count }} trips
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-3">No vehicles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Driver Trips -->
    <div class="card">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem;">👨‍✈️ Chauffeur Trip Performance</h3>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Chauffeur</th>
                        <th>Mobile</th>
                        <th>Rating</th>
                        <th>Trips Done</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($driverStats as $d)
                        <tr>
                            <td><strong>{{ $d->name }}</strong></td>
                            <td>{{ $d->mobile }}</td>
                            <td style="color: #d97706; font-weight: 700;">⭐ {{ number_format($d->rating, 1) }}</td>
                            <td>
                                <span class="badge {{ $d->bookings_count > 0 ? 'badge-success' : 'badge-outline' }}">
                                    {{ $d->bookings_count }} trips
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-3">No drivers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Daily Booking & Revenue Breakdown -->
<div class="card">
    <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem;">📅 Day-by-Day Revenue Breakdown</h3>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bookings Volume</th>
                    <th>Gross Daily Revenue (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dateWiseBookings as $day)
                    <tr>
                        <td>
                            <strong>{{ \Carbon\Carbon::parse($day->travel_date)->format('d M Y (l)') }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $day->bookings_count }} bookings</span>
                        </td>
                        <td style="font-weight: 800; font-size: 1rem; color: #16a34a;">
                            ₹{{ number_format($day->daily_revenue, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4" style="color: var(--slate-500);">
                            No bookings recorded in this date window.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
