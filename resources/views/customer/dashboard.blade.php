@extends('layouts.customer')

@section('title', 'Customer Dashboard')

@section('content')

    <!-- Welcome & Quick Booking Banner -->
    <div class="card" style="background: linear-gradient(135deg, var(--dark-950) 0%, var(--dark-900) 100%); color: #fff; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 2rem;">
        <div class="d-flex justify-between align-center flex-wrap gap-2">
            <div>
                <span class="badge badge-confirmed" style="background: rgba(245, 158, 11, 0.2); color: var(--primary); margin-bottom: 0.5rem;">
                    Vaishnavi Tours Traveler
                </span>
                <h1 style="color: #fff; font-size: 1.85rem; margin-top: 0.25rem;">
                    Welcome, {{ $user->name }}!
                </h1>
                <p style="color: var(--slate-300); font-size: 0.95rem; margin-top: 0.25rem;">
                    Manage your upcoming road trips, view assigned chauffeurs, and download GST tax invoices.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('booking') }}" class="btn btn-primary btn-lg">
                    🚖 Book a New Cab
                </a>
            </div>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid grid-4 gap-3" style="margin-bottom: 2.5rem;">
        <div class="card" style="border-left: 4px solid var(--dark-900);">
            <div style="font-size: 0.8rem; color: var(--slate-500); font-weight: 700; text-transform: uppercase;">Total Bookings</div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--dark-900); margin-top: 0.25rem;">{{ $stats['total_bookings'] }}</div>
            <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.25rem;">Lifetime reservations</div>
        </div>

        <div class="card" style="border-left: 4px solid var(--primary);">
            <div style="font-size: 0.8rem; color: var(--slate-500); font-weight: 700; text-transform: uppercase;">Upcoming Trip</div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--primary-dark); margin-top: 0.25rem;">{{ $stats['upcoming_trips'] }}</div>
            <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.25rem;">Active & scheduled</div>
        </div>

        <div class="card" style="border-left: 4px solid var(--success);">
            <div style="font-size: 0.8rem; color: var(--slate-500); font-weight: 700; text-transform: uppercase;">Completed Trips</div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--success); margin-top: 0.25rem;">{{ $stats['completed_trips'] }}</div>
            <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.25rem;">Safely completed</div>
        </div>

        <div class="card" style="border-left: 4px solid {{ $stats['pending_balance'] > 0 ? 'var(--danger)' : 'var(--slate-300)' }};">
            <div style="font-size: 0.8rem; color: var(--slate-500); font-weight: 700; text-transform: uppercase;">Pending Payment</div>
            <div style="font-size: 2rem; font-weight: 800; color: {{ $stats['pending_balance'] > 0 ? 'var(--danger)' : 'var(--dark-900)' }}; margin-top: 0.25rem;">
                ₹{{ number_format($stats['pending_balance'], 2) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.25rem;">Dues upon completion</div>
        </div>
    </div>

    <!-- Active / Upcoming Trip Highlight Card (Section 12) -->
    @if($upcomingTrip)
        <div class="card" style="border: 2px solid var(--primary); background: #FFFDF5; margin-bottom: 2.5rem; padding: 1.75rem;">
            <div class="d-flex justify-between align-center flex-wrap gap-2" style="border-bottom: 1.5px solid rgba(245,158,11,0.25); padding-bottom: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <span class="badge badge-ontrip" style="margin-bottom: 0.35rem;">
                        Trip Status: {{ $upcomingTrip->booking_status }}
                    </span>
                    <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--dark-900);">
                        Upcoming Trip: {{ $upcomingTrip->pickup_location }} ➔ {{ $upcomingTrip->destination }}
                    </h2>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customer.bookings.show', $upcomingTrip->id) }}" class="btn btn-primary">
                        📍 Track Trip Details & Chauffeur →
                    </a>
                    @if($upcomingTrip->invoice)
                        <a href="{{ route('invoice.show', $upcomingTrip->id) }}" class="btn btn-outline" target="_blank">
                            🧾 Invoice
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-4 gap-3 mb-4" style="font-size: 0.925rem;">
                <div>
                    <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Booking ID</span>
                    <div style="font-weight: 800; color: var(--dark-900); font-size: 1.15rem; font-family: monospace;">{{ $upcomingTrip->booking_id }}</div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Pickup Location</span>
                    <div style="font-weight: 700; color: var(--dark-900);">{{ $upcomingTrip->pickup_location }}</div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Destination</span>
                    <div style="font-weight: 700; color: var(--dark-900);">{{ $upcomingTrip->destination }}</div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Travel Date & Time</span>
                    <div style="font-weight: 700;">{{ $upcomingTrip->travel_date ? $upcomingTrip->travel_date->format('d M, Y') : '' }} at {{ date('h:i A', strtotime($upcomingTrip->travel_time)) }}</div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Vehicle</span>
                    <div style="font-weight: 700;">
                        {{ $upcomingTrip->vehicle ? $upcomingTrip->vehicle->name : 'Assigning Fleet...' }}
                    </div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Registration</span>
                    <div style="font-weight: 700; font-family: monospace;">
                        {{ $upcomingTrip->vehicle ? $upcomingTrip->vehicle->registration_number : 'Pending' }}
                    </div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Driver</span>
                    <div style="font-weight: 700;">
                        {{ $upcomingTrip->driver ? $upcomingTrip->driver->name : 'Allocating Chauffeur...' }}
                    </div>
                </div>
                <div>
                    <span style="color: var(--slate-500); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Driver Contact</span>
                    <div style="font-weight: 700; color: var(--dark-900);">
                        @if($upcomingTrip->driver)
                            📞 <a href="tel:{{ $upcomingTrip->driver->mobile }}" style="color: inherit; text-decoration: none;">{{ $upcomingTrip->driver->mobile }}</a>
                        @else
                            <span style="color: var(--slate-400);">Pending Allocation</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dynamic Trip Timeline (Section 12) -->
            <div style="background: #ffffff; border: 1.5px solid rgba(245,158,11,0.3); border-radius: 8px; padding: 1.25rem;">
                <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--slate-500); margin-bottom: 0.75rem;">
                    📍 Journey Progress Timeline (Real-Time Database Status)
                </div>

                @php
                    $milestones = [
                        'Confirmed' => 'Booking Confirmed',
                        'Vehicle Assigned' => 'Vehicle Assigned',
                        'Driver Assigned' => 'Driver Assigned',
                        'Trip Started' => 'Trip Started',
                        'On The Way' => 'On The Way',
                        'Completed' => 'Completed',
                    ];
                    $keys = array_keys($milestones);
                    $currentStatus = $upcomingTrip->booking_status;
                    $statusIdx = array_search($currentStatus, $keys);
                    if ($currentStatus === 'Pending') $statusIdx = -1;
                @endphp

                <div class="d-flex justify-between align-center flex-wrap gap-2" style="position: relative;">
                    @foreach($milestones as $key => $title)
                        @php
                            $stepIndex = array_search($key, $keys);
                            $isDone = $statusIdx > $stepIndex;
                            $isCurrent = $statusIdx === $stepIndex;
                        @endphp
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
                            @if($isDone)
                                <span style="color: #059669; font-weight: 800; font-size: 1.1rem;">✓</span>
                                <span style="color: #059669; font-weight: 700;">{{ $title }}</span>
                            @elseif($isCurrent)
                                <span style="color: #D97706; font-weight: 800; font-size: 1.25rem;">●</span>
                                <span style="color: #D97706; font-weight: 800; text-decoration: underline;">{{ $title }}</span>
                            @else
                                <span style="color: var(--slate-400); font-size: 1.1rem;">○</span>
                                <span style="color: var(--slate-400);">{{ $title }}</span>
                            @endif

                            @if(!$loop->last)
                                <span style="color: var(--slate-300); margin-left: 6px;">➔</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-3 gap-3">
        <!-- Recent Bookings (2 spans) -->
        <div style="grid-column: span 2;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Bookings</h2>
                    <a href="{{ route('customer.bookings.index') }}" class="btn btn-outline btn-sm">View All Bookings →</a>
                </div>

                @if($recentBookings->isEmpty())
                    <p style="color: var(--slate-500); padding: 1rem 0;">You have not made any bookings yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Route</th>
                                    <th>Date</th>
                                    <th>Fare</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings as $b)
                                    <tr>
                                        <td>
                                            <a href="{{ route('customer.bookings.show', $b->id) }}" style="font-weight: 800; color: var(--primary-dark);">
                                                #{{ $b->booking_id }}
                                            </a>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700;">{{ $b->pickup_location }} ➔ {{ $b->destination }}</div>
                                            <div style="font-size: 0.75rem; color: var(--slate-500);">{{ $b->trip_type }}</div>
                                        </td>
                                        <td>{{ $b->travel_date->format('d M, Y') }}</td>
                                        <td>
                                            <strong>₹{{ number_format($b->total_amount, 2) }}</strong>
                                            @if($b->balance_amount > 0)
                                                <div style="font-size: 0.725rem; color: var(--danger);">Bal: ₹{{ number_format($b->balance_amount, 2) }}</div>
                                            @else
                                                <div style="font-size: 0.725rem; color: var(--success);">Paid</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ strtolower(str_replace(' ', '', $b->booking_status)) }}">
                                                {{ $b->booking_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('customer.bookings.show', $b->id) }}" class="btn btn-outline btn-sm">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Notifications -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Alerts</h2>
                    <a href="{{ route('customer.notifications') }}" style="font-size: 0.825rem; color: var(--primary-dark); font-weight: 700;">All</a>
                </div>

                @if($notifications->isEmpty())
                    <p style="color: var(--slate-500); font-size: 0.9rem;">No new notifications.</p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                        @foreach($notifications as $notif)
                            <div style="border-left: 3px solid var(--primary); padding-left: 0.75rem;">
                                <div style="font-weight: 700; font-size: 0.875rem; color: var(--dark-900);">{{ $notif->title }}</div>
                                <div style="font-size: 0.8rem; color: var(--slate-600); margin-top: 0.2rem;">{{ $notif->message }}</div>
                                <div style="font-size: 0.725rem; color: var(--slate-400); margin-top: 0.25rem;">{{ $notif->created_at->diffForHumans() }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div style="margin-top: 1.5rem; border-top: 1px solid var(--slate-100); padding-top: 1rem;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--dark-900); margin-bottom: 0.25rem;">Need Assistance?</div>
                    <div style="font-size: 0.8rem; color: var(--slate-500); margin-bottom: 0.75rem;">Our 24/7 dispatch desk is always on standby.</div>
                    <a href="tel:+919893012345" class="btn btn-outline btn-sm" style="width: 100%;">📞 Call +91 98930 12345</a>
                </div>
            </div>
        </div>
    </div>

@endsection
