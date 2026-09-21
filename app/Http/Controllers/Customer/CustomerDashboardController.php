<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookings = Booking::with(['vehicle', 'driver', 'trip'])
            ->where('customer_id', $user->id)
            ->latest()
            ->get();

        $stats = [
            'total_bookings' => $bookings->count(),
            'upcoming_trips' => $bookings->whereIn('booking_status', ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned'])->count(),
            'completed_trips' => $bookings->where('booking_status', 'Completed')->count(),
            'pending_balance' => $bookings->where('balance_amount', '>', 0)->sum('balance_amount'),
        ];

        $upcomingTrip = $bookings->first(function ($b) {
            return in_array($b->booking_status, ['Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way']);
        });

        $recentBookings = $bookings->take(5);
        $notifications = Notification::where('user_id', $user->id)->latest()->take(5)->get();

        return view('customer.dashboard', compact('user', 'stats', 'upcomingTrip', 'recentBookings', 'notifications'));
    }
}
