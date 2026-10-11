<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Vehicle;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = date('Y-m-d');

        $totalBookings = Booking::count();
        $todaysTrips = Booking::whereDate('travel_date', $today)->count();
        $upcomingTrips = Booking::where('travel_date', '>', $today)
            ->whereIn('booking_status', ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned'])
            ->count();

        $totalRevenue = Booking::where('booking_status', '!=', 'Cancelled')->sum('total_amount');
        $collectedAmount = Payment::where('status', 'Success')->sum('amount');
        $pendingPayments = Booking::where('booking_status', '!=', 'Cancelled')->sum('balance_amount');

        $activeVehicles = Vehicle::where('status', '!=', 'Inactive')->count();
        $availableVehicles = Vehicle::where('status', 'Available')->count();
        $activeDrivers = Driver::where('status', '!=', 'Inactive')->count();

        $recentBookings = Booking::with(['customer', 'vehicle', 'driver'])
            ->latest()
            ->take(8)
            ->get();

        $recentPayments = Payment::with(['booking', 'customer'])
            ->latest()
            ->take(6)
            ->get();

        // Status breakdown
        $statusCounts = Booking::selectRaw('booking_status, count(*) as count')
            ->groupBy('booking_status')
            ->pluck('count', 'booking_status')
            ->toArray();

        // Notification count
        $unreadNotificationsCount = Notification::whereNull('user_id')->where('is_read', false)->count();

        // Revenue chart data: last 6 months (Real database records)
        $revenueMonths = [];
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->format('M Y');
            $rev = Payment::where('status', 'Success')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');
            $revenueMonths[] = $monthLabel;
            $revenueData[] = round((float) $rev, 2);
        }

        // Module 1: Daily, Weekly, and Monthly Booking & Revenue Statistics
        $dailyStats = [
            'bookings' => Booking::whereDate('created_at', now()->toDateString())->count(),
            'travels_today' => Booking::whereDate('travel_date', now()->toDateString())->count(),
            'completed' => Booking::whereDate('travel_date', now()->toDateString())->where('booking_status', 'Completed')->count(),
            'revenue' => (float) Payment::where('status', 'Success')->whereDate('created_at', now()->toDateString())->sum('amount'),
        ];

        $weeklyStats = [
            'bookings' => Booking::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'completed' => Booking::whereBetween('travel_date', [now()->startOfWeek(), now()->endOfWeek()])->where('booking_status', 'Completed')->count(),
            'revenue' => (float) Payment::where('status', 'Success')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount'),
        ];

        $monthlyStats = [
            'bookings' => Booking::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'completed' => Booking::whereBetween('travel_date', [now()->startOfMonth(), now()->endOfMonth()])->where('booking_status', 'Completed')->count(),
            'revenue' => (float) Payment::where('status', 'Success')->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
        ];

        return view('admin.dashboard', compact(
            'totalBookings',
            'todaysTrips',
            'upcomingTrips',
            'totalRevenue',
            'collectedAmount',
            'pendingPayments',
            'activeVehicles',
            'availableVehicles',
            'activeDrivers',
            'recentBookings',
            'recentPayments',
            'statusCounts',
            'unreadNotificationsCount',
            'revenueMonths',
            'revenueData',
            'dailyStats',
            'weeklyStats',
            'monthlyStats'
        ));
    }
}
