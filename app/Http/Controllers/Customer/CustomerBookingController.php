<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CustomerBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle', 'driver', 'trip', 'invoice'])
            ->where('customer_id', Auth::id());

        // Section 13 filters: Upcoming, Completed, Cancelled, All
        $filter = $request->get('filter', 'all');

        if ($filter === 'upcoming') {
            $query->whereIn('booking_status', ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way']);
        } elseif ($filter === 'completed') {
            $query->where('booking_status', 'Completed');
        } elseif ($filter === 'cancelled') {
            $query->where('booking_status', 'Cancelled');
        } elseif ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                  ->orWhere('pickup_location', 'like', "%{$s}%")
                  ->orWhere('destination', 'like', "%{$s}%");
            });
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        return view('customer.bookings.index', compact('bookings', 'filter'));
    }

    public function show(Booking $booking)
    {
        Gate::authorize('view', $booking);

        $booking->load(['vehicle', 'driver', 'trip', 'payments', 'invoice', 'statusHistories.changer']);

        return view('customer.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        Gate::authorize('cancel', $booking);

        if (in_array($booking->booking_status, ['Trip Started', 'On The Way', 'Completed', 'Cancelled'])) {
            return back()->with('error', 'This booking cannot be cancelled because it is already ' . strtolower($booking->booking_status) . '.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $booking->update([
            'cancellation_status' => 'Requested',
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'changed_by' => Auth::id(),
            'old_status' => $booking->booking_status,
            'new_status' => $booking->booking_status,
            'remarks' => 'Cancellation requested by customer. Reason: ' . $request->cancellation_reason,
            'comment' => 'Cancellation requested by customer. Reason: ' . $request->cancellation_reason,
            'created_at' => now(),
        ]);

        // Notify Admin
        Notification::create([
            'user_id' => null,
            'title' => 'Cancellation Requested: #' . $booking->booking_id,
            'message' => 'Customer ' . Auth::user()->name . ' requested cancellation for booking ' . $booking->booking_id . '. Reason: ' . $request->cancellation_reason,
            'type' => 'booking',
            'action_url' => '/admin/bookings/' . $booking->id,
        ]);

        return back()->with('success', 'Your cancellation request has been submitted to dispatch for review.');
    }
}
