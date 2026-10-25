@extends('layouts.admin')

@section('title', 'Advance Booking Calendar — ' . $currentDate->format('F Y'))
@section('page_title', 'Booking Calendar')

@push('styles')
<style>
    .calendar-wrapper {
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: var(--slate-200);
    }
    .calendar-day-header {
        text-align: center;
        font-weight: 700;
        font-size: 0.78rem;
        color: var(--slate-600);
        padding: 10px 4px;
        background: var(--slate-50);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .calendar-cell {
        min-height: 82px;
        background: #ffffff;
        padding: 6px 8px;
        display: flex;
        flex-direction: column;
        transition: background 0.15s;
        position: relative;
    }
    .calendar-cell:hover {
        background: #F8FAFC;
    }
    .calendar-cell.today {
        background: #FFFBEB;
    }
    .calendar-cell.empty {
        background: #F8FAFC;
        min-height: 60px;
    }
    .calendar-date-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .calendar-date-num {
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--slate-700);
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .calendar-cell.today .calendar-date-num {
        background: var(--primary);
        color: #000;
        font-weight: 800;
    }
    .calendar-count-badge {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 10px;
        line-height: 1.3;
    }
    .calendar-trip-pill {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
        margin-bottom: 3px;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: transform 0.1s, opacity 0.1s;
        line-height: 1.3;
    }
    .calendar-trip-pill:hover {
        opacity: 0.85;
        transform: translateX(1px);
    }
    .trip-confirmed { background: #EEF2FF; color: #4338CA; border-left: 3px solid #6366F1; }
    .trip-assigned { background: #FEF3C7; color: #92400E; border-left: 3px solid #F59E0B; }
    .trip-started { background: #D1FAE5; color: #065F46; border-left: 3px solid #10B981; }
    .trip-completed { background: #F1F5F9; color: #475569; border-left: 3px solid #94A3B8; }
    .trip-pending { background: #FEF2F2; color: #991B1B; border-left: 3px solid #EF4444; }

    @media (max-width: 768px) {
        .calendar-grid {
            grid-template-columns: repeat(7, minmax(40px, 1fr));
        }
        .calendar-cell {
            min-height: 65px;
            padding: 4px;
        }
        .calendar-trip-pill {
            font-size: 0.6rem;
            padding: 1px 3px;
        }
    }
</style>
@endpush

@section('content')
<!-- Header Bar -->
<div class="mb-4 d-flex justify-between align-center flex-wrap gap-3">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--dark-900); display: flex; align-items: center; gap: 8px;">
            <x-icon name="calendar" :size="24" style="color: var(--primary);" />
            Advance Booking Calendar
        </h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.85rem;">Date-wise dispatch schedule, fleet deployment and chauffeur assignment matrix.</p>
    </div>
    
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.calendar.index', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}" class="btn btn-outline btn-sm d-inline-flex align-center gap-1">
            <x-icon name="chevron-left" :size="14" /> {{ $prevDate->format('M Y') }}
        </a>
        <a href="{{ route('admin.calendar.index', ['year' => now()->year, 'month' => now()->month]) }}" class="btn {{ $currentDate->isCurrentMonth() ? 'btn-primary' : 'btn-outline' }} btn-sm font-weight-bold">
            Today
        </a>
        <a href="{{ route('admin.calendar.index', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}" class="btn btn-outline btn-sm d-inline-flex align-center gap-1">
            {{ $nextDate->format('M Y') }} <x-icon name="chevron-right" :size="14" />
        </a>
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm d-inline-flex align-center gap-1 ml-2">
            <x-icon name="plus" :size="14" /> + New Booking
        </a>
    </div>
</div>

<!-- 3 KPI Cards -->
<div class="grid grid-3 gap-3 mb-4">
    <div class="card d-flex align-center gap-3" style="padding: 1rem 1.25rem; border-left: 4px solid var(--primary);">
        <div style="width: 42px; height: 42px; border-radius: 8px; background: #FFFBEB; color: #B45309; display: flex; align-items: center; justify-content: center;">
            <x-icon name="calendar" :size="22" />
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase;">
                {{ $currentDate->format('F Y') }} Trips
            </div>
            <div style="font-size: 1.4rem; font-weight: 900; color: var(--dark-950); line-height: 1.2;">
                {{ $sortedBookings->count() }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--slate-500);">Bookings</span>
            </div>
        </div>
    </div>

    <div class="card d-flex align-center gap-3" style="padding: 1rem 1.25rem; border-left: 4px solid #10B981;">
        <div style="width: 42px; height: 42px; border-radius: 8px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;">
            <x-icon name="car" :size="22" />
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase;">
                Active Fleet
            </div>
            <div style="font-size: 1.4rem; font-weight: 900; color: var(--dark-950); line-height: 1.2;">
                {{ $totalVehicles }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--slate-500);">Vehicles</span>
            </div>
        </div>
    </div>

    <div class="card d-flex align-center gap-3" style="padding: 1rem 1.25rem; border-left: 4px solid #6366F1;">
        <div style="width: 42px; height: 42px; border-radius: 8px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center;">
            <x-icon name="users" :size="22" />
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase;">
                Chauffeurs
            </div>
            <div style="font-size: 1.4rem; font-weight: 900; color: var(--dark-950); line-height: 1.2;">
                {{ $totalDrivers }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--slate-500);">Drivers</span>
            </div>
        </div>
    </div>
</div>

<!-- Compact Month View & Legend -->
<div class="calendar-wrapper mb-4">
    <!-- Month Title & Status Legend Bar -->
    <div style="padding: 0.75rem 1.25rem; background: #F8FAFC; border-bottom: 1px solid var(--slate-200); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
        <div style="font-size: 1.1rem; font-weight: 800; color: var(--dark-900); display: flex; align-items: center; gap: 6px;">
            <span>{{ $currentDate->format('F Y') }}</span>
            @if($currentDate->isCurrentMonth())
                <span class="badge badge-success" style="font-size: 0.7rem; padding: 2px 6px;">Current Month</span>
            @endif
        </div>
        <div class="d-flex align-center gap-3 flex-wrap" style="font-size: 0.75rem; color: var(--slate-600); font-weight: 600;">
            <span class="d-inline-flex align-center gap-1"><span style="width: 8px; height: 8px; background: #6366F1; border-radius: 2px;"></span> Confirmed</span>
            <span class="d-inline-flex align-center gap-1"><span style="width: 8px; height: 8px; background: #F59E0B; border-radius: 2px;"></span> Vehicle/Driver Assigned</span>
            <span class="d-inline-flex align-center gap-1"><span style="width: 8px; height: 8px; background: #10B981; border-radius: 2px;"></span> On Trip</span>
            <span class="d-inline-flex align-center gap-1"><span style="width: 8px; height: 8px; background: #94A3B8; border-radius: 2px;"></span> Completed</span>
        </div>
    </div>

    <!-- Day Headers -->
    <div class="calendar-grid">
        <div class="calendar-day-header">Sun</div>
        <div class="calendar-day-header">Mon</div>
        <div class="calendar-day-header">Tue</div>
        <div class="calendar-day-header">Wed</div>
        <div class="calendar-day-header">Thu</div>
        <div class="calendar-day-header">Fri</div>
        <div class="calendar-day-header">Sat</div>
    </div>

    <!-- Calendar Month Cells -->
    <div class="calendar-grid">
        {{-- Leading empty cells for previous month --}}
        @for($i = 0; $i < $startDayOfWeek; $i++)
            <div class="calendar-cell empty"></div>
        @endfor

        {{-- Month Days --}}
        @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $dayData = $calendarDays[$d];
                $isToday = $dayData['date'] === now()->format('Y-m-d');
                $bookingsCount = $dayData['bookings']->count();
            @endphp
            <div class="calendar-cell {{ $isToday ? 'today' : '' }}">
                <div class="calendar-date-header">
                    <span class="calendar-date-num">{{ $d }}</span>
                    @if($bookingsCount > 0)
                        <span class="calendar-count-badge" style="background: #EEF2FF; color: #4338CA;">
                            {{ $bookingsCount }} {{ Str::plural('trip', $bookingsCount) }}
                        </span>
                    @endif
                </div>

                {{-- Bookings on this day --}}
                <div style="flex: 1; overflow-y: auto; max-height: 68px;">
                    @foreach($dayData['bookings'] as $booking)
                        @php
                            $pillClass = match($booking->booking_status) {
                                'Confirmed' => 'trip-confirmed',
                                'Vehicle Assigned', 'Driver Assigned' => 'trip-assigned',
                                'Trip Started', 'On The Way' => 'trip-started',
                                'Completed' => 'trip-completed',
                                default => 'trip-pending'
                            };
                        @endphp
                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="calendar-trip-pill {{ $pillClass }}" title="Booking #{{ $booking->booking_id }} · {{ $booking->pickup_location }} ➔ {{ $booking->destination }} ({{ $booking->customer?->name }})">
                            #{{ $booking->booking_id }} · {{ Str::limit($booking->destination, 10) }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endfor
    </div>
</div>

<!-- Monthly Dispatch Schedule Table -->
<div class="card">
    <div class="d-flex justify-between align-center mb-3 flex-wrap gap-2">
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--dark-900); display: flex; align-items: center; gap: 6px;">
                <x-icon name="list" :size="18" style="color: var(--primary);" />
                Dispatch & Schedule Overview ({{ $currentDate->format('F Y') }})
            </h3>
            <p style="font-size: 0.8rem; color: var(--slate-500); margin: 2px 0 0 0;">Chronological list of all trips booked in this calendar month.</p>
        </div>
        <span class="badge badge-primary font-weight-bold" style="font-size: 0.75rem;">
            {{ $sortedBookings->count() }} Total Trips
        </span>
    </div>

    @if($sortedBookings->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th style="font-size: 0.75rem; text-transform: uppercase;">Date & Time</th>
                        <th style="font-size: 0.75rem; text-transform: uppercase;">Booking ID</th>
                        <th style="font-size: 0.75rem; text-transform: uppercase;">Customer</th>
                        <th style="font-size: 0.75rem; text-transform: uppercase;">Route</th>
                        <th style="font-size: 0.75rem; text-transform: uppercase;">Vehicle & Driver</th>
                        <th style="font-size: 0.75rem; text-transform: uppercase;">Fare & Status</th>
                        <th style="text-align: right; font-size: 0.75rem; text-transform: uppercase;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sortedBookings as $bk)
                        <tr>
                            <td>
                                <strong style="color: var(--dark-950); display: block;">{{ $bk->travel_date ? $bk->travel_date->format('d M Y') : '—' }}</strong>
                                <span style="font-size: 0.75rem; color: var(--slate-500);">{{ $bk->travel_time ?: '09:00' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $bk->id) }}" style="font-weight: 800; color: #2563EB; text-decoration: none;">
                                    #{{ $bk->booking_id }}
                                </a>
                                @if($bk->booking_category === 'CORP')
                                    <span class="badge" style="background: #EDE9FE; color: #5B21B6; font-size: 0.65rem; display: block; width: fit-content; margin-top: 2px;">CORP</span>
                                @elseif($bk->booking_category === 'TP')
                                    <span class="badge" style="background: #FEF3C7; color: #92400E; font-size: 0.65rem; display: block; width: fit-content; margin-top: 2px;">TP</span>
                                @endif
                            </td>
                            <td>
                                <strong style="color: var(--dark-900); display: block;">{{ $bk->customer?->name ?: 'Guest' }}</strong>
                                <span style="font-size: 0.75rem; color: var(--slate-500);">{{ $bk->customer?->phone }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong style="color: var(--dark-900);">{{ $bk->pickup_location }}</strong>
                                    <span style="color: var(--slate-400); margin: 0 4px;">➔</span>
                                    <strong style="color: #2563EB;">{{ $bk->destination }}</strong>
                                </div>
                                <span style="font-size: 0.72rem; color: var(--slate-500);">{{ $bk->trip_type ?: 'Outstation' }}</span>
                            </td>
                            <td>
                                <div>
                                    <span style="font-weight: 600; color: var(--slate-800);">{{ $bk->vehicle ? $bk->vehicle->name . ' (' . $bk->vehicle->registration_number . ')' : '—' }}</span>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--slate-500);">
                                    Driver: {{ $bk->driver ? $bk->driver->name : 'Unassigned' }}
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong style="color: var(--dark-950);">₹{{ number_format($bk->total_amount, 2) }}</strong>
                                </div>
                                @php
                                    $stBadge = match($bk->booking_status) {
                                        'Confirmed' => 'badge-primary',
                                        'Vehicle Assigned', 'Driver Assigned' => 'badge-warning',
                                        'Trip Started', 'On The Way' => 'badge-success',
                                        'Completed' => 'badge-secondary',
                                        default => 'badge-danger'
                                    };
                                @endphp
                                <span class="badge {{ $stBadge }}" style="font-size: 0.7rem; padding: 2px 6px;">{{ $bk->booking_status }}</span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.bookings.show', $bk->id) }}" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--slate-400);">
            <x-icon name="calendar" :size="36" style="margin-bottom: 0.5rem; opacity: 0.4;" />
            <p style="font-size: 0.95rem; margin-bottom: 0.75rem; color: var(--slate-600); font-weight: 600;">No trips scheduled for {{ $currentDate->format('F Y') }}</p>
            <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">
                + Create New Booking
            </a>
        </div>
    @endif
</div>
@endsection
