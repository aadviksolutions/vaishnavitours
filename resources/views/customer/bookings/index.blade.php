@extends('layouts.customer')

@section('title', 'My Bookings - Vaishnavi Tours')

@section('content')

    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
        <div>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--dark-950); margin: 0;">My Bookings</h1>
            <p style="color: var(--slate-500); font-size: 0.9rem; margin-top: 2px;">
                Track your active journeys, chauffeur allocations, payment receipts, and tax invoices.
            </p>
        </div>
        <a href="{{ route('booking') }}" class="btn btn-primary">
            + Book Another Taxi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-3">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- Section 13 Filters: Upcoming, Completed, Cancelled, All -->
    <div class="d-flex justify-between align-center flex-wrap gap-3 mb-4">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('customer.bookings.index', ['filter' => 'all']) }}"
               class="btn btn-sm {{ ($filter ?? 'all') === 'all' ? 'btn-dark' : 'btn-outline' }}">
                All Bookings
            </a>
            <a href="{{ route('customer.bookings.index', ['filter' => 'upcoming']) }}"
               class="btn btn-sm {{ ($filter ?? '') === 'upcoming' ? 'btn-primary' : 'btn-outline' }}">
                Upcoming Trips
            </a>
            <a href="{{ route('customer.bookings.index', ['filter' => 'completed']) }}"
               class="btn btn-sm {{ ($filter ?? '') === 'completed' ? 'btn-success' : 'btn-outline' }}">
                Completed
            </a>
            <a href="{{ route('customer.bookings.index', ['filter' => 'cancelled']) }}"
               class="btn btn-sm {{ ($filter ?? '') === 'cancelled' ? 'btn-danger' : 'btn-outline' }}">
                Cancelled
            </a>
        </div>

        <form method="GET" action="{{ route('customer.bookings.index') }}" class="d-flex gap-2">
            <input type="hidden" name="filter" value="{{ $filter ?? 'all' }}">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ID or route..." value="{{ request('search') }}" style="min-width: 200px;">
            <button type="submit" class="btn btn-outline btn-sm">Search</button>
            @if(request('search'))
                <a href="{{ route('customer.bookings.index', ['filter' => $filter ?? 'all']) }}" class="btn btn-outline btn-sm">Clear</a>
            @endif
        </form>
    </div>

    <!-- Section 13 Table: Booking ID, Date, Route, Vehicle, Amount, Payment Status, Trip Status, Buttons: View Details, Invoice -->
    <div class="card">
        @if($bookings->isEmpty())
            <div style="text-align: center; padding: 3.5rem 1rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🚖</div>
                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">No Bookings Found</h3>
                <p style="color: var(--slate-500); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    You do not have any bookings in this section.
                </p>
                <a href="{{ route('booking') }}" class="btn btn-primary">Book a Taxi Now</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table" style="font-size: 0.875rem;">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Date</th>
                            <th>Route</th>
                            <th>Vehicle</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Trip Status</th>
                            <th style="text-align: right; min-width: 170px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr>
                                <td>
                                    <a href="{{ route('customer.bookings.show', $booking->id) }}" style="font-weight: 800; color: var(--primary-dark); font-family: monospace; font-size: 0.95rem;">
                                        {{ $booking->booking_id }}
                                    </a>
                                </td>
                                <td style="white-space: nowrap; font-weight: 600;">
                                    {{ $booking->travel_date->format('d M, Y') }}<br>
                                    <span style="font-size: 0.775rem; color: var(--slate-500);">{{ date('h:i A', strtotime($booking->travel_time)) }}</span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--dark-900);">
                                        {{ $booking->pickup_location }} ➔ {{ $booking->destination }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--slate-500);">
                                        <span class="badge" style="background: var(--slate-200); color: var(--dark-800); font-size: 0.7rem;">{{ $booking->trip_type }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($booking->vehicle)
                                        <div style="font-weight: 700;">{{ $booking->vehicle->name }}</div>
                                        <div style="font-size: 0.75rem; color: var(--slate-500); font-family: monospace;">{{ $booking->vehicle->registration_number }}</div>
                                    @else
                                        <span style="font-size: 0.8rem; color: var(--slate-400); font-style: italic;">Vehicle allocation pending</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <div style="font-weight: 800; font-size: 0.95rem;">₹{{ number_format($booking->total_amount, 2) }}</div>
                                    @if($booking->balance_amount > 0)
                                        <div style="font-size: 0.75rem; color: #dc2626; font-weight: 600;">
                                            Due: ₹{{ number_format($booking->balance_amount, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <span class="badge badge-sm {{ $booking->payment_status === 'Paid' ? 'badge-paid' : ($booking->payment_status === 'Partial' ? 'badge-warning' : 'badge-pending') }}">
                                        {{ $booking->payment_status }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <span class="badge badge-sm
                                        {{ $booking->booking_status === 'Completed' ? 'badge-success' : '' }}
                                        {{ in_array($booking->booking_status, ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned']) ? 'badge-primary' : '' }}
                                        {{ in_array($booking->booking_status, ['Trip Started', 'On The Way']) ? 'badge-warning' : '' }}
                                        {{ $booking->booking_status === 'Cancelled' ? 'badge-danger' : '' }}
                                    ">
                                        {{ $booking->booking_status }}
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div class="d-flex justify-end gap-1">
                                        <a href="{{ route('customer.bookings.show', $booking->id) }}" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.6rem; font-size: 0.775rem;">
                                            View Details
                                        </a>
                                        <a href="{{ route('invoice.show', $booking->id) }}" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.6rem; font-size: 0.775rem;" target="_blank">
                                            Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
                <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--slate-200);">
                    {{ $bookings->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
