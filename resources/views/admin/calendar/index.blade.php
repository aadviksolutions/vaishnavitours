@extends('layouts.admin')

@section('title', 'Advance Booking Calendar')
@section('page_title', 'Booking Calendar')

@push('styles')
<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }
    .calendar-day-header {
        text-align: center;
        font-weight: 800;
        font-size: 0.8rem;
        color: var(--slate-600);
        padding: 8px 4px;
        background: var(--slate-100);
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .calendar-cell {
        min-height: 125px;
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: 8px;
        padding: 6px 8px;
        display: flex;
        flex-direction: column;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .calendar-cell:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .calendar-cell.today {
        border: 2px solid var(--primary);
        background: #FFFDF7;
    }
    .calendar-cell.empty {
        background: var(--slate-50);
        border-color: transparent;
        min-height: 80px;
    }
    .calendar-date-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }
    .calendar-date-num {
        font-weight: 800;
        font-size: 0.95rem;
        color: var(--dark-900);
    }
    .calendar-cell.today .calendar-date-num {
        background: var(--primary);
        color: #000;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    .calendar-badge {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 5px;
        border-radius: 4px;
        line-height: 1;
    }
    .calendar-trip-pill {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 6px;
        border-radius: 4px;
        margin-bottom: 4px;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: transform 0.1s;
    }
    .calendar-trip-pill:hover {
        transform: translateX(2px);
    }
    .trip-confirmed { background: #E0E7FF; color: #3730A3; border-left: 3px solid #4F46E5; }
    .trip-assigned { background: #FEF3C7; color: #92400E; border-left: 3px solid #F59E0B; }
    .trip-started { background: #D1FAE5; color: #065F46; border-left: 3px solid #10B981; }
    .trip-pending { background: #F1F5F9; color: #475569; border-left: 3px solid #94A3B8; }
</style>
@endpush

@section('content')
<div class="mb-4 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-900);">Advance Booking Calendar</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Interactive date-wise fleet & driver availability matrix.</p>
    </div>
    
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.calendar.index', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}" class="btn btn-outline btn-sm">
            ← {{ $prevDate->format('M Y') }}
        </a>
        <a href="{{ route('admin.calendar.index', ['year' => now()->year, 'month' => now()->month]) }}" class="btn btn-primary btn-sm">
            Current Month ({{ now()->format('M Y') }})
        </a>
        <a href="{{ route('admin.calendar.index', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}" class="btn btn-outline btn-sm">
            {{ $nextDate->format('M Y') }} →
        </a>
    </div>
</div>

<!-- Fleet Capacity Info Banner -->
<div class="card mb-4" style="padding: 1rem 1.25rem; background: linear-gradient(135deg, #1E293B, #0F172A); color: #fff;">
    <div class="d-flex justify-between align-center flex-wrap gap-3">
        <div>
            <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">
                🗓️ {{ $currentDate->format('F Y') }}
            </div>
            <div style="font-size: 0.8rem; color: #94A3B8;">
                Total Fleet: <strong>{{ $totalVehicles }} Active Vehicles</strong> · Total Chauffeurs: <strong>{{ $totalDrivers }} Available Drivers</strong>
            </div>
        </div>
        <div class="d-flex gap-3 align-center">
            <span style="font-size: 0.75rem; color: #CBD5E1;"><span style="display:inline-block; width:10px; height:10px; background:#4F46E5; border-radius:2px; margin-right:4px;"></span>Confirmed</span>
            <span style="font-size: 0.75rem; color: #CBD5E1;"><span style="display:inline-block; width:10px; height:10px; background:#F59E0B; border-radius:2px; margin-right:4px;"></span>Assigned</span>
            <span style="font-size: 0.75rem; color: #CBD5E1;"><span style="display:inline-block; width:10px; height:10px; background:#10B981; border-radius:2px; margin-right:4px;"></span>On Trip</span>
        </div>
    </div>
</div>

<!-- Calendar Grid -->
<div class="card" style="padding: 1.25rem;">
    <!-- Day Headers -->
    <div class="calendar-grid mb-2">
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
        {{-- Leading empty cells --}}
        @for($i = 0; $i < $startDayOfWeek; $i++)
            <div class="calendar-cell empty"></div>
        @endfor

        {{-- Month Days --}}
        @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $dayData = $calendarDays[$d];
                $isToday = $dayData['date'] === now()->format('Y-m-d');
            @endphp
            <div class="calendar-cell {{ $isToday ? 'today' : '' }}">
                <div class="calendar-date-header">
                    <span class="calendar-date-num">{{ $d }}</span>
                    <div>
                        <span class="calendar-badge" style="background: #ECFDF5; color: #047857;" title="Available Vehicles">
                            🚗 {{ $dayData['vehicles_available'] }} free
                        </span>
                    </div>
                </div>

                {{-- Bookings on this day --}}
                <div style="flex: 1; overflow-y: auto; max-height: 100px;">
                    @forelse($dayData['bookings'] as $booking)
                        @php
                            $pillClass = match($booking->booking_status) {
                                'Confirmed' => 'trip-confirmed',
                                'Vehicle Assigned', 'Driver Assigned' => 'trip-assigned',
                                'Trip Started', 'On The Way' => 'trip-started',
                                default => 'trip-pending'
                            };
                        @endphp
                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="calendar-trip-pill {{ $pillClass }}" title="{{ $booking->pickup_location }} ➔ {{ $booking->destination }} ({{ $booking->booking_status }})">
                            #{{ $booking->booking_id }} · {{ Str::limit($booking->destination, 8) }}
                            @if($booking->vehicle)
                                <small style="display:block; font-size:0.65rem; opacity:0.8;">{{ $booking->vehicle->name }}</small>
                            @endif
                        </a>
                    @empty
                        <div style="font-size: 0.68rem; color: var(--slate-400); margin-top: 4px;">No trips scheduled</div>
                    @endforelse
                </div>
            </div>
        @endfor
    </div>
</div>
@endsection
