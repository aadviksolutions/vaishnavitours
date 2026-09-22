@extends('layouts.admin')

@section('title', 'Customer: ' . $user->name)
@section('page_title', 'Customer Profile')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('admin.customers.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
            ← Back to Customer Directory
        </a>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">{{ $user->name }}</h1>
        <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Passenger account profile, lifetime value, and booking history.</p>
    </div>
</div>

<!-- Customer Metrics -->
<div class="grid grid-3 gap-4 mb-4">
    <div class="kpi-card">
        <div class="kpi-icon" style="background: #eff6ff; color: #2563eb;"><x-icon name="car-front" size="24" /></div>
        <div class="kpi-label">Total Bookings</div>
        <div class="kpi-value">{{ $totalBookings }}</div>
        <div class="kpi-sub">Total trips created</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: #f0fdf4; color: #16a34a;"><x-icon name="circle-check" size="24" /></div>
        <div class="kpi-label">Completed Trips</div>
        <div class="kpi-value">{{ $completedTrips }}</div>
        <div class="kpi-sub">Successfully fulfilled journeys</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: #fef3c7; color: #d97706;"><x-icon name="wallet" size="24" /></div>
        <div class="kpi-label">Lifetime Spend</div>
        <div class="kpi-value">₹{{ number_format($totalSpent, 2) }}</div>
        <div class="kpi-sub">Successful payments recorded</div>
    </div>
</div>

<div class="grid grid-3 gap-4">
    <!-- Left 1 Col: Profile Card -->
    <div>
        <div class="card mb-4">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;"><x-icon name="user" size="20" class="text-primary" style="margin-right: 6px;" /> Contact Details</h3>

            <div style="font-weight: 800; font-size: 1.15rem; color: var(--dark-900);">{{ $user->name }}</div>
            <div style="margin-top: 6px; font-size: 0.9rem;"><x-icon name="phone" size="14" style="margin-right: 4px;" /><a href="tel:{{ $user->phone }}">{{ $user->phone }}</a></div>
            <div style="font-size: 0.9rem; color: var(--slate-600);">✉️ {{ $user->email }}</div>

            @if($user->customer)
                @if($user->customer->alternate_phone)
                    <div style="font-size: 0.85rem; color: var(--slate-500); margin-top: 4px;">Alt Phone: {{ $user->customer->alternate_phone }}</div>
                @endif
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--slate-200); font-size: 0.85rem; color: var(--slate-600);">
                    <strong>Pickup / Billing Address:</strong><br>
                    {{ $user->customer->address ?? 'Bilaspur' }}<br>
                    {{ $user->customer->city ?? 'Bilaspur' }}, {{ $user->customer->state ?? 'Chhattisgarh' }} - {{ $user->customer->pincode ?? '495001' }}
                </div>
            @endif

            <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--slate-400);">
                Joined: {{ $user->created_at->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    <!-- Right 2 Cols: Bookings & Payments -->
    <div style="grid-column: span 2;">
        <!-- Bookings History -->
        <div class="card mb-4">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;">🚖 Bookings History</h3>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Fare (₹)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->bookings as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" style="font-weight: 800; color: var(--dark-900);">
                                        #{{ $b->booking_id }}
                                    </a>
                                </td>
                                <td>{{ $b->pickup_location }} ➔ {{ $b->destination }}</td>
                                <td>{{ $b->travel_date ? $b->travel_date->format('d M Y') : '' }}</td>
                                <td style="font-weight: 800;">₹{{ number_format($b->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge badge-sm badge-secondary">{{ $b->booking_status }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4" style="color: var(--slate-500);">No bookings recorded for this passenger yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payments Ledger -->
        <div class="card">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem;">💳 Payments Ledger</h3>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Booking Ref</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->payments as $p)
                            <tr>
                                <td style="font-family: monospace; font-size: 0.85rem;">{{ $p->payment_id }}</td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $p->booking_id) }}" style="color: var(--primary-dark); font-weight: 600;">
                                        #{{ $p->booking->booking_id ?? $p->booking_id }}
                                    </a>
                                </td>
                                <td style="font-weight: 800; color: #16a34a;">₹{{ number_format($p->amount, 2) }}</td>
                                <td>{{ $p->payment_method }}</td>
                                <td><span class="badge badge-sm badge-success">{{ $p->status }}</span></td>
                                <td style="font-size: 0.8rem; color: var(--slate-500);">{{ $p->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4" style="color: var(--slate-500);">No payment transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
