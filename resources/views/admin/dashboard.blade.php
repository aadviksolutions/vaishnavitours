@extends('layouts.admin')

@section('title', 'Operations Dashboard — Vaishnavi Tours')
@section('page_title', 'Operations Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* Enterprise Dashboard CSS */
    .dash-welcome {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .dash-welcome-title {
        font-size: 20px;
        font-weight: 800;
        color: #172033;
        margin: 0;
        line-height: 1.2;
    }
    .dash-welcome-sub {
        font-size: 13px;
        color: #64748B;
        margin: 3px 0 0 0;
    }
    .dash-date-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    /* Quick Action Toolbar */
    .action-toolbar {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-bottom: 1.25rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .action-toolbar-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748B;
        margin-right: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .action-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #2563EB;
        color: #FFFFFF !important;
        font-size: 12.5px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        border: 1px solid #1D4ED8;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .action-btn-primary:hover {
        background: #1D4ED8;
        transform: translateY(-1px);
    }
    .action-btn-neutral {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #FFFFFF;
        color: #172033 !important;
        font-size: 12.5px;
        font-weight: 500;
        padding: 6px 11px;
        border-radius: 6px;
        text-decoration: none;
        border: 1px solid #E2E8F0;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .action-btn-neutral:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #0F172A !important;
        transform: translateY(-1px);
    }

    /* 4-Col Responsive KPI Grid */
    .kpi-matrix {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 1100px) {
        .kpi-matrix {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 580px) {
        .kpi-matrix {
            grid-template-columns: 1fr;
        }
    }

    /* Enterprise White KPI Card */
    .kpi-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 1rem 1.15rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        position: relative;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: #CBD5E1;
    }
    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    .kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748B;
    }
    .kpi-icon-box {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #F1F5F9;
        color: #475569;
    }
    .kpi-value {
        font-size: 22px;
        font-weight: 800;
        color: #172033;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }
    .kpi-sub {
        font-size: 11.5px;
        color: #64748B;
        line-height: 1.3;
    }

    /* Semantic Tinting for urgent states */
    .kpi-card.urgent-attention {
        border-color: #FECACA;
        background: #FEF2F2;
    }
    .kpi-card.urgent-attention .kpi-icon-box {
        background: #FEE2E2;
        color: #B91C1C;
    }
    .kpi-card.urgent-attention .kpi-label {
        color: #991B1B;
    }
    .kpi-card.urgent-attention .kpi-value {
        color: #B91C1C;
    }

    .kpi-card.warning-attention {
        border-color: #FDE68A;
        background: #FFFDF5;
    }
    .kpi-card.warning-attention .kpi-icon-box {
        background: #FEF3C7;
        color: #B45309;
    }

    /* Enterprise White Card Container */
    .surface-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .surface-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #F1F5F9;
    }
    .surface-card-title {
        font-size: 15px;
        font-weight: 700;
        color: #172033;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .surface-card-sub {
        font-size: 12px;
        color: #64748B;
        margin: 2px 0 0 0;
    }

    /* SaaS Compact Table Styling */
    .saas-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }
    .saas-table thead th {
        background: #F8FAFC;
        color: #475569;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 12px;
        border-bottom: 1px solid #E2E8F0;
        text-align: left;
        white-space: nowrap;
    }
    .saas-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #F1F5F9;
        color: #172033;
        vertical-align: middle;
    }
    .saas-table tbody tr:hover td {
        background-color: #F8FAFC;
    }

    /* Category Pill */
    .cat-pill {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.3;
    }
    .cat-pill.corp { background: #EDE9FE; color: #5B21B6; }
    .cat-pill.web { background: #DBEAFE; color: #1E40AF; }
    .cat-pill.man { background: #F1F5F9; color: #475569; }
    .cat-pill.tp { background: #FEF3C7; color: #92400E; }
</style>
@endpush

@section('content')
<!-- Section 5: Dashboard Welcome Section -->
<div class="dash-welcome">
    <div>
        <h1 class="dash-welcome-title">Operations Dashboard</h1>
        <p class="dash-welcome-sub">Monitor bookings, fleet availability, trips and payments.</p>
    </div>
    <div class="dash-date-badge">
        <x-icon name="calendar" :size="14" style="color: #64748B;" />
        <span>{{ now()->format('l, d F Y') }}</span>
    </div>
</div>

<!-- Section 6: Action Toolbar (Consistent, restrained, enterprise-grade) -->
<div class="action-toolbar">
    <span class="action-toolbar-label">
        <x-icon name="zap" :size="13" style="color: #2563EB;" /> Quick Actions:
    </span>
    <!-- Primary Action: + Create Booking -->
    <a href="{{ route('admin.bookings.create') }}" class="action-btn-primary">
        <x-icon name="plus" :size="14" /> Create Booking
    </a>
    <!-- Neutral Secondary Actions -->
    <a href="{{ route('admin.bookings.index', ['filter' => 'unassigned_driver']) }}" class="action-btn-neutral">
        <x-icon name="user-check" :size="14" /> Assign Driver
    </a>
    <a href="{{ route('admin.bookings.index', ['filter' => 'unassigned_vehicle']) }}" class="action-btn-neutral">
        <x-icon name="car" :size="14" /> Assign Vehicle
    </a>
    <a href="{{ route('admin.customers.create') }}" class="action-btn-neutral">
        <x-icon name="user-plus" :size="14" /> Add Customer
    </a>
    <a href="{{ route('admin.locations.create') }}" class="action-btn-neutral">
        <x-icon name="map-pin" :size="14" /> Add Pickup Location
    </a>
    <a href="{{ route('admin.tour-packages.create') }}" class="action-btn-neutral">
        <x-icon name="compass" :size="14" /> Add Tour Package
    </a>
    <a href="{{ route('admin.expenses.create') }}" class="action-btn-neutral">
        <x-icon name="dollar-sign" :size="14" /> Add Expense
    </a>
    <a href="{{ route('admin.invoices.index') }}" class="action-btn-neutral">
        <x-icon name="receipt" :size="14" /> Create/View Invoice
    </a>
    <a href="{{ route('admin.enquiries.index') }}" class="action-btn-neutral">
        <x-icon name="inbox" :size="14" /> View Enquiries
    </a>
    <a href="{{ route('admin.drivers.create') }}" class="action-btn-neutral">
        <x-icon name="user" :size="14" /> Add Driver
    </a>
    <a href="{{ route('admin.vehicles.create') }}" class="action-btn-neutral">
        <x-icon name="plus" :size="14" /> Add Vehicle
    </a>
</div>

<!-- Section 8: 11 Functional KPI Cards in 4-Column Responsive Grid -->
<div class="kpi-matrix">
    <!-- 1. Today's Bookings -->
    <a href="{{ route('admin.bookings.index', ['date' => now()->toDateString()]) }}" class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Today's Bookings</span>
            <div class="kpi-icon-box" style="background: #EFF6FF; color: #2563EB;">
                <x-icon name="calendar-check" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($todaysBookings) }}</div>
        <div class="kpi-sub">Scheduled for today</div>
    </a>

    <!-- 2. Upcoming Bookings -->
    <a href="{{ route('admin.bookings.index', ['filter' => 'upcoming']) }}" class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Upcoming Bookings</span>
            <div class="kpi-icon-box" style="background: #EEF2FF; color: #4F46E5;">
                <x-icon name="clock" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($upcomingBookings) }}</div>
        <div class="kpi-sub">Future reserved trips</div>
    </a>

    <!-- 3. Pending Enquiries -->
    <a href="{{ route('admin.enquiries.index', ['status' => 'New']) }}" class="kpi-card {{ $pendingEnquiries > 0 ? 'warning-attention' : '' }}">
        <div class="kpi-top">
            <span class="kpi-label">Pending Enquiries</span>
            <div class="kpi-icon-box">
                <x-icon name="inbox" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($pendingEnquiries) }}</div>
        <div class="kpi-sub">Awaiting quote/contact</div>
    </a>

    <!-- 4. Unassigned Bookings (Semantic Rose if > 0) -->
    <a href="{{ route('admin.bookings.index', ['filter' => 'unassigned']) }}" class="kpi-card {{ $unassignedBookings > 0 ? 'urgent-attention' : '' }}">
        <div class="kpi-top">
            <span class="kpi-label">Unassigned Bookings</span>
            <div class="kpi-icon-box">
                <x-icon name="alert-triangle" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($unassignedBookings) }}</div>
        <div class="kpi-sub">Needs vehicle or driver</div>
    </a>

    <!-- 5. Available Vehicles -->
    <a href="{{ route('admin.vehicles.index', ['status' => 'Available']) }}" class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Available Vehicles</span>
            <div class="kpi-icon-box" style="background: #ECFDF5; color: #059669;">
                <x-icon name="car" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($availableVehicles) }}</div>
        <div class="kpi-sub">Ready for dispatch</div>
    </a>

    <!-- 6. Available Drivers -->
    <a href="{{ route('admin.drivers.index', ['filter' => 'unassigned']) }}" class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Available Drivers</span>
            <div class="kpi-icon-box" style="background: #F0FDFA; color: #0D9488;">
                <x-icon name="user-check" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($availableDrivers) }}</div>
        <div class="kpi-sub">Unassigned & active</div>
    </a>

    <!-- 7. Ongoing Trips -->
    <a href="{{ route('admin.trips.index', ['status' => 'Trip Started']) }}" class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Ongoing Trips</span>
            <div class="kpi-icon-box" style="background: #FAF5FF; color: #7C3AED;">
                <x-icon name="navigation" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($ongoingTrips) }}</div>
        <div class="kpi-sub">Currently on road</div>
    </a>

    <!-- 8. Incomplete Trips -->
    <a href="{{ route('admin.trips.index', ['trip_status' => 'Open', 'payment_status' => 'pending_payment']) }}" class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Incomplete Trips</span>
            <div class="kpi-icon-box" style="background: #FFF7ED; color: #C2410C;">
                <x-icon name="pause-circle" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($incompleteTrips) }}</div>
        <div class="kpi-sub">Open / pending payment</div>
    </a>

    <!-- 9. Pending Payments -->
    <a href="{{ route('admin.bookings.index', ['payment_status' => 'Pending']) }}" class="kpi-card {{ $pendingPayments > 0 ? 'warning-attention' : '' }}">
        <div class="kpi-top">
            <span class="kpi-label">Pending Payments</span>
            <div class="kpi-icon-box">
                <x-icon name="credit-card" :size="16" />
            </div>
        </div>
        <div class="kpi-value">₹{{ number_format($pendingPayments, 2) }}</div>
        <div class="kpi-sub">Uncollected balance</div>
    </a>

    <!-- 10. Trip Expenses -->
    <a href="{{ route('admin.expenses.index') }}" class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Trip Expenses</span>
            <div class="kpi-icon-box" style="background: #F1F5F9; color: #475569;">
                <x-icon name="dollar-sign" :size="16" />
            </div>
        </div>
        <div class="kpi-value">₹{{ number_format($outstandingExpenses, 2) }}</div>
        <div class="kpi-sub">Fuel, toll, parking recorded</div>
    </a>

    <!-- 11. GST Invoices -->
    <a href="{{ route('admin.invoices.index') }}" class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">GST Invoices</span>
            <div class="kpi-icon-box" style="background: #ECFEFF; color: #0891B2;">
                <x-icon name="receipt" :size="16" />
            </div>
        </div>
        <div class="kpi-value">{{ number_format($gstInvoicesCount) }}</div>
        <div class="kpi-sub">Compliant tax bills</div>
    </a>
</div>

<!-- Section 7: Urgent Enquiries Table (Clean SaaS Table) -->
@if($recentQueries->isNotEmpty())
    <div class="surface-card">
        <div class="surface-card-header">
            <div>
                <h3 class="surface-card-title">
                    <x-icon name="inbox" :size="16" style="color: #B45309;" />
                    Urgent Route Queries & Custom Quotations
                </h3>
                <p class="surface-card-sub">Routes where rate was not pre-configured or round-trip queries automatically converted into quotation enquiries.</p>
            </div>
            <a href="{{ route('admin.enquiries.index', ['status' => 'New']) }}" class="action-btn-neutral" style="font-size: 12px; padding: 4px 10px;">
                View All Enquiries ({{ $pendingEnquiries }}) →
            </a>
        </div>

        <div class="table-responsive">
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>Enquiry ID</th>
                        <th>Customer & Contact</th>
                        <th>Requested Route</th>
                        <th>Travel Date</th>
                        <th>Vehicle Category</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentQueries as $query)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: #2563EB;">
                                {{ $query->enquiry_id }}
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #172033;">{{ $query->name }}</div>
                                <div style="font-size: 11px; color: #64748B;">
                                    <a href="tel:{{ $query->phone }}" style="color: #2563EB; text-decoration: none;">{{ $query->phone }}</a>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #172033;">
                                    {{ $query->pickup_location }} <span style="color: #94A3B8; margin: 0 3px;">➔</span> {{ $query->destination }}
                                </div>
                                <div style="font-size: 11px; color: #64748B;">
                                    {{ $query->booking_type ?? 'One-Way' }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $query->travel_date ? \Carbon\Carbon::parse($query->travel_date)->format('d M Y') : 'Immediate' }}</div>
                                <div style="font-size: 10.5px; color: #94A3B8;">{{ $query->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <span class="cat-pill tp">
                                    {{ $query->vehicle_preference ?? 'Standard AC' }}
                                </span>
                            </td>
                            <td>
                                <span class="cat-pill" style="background: #FEF2F2; color: #991B1B;">
                                    Awaiting Quote
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                @if($query->converted_booking_id)
                                    <a href="{{ route('admin.bookings.show', $query->converted_booking_id) }}" class="action-btn-primary" style="font-size: 11.5px; padding: 4px 10px;">
                                        View / Quote
                                    </a>
                                @else
                                    <a href="{{ route('admin.enquiries.show', $query) }}" class="action-btn-primary" style="font-size: 11.5px; padding: 4px 10px;">
                                        View / Quote
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Latest Reservations & Dispatch Status -->
<div class="surface-card">
    <div class="surface-card-header">
        <div>
            <h3 class="surface-card-title">
                <x-icon name="calendar-check" :size="16" style="color: #2563EB;" />
                Latest Reservations & Dispatch Status
            </h3>
            <p class="surface-card-sub">Real-time booking queue with vehicle and chauffeur assignment tracking.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="action-btn-neutral" style="font-size: 12px; padding: 4px 10px;">
            View All Bookings →
        </a>
    </div>

    <div class="table-responsive">
        <table class="saas-table">
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
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $b)
                    <tr>
                        <td>
                            <a href="{{ route('admin.bookings.show', $b) }}" style="font-weight: 700; color: #2563EB; text-decoration: none;">
                                #{{ $b->booking_id }}
                            </a>
                        </td>
                        <td>
                            @php
                                $catClass = match($b->booking_category) {
                                    'CORP' => 'corp',
                                    'WEB' => 'web',
                                    'TP' => 'tp',
                                    default => 'man'
                                };
                            @endphp
                            <span class="cat-pill {{ $catClass }}">
                                {{ $b->booking_category }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #172033;">{{ $b->customer?->name ?? 'Guest' }}</div>
                            <div style="font-size: 11px; color: #64748B;">{{ $b->customer?->phone }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 500;">
                                {{ $b->pickup_location }}
                                @if($b->via_location)
                                    <span style="color: #94A3B8; font-size: 11px;">via {{ $b->via_location }}</span>
                                @endif
                                <span style="color: #94A3B8; margin: 0 3px;">➔</span> {{ $b->destination }}
                            </div>
                            <div style="font-size: 11px; color: #64748B;">
                                {{ $b->trip_type }} ({{ $b->vehicle_category }})
                            </div>
                        </td>
                        <td>
                            <div>{{ \Carbon\Carbon::parse($b->travel_date)->format('d M Y') }}</div>
                            <div style="font-size: 11px; color: #64748B;">{{ $b->travel_time }}</div>
                        </td>
                        <td>
                            @if($b->vehicle)
                                <div style="font-weight: 600; color: #172033;">{{ $b->vehicle->name }}</div>
                                <div style="font-size: 11px; color: #64748B;">{{ $b->vehicle->registration_number }}</div>
                            @else
                                <a href="{{ route('admin.bookings.show', $b) }}" style="color: #B91C1C; font-size: 11.5px; font-weight: 600; text-decoration: none;">
                                    + Assign Vehicle
                                </a>
                            @endif
                        </td>
                        <td>
                            @if($b->driver)
                                <div style="font-weight: 600; color: #172033;">{{ $b->driver->name }}</div>
                                <div style="font-size: 11px; color: #64748B;">{{ $b->driver->mobile }}</div>
                            @else
                                <a href="{{ route('admin.bookings.show', $b) }}" style="color: #B91C1C; font-size: 11.5px; font-weight: 600; text-decoration: none;">
                                    + Assign Driver
                                </a>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #172033;">₹{{ number_format($b->total_amount, 2) }}</div>
                            @if((float) $b->balance_amount > 0)
                                <div style="font-size: 11px; color: #B91C1C; font-weight: 600;">Due: ₹{{ number_format($b->balance_amount, 2) }}</div>
                            @else
                                <div style="font-size: 11px; color: #15803D; font-weight: 600;">Paid</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeStyle = match($b->booking_status) {
                                    'Confirmed' => 'background: #EFF6FF; color: #1D4ED8;',
                                    'Vehicle Assigned', 'Driver Assigned' => 'background: #FEF3C7; color: #92400E;',
                                    'Trip Started', 'On The Way' => 'background: #ECFDF5; color: #047857;',
                                    'Completed' => 'background: #F1F5F9; color: #475569;',
                                    default => 'background: #FEF2F2; color: #991B1B;'
                                };
                            @endphp
                            <span class="cat-pill" style="{{ $badgeStyle }}">
                                {{ $b->booking_status }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.bookings.show', $b) }}" class="action-btn-neutral" style="font-size: 11.5px; padding: 3px 8px;">
                                Manage
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2.5rem; color: #94A3B8;">
                            No reservations recorded yet. Click <strong>+ Create Booking</strong> above to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Revenue Analytics & Recent Payments -->
<div class="grid grid-2 gap-3 mb-4">
    <!-- Chart: 6 Months Revenue Trend -->
    <div class="surface-card mb-0">
        <div class="surface-card-header">
            <div>
                <h3 class="surface-card-title">
                    <x-icon name="trending-up" :size="16" style="color: #2563EB;" />
                    Revenue Trend (Last 6 Months)
                </h3>
            </div>
            <span class="cat-pill web" style="font-size: 12px;">
                Total: ₹{{ number_format(array_sum($revenueData), 2) }}
            </span>
        </div>
        <div style="position: relative; height: 240px; width: 100%;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Recent Payments Collected -->
    <div class="surface-card mb-0">
        <div class="surface-card-header">
            <div>
                <h3 class="surface-card-title">
                    <x-icon name="credit-card" :size="16" style="color: #059669;" />
                    Recent Collected Payments
                </h3>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="action-btn-neutral" style="font-size: 11.5px; padding: 3px 8px;">
                All Payments →
            </a>
        </div>
        <div class="table-responsive">
            <table class="saas-table">
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
                            <td style="font-size: 11.5px; color: #64748B;">{{ $p->created_at->format('d M, h:i A') }}</td>
                            <td>
                                @if($p->booking)
                                    <a href="{{ route('admin.bookings.show', $p->booking) }}" style="font-weight: 600; color: #2563EB; text-decoration: none;">
                                        #{{ $p->booking->booking_id }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-weight: 500;">{{ $p->customer?->name ?? 'Guest' }}</td>
                            <td style="font-weight: 700; color: #15803D;">₹{{ number_format($p->amount, 2) }}</td>
                            <td>
                                <span class="cat-pill man" style="font-size: 10.5px;">
                                    {{ $p->payment_method ?? 'Cash/UPI' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94A3B8; padding: 2rem;">
                                No payment transactions recorded yet.
                            </td>
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
                        backgroundColor: 'rgba(37, 99, 235, 0.06)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#2563EB',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#F1F5F9'
                            },
                            ticks: {
                                font: { size: 11 },
                                callback: function(value) { return '₹' + value.toLocaleString(); }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 11 }
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
