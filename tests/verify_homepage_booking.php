<?php

use App\Http\Controllers\PublicWebsiteController;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=====================================================\n";
echo "VAISHNAVI TOURS — HOMEPAGE INSTANT BOOKING 6-TYPES TEST\n";
echo "=====================================================\n\n";

$pass = 0;
$fail = 0;

function testStep($label, $callback)
{
    global $pass, $fail;
    try {
        $result = $callback();
        if ($result === true) {
            echo " [PASS] {$label}\n";
            $pass++;
        } else {
            echo " [FAIL] {$label}: {$result}\n";
            $fail++;
        }
    } catch (Throwable $e) {
        echo " [ERROR] {$label}: ".$e->getMessage()."\n";
        $fail++;
    }
}

// 1. Check Homepage rendering
testStep('Homepage contains 6 Booking Type Tabs', function () use ($app) {
    $req = Request::create('/', 'GET');
    $res = $app->handle($req);
    $html = $res->getContent();

    $expectedTabs = ['One Way', 'Round Trip', 'Airport Transfer', 'Local Rental', 'Tour Package', 'Ambulance'];
    foreach ($expectedTabs as $tab) {
        if (! str_contains($html, $tab)) {
            return "Missing tab: {$tab}";
        }
    }

    return true;
});

testStep('Homepage contains Searchable Location Elements', function () use ($app) {
    $req = Request::create('/', 'GET');
    $res = $app->handle($req);
    $html = $res->getContent();

    if (! str_contains($html, 'id="pickupSelectWrap"') || ! str_contains($html, 'id="destinationSelectWrap"')) {
        return 'Searchable location select wrappers missing';
    }
    if (! str_contains($html, 'id="swapLocationsBtn"')) {
        return 'Swap locations button missing';
    }

    return true;
});

// Helper for POST submission
function submitBooking($postData)
{
    global $app;
    $postData['_token'] = 'test-token';
    $postData['terms_accepted'] = '1';

    // Bypass CSRF for internal unit request testing
    $req = Request::create('/booking', 'POST', $postData);
    $req->setLaravelSession($app['session']->driver());
    $res = app()->make(PublicWebsiteController::class)
        ->storeBooking($req, app()->make(BookingService::class));

    return $res;
}

// Test 1: One Way Outstation Booking
testStep('1. Submit One Way Booking (Bilaspur -> Korba)', function () {
    $res = submitBooking([
        'customer_name' => 'Rajesh Sharma',
        'mobile' => '9827100001',
        'trip_type' => 'One-Way',
        'rate_category' => 'outstation',
        'pickup_location' => 'Bilaspur',
        'destination' => 'Korba',
        'vehicle_category' => 'Sedan',
        'travel_date' => date('Y-m-d', strtotime('+1 day')),
        'travel_time' => '09:00',
    ]);

    $booking = Booking::where('customer_id', User::where('phone', '9827100001')->value('id'))
        ->latest()->first();

    if (! $booking) {
        return 'Booking not found in DB';
    }
    if ($booking->trip_type !== 'One-Way') {
        return "Unexpected trip_type: {$booking->trip_type}";
    }
    if ($booking->rate_category !== 'outstation') {
        return "Unexpected rate_category: {$booking->rate_category}";
    }
    if (! str_starts_with($booking->booking_id, 'VT-')) {
        return "Invalid Booking ID: {$booking->booking_id}";
    }
    if ($booking->total_amount <= 0) {
        return "Rate not calculated properly: {$booking->total_amount}";
    }

    return true;
});

// Test 2: Round Trip Booking
testStep('2. Submit Round Trip Booking (Bilaspur -> Raipur with Return)', function () {
    $res = submitBooking([
        'customer_name' => 'Amit Patel',
        'mobile' => '9827100002',
        'trip_type' => 'Round-Trip',
        'rate_category' => 'round_trip',
        'pickup_location' => 'Bilaspur',
        'destination' => 'Raipur',
        'vehicle_category' => 'Ertiga',
        'estimated_km' => '250',
        'travel_date' => date('Y-m-d', strtotime('+2 days')),
        'travel_time' => '08:00',
        'return_date' => date('Y-m-d', strtotime('+3 days')),
        'return_time' => '20:00',
    ]);

    $booking = Booking::where('customer_id', User::where('phone', '9827100002')->value('id'))
        ->latest()->first();

    if (! $booking) {
        return 'Booking not found in DB';
    }
    if ($booking->trip_type !== 'Round-Trip') {
        return "Unexpected trip_type: {$booking->trip_type}";
    }
    if ($booking->rate_category !== 'round_trip') {
        return "Unexpected rate_category: {$booking->rate_category}";
    }
    if ($booking->total_amount <= 0) {
        return "Estimated round-trip total missing: {$booking->total_amount}";
    }

    return true;
});

// Test 3: Airport Transfer Booking
testStep('3. Submit Airport Transfer Booking (Bilaspur -> Raipur Airport)', function () {
    $res = submitBooking([
        'customer_name' => 'Pooja Verma',
        'mobile' => '9827100003',
        'trip_type' => 'Airport Transfer',
        'rate_category' => 'airport',
        'pickup_location' => 'Bilaspur',
        'destination' => 'Raipur Airport',
        'vehicle_category' => 'Innova Crysta',
        'travel_date' => date('Y-m-d', strtotime('+1 day')),
        'travel_time' => '05:30',
        'flight_number' => '6E-204',
    ]);

    $booking = Booking::where('customer_id', User::where('phone', '9827100003')->value('id'))
        ->latest()->first();

    if (! $booking) {
        return 'Booking not found in DB';
    }
    if ($booking->trip_type !== 'Airport Transfer') {
        return "Unexpected trip_type: {$booking->trip_type}";
    }
    if ($booking->total_amount != 4500.00) {
        return "Unexpected Innova airport rate: {$booking->total_amount}";
    }
    if (! str_contains($booking->notes ?? '', '6E-204')) {
        return "Flight number not recorded in notes: {$booking->notes}";
    }

    return true;
});

// Test 4: Local Rental Booking
testStep('4. Submit Local Rental Booking (8 Hours / 80 KM)', function () {
    $res = submitBooking([
        'customer_name' => 'Sunil Gupta',
        'mobile' => '9827100004',
        'trip_type' => 'Local Hourly',
        'rate_category' => 'local_8h_80km',
        'pickup_location' => 'Bilaspur',
        'destination' => 'Bilaspur (Local)',
        'vehicle_category' => 'Sedan',
        'travel_date' => date('Y-m-d', strtotime('+1 day')),
        'travel_time' => '10:00',
    ]);

    $booking = Booking::where('customer_id', User::where('phone', '9827100004')->value('id'))
        ->latest()->first();

    if (! $booking) {
        return 'Booking not found in DB';
    }
    if ($booking->rate_category !== 'local_8h_80km') {
        return "Unexpected rate_category: {$booking->rate_category}";
    }
    if ($booking->total_amount != 2200.00) {
        return "Unexpected local 8h rate: {$booking->total_amount}";
    }

    return true;
});

// Test 5: Tour Package Booking
testStep('5. Submit Tour Package Booking (Mainpat Hill Station)', function () {
    $res = submitBooking([
        'customer_name' => 'Vikram Singh',
        'mobile' => '9827100005',
        'trip_type' => 'Tour Package',
        'rate_category' => 'tour_package',
        'pickup_location' => 'Bilaspur',
        'destination' => 'Mainpat Hill Station Tour (Shimla of CG)',
        'vehicle_category' => 'Innova Crysta',
        'travel_date' => date('Y-m-d', strtotime('+4 days')),
        'travel_time' => '07:00',
        'package_duration' => '2 Days / 1 Night',
        'passenger_count' => 6,
    ]);

    $booking = Booking::where('customer_id', User::where('phone', '9827100005')->value('id'))
        ->latest()->first();

    if (! $booking) {
        return 'Booking not found in DB';
    }
    if ($booking->trip_type !== 'Tour Package') {
        return "Unexpected trip_type: {$booking->trip_type}";
    }
    if (! str_contains($booking->notes ?? '', '2 Days / 1 Night')) {
        return 'Tour duration missing in notes';
    }
    if (! str_contains($booking->notes ?? '', 'Passengers: 6')) {
        return 'Passenger count missing in notes';
    }

    return true;
});

// Test 6: Ambulance Emergency Booking
testStep('6. Submit Ambulance Booking (Emergency Patient Transit)', function () {
    $res = submitBooking([
        'customer_name' => 'Dr. Manoj Agrawal',
        'mobile' => '9827100006',
        'trip_type' => 'Ambulance Service',
        'rate_category' => 'emergency',
        'pickup_location' => 'Vyapar Vihar, Bilaspur',
        'destination' => 'Apollo Hospital, Bilaspur',
        'vehicle_category' => 'Sedan',
        'travel_date' => date('Y-m-d'),
        'travel_time' => '14:00',
        'service_category' => 'Basic Life Support (BLS) Ambulance',
        'notes' => 'Patient requires oxygen cylinder support',
    ]);

    $booking = Booking::where('customer_id', User::where('phone', '9827100006')->value('id'))
        ->latest()->first();

    if (! $booking) {
        return 'Booking not found in DB';
    }
    if ($booking->trip_type !== 'Ambulance Service') {
        return "Unexpected trip_type: {$booking->trip_type}";
    }
    if (! str_contains($booking->notes ?? '', 'Basic Life Support')) {
        return 'Service category missing in notes';
    }
    if (! str_contains($booking->notes ?? '', 'oxygen cylinder')) {
        return 'Emergency notes missing';
    }

    return true;
});

echo "\n-----------------------------------------------------\n";
echo "SUMMARY: {$pass} Passed, {$fail} Failed\n";
echo "-----------------------------------------------------\n";

exit($fail > 0 ? 1 : 0);
