<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BookingService;
use App\Models\Notification;
use App\Models\Booking;

echo "--- TESTING BOOKING FLOW ---\n";
$service = app(BookingService::class);
$booking = $service->createBooking([
    'customer_name' => 'Vikram Singhania',
    'customer_phone' => '9876543299',
    'customer_email' => 'vikram.test@example.com',
    'pickup_location' => 'Bilaspur Railway Station',
    'destination' => 'Raipur Airport (RPR)',
    'travel_date' => date('Y-m-d', strtotime('+3 days')),
    'travel_time' => '14:30',
    'vehicle_category' => 'Sedan',
    'special_notes' => 'Airport pickup with 2 large luggage bags',
]);

echo "Created Booking ID: " . $booking->booking_id . "\n";
echo "Status: " . $booking->booking_status . " (Expected: Pending)\n";
echo "Customer ID linked: " . ($booking->customer_id ?: 'Guest/None') . "\n";

$adminNotif = Notification::where('title', 'New Booking Received')->latest()->first();
echo "Admin Notification: " . ($adminNotif ? $adminNotif->message : 'None') . "\n";

if (preg_match('/^VT-\d{4,}$/', $booking->booking_id) && $booking->booking_status === 'Pending') {
    echo "SUCCESS: Public booking workflow verified.\n";
} else {
    echo "ERROR: Validation failed.\n";
    exit(1);
}

// Clean up
$booking->delete();
echo "--- ALL VERIFICATIONS COMPLETE ---\n";
