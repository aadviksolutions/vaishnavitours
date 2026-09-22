<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Create a new booking with initial Pending status and admin notification.
     */
    public function createBooking(array $data, ?User $user = null): Booking
    {
        return DB::transaction(function () use ($data, $user) {
            // If customer is not logged in, find existing by phone or email, or create new without duplicate collisions
            if (!$user) {
                $phone = trim($data['mobile'] ?? $data['phone'] ?? '');
                $email = trim($data['email'] ?? '');

                $existingUser = null;
                if ($phone) {
                    $existingUser = User::where('phone', $phone)->first();
                }
                if (!$existingUser && $email) {
                    $existingUser = User::where('email', $email)->first();
                }

                if ($existingUser) {
                    $user = $existingUser;
                } else {
                    $user = User::create([
                        'name' => $data['customer_name'] ?? 'Traveler',
                        'phone' => $phone ?: null,
                        'email' => $email ?: ($phone ? $phone . '@vaishnavitours.in' : 'guest_' . uniqid() . '@vaishnavitours.in'),
                        'password' => bcrypt('Password@123'),
                        'role' => 'customer',
                        'is_active' => true,
                    ]);

                    Customer::create([
                        'user_id' => $user->id,
                        'city' => 'Bilaspur',
                        'state' => 'Chhattisgarh',
                        'pincode' => '495001',
                    ]);
                }
            }

            // Determine vehicle category / model
            $vehicle = null;
            if (!empty($data['vehicle_id'])) {
                $vehicle = Vehicle::find($data['vehicle_id']);
            }

            // Estimate total amount
            $tripType = $data['trip_type'] ?? 'One-Way';
            $totalAmount = (float)($data['total_amount'] ?? 0);
            if ($totalAmount <= 0 && $vehicle) {
                $totalAmount = $this->calculateEstimatedFare($tripType, $vehicle);
            }
            if ($totalAmount <= 0) {
                $totalAmount = 2500.00;
            }

            $paidAmount = (float)($data['paid_amount'] ?? 0.00);
            $balanceAmount = max(0, $totalAmount - $paidAmount);
            $paymentStatus = $paidAmount >= $totalAmount && $totalAmount > 0 ? 'Paid' : ($paidAmount > 0 ? 'Partial' : 'Pending');

            $booking = Booking::create([
                'booking_id' => Booking::generateBookingId(),
                'customer_id' => $user->id,
                'trip_type' => $tripType,
                'pickup_location' => $data['pickup_location'],
                'destination' => $data['destination'],
                'travel_date' => $data['travel_date'],
                'travel_time' => $data['travel_time'],
                'return_date' => $data['return_date'] ?? null,
                'vehicle_id' => $vehicle?->id,
                'driver_id' => null,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'payment_status' => $paymentStatus,
                'booking_status' => 'Pending', // Strictly initial status: Pending
                'notes' => $data['notes'] ?? null,
            ]);

            // Status History log
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $user->id,
                'old_status' => null,
                'new_status' => 'Pending',
                'remarks' => 'Booking request submitted by customer. Awaiting confirmation.',
                'comment' => 'Booking request submitted by customer. Awaiting confirmation.',
                'created_at' => now(),
            ]);

            // Admin notification: Section 15: "New booking VT-1006 received."
            Notification::create([
                'user_id' => null, // Broadcast to admins
                'title' => 'New Booking: #' . $booking->booking_id,
                'message' => 'New booking ' . $booking->booking_id . ' received.',
                'type' => 'booking',
                'action_url' => '/admin/bookings/' . $booking->id,
            ]);

            // Customer notification
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Booking Request Received: #' . $booking->booking_id,
                'message' => 'Your booking request ' . $booking->booking_id . ' has been received. Our dispatch team will confirm shortly.',
                'type' => 'booking',
                'action_url' => '/customer/bookings/' . $booking->id,
            ]);

            return $booking;
        });
    }

    /**
     * Transition booking status following the strict workflow:
     * Pending -> Confirmed -> Vehicle Assigned -> Driver Assigned -> Trip Started -> On The Way -> Completed (or Cancelled)
     */
    public function updateStatus(Booking $booking, string $newStatus, ?string $remarks = null, ?User $changedBy = null): Booking
    {
        return DB::transaction(function () use ($booking, $newStatus, $remarks, $changedBy) {
            $oldStatus = $booking->booking_status;
            if ($oldStatus === $newStatus) {
                return $booking;
            }

            if (!$booking->canTransitionTo($newStatus)) {
                throw ValidationException::withMessages([
                    'booking_status' => ["Invalid status transition from '{$oldStatus}' to '{$newStatus}'."],
                ]);
            }

            $booking->update([
                'booking_status' => $newStatus,
            ]);

            // If status is Driver Assigned, create or activate related trip
            if ($newStatus === 'Driver Assigned') {
                Trip::updateOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'vehicle_id' => $booking->vehicle_id,
                        'driver_id' => $booking->driver_id,
                        'status' => 'Driver Assigned',
                        'trip_status' => 'Driver Assigned',
                    ]
                );
            }

            // Sync Trip status and timestamps if Trip exists
            if ($booking->trip) {
                $tripUpdate = [
                    'status' => $newStatus,
                    'trip_status' => $newStatus,
                ];
                if ($newStatus === 'Trip Started' && !$booking->trip->started_at) {
                    $tripUpdate['started_at'] = now();
                } elseif ($newStatus === 'Completed' && !$booking->trip->completed_at) {
                    $tripUpdate['completed_at'] = now();
                }
                $booking->trip->update($tripUpdate);
            }

            // Release resources on completion or cancellation
            if ($newStatus === 'Completed' || $newStatus === 'Cancelled') {
                if ($booking->vehicle && $booking->vehicle->status === 'On Trip') {
                    $booking->vehicle->update(['status' => 'Available']);
                }
                if ($booking->driver && in_array($booking->driver->status, ['Assigned', 'On Trip'])) {
                    $booking->driver->update(['status' => 'Available']);
                }
            }

            // If transition to Trip Started, ensure vehicle and driver are marked On Trip
            if ($newStatus === 'Trip Started' || $newStatus === 'On The Way') {
                if ($booking->vehicle && $booking->vehicle->status !== 'Maintenance') {
                    $booking->vehicle->update(['status' => 'On Trip']);
                }
                if ($booking->driver && $booking->driver->status !== 'Inactive') {
                    $booking->driver->update(['status' => 'On Trip']);
                }
            }

            // Audit Status History
            $historyRemarks = $remarks ?: "Status transitioned from {$oldStatus} to {$newStatus}.";
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $changedBy ? $changedBy->id : null,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks' => $historyRemarks,
                'comment' => $historyRemarks,
                'created_at' => now(),
            ]);

            // Customer notification messages tailored to prompt requirement 14
            $customerMessage = match ($newStatus) {
                'Confirmed' => "Your booking {$booking->booking_id} has been confirmed.",
                'Vehicle Assigned' => "Vehicle has been assigned to booking {$booking->booking_id}.",
                'Driver Assigned' => "Driver has been assigned.",
                'Trip Started' => "Your trip has started.",
                'On The Way' => "Your cab is on the way for booking {$booking->booking_id}.",
                'Completed' => "Your trip has been completed.",
                'Cancelled' => "Your booking {$booking->booking_id} has been cancelled.",
                default => "Your booking {$booking->booking_id} is now {$newStatus}.",
            };

            Notification::create([
                'user_id' => $booking->customer_id,
                'title' => "Booking #{$booking->booking_id} {$newStatus}",
                'message' => $customerMessage,
                'type' => 'trip',
                'action_url' => '/customer/bookings/' . $booking->id,
            ]);

            return $booking;
        });
    }

    /**
     * Assign vehicle to booking with availability and overlap validation.
     */
    public function assignVehicle(Booking $booking, int $vehicleId, ?User $admin = null): Booking
    {
        return DB::transaction(function () use ($booking, $vehicleId, $admin) {
            $vehicle = Vehicle::findOrFail($vehicleId);

            // Validation: Only Available vehicles allowed
            if ($vehicle->status !== 'Available') {
                throw ValidationException::withMessages([
                    'vehicle_id' => ["Selected vehicle is currently {$vehicle->status}. Only Available vehicles can be assigned."],
                ]);
            }

            // Overlap check: Prevent same vehicle being assigned to two active trips at overlapping travel times
            $overlapExists = Booking::where('vehicle_id', $vehicle->id)
                ->where('id', '!=', $booking->id)
                ->where('travel_date', $booking->travel_date->format('Y-m-d'))
                ->whereIn('booking_status', ['Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way'])
                ->exists();

            if ($overlapExists) {
                throw ValidationException::withMessages([
                    'vehicle_id' => ['This vehicle is already assigned to another trip at this time.'],
                ]);
            }

            // Release previous vehicle if changing
            if ($booking->vehicle_id && $booking->vehicle_id !== $vehicle->id) {
                $prev = Vehicle::find($booking->vehicle_id);
                if ($prev && $prev->status === 'On Trip') {
                    $prev->update(['status' => 'Available']);
                }
            }

            // Update booking vehicle
            $booking->update(['vehicle_id' => $vehicle->id]);

            // Vehicle status: Available -> On Trip
            $vehicle->update(['status' => 'On Trip']);

            // If trip exists, update vehicle_id
            if ($booking->trip) {
                $booking->trip->update(['vehicle_id' => $vehicle->id]);
            }

            // Booking status: Confirmed -> Vehicle Assigned (or advance from Pending)
            $oldStatus = $booking->booking_status;
            $newStatus = 'Vehicle Assigned';

            $remarks = "Vehicle {$vehicle->name} ({$vehicle->registration_number}) assigned by " . ($admin?->name ?? 'Administrator') . '.';

            $booking->update(['booking_status' => $newStatus]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $admin?->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks' => $remarks,
                'comment' => $remarks,
                'created_at' => now(),
            ]);

            // Customer notification
            Notification::create([
                'user_id' => $booking->customer_id,
                'title' => "Vehicle Assigned: #{$booking->booking_id}",
                'message' => "Vehicle has been assigned to booking {$booking->booking_id}.",
                'type' => 'trip',
                'action_url' => '/customer/bookings/' . $booking->id,
            ]);

            return $booking;
        });
    }

    /**
     * Assign driver to booking with availability and overlap validation,
     * and automatically create or activate the related trip.
     */
    public function assignDriver(Booking $booking, int $driverId, ?User $admin = null): Booking
    {
        return DB::transaction(function () use ($booking, $driverId, $admin) {
            $driver = Driver::findOrFail($driverId);

            // Validation: Only Available drivers allowed
            if ($driver->status !== 'Available') {
                throw ValidationException::withMessages([
                    'driver_id' => ["Selected chauffeur is currently {$driver->status}. Only Available drivers can be assigned."],
                ]);
            }

            // Overlap check: Prevent same driver being assigned to two active trips at overlapping travel times
            $overlapExists = Booking::where('driver_id', $driver->id)
                ->where('id', '!=', $booking->id)
                ->where('travel_date', $booking->travel_date->format('Y-m-d'))
                ->whereIn('booking_status', ['Driver Assigned', 'Trip Started', 'On The Way'])
                ->exists();

            if ($overlapExists) {
                throw ValidationException::withMessages([
                    'driver_id' => ['This driver is already assigned to another trip at this time.'],
                ]);
            }

            // Release previous driver if changing
            if ($booking->driver_id && $booking->driver_id !== $driver->id) {
                $prev = Driver::find($booking->driver_id);
                if ($prev && $prev->status === 'Assigned') {
                    $prev->update(['status' => 'Available']);
                }
            }

            // Update booking driver
            $booking->update(['driver_id' => $driver->id]);

            // Driver status: Available -> Assigned
            $driver->update(['status' => 'Assigned']);

            // Booking status: Vehicle Assigned -> Driver Assigned
            $oldStatus = $booking->booking_status;
            $newStatus = 'Driver Assigned';

            $booking->update(['booking_status' => $newStatus]);

            // Section 10: Once a booking becomes Driver Assigned, create or activate the related trip
            Trip::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'vehicle_id' => $booking->vehicle_id,
                    'driver_id' => $driver->id,
                    'status' => 'Driver Assigned',
                    'trip_status' => 'Driver Assigned',
                ]
            );

            $remarks = "Chauffeur {$driver->name} ({$driver->mobile}) assigned by " . ($admin?->name ?? 'Administrator') . '.';

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $admin?->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks' => $remarks,
                'comment' => $remarks,
                'created_at' => now(),
            ]);

            // Customer notification: Section 14: "Driver has been assigned."
            Notification::create([
                'user_id' => $booking->customer_id,
                'title' => "Driver Assigned: #{$booking->booking_id}",
                'message' => 'Driver has been assigned.',
                'type' => 'trip',
                'action_url' => '/customer/bookings/' . $booking->id,
            ]);

            return $booking;
        });
    }

    /**
     * Cancel a booking (admin action or approved customer cancellation request).
     */
    public function cancelBooking(Booking $booking, string $reason, ?User $cancelledBy = null): Booking
    {
        return DB::transaction(function () use ($booking, $reason, $cancelledBy) {
            $oldStatus = $booking->booking_status;

            // Release vehicle and driver if assigned
            if ($booking->vehicle && $booking->vehicle->status === 'On Trip') {
                $booking->vehicle->update(['status' => 'Available']);
            }
            if ($booking->driver && in_array($booking->driver->status, ['Assigned', 'On Trip'])) {
                $booking->driver->update(['status' => 'Available']);
            }

            if ($booking->trip) {
                $booking->trip->update([
                    'status' => 'Cancelled',
                    'trip_status' => 'Cancelled',
                ]);
            }

            $booking->update([
                'booking_status' => 'Cancelled',
                'cancellation_status' => 'Approved',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledBy?->id,
            ]);

            $remarks = "Booking cancelled by " . ($cancelledBy?->name ?? 'Administrator') . ". Reason: {$reason}";

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $cancelledBy?->id,
                'old_status' => $oldStatus,
                'new_status' => 'Cancelled',
                'remarks' => $remarks,
                'comment' => $remarks,
                'created_at' => now(),
            ]);

            Notification::create([
                'user_id' => $booking->customer_id,
                'title' => "Booking Cancelled: #{$booking->booking_id}",
                'message' => "Your booking {$booking->booking_id} has been cancelled. Reason: {$reason}",
                'type' => 'booking',
                'action_url' => '/customer/bookings/' . $booking->id,
            ]);

            return $booking;
        });
    }

    /**
     * Estimate rough fare based on service type & vehicle rate.
     */
    public function calculateEstimatedFare(string $tripType, Vehicle $vehicle): float
    {
        $rate = (float)($vehicle->per_km_rate ?: 14.00);

        return match ($tripType) {
            'Airport Transfer' => round($rate * 110, -1),
            'Local Hourly' => round((float)($vehicle->per_hour_rate ?: 250) * 8, -1),
            'Round-Trip' => round($rate * 300, -1),
            'Emergency' => round($rate * 80 + 500, -1),
            default => round($rate * 140, -1),
        };
    }
}
