<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date', date('Y-m-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));

        $query = Booking::whereBetween('travel_date', [$fromDate, $toDate]);

        $totalBookings = (clone $query)->count();
        $completedBookings = (clone $query)->where('booking_status', 'Completed')->count();
        $cancelledBookings = (clone $query)->where('booking_status', 'Cancelled')->count();

        $totalRevenue = (clone $query)->where('booking_status', '!=', 'Cancelled')->sum('total_amount');
        $collectedRevenue = (clone $query)->where('booking_status', '!=', 'Cancelled')->sum('paid_amount');
        $outstandingAmount = (clone $query)->where('booking_status', '!=', 'Cancelled')->sum('balance_amount');

        $paymentStatusCounts = (clone $query)->selectRaw('payment_status, count(*) as count')
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();

        // Vehicle utilization in range
        $vehicleStats = Vehicle::withCount(['bookings' => function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('travel_date', [$fromDate, $toDate]);
        }])->get();

        // Driver trips in range
        $driverStats = Driver::withCount(['bookings' => function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('travel_date', [$fromDate, $toDate]);
        }])->get();

        // Date-wise breakdown
        $dateWiseBookings = (clone $query)
            ->selectRaw('travel_date, count(*) as bookings_count, sum(total_amount) as daily_revenue')
            ->groupBy('travel_date')
            ->orderBy('travel_date', 'desc')
            ->get();

        return view('admin.reports.index', compact(
            'fromDate',
            'toDate',
            'totalBookings',
            'completedBookings',
            'cancelledBookings',
            'totalRevenue',
            'collectedRevenue',
            'outstandingAmount',
            'paymentStatusCounts',
            'vehicleStats',
            'driverStats',
            'dateWiseBookings'
        ));
    }
}
