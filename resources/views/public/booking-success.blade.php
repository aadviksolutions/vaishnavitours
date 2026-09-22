@extends('layouts.app')

@section('title', 'Booking Request Received - Vaishnavi Tours')

@section('content')
<section style="padding: 4rem 0 6rem; background: var(--slate-50);">
    <div class="container" style="max-width: 720px;">
        <div class="card" style="text-align: center; padding: 3rem 2.5rem; border-top: 6px solid var(--primary); box-shadow: var(--shadow-lg);">
            <div style="width: 76px; height: 76px; background: #FEF3C7; color: #D97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.25rem;">
                ⏳
            </div>

            <span class="badge badge-warning" style="font-size: 0.95rem; padding: 0.4rem 1.25rem; font-weight: 800; text-transform: uppercase; margin-bottom: 0.75rem; display: inline-block;">
                Booking Request Received
            </span>

            <h1 style="font-size: 2.15rem; font-weight: 900; margin-bottom: 0.5rem; color: var(--dark-950);">
                Thank You, {{ $booking->customer->name ?? 'Customer' }}!
            </h1>
            <p style="color: var(--slate-600); font-size: 1.05rem; margin-bottom: 2rem;">
                Your cab booking request has been received by our Bilaspur dispatch team. We are assigning your vehicle & chauffeur.
            </p>

            <!-- Booking Summary Card -->
            <div style="background: #ffffff; border: 1.5px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.75rem; text-align: left; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
                <div class="d-flex justify-between align-center" style="border-bottom: 1.5px solid var(--slate-100); padding-bottom: 0.85rem; margin-bottom: 1rem;">
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">BOOKING ID</span>
                        <div style="color: var(--dark-950); font-size: 1.35rem; font-weight: 900;">{{ $booking->booking_id }}</div>
                    </div>
                    <div style="text-align: right;">
                        <span style="color: var(--slate-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">BOOKING STATUS</span>
                        <div>
                            <span class="badge badge-warning" style="font-size: 0.85rem; font-weight: 800;">
                                {{ $booking->booking_status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-2 gap-3" style="font-size: 0.95rem;">
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Customer:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">{{ $booking->customer->name }} (📞 {{ $booking->customer->phone }})</div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Vehicle:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">
                            {{ $booking->vehicle ? $booking->vehicle->name . ' (' . $booking->vehicle->vehicle_type . ')' : 'AC Cab Category' }}
                        </div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Pickup:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">{{ $booking->pickup_location }}</div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Destination:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">{{ $booking->destination }}</div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Travel Date:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">{{ $booking->travel_date->format('d M, Y') }}</div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Travel Time:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">{{ date('h:i A', strtotime($booking->travel_time)) }}</div>
                    </div>
                </div>

                <div class="d-flex justify-between align-center" style="border-top: 1.5px solid var(--slate-100); padding-top: 1rem; margin-top: 1.25rem;">
                    <span style="color: var(--slate-600); font-weight: 700;">Estimated Base Fare:</span>
                    <strong style="font-size: 1.35rem; color: var(--dark-950);">₹{{ number_format($booking->total_amount, 2) }}</strong>
                </div>
            </div>

            <!-- If customer is not logged in: Ask whether they want to create an account -->
            @guest
                <div style="background: #FFFBEB; border: 1.5px dashed #F59E0B; border-radius: var(--radius-md); padding: 1.5rem; text-align: left; margin-bottom: 2rem;">
                    <div class="d-flex align-start gap-3">
                        <div style="font-size: 1.75rem;">👤</div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--dark-950); margin-bottom: 0.25rem;">
                                Create an Account for Real-Time Trip Tracking
                            </h4>
                            <p style="color: var(--slate-600); font-size: 0.875rem; margin-bottom: 1rem;">
                                An account lets you track chauffeur allocation live, monitor vehicle location, view payment receipts, and download GST tax invoices.
                            </p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('register', ['phone' => $booking->customer->phone, 'name' => $booking->customer->name, 'email' => $booking->customer->email]) }}" class="btn btn-primary btn-sm">
                                    Create My Account
                                </a>
                                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">
                                    Log In to Existing Account
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div style="margin-bottom: 2rem;">
                    <a href="{{ route('customer.bookings.show', $booking->id) }}" class="btn btn-primary">
                        📍 Track Booking & View Journey Details →
                    </a>
                </div>
            @endguest

            <p style="font-size: 0.875rem; color: var(--slate-500); margin-bottom: 2rem;">
                Our central dispatch desk will contact you at <strong>{{ $booking->customer->phone }}</strong>. For urgent queries, reach out to our support team.
            </p>

            <div class="d-flex justify-center gap-3 flex-wrap">
                <a href="{{ route('home') }}" class="btn btn-outline">← Back to Home</a>
                <a href="{{ route('contact') }}" class="btn btn-primary">💬 Contact Support</a>
            </div>
        </div>
    </div>
</section>
@endsection
