<?php

/**
 * Vaishnavi Tours — Final Pre-Deployment Comprehensive Route & Asset Audit
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$passed = 0;
$failed = 0;

function runAuditTest($name, $closure) {
    global $passed, $failed;
    try {
        $result = $closure();
        if ($result === true) {
            echo "  [PASS] {$name}\n";
            $passed++;
        } else {
            echo "  [FAIL] {$name}: {$result}\n";
            $failed++;
        }
    } catch (Throwable $e) {
        echo "  [ERROR] {$name}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "=======================================================\n";
echo "VAISHNAVI TOURS — PRODUCTION AUDIT EXECUTION\n";
echo "=======================================================\n\n";

// Helper to simulate GET requests through Laravel
function getResponse($uri, $user = null) {
    global $app;
    $request = Request::create($uri, 'GET');
    $app->instance('request', $request);

    if ($user) {
        Auth::setUser($user);
    } else {
        Auth::forgetUser();
    }

    $response = $app->handle($request);
    return $response;
}

$admin = User::where('role', 'admin')->first();
$customer = User::where('role', 'customer')->first();

echo "1. PUBLIC ROUTES & HOMEPAGE\n";
runAuditTest("Homepage (/) returns 200 OK", function () {
    $res = getResponse('/');
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

runAuditTest("Homepage contains Vaishnavi Tours branding & logo", function () {
    $res = getResponse('/');
    $content = $res->getContent();
    return (strpos($content, 'Vaishnavi') !== false && strpos($content, 'vaishnavi-tours-logo.png') !== false)
        ? true : "Brand text or logo missing";
});

runAuditTest("Public Booking Page (/booking) returns 200 OK", function () {
    $res = getResponse('/booking');
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

echo "\n2. AUTHENTICATION ROUTES\n";
runAuditTest("Login page (/login) returns 200 OK with CSRF token", function () {
    $res = getResponse('/login');
    $content = $res->getContent();
    return ($res->getStatusCode() === 200 && strpos($content, 'name="_token"') !== false)
        ? true : "HTTP " . $res->getStatusCode();
});

runAuditTest("Register page (/register) returns 200 OK", function () {
    $res = getResponse('/register');
    $content = $res->getContent();
    return ($res->getStatusCode() === 200 && strpos($content, 'Register') !== false)
        ? true : "HTTP " . $res->getStatusCode();
});

echo "\n3. CUSTOMER DASHBOARD & PORTAL\n";
runAuditTest("Customer Dashboard (/customer/dashboard) returns 200 OK for customer", function () use ($customer) {
    $res = getResponse('/customer/dashboard', $customer);
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

runAuditTest("Customer Root Alias (/dashboard) redirects to customer.dashboard", function () use ($customer) {
    $res = getResponse('/dashboard', $customer);
    return ($res->getStatusCode() === 302 && strpos($res->headers->get('Location'), '/customer/dashboard') !== false)
        ? true : "Did not redirect properly (Status " . $res->getStatusCode() . ")";
});

runAuditTest("Customer Bookings (/customer/bookings) returns 200 OK", function () use ($customer) {
    $res = getResponse('/customer/bookings', $customer);
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

echo "\n4. ADMIN PANEL & DISPATCH DASHBOARD\n";
runAuditTest("Admin Root (/admin) redirects to admin.dashboard", function () use ($admin) {
    $res = getResponse('/admin', $admin);
    return ($res->getStatusCode() === 302 && strpos($res->headers->get('Location'), '/admin/dashboard') !== false)
        ? true : "Redirect failed (Status " . $res->getStatusCode() . ")";
});

runAuditTest("Admin Dashboard (/admin/dashboard) returns 200 OK for admin", function () use ($admin) {
    $res = getResponse('/admin/dashboard', $admin);
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

runAuditTest("Admin Bookings (/admin/bookings) returns 200 OK", function () use ($admin) {
    $res = getResponse('/admin/bookings', $admin);
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

runAuditTest("Admin Vehicles (/admin/vehicles) returns 200 OK", function () use ($admin) {
    $res = getResponse('/admin/vehicles', $admin);
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

runAuditTest("Admin Drivers (/admin/drivers) returns 200 OK", function () use ($admin) {
    $res = getResponse('/admin/drivers', $admin);
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

runAuditTest("Admin Trips (/admin/trips) returns 200 OK", function () use ($admin) {
    $res = getResponse('/admin/trips', $admin);
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

runAuditTest("Admin Invoices (/admin/invoices) returns 200 OK", function () use ($admin) {
    $res = getResponse('/admin/invoices', $admin);
    return $res->getStatusCode() === 200 ? true : "HTTP " . $res->getStatusCode();
});

echo "\n5. INVOICE RENDERING & ACCESS\n";
runAuditTest("Dedicated Booking Invoice (/invoice/{booking}) returns 200 OK for authorized admin", function () use ($admin) {
    $booking = Booking::first();
    if (!$booking) return "No booking found";
    $res = getResponse("/invoice/{$booking->id}", $admin);
    return ($res->getStatusCode() === 200 && strpos($res->getContent(), 'TAX INVOICE') !== false)
        ? true : "HTTP " . $res->getStatusCode();
});

echo "\n6. STATIC ASSET EXISTENCE ON DISK\n";
$assetFiles = [
    'public/assets/branding/vaishnavi-tours-logo.png',
    'public/assets/images/hero-taxi.jpg',
    'public/assets/images/emergency-ambulance.jpg',
    'public/assets/images/about-travel.jpg',
    'public/assets/vehicles/maruti-suzuki-dzire.jpg',
    'public/assets/vehicles/maruti-suzuki-ertiga.jpg',
    'public/assets/vehicles/toyota-innova-crysta.jpg',
    'public/assets/vehicles/force-tempo-traveller.jpg',
    'public/assets/vehicles/tata-tiago-wagonr.jpg',
    'public/assets/vehicle/maruti-suzuki-dzire.jpg',
    'public/assets/vehicle/maruti-suzuki-ertiga.jpg',
    'public/assets/vehicle/toyota-innova-crysta.jpg',
    'public/assets/vehicle/force-tempo-traveller.jpg',
    'public/assets/vehicle/tata-tiago-wagonr.jpg',
    'public/assets/sedan.svg',
    'public/assets/suv.svg',
    'public/assets/innova.svg',
    'public/assets/tempo.svg',
    'public/css/style.css',
    'public/favicon.png',
];

foreach ($assetFiles as $file) {
    runAuditTest("Asset exists: {$file}", function () use ($file) {
        $path = __DIR__ . '/../' . $file;
        return (file_exists($path) && filesize($path) > 0) ? true : "File not found or empty: {$file}";
    });
}

echo "\n7. CUSTOM BRANDED ERROR PAGES\n";
$errorViews = ['404', '403', '419', '429', '500'];
foreach ($errorViews as $code) {
    runAuditTest("Error view {$code}.blade.php exists and renders", function () use ($app, $code) {
        $req = Request::create("/error-{$code}", 'GET');
        $app->instance('request', $req);
        $view = view("errors.{$code}")->render();
        return (strpos($view, $code) !== false && strpos($view, 'Vaishnavi Tours') !== false)
            ? true : "View did not render properly";
    });
}

echo "\n=======================================================\n";
echo "AUDIT RESULTS: {$passed} PASSED / {$failed} FAILED\n";
echo "=======================================================\n";

exit($failed > 0 ? 1 : 0);
