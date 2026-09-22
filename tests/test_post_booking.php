<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vehicle = \App\Models\Vehicle::first();
echo "Testing POST /booking with vehicle: {$vehicle->model_name} (ID: {$vehicle->id})\n";

$cookieJar = sys_get_temp_dir() . '/test_cookies_' . uniqid() . '.txt';

// Step 1: GET booking page to get CSRF
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/booking');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$html = curl_exec($ch);

preg_match('/name="_token" value="([^"]+)"/', $html, $m);
$token = $m[1] ?? '';

// Step 2: POST form
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $token,
    'customer_name' => 'Aditya Verma',
    'mobile' => '9244784443',
    'email' => 'aditya.verma@gmail.com',
    'trip_type' => 'One-Way',
    'pickup_location' => 'Vyapar Vihar, Bilaspur',
    'destination' => 'Swami Vivekananda Airport, Raipur',
    'travel_date' => date('Y-m-d', strtotime('+5 days')),
    'travel_time' => '10:00',
    'vehicle_id' => $vehicle->id,
    'notes' => 'Need clean car with AC on continuously',
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

echo "HTTP Code: {$code}\n";
echo "Final URL: {$effectiveUrl}\n";
echo "Contains 'Booking Request Received': " . (strpos($res, 'Booking Request Received') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'Pending': " . (strpos($res, 'Pending') !== false ? 'YES' : 'NO') . "\n";
preg_match('/VT-\d{4,}/', $res, $vtMatch);
echo "Booking ID matched: " . ($vtMatch[0] ?? 'None') . "\n";

// Check Admin Notification in DB
$notif = \App\Models\Notification::where('title', 'like', '%New Booking%')->latest()->first();
echo "Latest Admin Notification: " . ($notif ? $notif->message : 'None') . "\n";

@unlink($cookieJar);

if ($code === 200 && strpos($res, 'Booking Request Received') !== false && !empty($vtMatch[0])) {
    echo "--- PUBLIC BOOKING TEST PASSED COMPLETELY ---\n";
} else {
    echo "--- TEST FAILED ---\n";
    exit(1);
}
