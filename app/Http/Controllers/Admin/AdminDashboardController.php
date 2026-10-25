<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Enquiry;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\Vehicle;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // 11 Specific Dashboard Summary Cards required by specification:
        $todaysBookings = Booking::whereDate('travel_date', $today)->count();
        $upcomingBookings = Booking::where('travel_date', '>', $today)
            ->whereNotIn('booking_status', ['Completed', 'Cancelled'])
            ->count();
        $pendingEnquiries = Enquiry::whereIn('status', ['New', 'Contacted'])
            ->orWhereNull('status')
            ->count();
        $unassignedBookings = Booking::where(function ($q) {
            $q->whereNull('driver_id')->orWhereNull('vehicle_id');
        })->whereNotIn('booking_status', ['Completed', 'Cancelled'])->count();
        $availableVehicles = Vehicle::where('status', 'Available')->count();
        $availableDrivers = Driver::where('status', 'Available')->count();
        $ongoingTrips = Trip::where('journey_status', 'Ongoing')
            ->orWhere('status', 'Trip Started')
            ->count();
        $incompleteTrips = Trip::where('trip_status', 'Open')
            ->whereHas('booking', fn ($b) => $b->where('balance_amount', '>', 0))
            ->count();
        $pendingPayments = (float) Booking::where('booking_status', '!=', 'Cancelled')->sum('balance_amount');
        $outstandingExpenses = (float) Expense::sum('amount');
        $gstInvoicesCount = Invoice::count();

        // Summary Card Data Structure
        $summaryCards = [
            'todays_bookings' => [
                'title' => "Today's Bookings",
                'value' => $todaysBookings,
                'icon' => 'calendar',
                'color' => 'blue',
                'route' => route('admin.bookings.index', ['date' => $today]),
            ],
            'upcoming_bookings' => [
                'title' => 'Upcoming Bookings',
                'value' => $upcomingBookings,
                'icon' => 'clock',
                'color' => 'indigo',
                'route' => route('admin.bookings.index', ['filter' => 'upcoming']),
            ],
            'pending_enquiries' => [
                'title' => 'Pending Enquiries',
                'value' => $pendingEnquiries,
                'icon' => 'inbox',
                'color' => 'amber',
                'route' => route('admin.enquiries.index', ['status' => 'New']),
            ],
            'unassigned_bookings' => [
                'title' => 'Unassigned Bookings',
                'value' => $unassignedBookings,
                'icon' => 'alert-triangle',
                'color' => 'rose',
                'route' => route('admin.bookings.index', ['filter' => 'unassigned']),
            ],
            'available_vehicles' => [
                'title' => 'Available Vehicles',
                'value' => $availableVehicles,
                'icon' => 'truck',
                'color' => 'emerald',
                'route' => route('admin.vehicles.index', ['status' => 'Available']),
            ],
            'available_drivers' => [
                'title' => 'Available Drivers',
                'value' => $availableDrivers,
                'icon' => 'user-check',
                'color' => 'teal',
                'route' => route('admin.drivers.index', ['filter' => 'unassigned']),
            ],
            'ongoing_trips' => [
                'title' => 'Ongoing Trips',
                'value' => $ongoingTrips,
                'icon' => 'navigation',
                'color' => 'purple',
                'route' => route('admin.trips.index', ['status' => 'Trip Started']),
            ],
            'incomplete_trips' => [
                'title' => 'Incomplete Trips',
                'value' => $incompleteTrips,
                'icon' => 'pause-circle',
                'color' => 'orange',
                'route' => route('admin.trips.index', ['trip_status' => 'Open', 'payment_status' => 'pending_payment']),
            ],
            'pending_payments' => [
                'title' => 'Pending Payments',
                'value' => '₹'.number_format($pendingPayments, 2),
                'icon' => 'credit-card',
                'color' => 'red',
                'route' => route('admin.bookings.index', ['payment_status' => 'Pending']),
            ],
            'outstanding_expenses' => [
                'title' => 'Total Expenses',
                'value' => '₹'.number_format($outstandingExpenses, 2),
                'icon' => 'dollar-sign',
                'color' => 'slate',
                'route' => route('admin.expenses.index'),
            ],
            'gst_invoices' => [
                'title' => 'GST Invoices',
                'value' => $gstInvoicesCount,
                'icon' => 'file-text',
                'color' => 'cyan',
                'route' => route('admin.invoices.index'),
            ],
        ];

        // Quick-Action Buttons (Section 6 & 9)
        $quickActions = [
            ['title' => 'Create Booking', 'route' => route('admin.bookings.create'), 'icon' => 'plus-circle', 'bg' => 'bg-emerald-600 hover:bg-emerald-700 text-white'],
            ['title' => 'Assign Driver', 'route' => route('admin.bookings.index', ['filter' => 'unassigned_driver']), 'icon' => 'user-plus', 'bg' => 'bg-indigo-600 hover:bg-indigo-700 text-white'],
            ['title' => 'Assign Vehicle', 'route' => route('admin.bookings.index', ['filter' => 'unassigned_vehicle']), 'icon' => 'truck', 'bg' => 'bg-blue-600 hover:bg-blue-700 text-white'],
            ['title' => 'Add Customer', 'route' => route('admin.customers.create'), 'icon' => 'user-plus', 'bg' => 'bg-blue-600 hover:bg-blue-700 text-white'],
            ['title' => 'Add Driver', 'route' => route('admin.drivers.create'), 'icon' => 'user-check', 'bg' => 'bg-slate-700 hover:bg-slate-800 text-white'],
            ['title' => 'Add Vehicle', 'route' => route('admin.vehicles.create'), 'icon' => 'plus', 'bg' => 'bg-slate-700 hover:bg-slate-800 text-white'],
            ['title' => 'Add Pickup Location', 'route' => route('admin.locations.create'), 'icon' => 'map-pin', 'bg' => 'bg-amber-600 hover:bg-amber-700 text-white'],
            ['title' => 'Add Tour Package', 'route' => route('admin.tour-packages.create'), 'icon' => 'compass', 'bg' => 'bg-teal-600 hover:bg-teal-700 text-white'],
            ['title' => 'Add Expense', 'route' => route('admin.expenses.create'), 'icon' => 'dollar-sign', 'bg' => 'bg-rose-600 hover:bg-rose-700 text-white'],
            ['title' => 'Create Invoice', 'route' => route('admin.invoices.index'), 'icon' => 'file-text', 'bg' => 'bg-cyan-600 hover:bg-cyan-700 text-white'],
            ['title' => 'View Enquiries', 'route' => route('admin.enquiries.index'), 'icon' => 'inbox', 'bg' => 'bg-purple-600 hover:bg-purple-700 text-white'],
        ];

        $recentBookings = Booking::with(['customer', 'vehicle', 'driver'])
            ->latest()
            ->take(8)
            ->get();

        $recentPayments = Payment::with(['booking', 'customer'])
            ->latest()
            ->take(6)
            ->get();

        $unreadNotificationsCount = Notification::whereNull('user_id')->where('is_read', false)->count();

        // Urgent route queries awaiting custom quotation
        $recentQueries = Enquiry::with('convertedBooking')
            ->whereIn('status', ['New', 'Contacted'])
            ->orWhereNull('status')
            ->latest()
            ->take(6)
            ->get();

        $unreadNotifications = Notification::whereNull('user_id')
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        // Revenue chart data: last 6 months
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

        return view('admin.dashboard', compact(
            'summaryCards',
            'quickActions',
            'todaysBookings',
            'upcomingBookings',
            'pendingEnquiries',
            'unassignedBookings',
            'availableVehicles',
            'availableDrivers',
            'ongoingTrips',
            'incompleteTrips',
            'pendingPayments',
            'outstandingExpenses',
            'gstInvoicesCount',
            'recentBookings',
            'recentPayments',
            'unreadNotificationsCount',
            'recentQueries',
            'unreadNotifications',
            'revenueMonths',
            'revenueData'
        ));
    }
}
