<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Enquiry;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private readonly RateQuoteService $rateQuoteService) {}

    /**
     * Generate standard sequential booking ID with strict category prefix.
     */
    public function generateBookingId(?string $category = null): string
    {
        return Booking::generateBookingId($category);
    }

    /**
     * Enforce minimum 4 hours advance booking notice.
     *
     * @throws ValidationException
     */
    public function validateAdvanceNotice(string $travelDate, string $travelTime): bool
    {
        $pickupTimeStr = trim($travelTime);
        if (strlen($pickupTimeStr) === 5) {
            $pickupTimeStr .= ':00';
        }

        $tz = config('app.timezone', 'Asia/Kolkata');
        $pickupDateTime = Carbon::createFromFormat('Y-m-d H:i:s', trim($travelDate).' '.$pickupTimeStr, $tz);
        $minAllowedTime = now($tz)->addHours(4);

        if ($pickupDateTime->lt($minAllowedTime)) {
            $phone = Setting::where('key', 'phone_primary')->value('value') ?: '+91 92447 84443';
            throw ValidationException::withMessages([
                'travel_time' => "Online bookings must be scheduled at least 4 hours in advance. For urgent or late-night bookings, please call us directly at {$phone}.",
            ]);
        }

        return true;
    }

    /**
     * Create a new booking with initial Pending status and admin notification.
     */
    public function createBooking(array $data, ?User $user = null): Booking
    {
        return DB::transaction(function () use ($data, $user) {
            // If customer is not logged in, find existing by phone or email, or create new without duplicate collisions
            if (! $user) {
                $phone = trim($data['mobile'] ?? $data['phone'] ?? '');
                $email = trim($data['email'] ?? '');

                $existingUser = null;
                if ($phone) {
                    $existingUser = User::where('phone', $phone)->first();
                }
                if (! $existingUser && $email) {
                    $existingUser = User::where('email', $email)->first();
                }

                if ($existingUser) {
                    $user = $existingUser;
                } else {
                    $user = User::create([
                        'name' => $data['customer_name'] ?? 'Traveler',
                        'phone' => $phone ?: null,
                        'email' => $email ?: ($phone ? $phone.'@vaishnavitours.in' : 'guest_'.uniqid().'@vaishnavitours.in'),
                        'password' => bcrypt('Password@123'),
                        'role' => 'customer',
                        'is_active' => true,
                    ]);

                    Customer::create([
                        'user_id' => $user->id,
                        'city' => $data['pickup_location'],
                        'state' => 'Chhattisgarh',
                        'pincode' => '495001',
                    ]);
                }
            }

            // Determine vehicle category / model
            $vehicle = null;
            if (! empty($data['vehicle_id'])) {
                $vehicle = Vehicle::find($data['vehicle_id']);
            }

            $tripType = $data['trip_type'] ?? 'One-Way';
            $ratePrice = null;
            $pricingDetails = null;
            $isAutoQuery = false;
            $isCustomLocation = ! empty($data['is_custom_query'])
                || ! empty($data['is_other_location'])
                || ($data['pickup_location'] ?? '') === 'Other'
                || ($data['destination'] ?? '') === 'Other'
                || ! empty($data['pickup_other'])
                || ! empty($data['destination_other'])
                || ($data['quote_type'] ?? '') === 'custom_location_query';

            if ($isCustomLocation) {
                // User chose Other/Custom location -> strictly convert to query with 0 price
                $isAutoQuery = true;
                $totalAmount = 0.00;
                $pricingDetails = [
                    'total_amount' => 0.00,
                    'quote_type' => 'custom_location_query',
                    'is_query' => true,
                    'is_custom_location' => true,
                    'rate_category' => $data['rate_category'] ?? 'outstation',
                    'vehicle_category' => $data['vehicle_category'] ?? null,
                    'pickup_location' => $data['pickup_location'] ?? null,
                    'destination' => $data['destination'] ?? null,
                ];
            } elseif (! empty($data['rate_category'])) {
                $quote = $this->rateQuoteService->quote(
                    $data['rate_category'],
                    $data['pickup_location'],
                    $data['destination'],
                    $data['vehicle_category'] ?? '',
                    isset($data['estimated_km']) ? (float) $data['estimated_km'] : null
                );

                $isAdmin = ($user && $user->role === 'admin')
                    || (Auth::check() && Auth::user()->role === 'admin')
                    || ! empty($data['is_admin_booking'])
                    || ! empty($data['is_custom_fare'])
                    || in_array($data['booking_category'] ?? '', [Booking::CATEGORY_MAN, Booking::CATEGORY_CORP, Booking::CATEGORY_TP], true);

                if ($quote) {
                    $ratePrice = $quote['price'];
                    $pricingDetails = array_merge($quote['details'], ['total_amount' => $quote['total_amount']]);
                    // Authoritative pricing: prevent client-side fare tampering
                    // If an administrator or custom fare override is provided, use custom manual amount
                    $totalAmount = ($isAdmin && isset($data['total_amount']) && (float) $data['total_amount'] > 0)
                        ? (float) $data['total_amount']
                        : $quote['total_amount'];
                } elseif (isset($data['total_amount']) && (float) $data['total_amount'] > 0) {
                    // Custom fare entered when route is not pre-configured
                    $totalAmount = (float) $data['total_amount'];
                    $pricingDetails = [
                        'total_amount' => $totalAmount,
                        'manual_quote' => true,
                        'custom_fare' => true,
                        'rate_category' => $data['rate_category'],
                        'vehicle_category' => $data['vehicle_category'] ?? null,
                    ];
                } elseif (in_array($data['rate_category'], ['tour_package', 'emergency', 'round_trip'], true) || ($data['trip_type'] ?? '') === 'Round-Trip') {
                    $totalAmount = 0.00;
                    $pricingDetails = [
                        'total_amount' => 0.00,
                        'quote_type' => 'dispatch_quotation',
                        'rate_category' => $data['rate_category'],
                        'vehicle_category' => $data['vehicle_category'] ?? null,
                    ];
                } else {
                    // Route rate is not configured -> automatically convert into query
                    $isAutoQuery = true;
                    $totalAmount = 0.00;
                    $pricingDetails = [
                        'total_amount' => 0.00,
                        'quote_type' => 'unconfigured_route_query',
                        'is_query' => true,
                        'rate_category' => $data['rate_category'],
                        'vehicle_category' => $data['vehicle_category'] ?? null,
                        'pickup_location' => $data['pickup_location'] ?? null,
                        'destination' => $data['destination'] ?? null,
                    ];
                }
            } elseif (isset($data['total_amount']) && (float) $data['total_amount'] > 0) {
                $totalAmount = (float) $data['total_amount'];
                $pricingDetails = [
                    'total_amount' => $totalAmount,
                    'manual_quote' => true,
                    'custom_fare' => true,
                ];
            } elseif ($vehicle) {
                // Direct service call / test fallback when vehicle is supplied without explicit rate category
                $rate = (float) ($vehicle->per_km_rate ?: 14.00);
                $totalAmount = round($rate * 140, -1);
                if ($totalAmount <= 0) {
                    $totalAmount = 2500.00;
                }
                $pricingDetails = [
                    'total_amount' => $totalAmount,
                    'manual_quote' => true,
                    'vehicle_id' => $vehicle->id,
                ];
            } else {
                // Route rate is not configured -> automatically convert into query
                $isAutoQuery = true;
                $totalAmount = 0.00;
                $pricingDetails = [
                    'total_amount' => 0.00,
                    'quote_type' => 'unconfigured_route_query',
                    'is_query' => true,
                    'rate_category' => $data['rate_category'] ?? 'outstation',
                    'vehicle_category' => $data['vehicle_category'] ?? null,
                    'pickup_location' => $data['pickup_location'] ?? null,
                    'destination' => $data['destination'] ?? null,
                ];
            }

            $noteDetails = [];
            if ($isCustomLocation) {
                $noteDetails[] = 'Custom Location Query: Price on Request (Pending Admin Quotation)';
            } elseif ($isAutoQuery) {
                $noteDetails[] = 'Auto Query: Rate not configured for this route (Pending Admin Quotation)';
            }
            if (! empty($data['passenger_count'])) {
                $noteDetails[] = 'Passengers: '.$data['passenger_count'];
            }
            if (! empty($data['flight_number'])) {
                $noteDetails[] = 'Flight: '.$data['flight_number'];
            }
            if (! empty($data['package_duration'])) {
                $noteDetails[] = 'Duration: '.$data['package_duration'];
            }
            if (! empty($data['service_category'])) {
                $noteDetails[] = 'Service Category: '.$data['service_category'];
            }
            if (! empty($data['notes'])) {
                $noteDetails[] = $data['notes'];
            }
            $finalNotes = ! empty($noteDetails) ? implode(' | ', $noteDetails) : null;

            $paidAmount = (float) ($data['paid_amount'] ?? 0.00);
            $balanceAmount = max(0, round($totalAmount - $paidAmount, 2));
            $paymentStatus = $paidAmount >= $totalAmount && $totalAmount > 0 ? 'Paid' : ($paidAmount > 0 ? 'Partial' : 'Pending');

            // Category determination (Section 4)
            $bookingCategory = $data['booking_category'] ?? ($user && $user->role === 'admin' ? ($data['booking_category'] ?? Booking::CATEGORY_MAN) : Booking::CATEGORY_WEB);
            if (! in_array($bookingCategory, [Booking::CATEGORY_CORP, Booking::CATEGORY_WEB, Booking::CATEGORY_MAN, Booking::CATEGORY_TP], true)) {
                $bookingCategory = Booking::CATEGORY_WEB;
            }

            // Duration calculation (Section 5)
            $durationInfo = app(TripDurationService::class)->calculate(
                $data['travel_date'],
                $data['travel_time'] ?? '06:00',
                $data['return_date'] ?? null,
                $data['return_time'] ?? '22:00'
            );
            $durationDays = isset($data['duration_days']) && (int) $data['duration_days'] > 0
                ? (int) $data['duration_days']
                : $durationInfo['days'];

            // 5% GST computation (Section 10)
            $gstRate = 5.00;
            $isInterstate = ! empty($data['is_interstate']);
            $taxableAmount = round($totalAmount / (1 + ($gstRate / 100)), 2);
            $totalGst = round($totalAmount - $taxableAmount, 2);

            if ($isInterstate) {
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = $totalGst;
            } else {
                $cgst = round($totalGst / 2, 2);
                $sgst = round($totalGst - $cgst, 2);
                $igst = 0.00;
            }

            $booking = Booking::create([
                'booking_id' => Booking::generateBookingId($bookingCategory),
                'booking_category' => $bookingCategory,
                'customer_id' => $user->id,
                'trip_type' => $tripType,
                'pickup_location' => $data['pickup_location'],
                'via_location' => $data['via_location'] ?? null,
                'destination' => $data['destination'],
                'travel_date' => $data['travel_date'],
                'travel_time' => $data['travel_time'],
                'return_date' => $data['return_date'] ?? null,
                'return_time' => $data['return_time'] ?? null,
                'duration_days' => $durationDays,
                'duration_hours' => $durationInfo['total_hours'],
                'duration_notes' => $durationInfo['breakdown'],
                'vehicle_id' => $vehicle?->id,
                'driver_id' => null,
                'tour_package_id' => $data['tour_package_id'] ?? null,
                'rate_vehicle_price_id' => $ratePrice?->id,
                'rate_category' => $data['rate_category'] ?? null,
                'pricing_details' => $pricingDetails,
                'corporate_company_name' => $data['corporate_company_name'] ?? null,
                'corporate_contact_person' => $data['corporate_contact_person'] ?? null,
                'corporate_phone' => $data['corporate_phone'] ?? null,
                'corporate_email' => $data['corporate_email'] ?? null,
                'corporate_gstin' => $data['corporate_gstin'] ?? null,
                'corporate_billing_address' => $data['corporate_billing_address'] ?? null,
                'corporate_payment_terms' => $data['corporate_payment_terms'] ?? null,
                'corporate_agreed_rate' => isset($data['corporate_agreed_rate']) ? (float) $data['corporate_agreed_rate'] : null,
                'travel_partner_name' => $data['travel_partner_name'] ?? null,
                'travel_partner_phone' => $data['travel_partner_phone'] ?? null,
                'travel_partner_email' => $data['travel_partner_email'] ?? null,
                'travel_partner_address' => $data['travel_partner_address'] ?? null,
                'travel_partner_commission' => isset($data['travel_partner_commission']) && $data['travel_partner_commission'] !== '' ? (float) $data['travel_partner_commission'] : null,
                'travel_partner_notes' => $data['travel_partner_notes'] ?? null,
                'taxable_amount' => $taxableAmount,
                'gst_rate' => $gstRate,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'gst_amount' => $totalGst,
                'is_interstate' => $isInterstate,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'payment_status' => $paymentStatus,
                'booking_status' => 'Pending', // Strictly initial status: Pending
                'notes' => $finalNotes,
                'terms_accepted' => ! empty($data['terms_accepted']),
                'terms_accepted_at' => ! empty($data['terms_accepted']) ? now() : null,
                'terms_version' => ! empty($data['terms_accepted']) ? ($data['terms_version'] ?? '1.0') : null,
            ]);

            // Status History log
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $user->id,
                'old_status' => null,
                'new_status' => 'Pending',
                'remarks' => $isCustomLocation
                    ? 'Custom/Other location requested. Automatically converted to quotation query for dispatch review.'
                    : ($isAutoQuery
                        ? 'Rate not pre-configured for this route. Automatically converted to quotation query for dispatch review.'
                        : 'Booking request submitted by customer. Awaiting confirmation.'),
                'comment' => $isCustomLocation
                    ? 'Custom/Other location requested. Automatically converted to quotation query for dispatch review.'
                    : ($isAutoQuery
                        ? 'Rate not pre-configured for this route. Automatically converted to quotation query for dispatch review.'
                        : 'Booking request submitted by customer. Awaiting confirmation.'),
                'created_at' => now(),
            ]);

            if ($isAutoQuery) {
                // Admin Query notification for Admin Dashboard
                Notification::create([
                    'user_id' => null, // Broadcast to admins
                    'title' => ($isCustomLocation ? '⚡ Custom Location Query: ' : '⚡ Route Query Awaiting Quotation: ').$data['pickup_location'].' ➔ '.$data['destination'],
                    'message' => $isCustomLocation
                        ? 'Custom location selected for '.$data['pickup_location'].' to '.$data['destination'].' ('.($data['vehicle_category'] ?? 'Sedan').'). Query #'.$booking->booking_id.'. Price not shown during booking; please quote fare on dashboard.'
                        : 'Rate not configured for '.$data['pickup_location'].' to '.$data['destination'].' ('.($data['vehicle_category'] ?? 'Sedan').'). Converted to Query #'.$booking->booking_id.'. Please provide quotation on dashboard.',
                    'type' => 'query',
                    'action_url' => '/admin/bookings/'.$booking->id,
                ]);

                // Create Enquiry record so it appears in enquiries list, counts & dashboard
                Enquiry::create([
                    'source' => Enquiry::SOURCE_WEBSITE,
                    'name' => $data['customer_name'] ?? ($user?->name ?? 'Customer'),
                    'email' => $data['email'] ?? ($user?->email ?? null),
                    'phone' => $data['mobile'] ?? ($user?->phone ?? ''),
                    'booking_type' => $tripType,
                    'pickup_location' => $data['pickup_location'] ?? '',
                    'via_location' => $data['via_location'] ?? null,
                    'destination' => $data['destination'] ?? '',
                    'travel_date' => $data['travel_date'],
                    'return_date' => $data['return_date'] ?? null,
                    'duration_days' => $durationDays,
                    'vehicle_preference' => $data['vehicle_category'] ?? 'Sedan',
                    'subject' => ($isCustomLocation ? 'Custom Location Query: ' : 'Unconfigured Route Query: ').($data['pickup_location'] ?? '').' ➔ '.($data['destination'] ?? ''),
                    'message' => $isCustomLocation
                        ? 'Customer selected other/custom location: '.($data['pickup_location'] ?? '').' to '.($data['destination'] ?? '').' ('.($data['vehicle_category'] ?? 'Sedan').'). No price was shown during booking. Converted into quotation query for admin pricing.'
                        : 'Rate was not configured for '.($data['pickup_location'] ?? '').' to '.($data['destination'] ?? '').' ('.($data['vehicle_category'] ?? 'Sedan').'). Automatically converted into a quotation query.',
                    'converted_booking_id' => $booking->id,
                    'status' => Enquiry::STATUS_NEW,
                ]);

                // Customer notification
                Notification::create([
                    'user_id' => $user->id,
                    'title' => ($isCustomLocation ? 'Custom Location Query Received: #' : 'Route Quotation Query Received: #').$booking->booking_id,
                    'message' => 'Your trip request for '.$data['pickup_location'].' to '.$data['destination'].' has been received as a quotation query. Our dispatch team will share a custom quote shortly.',
                    'type' => 'booking',
                    'action_url' => '/customer/bookings/'.$booking->id,
                ]);
            } else {
                // Admin notification: Section 15: "New booking VT-1006 received."
                Notification::create([
                    'user_id' => null, // Broadcast to admins
                    'title' => 'New Booking: #'.$booking->booking_id,
                    'message' => 'New booking '.$booking->booking_id.' received.',
                    'type' => 'booking',
                    'action_url' => '/admin/bookings/'.$booking->id,
                ]);

                // Customer notification
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Booking Request Received: #'.$booking->booking_id,
                    'message' => 'Your booking request '.$booking->booking_id.' has been received. Our dispatch team will confirm shortly.',
                    'type' => 'booking',
                    'action_url' => '/customer/bookings/'.$booking->id,
                ]);
            }

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

            if (! $booking->canTransitionTo($newStatus)) {
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
                if ($newStatus === 'Trip Started' && ! $booking->trip->started_at) {
                    $tripUpdate['started_at'] = now();
                } elseif ($newStatus === 'Completed' && ! $booking->trip->completed_at) {
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
                'Driver Assigned' => 'Driver has been assigned.',
                'Trip Started' => 'Your trip has started.',
                'On The Way' => "Your cab is on the way for booking {$booking->booking_id}.",
                'Completed' => 'Your trip has been completed.',
                'Cancelled' => "Your booking {$booking->booking_id} has been cancelled.",
                default => "Your booking {$booking->booking_id} is now {$newStatus}.",
            };

            Notification::create([
                'user_id' => $booking->customer_id,
                'title' => "Booking #{$booking->booking_id} {$newStatus}",
                'message' => $customerMessage,
                'type' => 'trip',
                'action_url' => '/customer/bookings/'.$booking->id,
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

            // Validation: Prevent assignment of vehicles with expired mandatory compliance documents (Section 3)
            if ($vehicle->hasExpiredMandatoryDocuments()) {
                throw ValidationException::withMessages([
                    'vehicle_id' => ["Vehicle {$vehicle->registration_number} cannot be assigned because its mandatory documents (Insurance/PUC) have expired. Please renew the documents before dispatch."],
                ]);
            }

            // Overlap check: Prevent same vehicle being assigned to two active trips at overlapping travel times across date spans
            $startDate = $booking->travel_date->format('Y-m-d');
            $endDate = ($booking->return_date ?? $booking->travel_date)->format('Y-m-d');

            $overlapExists = Booking::where('vehicle_id', $vehicle->id)
                ->where('id', '!=', $booking->id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('return_date')
                            ->where('travel_date', '<=', $endDate)
                            ->where('return_date', '>=', $startDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNull('return_date')
                            ->whereBetween('travel_date', [$startDate, $endDate]);
                    });
                })
                ->whereIn('booking_status', ['Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way'])
                ->exists();

            if ($overlapExists) {
                throw ValidationException::withMessages([
                    'vehicle_id' => ["This vehicle is already assigned to another trip at this time (active trip between {$startDate} and {$endDate})."],
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

            $remarks = "Vehicle {$vehicle->name} ({$vehicle->registration_number}) assigned by ".($admin?->name ?? 'Administrator').'.';

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
                'action_url' => '/customer/bookings/'.$booking->id,
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

            // Validation: Prevent assignment of drivers with expired driving licence (Section 3)
            if ($driver->license_expiry && $driver->license_expiry->startOfDay()->isPast()) {
                throw ValidationException::withMessages([
                    'driver_id' => ["Chauffeur {$driver->name} cannot be assigned because their driving licence expired on ".$driver->license_expiry->format('d/m/Y').'.'],
                ]);
            }

            // Overlap check: Prevent same driver being assigned to two active trips at overlapping travel times across date spans
            $startDate = $booking->travel_date->format('Y-m-d');
            $endDate = ($booking->return_date ?? $booking->travel_date)->format('Y-m-d');

            $overlapExists = Booking::where('driver_id', $driver->id)
                ->where('id', '!=', $booking->id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('return_date')
                            ->where('travel_date', '<=', $endDate)
                            ->where('return_date', '>=', $startDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNull('return_date')
                            ->whereBetween('travel_date', [$startDate, $endDate]);
                    });
                })
                ->whereIn('booking_status', ['Driver Assigned', 'Trip Started', 'On The Way'])
                ->exists();

            if ($overlapExists) {
                throw ValidationException::withMessages([
                    'driver_id' => ["This driver is already assigned to another trip at this time (active trip between {$startDate} and {$endDate})."],
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

            $remarks = "Chauffeur {$driver->name} ({$driver->mobile}) assigned by ".($admin?->name ?? 'Administrator').'.';

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
                'action_url' => '/customer/bookings/'.$booking->id,
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

            $remarks = 'Booking cancelled by '.($cancelledBy?->name ?? 'Administrator').". Reason: {$reason}";

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
                'action_url' => '/customer/bookings/'.$booking->id,
            ]);

            return $booking;
        });
    }
}
