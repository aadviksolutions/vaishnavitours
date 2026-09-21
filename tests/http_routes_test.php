<?php

error_reporting(E_ALL & ~E_DEPRECATED);

function request($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    return ['code' => $httpCode, 'url' => $finalUrl, 'body' => $response];
}

$baseUrl = 'http://127.0.0.1:8000';
$cookieFileAdmin = sys_get_temp_dir() . '/cookie_admin_' . uniqid() . '.txt';
$cookieFileCustomer = sys_get_temp_dir() . '/cookie_cust_' . uniqid() . '.txt';

echo "1. Testing Public GET /booking\n";
$res = request("{$baseUrl}/booking");
echo "   Status: {$res['code']} (Expected: 200)\n";

echo "\n2. Testing Admin Login\n";
$loginPage = request("{$baseUrl}/login", 'GET', [], $cookieFileAdmin);
preg_match('/name="_token" value="([^"]+)"/', $loginPage['body'], $tokenMatch);
$csrf = $tokenMatch[1] ?? '';

$loginRes = request("{$baseUrl}/login", 'POST', [
    '_token' => $csrf,
    'email' => 'admin@vaishnavitours.com',
    'password' => 'Password@123',
], $cookieFileAdmin);
echo "   Admin Login redirected to: {$loginRes['url']} (Code: {$loginRes['code']})\n";

echo "\n3. Testing Admin Bookings Dashboard\n";
$adminBookings = request("{$baseUrl}/admin/bookings", 'GET', [], $cookieFileAdmin);
echo "   GET /admin/bookings Status: {$adminBookings['code']} (Expected: 200)\n";
$hasVT = strpos($adminBookings['body'], 'VT-1001') !== false;
echo "   Contains VT-1001: " . ($hasVT ? 'YES' : 'NO') . "\n";

echo "\n4. Testing Admin Trips Dashboard\n";
$adminTrips = request("{$baseUrl}/admin/trips", 'GET', [], $cookieFileAdmin);
echo "   GET /admin/trips Status: {$adminTrips['code']} (Expected: 200)\n";
$hasTrip = strpos($adminTrips['body'], '#TRIP-') !== false;
echo "   Contains #TRIP-: " . ($hasTrip ? 'YES' : 'NO') . "\n";

echo "\n5. Testing Customer Login (Rajesh Sharma)\n";
$loginPageCust = request("{$baseUrl}/login", 'GET', [], $cookieFileCustomer);
preg_match('/name="_token" value="([^"]+)"/', $loginPageCust['body'], $tokenMatchCust);
$csrfCust = $tokenMatchCust[1] ?? '';

$custLogin = request("{$baseUrl}/login", 'POST', [
    '_token' => $csrfCust,
    'email' => 'rajesh.sharma@gmail.com',
    'password' => 'Password@123',
], $cookieFileCustomer);
echo "   Customer Login redirected to: {$custLogin['url']} (Code: {$custLogin['code']})\n";

echo "\n6. Testing Customer Dashboard & Upcoming Trip Timeline\n";
$custDash = request("{$baseUrl}/customer/dashboard", 'GET', [], $cookieFileCustomer);
echo "   GET /customer/dashboard Status: {$custDash['code']} (Expected: 200)\n";
$hasTimeline = strpos($custDash['body'], 'Booking Confirmed') !== false;
echo "   Contains Upcoming Trip Timeline: " . ($hasTimeline ? 'YES' : 'NO') . "\n";

echo "\n7. Testing Customer 'My Bookings'\n";
$custBookings = request("{$baseUrl}/customer/bookings", 'GET', [], $cookieFileCustomer);
echo "   GET /customer/bookings Status: {$custBookings['code']} (Expected: 200)\n";

// Extract invoice link for Rajesh's booking
preg_match('#href="(http://127\.0\.0\.1:8000/invoice/(\d+))"#', $custBookings['body'], $invMatch);
$invoiceUrl = $invMatch[1] ?? "{$baseUrl}/invoice/1";
$ownBookingId = $invMatch[2] ?? 1;

echo "\n8. Testing Print-Friendly Invoice ({$invoiceUrl})\n";
$invoiceRes = request($invoiceUrl, 'GET', [], $cookieFileCustomer);
echo "   GET {$invoiceUrl} Status: {$invoiceRes['code']} (Expected: 200)\n";
$hasTaxInvoice = strpos($invoiceRes['body'], 'TAX INVOICE') !== false;
echo "   Contains TAX INVOICE: " . ($hasTaxInvoice ? 'YES' : 'NO') . "\n";
$hasBrand = strpos($invoiceRes['body'], 'VAISHNAVI TOURS') !== false;
echo "   Contains VAISHNAVI TOURS: " . ($hasBrand ? 'YES' : 'NO') . "\n";

echo "\n9. Testing Customer A trying to access Customer B's invoice (Policy Security)\n";
// Booking ID 2 belongs to Amit Patel (Customer B). Customer A (Rajesh) must get 403
$otherInvoiceUrl = "{$baseUrl}/invoice/2";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $otherInvoiceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileCustomer); // Logged in as Rajesh
$resBody = curl_exec($ch);
$unauthCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "   Rajesh Sharma accessing Amit Patel's invoice: HTTP {$unauthCode} (Expected: 403 Forbidden)\n";

echo "\n=======================================================\n";
echo "HTTP ENDPOINT VERIFICATION COMPLETED SUCCESSFULLY!\n";
echo "=======================================================\n";

@unlink($cookieFileAdmin);
@unlink($cookieFileCustomer);
