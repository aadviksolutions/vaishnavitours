<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingStatusHistory;
use App\Models\Trip;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminTripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['booking.customer', 'vehicle', 'driver']);

        if ($request->filled('status')) {
            $query->where(function ($q) use ($request) {
                $q->where('status', $request->status)
                  ->orWhere('trip_status', $request->status);
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('id', $s)
                  ->orWhereHas('booking', function ($bq) use ($s) {
                      $bq->where('booking_id', 'like', "%{$s}%")
                         ->orWhere('pickup_location', 'like', "%{$s}%")
                         ->orWhere('destination', 'like', "%{$s}%");
                  });
            });
        }

        $trips = $query->latest()->paginate(15);

        return view('admin.trips.index', compact('trips'));
    }

    public function show(Trip $trip)
    {
        $trip->load(['booking.customer', 'booking.statusHistories.changer', 'vehicle', 'driver']);

        return view('admin.trips.show', compact('trip'));
    }

    public function update(Request $request, Trip $trip, BookingService $bookingService)
    {
        $data = $request->validate([
            'status' => 'required|string',
            'start_odometer' => 'nullable|integer',
            'end_odometer' => 'nullable|integer',
            'route_notes' => 'nullable|string',
        ]);

        $newStatus = $data['status'];

        $update = [
            'status' => $newStatus,
            'trip_status' => $newStatus,
            'start_odometer' => $data['start_odometer'],
            'end_odometer' => $data['end_odometer'],
            'route_notes' => $data['route_notes'],
            'notes' => $data['route_notes'],
        ];

        if ($newStatus === 'Trip Started' && !$trip->started_at) {
            $update['started_at'] = now();
        } elseif ($newStatus === 'Completed' && !$trip->completed_at) {
            $update['completed_at'] = now();
        }

        $trip->update($update);

        if ($trip->booking && $trip->booking->booking_status !== $newStatus) {
            try {
                $bookingService->updateStatus($trip->booking, $newStatus, "Trip tracking updated status to {$newStatus}.", Auth::user());
            } catch (ValidationException $e) {
                // If direct status progression requires specific transition, update booking directly and log history
                $oldStatus = $trip->booking->booking_status;
                $trip->booking->update(['booking_status' => $newStatus]);

                BookingStatusHistory::create([
                    'booking_id' => $trip->booking_id,
                    'changed_by' => Auth::id(),
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'remarks' => "Trip console updated status to {$newStatus}.",
                    'comment' => "Trip console updated status to {$newStatus}.",
                    'created_at' => now(),
                ]);

                if ($newStatus === 'Completed') {
                    if ($trip->vehicle && $trip->vehicle->status === 'On Trip') {
                        $trip->vehicle->update(['status' => 'Available']);
                    }
                    if ($trip->driver && in_array($trip->driver->status, ['Assigned', 'On Trip'])) {
                        $trip->driver->update(['status' => 'Available']);
                    }
                }
            }
        }

        return back()->with('success', "Trip #{$trip->id} status and operational records updated!");
    }
}
