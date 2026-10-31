<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingStatusHistory;
use App\Models\Trip;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        if ($request->filled('trip_status')) {
            $query->where('trip_status', $request->trip_status);
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'pending_payment') {
                $query->whereHas('booking', function ($bq) {
                    $bq->where('balance_amount', '>', 0);
                });
            } elseif ($request->payment_status === 'paid') {
                $query->whereHas('booking', function ($bq) {
                    $bq->where('balance_amount', '<=', 0);
                });
            }
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

        $trips = $query->latest()->paginate(15)->withQueryString();

        $openCount = Trip::where('trip_status', 'Open')->count();
        $unpaidCompletedTripsCount = Trip::where('journey_status', 'Journey Completed')
            ->where('trip_status', 'Open')
            ->whereHas('booking', fn ($b) => $b->where('balance_amount', '>', 0))
            ->count();

        return view('admin.trips.index', compact('trips', 'openCount', 'unpaidCompletedTripsCount'));
    }

    public function show(Trip $trip)
    {
        $trip->load(['booking.customer', 'booking.statusHistories.changer', 'booking.payments', 'booking.expenses', 'vehicle', 'driver', 'closedByUser']);

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
            'start_odometer' => $data['start_odometer'],
            'end_odometer' => $data['end_odometer'],
            'route_notes' => $data['route_notes'],
            'notes' => $data['route_notes'],
        ];

        if ($newStatus === 'Trip Started' && ! $trip->started_at) {
            $update['started_at'] = now();
            $update['journey_status'] = 'Ongoing';
            $update['trip_status'] = 'Open';
        } elseif ($newStatus === 'Completed' || $newStatus === 'Journey Completed') {
            if (! $trip->completed_at) {
                $update['completed_at'] = now();
            }
            $update['journey_status'] = 'Journey Completed';

            // Critical Business Rule: Trip remains Open / Incomplete if balance is pending
            $booking = $trip->booking;
            if ($booking && (float) $booking->balance_amount > 0) {
                $update['trip_status'] = 'Open';
            } else {
                $update['trip_status'] = 'Closed';
                $update['closed_at'] = now();
                $update['closed_by'] = Auth::id();
                $update['closure_reason'] = 'Payment and journey both completed.';
            }
        }

        $trip->update($update);

        $canonicalStatus = ($newStatus === 'Journey Completed') ? 'Completed' : $newStatus;

        if ($trip->booking && $trip->booking->booking_status !== $canonicalStatus) {
            try {
                $bookingService->updateStatus($trip->booking, $canonicalStatus, "Trip tracking updated status to {$canonicalStatus}.", Auth::user());
            } catch (\Throwable $e) {
                $oldStatus = $trip->booking->booking_status;
                $trip->booking->update(['booking_status' => $canonicalStatus]);

                BookingStatusHistory::create([
                    'booking_id' => $trip->booking_id,
                    'changed_by' => Auth::id(),
                    'old_status' => $oldStatus,
                    'new_status' => $canonicalStatus,
                    'remarks' => "Trip console updated status to {$canonicalStatus}.",
                    'comment' => "Trip console updated status to {$canonicalStatus}.",
                    'created_at' => now(),
                ]);

                if ($canonicalStatus === 'Completed') {
                    if ($trip->vehicle && in_array($trip->vehicle->status, ['On Trip', 'Assigned'])) {
                        $trip->vehicle->update(['status' => 'Available']);
                    }
                    if ($trip->driver && in_array($trip->driver->status, ['Assigned', 'On Trip'])) {
                        $trip->driver->update(['status' => 'Available']);
                    }
                }
            }
        }

        // Automatic final fare recalculation based on actual odometer distance
        if (($newStatus === 'Completed' || $newStatus === 'Journey Completed') && $data['start_odometer'] !== null && $data['end_odometer'] !== null && $trip->booking) {
            try {
                $startOdo = (int) $data['start_odometer'];
                $endOdo = (int) $data['end_odometer'];
                if ($endOdo > $startOdo) {
                    $actualKm = $endOdo - $startOdo;
                    $booking = $trip->booking->fresh();
                    $details = is_array($booking->pricing_details) ? $booking->pricing_details : [];
                    $includedKm = (float) ($details['included_km'] ?? $details['distance_km'] ?? 0);
                    $extraRate = isset($details['extra_km_rate']) && $details['extra_km_rate'] !== null && $details['extra_km_rate'] !== ''
                        ? (float) $details['extra_km_rate']
                        : null;

                    if ($includedKm > 0 && $actualKm > $includedKm && empty($details['extra_km_charged'])) {
                        $extraKm = $actualKm - $includedKm;
                        if ($extraRate !== null && $extraRate > 0) {
                            $extraFare = round($extraKm * $extraRate, 2);

                            $details['actual_km'] = $actualKm;
                            $details['extra_km'] = $extraKm;
                            $details['extra_km_charge'] = $extraFare;
                            $details['extra_km_charged'] = true;

                            $newTotal = round((float) $booking->total_amount + $extraFare, 2);
                            $newBalance = round($newTotal - (float) $booking->paid_amount, 2);

                            $booking->update([
                                'total_amount' => $newTotal,
                                'balance_amount' => $newBalance,
                                'pricing_details' => $details,
                            ]);
                        } else {
                            $details['actual_km'] = $actualKm;
                            $details['extra_km'] = $extraKm;
                            $details['extra_km_unconfigured'] = true;
                            $booking->update(['pricing_details' => $details]);
                        }
                    } else {
                        $details['actual_km'] = $actualKm;
                        $booking->update(['pricing_details' => $details]);
                    }
                }
            } catch (\Throwable $odoErr) {
                Log::warning("Odometer calculation skipped for Trip #{$trip->id}: ".$odoErr->getMessage());
            }
        }

        return back()->with('success', "Trip #{$trip->id} status and operational records updated!");
    }

    public function closeTrip(Request $request, Trip $trip)
    {
        $booking = $trip->booking;
        $balance = (float) ($booking?->balance_amount ?? 0);

        // If payment collected was provided with the trip close submission, record it
        if ($booking && $request->filled('payment_collected') && (float) $request->input('payment_collected') > 0) {
            $collected = (float) $request->input('payment_collected');
            $newPaid = (float) $booking->paid_amount + $collected;
            $newBalance = max(0, (float) $booking->total_amount - $newPaid);
            $booking->update([
                'paid_amount' => $newPaid,
                'balance_amount' => $newBalance,
                'payment_status' => $newBalance <= 0 ? 'Paid' : 'Partial',
            ]);
            $balance = $newBalance;
        }

        if ($balance > 0 && ! $request->boolean('credit_exception')) {
            return back()->with('error', 'Trip cannot be closed: Pending balance of ₹'.number_format($balance, 2).' remains unpaid. Please collect payment or provide an authorized credit exception reason.');
        }

        $reason = $balance > 0
            ? 'Authorized credit exception: '.$request->input('exception_reason', 'Corporate / Credit terms approved by Admin')
            : 'Full payment received and journey verified.';

        $trip->update([
            'trip_status' => 'Closed',
            'status' => 'Completed',
            'journey_status' => 'Journey Completed',
            'completed_at' => $trip->completed_at ?: now(),
            'closed_at' => now(),
            'closed_by' => Auth::id(),
            'closure_reason' => $reason,
        ]);

        if ($trip->vehicle && in_array($trip->vehicle->status, ['On Trip', 'Assigned'])) {
            $trip->vehicle->update(['status' => 'Available']);
        }
        if ($trip->driver && in_array($trip->driver->status, ['Assigned', 'On Trip'])) {
            $trip->driver->update(['status' => 'Available']);
        }

        if ($booking) {
            $oldStatus = $booking->booking_status;
            $booking->update([
                'booking_status' => 'Completed',
                'payment_status' => $balance <= 0 ? 'Paid' : $booking->payment_status,
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => 'Completed',
                'remarks' => "Trip officially closed: {$reason}",
                'comment' => "Trip officially closed: {$reason}",
                'created_at' => now(),
            ]);
        }

        return back()->with('success', "Trip #{$trip->id} has been formally closed and marked Completed.");
    }
}
