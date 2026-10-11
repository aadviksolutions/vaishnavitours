@extends('layouts.admin')

@section('title', $categoryMeta['title'] . ' — Reports & Analytics')
@section('page_title', 'Business Reports & Analytics')

@section('content')
<div class="mb-4 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-900);">
            📊 Business Reports & Analytics
        </h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">
            Real-time business intelligence, category-wise operational ledgers, Excel (.xlsx) and PDF export center.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.reports.export.excel', request()->all()) }}" class="btn btn-outline btn-sm" style="color: #059669; border-color: #10B981; font-weight: 700;">
            📥 Download Excel (.xlsx)
        </a>
        <a href="{{ route('admin.reports.export.pdf', request()->all()) }}" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #EF4444; font-weight: 700;">
            📄 Download PDF (.pdf)
        </a>
        <a href="{{ route('admin.reports.print', request()->all()) }}" target="_blank" class="btn btn-primary btn-sm" style="font-weight: 700;">
            🖨️ Print View
        </a>
    </div>
</div>

<!-- Category Switcher Nav Tabs -->
<div class="card mb-4" style="padding: 1rem 1.25rem;">
    <div class="d-flex justify-between align-center mb-2">
        <span style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--slate-500); letter-spacing: 0.5px;">
            Select Report Category (18 Categories Supported)
        </span>
        <span class="badge badge-primary">
            Active: {{ $categoryMeta['title'] }}
        </span>
    </div>

    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: thin;">
        @foreach($categories as $catKey => $cat)
            @php $isActive = ($currentCategory === $catKey); @endphp
            <a href="{{ route('admin.reports.index', array_merge(request()->except(['page']), ['category' => $catKey])) }}"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; white-space: nowrap; text-decoration: none; transition: all 0.2s; border: 1px solid {{ $isActive ? 'var(--primary)' : 'var(--slate-200)' }}; background: {{ $isActive ? 'var(--primary)' : '#fff' }}; color: {{ $isActive ? '#fff' : 'var(--slate-700)' }}; box-shadow: {{ $isActive ? '0 2px 4px rgba(37,99,235,0.2)' : 'none' }};">
                <span>{{ $cat['icon'] }}</span>
                <span>{{ $cat['title'] }}</span>
            </a>
        @endforeach
    </div>
</div>

<!-- Reusable Filter Toolbar -->
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.reports.index') }}" id="reportFilterForm">
        <input type="hidden" name="category" value="{{ $currentCategory }}">

        <div class="d-flex align-center justify-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-center gap-2">
                <span style="font-size: 1.2rem;">{{ $categoryMeta['icon'] }}</span>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0; color: var(--dark-900);">
                        {{ $categoryMeta['title'] }}
                    </h3>
                    <p style="font-size: 0.775rem; color: var(--slate-500); margin: 0;">
                        {{ $categoryMeta['description'] }}
                    </p>
                </div>
            </div>
            <div class="d-flex align-center gap-2">
                <span style="font-size: 0.775rem; color: var(--slate-500);">Current Period:</span>
                <span class="badge badge-secondary" style="font-size: 0.8rem; font-family: monospace;">
                    {{ date('d M Y', strtotime($data['fromDate'])) }} &ndash; {{ date('d M Y', strtotime($data['toDate'])) }}
                </span>
            </div>
        </div>

        <div class="grid grid-4 gap-3 mb-3">
            <!-- Preset Date Dropdown -->
            <div class="form-group">
                <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">Date Range Preset</label>
                <select name="preset" id="presetSelect" class="form-select form-select-sm" onchange="handlePresetChange(this.value)">
                    <option value="today" {{ $data['preset'] === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $data['preset'] === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $data['preset'] === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $data['preset'] === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $data['preset'] === 'last_month' ? 'selected' : '' }}>Previous Month</option>
                    <option value="this_year" {{ $data['preset'] === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $data['preset'] === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <!-- From Date -->
            <div class="form-group">
                <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">From Date</label>
                <input type="date" name="from_date" id="fromDateInput" class="form-control form-control-sm" value="{{ $data['fromDate'] }}">
            </div>

            <!-- To Date -->
            <div class="form-group">
                <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">To Date</label>
                <input type="date" name="to_date" id="toDateInput" class="form-control form-control-sm" value="{{ $data['toDate'] }}">
            </div>

            <!-- Search Keyword -->
            <div class="form-group">
                <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">Search Keyword</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="ID, name, phone, location..." value="{{ request('search') }}">
            </div>
        </div>

        <!-- Category Specific Filter Row -->
        <div class="d-flex align-center gap-3 flex-wrap pt-2" style="border-top: 1px dashed var(--slate-200);">
            @if(in_array($currentCategory, ['bookings', 'tour_packages', 'ambulance']))
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Booking Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'Completed', 'Cancelled'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Trip Type</label>
                    <select name="trip_type" class="form-select form-select-sm">
                        <option value="">All Trip Types</option>
                        @foreach(['One-Way', 'Round-Trip', 'Local Hourly', 'Airport Transfer', 'Tour Package', 'Ambulance Service'] as $tt)
                            <option value="{{ $tt }}" {{ request('trip_type') === $tt ? 'selected' : '' }}>{{ $tt }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 160px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Assigned Vehicle</label>
                    <select name="vehicle_id" class="form-select form-select-sm">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }} ({{ $v->registration_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 150px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Assigned Driver</label>
                    <select name="driver_id" class="form-select form-select-sm">
                        <option value="">All Drivers</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($currentCategory === 'customers')
                <div style="min-width: 160px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Customer Activity</label>
                    <select name="activity" class="form-select form-select-sm">
                        <option value="all">All Customers</option>
                        <option value="active" {{ request('activity') === 'active' ? 'selected' : '' }}>Has Bookings</option>
                        <option value="no_bookings" {{ request('activity') === 'no_bookings' ? 'selected' : '' }}>No Bookings Yet</option>
                    </select>
                </div>
            @elseif($currentCategory === 'vehicles')
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Fleet Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Maintenance" {{ request('status') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Vehicle Category</label>
                    <select name="vehicle_type" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach(['Hatchback', 'Sedan', 'SUV', 'Innova Crysta', 'Tempo Traveller', 'Ambulance'] as $vt)
                            <option value="{{ $vt }}" {{ request('vehicle_type') === $vt ? 'selected' : '' }}>{{ $vt }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($currentCategory === 'drivers')
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Driver Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="On Duty" {{ request('status') === 'On Duty' ? 'selected' : '' }}>On Duty</option>
                        <option value="Leave" {{ request('status') === 'Leave' ? 'selected' : '' }}>Leave</option>
                        <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            @elseif($currentCategory === 'trips')
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Trip Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Scheduled" {{ request('status') === 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="Started" {{ request('status') === 'Started' ? 'selected' : '' }}>Started / Running</option>
                        <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            @elseif($currentCategory === 'payments')
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Payment Type</label>
                    <select name="payment_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="advance" {{ request('payment_type') === 'advance' ? 'selected' : '' }}>Advance</option>
                        <option value="balance" {{ request('payment_type') === 'balance' ? 'selected' : '' }}>Balance</option>
                        <option value="full" {{ request('payment_type') === 'full' ? 'selected' : '' }}>Full Payment</option>
                        <option value="refund" {{ request('payment_type') === 'refund' ? 'selected' : '' }}>Refund</option>
                    </select>
                </div>
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Payment Method</label>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="upi" {{ request('payment_method') === 'upi' ? 'selected' : '' }}>UPI / QR</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                    </select>
                </div>
            @elseif($currentCategory === 'revenue')
                <div style="min-width: 160px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Aggregation Mode</label>
                    <select name="group_by" class="form-select form-select-sm">
                        <option value="daily" {{ request('group_by') === 'daily' ? 'selected' : '' }}>Daily Ledger</option>
                        <option value="vehicle_category" {{ request('group_by') === 'vehicle_category' ? 'selected' : '' }}>By Vehicle Category</option>
                        <option value="trip_type" {{ request('group_by') === 'trip_type' ? 'selected' : '' }}>By Trip Type</option>
                    </select>
                </div>
            @elseif($currentCategory === 'assignments')
                <div style="min-width: 160px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Assignment Status</label>
                    <select name="assignment_status" class="form-select form-select-sm">
                        <option value="">All Bookings</option>
                        <option value="fully_assigned" {{ request('assignment_status') === 'fully_assigned' ? 'selected' : '' }}>Fully Dispatched (Cab + Driver)</option>
                        <option value="unassigned" {{ request('assignment_status') === 'unassigned' ? 'selected' : '' }}>Completely Unassigned</option>
                        <option value="vehicle_only" {{ request('assignment_status') === 'vehicle_only' ? 'selected' : '' }}>Vehicle Only (Missing Driver)</option>
                        <option value="driver_only" {{ request('assignment_status') === 'driver_only' ? 'selected' : '' }}>Driver Only (Missing Vehicle)</option>
                    </select>
                </div>
            @elseif($currentCategory === 'document_expiries')
                <div style="min-width: 160px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Compliance Filter</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Monitored Documents</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired Documents Only</option>
                        <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>Expiring in 30 Days</option>
                    </select>
                </div>
            @elseif($currentCategory === 'invoices')
                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Invoice Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Paid" {{ request('status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                        <option value="Partial" {{ request('status') === 'Partial' ? 'selected' : '' }}>Partial</option>
                        <option value="Unpaid" {{ request('status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
            @endif

            <div style="margin-left: auto; display: flex; gap: 8px;">
                <button type="submit" class="btn btn-dark btn-sm" style="font-weight: 700;">
                    🔍 Apply Filters
                </button>
                <a href="{{ route('admin.reports.index', ['category' => $currentCategory]) }}" class="btn btn-outline btn-sm">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- KPI Summary Cards -->
@if(!empty($data['kpis']))
    <div class="grid grid-{{ min(count($data['kpis']), 5) }} gap-3 mb-4">
        @foreach($data['kpis'] as $kpi)
            <div class="kpi-card" style="border-left: 4px solid {{ $kpi['color'] ?? 'var(--primary)' }};">
                <div class="d-flex justify-between align-center mb-1">
                    <span class="kpi-label">{{ $kpi['label'] }}</span>
                    <span style="font-size: 1.1rem;">{{ $kpi['icon'] ?? '📊' }}</span>
                </div>
                <div class="kpi-value" style="color: {{ $kpi['color'] ?? 'var(--dark-900)' }};">
                    {{ $kpi['value'] }}
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- Results Data Table -->
<div class="card mb-4">
    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0;">
                {{ $categoryMeta['title'] }} Ledger
            </h3>
            <span style="font-size: 0.8rem; color: var(--slate-500);">
                @if(isset($data['paginator']))
                    Showing {{ $data['paginator']->firstItem() ?? 0 }} to {{ $data['paginator']->lastItem() ?? 0 }} of {{ number_format($data['paginator']->total()) }} records
                @else
                    Total {{ count($data['rows']) }} records found
                @endif
            </span>
        </div>

        <div class="d-flex align-center gap-2">
            <a href="{{ route('admin.reports.export.excel', request()->all()) }}" class="btn btn-outline btn-sm" style="color: #059669; border-color: #10B981; font-weight: 700;">
                📥 Excel (.xlsx)
            </a>
            <a href="{{ route('admin.reports.export.pdf', request()->all()) }}" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #EF4444; font-weight: 700;">
                📄 PDF
            </a>
            <a href="{{ route('admin.reports.print', request()->all()) }}" target="_blank" class="btn btn-secondary btn-sm" style="font-weight: 700;">
                🖨️ Print
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 35px;">#</th>
                    @foreach($data['columns'] as $colKey => $colLabel)
                        <th>{{ $colLabel }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($data['rows'] as $idx => $row)
                    @php 
                        $r = (array) $row; 
                        $rowNumber = isset($data['paginator']) ? ($data['paginator']->firstItem() + $idx) : ($idx + 1);
                    @endphp
                    <tr>
                        <td style="color: var(--slate-400); font-size: 0.8rem;">{{ $rowNumber }}</td>
                        @foreach($data['columns'] as $colKey => $colLabel)
                            @php $val = $r[$colKey] ?? ''; @endphp
                            <td>
                                @if(str_contains(strtolower($colKey), 'status') || $colKey === 'compliance' || $colKey === 'conflict_flag')
                                    @php
                                        $badgeClass = match($val) {
                                            'Completed', 'Confirmed', 'Paid', 'Success', 'Approved', 'VALID', 'CLEAR', 'ASSIGNED' => 'badge-primary',
                                            'Pending', 'Partial', 'EXPIRING SOON', 'EXPIRING IN 30 DAYS' => 'badge-warning',
                                            'Cancelled', 'Failed', 'EXPIRED', 'OVERLAP ALERT', 'UNASSIGNED' => 'badge-danger',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $val }}</span>
                                @elseif(str_contains(strtolower($colKey), 'amount') || str_contains(strtolower($colKey), 'fare') || str_contains(strtolower($colKey), 'billed') || str_contains(strtolower($colKey), 'paid') || str_contains(strtolower($colKey), 'balance') || str_contains(strtolower($colKey), 'subtotal') || str_contains(strtolower($colKey), 'realized'))
                                    <strong style="color: var(--slate-800);">
                                        {{ is_numeric($val) ? ('₹' . number_format((float) $val, 2)) : $val }}
                                    </strong>
                                @elseif($colKey === 'booking_id')
                                    <a href="{{ route('admin.bookings.index', ['search' => $val]) }}" style="font-weight: 800; color: var(--primary); font-family: monospace;">
                                        {{ $val }}
                                    </a>
                                @else
                                    {{ $val }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($data['columns']) + 1 }}" class="text-center py-5">
                            <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">📭</div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.25rem;">No Matching Records Found</h4>
                            <p style="color: var(--slate-500); font-size: 0.875rem; margin: 0;">
                                No database records found in <strong>{{ $categoryMeta['title'] }}</strong> for the selected date range and filter criteria.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($data['paginator']) && $data['paginator']->hasPages())
        <div class="card-footer" style="padding: 1rem 1.25rem; background: var(--slate-50); border-top: 1px solid var(--slate-200);">
            {{ $data['paginator']->links() }}
        </div>
    @endif
</div>

<script>
    function handlePresetChange(val) {
        if (val !== 'custom') {
            document.getElementById('reportFilterForm').submit();
        }
    }
</script>
@endsection
