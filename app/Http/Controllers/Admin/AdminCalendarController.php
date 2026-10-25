<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminCalendarController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $currentDate = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $currentDate->daysInMonth;
        $startDayOfWeek = $currentDate->copy()->startOfMonth()->dayOfWeek; // 0 (Sun) to 6 (Sat)

        $monthStart = $currentDate->copy()->startOfMonth()->format('Y-m-d');
        $monthEnd = $currentDate->copy()->endOfMonth()->format('Y-m-d');

        // Fetch all active/relevant bookings covering this month
        $bookings = Booking::with(['customer', 'vehicle', 'driver'])
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('travel_date', [$monthStart, $monthEnd])
                    ->orWhere(function ($sub) use ($monthStart, $monthEnd) {
                        $sub->whereNotNull('return_date')
                            ->where('travel_date', '<=', $monthEnd)
                            ->where('return_date', '>=', $monthStart);
                    });
            })
            ->where('booking_status', '!=', 'Cancelled')
            ->get();

        // Total fleet and drivers
        $totalVehicles = Vehicle::where('status', '!=', 'Inactive')->count();
        $totalDrivers = Driver::where('status', '!=', 'Inactive')->count();

        // Organize bookings by day of month: 1..$daysInMonth
        $calendarDays = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayDate = Carbon::createFromDate($year, $month, $d)->format('Y-m-d');

            $dayBookings = $bookings->filter(function ($b) use ($dayDate) {
                $start = $b->travel_date->format('Y-m-d');
                $end = ($b->return_date ?? $b->travel_date)->format('Y-m-d');

                return $dayDate >= $start && $dayDate <= $end;
            });

            $assignedVehicleIds = $dayBookings->pluck('vehicle_id')->filter()->unique();
            $assignedDriverIds = $dayBookings->pluck('driver_id')->filter()->unique();

            $calendarDays[$d] = [
                'date' => $dayDate,
                'day' => $d,
                'bookings' => $dayBookings,
                'vehicles_assigned' => $assignedVehicleIds->count(),
                'vehicles_available' => max(0, $totalVehicles - $assignedVehicleIds->count()),
                'drivers_assigned' => $assignedDriverIds->count(),
                'drivers_available' => max(0, $totalDrivers - $assignedDriverIds->count()),
            ];
        }

        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();
        $sortedBookings = $bookings->sortBy('travel_date')->values();

        return view('admin.calendar.index', compact(
            'year',
            'month',
            'currentDate',
            'daysInMonth',
            'startDayOfWeek',
            'calendarDays',
            'totalVehicles',
            'totalDrivers',
            'prevDate',
            'nextDate',
            'sortedBookings'
        ));
    }
}
