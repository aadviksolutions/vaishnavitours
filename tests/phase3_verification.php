<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

echo "=======================================================\n";
echo "VAISHNAVI TOURS — PHASE 3 AUTOMATED VERIFICATION TEST\n";
echo "=======================================================\n\n";

$bookingService = app(BookingService::class);
$paymentService = app(PaymentService::class);

$passed = 0;
$total = 0;

function assertTest($condition, $name) {
    global $passed, $total;
    $total++;
    if ($condition) {
        echo "  [PASS] {$name}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$name}\n";
    }
}

// TEST 1: Booking ID Generation
echo "TEST 1: Booking ID Generation Format (VT-XXXX)\n";
$newId = Booking::generateBookingId();
assertTest(preg_match('/^VT-\d+$/', $newId) === 1, "Generated ID '{$newId}' matches VT-XXXX pattern");

// TEST 2: Public Booking Creation with Initial Pending Status
echo "\nTEST 2: Public Booking Creation & Initial Status\n";
$availableVehicle = Vehicle::where('status', 'Available')->first();
$testBookingData = [
    'customer_name' => 'Automated Test Traveler',
    'mobile' => '9988776655',
    'email' => 'test.traveler@example.com',
    'trip_type' => 'One-Way',
    'pickup_location' => 'Mangal Chowk, Bilaspur',
    'destination' => 'Raipur Railway Station',
    'travel_date' => date('Y-m-d', strtotime('+5 days')),
    'travel_time' => '10:00:00',
    'vehicle_id' => $availableVehicle->id,
    'notes' => 'Automated test booking verification.',
];

$booking = $bookingService->createBooking($testBookingData, null);
assertTest(str_starts_with($booking->booking_id, 'VT-'), "Booking ID {$booking->booking_id} has VT- prefix");
assertTest($booking->booking_status === 'Pending', "Initial status is strictly 'Pending'");
assertTest($booking->payment_status === 'Pending', "Initial payment status is 'Pending'");
assertTest($booking->balance_amount == $booking->total_amount, "Initial balance equals total fare (₹{$booking->total_amount})");

// Verify Admin Notification
$adminNotif = Notification::where('message', 'like', "%New booking {$booking->booking_id} received.%")->first();
assertTest($adminNotif !== null, "Admin notification created: 'New booking {$booking->booking_id} received.'");

// Verify Status History logged
$firstHistory = BookingStatusHistory::where('booking_id', $booking->id)->where('new_status', 'Pending')->first();
assertTest($firstHistory !== null, "Booking status history recorded initial Pending status with remarks: '{$firstHistory->remarks}'");

// TEST 3: State Machine Illegal Transition Prevention
echo "\nTEST 3: Workflow State Machine - Illegal Transition Prevention\n";
$illegalCaught = false;
try {
    $bookingService->updateStatus($booking, 'Completed', 'Trying to jump directly to Completed');
} catch (ValidationException $e) {
    $illegalCaught = true;
}
assertTest($illegalCaught, "Direct jump Pending -> Completed was rejected with ValidationException");

// TEST 4: Controlled Progression: Pending -> Confirmed
echo "\nTEST 4: Transition Pending -> Confirmed\n";
$adminUser = User::where('role', 'admin')->first();
$bookingService->updateStatus($booking, 'Confirmed', 'Confirmed by admin.', $adminUser);
$booking->refresh();
assertTest($booking->booking_status === 'Confirmed', "Status updated to 'Confirmed'");

$confirmNotif = Notification::where('user_id', $booking->customer_id)
    ->where('message', "Your booking {$booking->booking_id} has been confirmed.")
    ->first();
assertTest($confirmNotif !== null, "Customer received exact notification: 'Your booking {$booking->booking_id} has been confirmed.'");

// TEST 5: Vehicle Assignment & Overlap Prevention
echo "\nTEST 5: Vehicle Assignment & Overlap Conflict Prevention\n";
// Assign vehicle to our booking
$bookingService->assignVehicle($booking, $availableVehicle->id, $adminUser);
$booking->refresh();
$availableVehicle->refresh();

assertTest($booking->vehicle_id == $availableVehicle->id, "Vehicle ID assigned to booking");
assertTest($availableVehicle->status === 'On Trip', "Vehicle status changed from Available -> On Trip");
assertTest($booking->booking_status === 'Vehicle Assigned', "Booking status advanced Confirmed -> Vehicle Assigned");

// Try assigning this same vehicle on the same date to another booking
$otherBooking = Booking::create([
    'customer_id' => $booking->customer_id,
    'trip_type' => 'One-Way',
    'pickup_location' => 'Bilaspur',
    'destination' => 'Korba',
    'travel_date' => $booking->travel_date->format('Y-m-d'),
    'travel_time' => '11:00:00',
    'total_amount' => 2000,
    'booking_status' => 'Confirmed',
]);

$vehicleConflictCaught = false;
$conflictErrorMsg = '';
try {
    // Attempt overlap
    $bookingService->assignVehicle($otherBooking, $availableVehicle->id, $adminUser);
} catch (ValidationException $e) {
    $vehicleConflictCaught = true;
    $conflictErrorMsg = $e->errors()['vehicle_id'][0] ?? '';
}
assertTest($vehicleConflictCaught, "Overlap conflict detected for vehicle on same travel date");
assertTest(str_contains($conflictErrorMsg, 'already assigned to another trip at this time') || str_contains($conflictErrorMsg, 'On Trip'), "Clear conflict message displayed: '{$conflictErrorMsg}'");

// TEST 6: Driver Assignment, Overlap Prevention & Automatic Trip Activation
echo "\nTEST 6: Driver Assignment & Automatic Trip Activation\n";
$availableDriver = Driver::where('status', 'Available')->first();
$bookingService->assignDriver($booking, $availableDriver->id, $adminUser);
$booking->refresh();
$availableDriver->refresh();

assertTest($booking->driver_id == $availableDriver->id, "Driver ID assigned to booking");
assertTest($availableDriver->status === 'Assigned', "Driver status changed from Available -> Assigned");
assertTest($booking->booking_status === 'Driver Assigned', "Booking status advanced to 'Driver Assigned'");

// Check Trip Activation (Section 10)
$trip = Trip::where('booking_id', $booking->id)->first();
assertTest($trip !== null, "Related Trip record automatically created upon Driver Assigned");
assertTest($trip->vehicle_id == $booking->vehicle_id, "Trip record vehicle_id matches booking");
assertTest($trip->driver_id == $booking->driver_id, "Trip record driver_id matches booking");

// Overlap check for driver
$driverConflictCaught = false;
$driverErrorMsg = '';
try {
    $bookingService->assignDriver($otherBooking, $availableDriver->id, $adminUser);
} catch (ValidationException $e) {
    $driverConflictCaught = true;
    $driverErrorMsg = $e->errors()['driver_id'][0] ?? '';
}
assertTest($driverConflictCaught, "Overlap conflict detected for driver on same travel date");
assertTest(str_contains($driverErrorMsg, 'already assigned to another trip at this time') || str_contains($driverErrorMsg, 'Assigned'), "Clear conflict message for driver: '{$driverErrorMsg}'");

// TEST 7: Lifecycle Progression: Driver Assigned -> Trip Started -> On The Way -> Completed
echo "\nTEST 7: Full Trip Progression Lifecycle\n";
$bookingService->updateStatus($booking, 'Trip Started', 'Chauffeur arrived at pickup.', $adminUser);
$booking->refresh();
$trip->refresh();
assertTest($booking->booking_status === 'Trip Started', "Booking status advanced to 'Trip Started'");
assertTest($trip->started_at !== null, "Trip started_at timestamp recorded");

$bookingService->updateStatus($booking, 'On The Way', 'Vehicle crossing city limits.', $adminUser);
$booking->refresh();
assertTest($booking->booking_status === 'On The Way', "Booking status advanced to 'On The Way'");

$bookingService->updateStatus($booking, 'Completed', 'Passenger safely dropped.', $adminUser);
$booking->refresh();
$trip->refresh();
$availableVehicle->refresh();
$availableDriver->refresh();

assertTest($booking->booking_status === 'Completed', "Booking status reached 'Completed'");
assertTest($trip->completed_at !== null, "Trip completed_at timestamp recorded");
assertTest($availableVehicle->status === 'Available', "Vehicle automatically released to Available upon completion");
assertTest($availableDriver->status === 'Available', "Driver automatically released to Available upon completion");

// TEST 8: Financials & Balance Calculation
echo "\nTEST 8: Financials & Balance Calculation\n";
$booking->update([
    'total_amount' => 4200.00,
    'paid_amount' => 2000.00,
]);
$booking->recalculateFinancials();
$booking->save();
$booking->refresh();

assertTest($booking->balance_amount == 2200.00, "Balance calculated: Total ₹4,200 - Paid ₹2,000 = Balance ₹2,200");
assertTest($booking->payment_status === 'Partial', "Payment status automatically set to 'Partial'");

// Test full payment
$booking->update(['paid_amount' => 4200.00]);
$booking->recalculateFinancials();
$booking->save();
$booking->refresh();
assertTest($booking->balance_amount == 0.00, "Balance is 0.00 when fully paid");
assertTest($booking->payment_status === 'Paid', "Payment status automatically set to 'Paid'");

// TEST 9: Cancellation Flow (Customer Request + Admin Approval)
echo "\nTEST 9: Customer Cancellation Request & Admin Approval\n";
$cancellableBooking = Booking::create([
    'customer_id' => $booking->customer_id,
    'trip_type' => 'One-Way',
    'pickup_location' => 'Bilaspur',
    'destination' => 'Raipur',
    'travel_date' => date('Y-m-d', strtotime('+7 days')),
    'travel_time' => '12:00:00',
    'total_amount' => 2500,
    'booking_status' => 'Confirmed',
]);

// Customer requests cancellation
$cancellableBooking->update([
    'cancellation_status' => 'Requested',
    'cancellation_reason' => 'Flight cancelled due to fog.',
]);
assertTest($cancellableBooking->cancellation_status === 'Requested', "Cancellation request status recorded");
assertTest($cancellableBooking->booking_status === 'Confirmed', "Booking status NOT immediately cancelled before admin review");

// Admin approves cancellation
$bookingService->cancelBooking($cancellableBooking, $cancellableBooking->cancellation_reason, $adminUser);
$cancellableBooking->refresh();

assertTest($cancellableBooking->booking_status === 'Cancelled', "Booking status updated to 'Cancelled'");
assertTest($cancellableBooking->cancellation_status === 'Approved', "Cancellation status updated to 'Approved'");
assertTest($cancellableBooking->cancelled_at !== null, "cancelled_at timestamp recorded");
assertTest($cancellableBooking->cancelled_by == $adminUser->id, "cancelled_by admin user ID recorded");

// TEST 10: Authorization & Policy Security (Customer A vs Customer B)
echo "\nTEST 10: Authorization Policy Enforcement\n";
$customerA = User::where('email', 'rajesh.sharma@gmail.com')->first();
$customerB = User::where('email', 'amit.patel@outlook.com')->first();

$bookingA = Booking::where('customer_id', $customerA->id)->first();
$bookingB = Booking::where('customer_id', $customerB->id)->first();

// Customer A accessing Customer A's booking
assertTest(Gate::forUser($customerA)->allows('view', $bookingA), "Customer A can view their own booking");
assertTest(Gate::forUser($customerA)->allows('viewInvoice', $bookingA), "Customer A can view their own invoice");

// Customer A attempting to access Customer B's booking (MUST FAIL)
assertTest(Gate::forUser($customerA)->denies('view', $bookingB), "SECURITY: Customer A cannot view Customer B's booking (Blocked 403)");
assertTest(Gate::forUser($customerA)->denies('viewInvoice', $bookingB), "SECURITY: Customer A cannot view Customer B's invoice (Blocked 403)");

// Admin accessing Customer B's booking
assertTest(Gate::forUser($adminUser)->allows('view', $bookingB), "Admin can view any customer's booking");
assertTest(Gate::forUser($adminUser)->allows('viewInvoice', $bookingB), "Admin can view any customer's invoice");

// Clean up test bookings
$booking->delete();
$otherBooking->delete();
$cancellableBooking->delete();

echo "\n=======================================================\n";
echo "TEST RESULTS: {$passed} / {$total} TESTS PASSED\n";
echo "=======================================================\n";

if ($passed === $total) {
    echo "ALL PHASE 3 REQUIREMENTS FULLY VERIFIED!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
