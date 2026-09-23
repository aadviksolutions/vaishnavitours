<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=====================================================\n";
echo "VAISHNAVI TOURS — MOBILE FIXES VERIFICATION TEST\n";
echo "=====================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($condition, $name, $detail = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] {$name}\n";
        if ($detail) echo "        -> {$detail}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$name}\n";
        if ($detail) echo "        -> {$detail}\n";
        $failCount++;
    }
}

// 1. Fetch Homepage HTML
$ch = curl_init('http://127.0.0.1:8000/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest($httpCode === 200, "Homepage responds with HTTP 200", "HTTP Code: {$httpCode}");

// 2. Test Mobile Header & Topbar Markup
assertTest(strpos($html, 'class="top-bar-desktop') !== false, "Top bar has desktop wrapper (.top-bar-desktop)");
assertTest(strpos($html, 'class="top-bar-mobile"') !== false, "Top bar has responsive mobile wrapper (.top-bar-mobile)");
assertTest(strpos($html, 'Call: <strong>9244784443</strong>') !== false, "Mobile top bar shows verified phone 9244784443");
assertTest(strpos($html, '24/7 Support') !== false, "Mobile top bar shows 24/7 Support badge");

// 3. Test Hamburger Button & Attributes
assertTest(strpos($html, 'id="mobile-nav-btn"') !== false, "Hamburger button #mobile-nav-btn present");
assertTest(strpos($html, 'aria-controls="mobile-nav-drawer"') !== false, "Hamburger button has aria-controls='mobile-nav-drawer'");
assertTest(strpos($html, 'aria-expanded="false"') !== false, "Hamburger button has aria-expanded='false'");

// 4. Test Mobile Drawer & Backdrop Markup
assertTest(strpos($html, 'id="mobile-nav-backdrop"') !== false, "Backdrop overlay #mobile-nav-backdrop present");
assertTest(strpos($html, 'id="mobile-nav-drawer"') !== false, "Mobile nav drawer #mobile-nav-drawer present");
assertTest(strpos($html, 'id="mobile-drawer-close-btn"') !== false, "Drawer close button #mobile-drawer-close-btn present");
assertTest(strpos($html, 'BOOK A TAXI') !== false, "Drawer contains BOOK A TAXI CTA");
assertTest(strpos($html, 'Call 9244784443') !== false, "Drawer contains direct Call 9244784443 button");

// 5. Test Absence of Conflicting Inline Script
assertTest(strpos($html, 'navMenu.classList.toggle(\'show\')') === false, "No duplicate conflicting inline script in app layout");

// 6. Test Custom Accessible Vehicle Dropdown Markup
assertTest(strpos($html, 'id="vehicle-select-btn"') !== false, "Custom vehicle trigger button #vehicle-select-btn present");
assertTest(strpos($html, 'id="vehicle-select-menu"') !== false, "Custom vehicle listbox #vehicle-select-menu present");
assertTest(strpos($html, 'id="selected-vehicle-id"') !== false, "Hidden vehicle input #selected-vehicle-id present");
assertTest(strpos($html, 'name="vehicle_id"') !== false, "Form includes name='vehicle_id'");
assertTest(strpos($html, 'name="terms_accepted"') !== false, "Form includes name='terms_accepted'");
assertTest(strpos($html, 'data-name="Maruti Suzuki Dzire"') !== false, "Vehicle listbox includes Maruti Suzuki Dzire");
assertTest(strpos($html, 'data-name="Maruti Suzuki Ertiga"') !== false, "Vehicle listbox includes Maruti Suzuki Ertiga");
assertTest(strpos($html, 'data-name="Toyota Innova Crysta"') !== false, "Vehicle listbox includes Toyota Innova Crysta");
assertTest(strpos($html, 'data-name="Force Tempo Traveller"') !== false, "Vehicle listbox includes Force Tempo Traveller");
assertTest(strpos($html, 'data-name="Tata Tiago / WagonR"') !== false, "Vehicle listbox includes Tata Tiago / WagonR");
assertTest(strpos($html, 'option-radio-indicator') !== false, "Options contain custom circular radio indicator");

// 7. Verify CSS Rules in style.css
$css = file_get_contents(__DIR__ . '/../public/css/style.css');
assertTest(strpos($css, '.mobile-nav-drawer') !== false, "CSS has .mobile-nav-drawer defined");
assertTest(strpos($css, 'transform: translateX(100%)') !== false, "Drawer is off-screen to the RIGHT (translateX(100%))");
assertTest(strpos($css, '.mobile-nav-drawer.is-open') !== false && strpos($css, 'transform: translateX(0)') !== false, "Drawer slides in from the RIGHT (translateX(0))");
assertTest(strpos($css, '.mobile-nav-backdrop') !== false, "Backdrop styles defined");
assertTest(strpos($css, 'z-index: 2000') !== false, "Backdrop has z-index: 2000 (above content & header)");
assertTest(strpos($css, 'z-index: 2001') !== false, "Drawer has z-index: 2001 (above backdrop)");
assertTest(strpos($css, '.custom-select-trigger') !== false, "Custom select trigger styles defined");
assertTest(strpos($css, 'min-height: 46px') !== false, "Custom select trigger has min-height >= 44px");
assertTest(strpos($css, '.custom-select-option') !== false, "Custom select option styles defined");
assertTest(strpos($css, 'min-height: 48px') !== false, "Option items have min-height >= 44px (48px)");
assertTest(strpos($css, 'max-height: min(320px, 55vh)') !== false, "Dropdown menu max-height capped at 55vh inside viewport");
assertTest(strpos($css, 'overflow-x: hidden') !== false, "Root has overflow-x: hidden guard against horizontal scroll");

// 8. Test Booking Submission with Maruti Suzuki Dzire (vehicle_id = 1)
$cookieFile = sys_get_temp_dir() . '/cookie_' . uniqid() . '.txt';

// GET homepage with cookie jar to obtain session cookie & CSRF token
$ch = curl_init('http://127.0.0.1:8000/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$homeHtml = curl_exec($ch);
curl_close($ch);

preg_match('/name="_token" value="([^"]+)"/', $homeHtml, $tokenMatch);
$csrfToken = $tokenMatch[1] ?? '';

$postDzire = [
    '_token' => $csrfToken,
    'customer_name' => 'Aditya Verma',
    'mobile' => '9244784443',
    'trip_type' => 'One-Way',
    'pickup_location' => 'Mangal Chowk, Bilaspur',
    'destination' => 'Swami Vivekananda Airport, Raipur',
    'travel_date' => date('Y-m-d', strtotime('+1 day')),
    'travel_time' => '10:00',
    'vehicle_id' => '1',
    'terms_accepted' => '1',
];

$ch = curl_init('http://127.0.0.1:8000/booking');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postDzire));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resDzire = curl_exec($ch);
$urlDzire = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

$dzireSuccess = (strpos($urlDzire, 'booking-success') !== false) || (strpos($resDzire, 'Booking request received') !== false) || (strpos($resDzire, 'VT-') !== false);
assertTest($dzireSuccess, "Booking submission with Maruti Suzuki Dzire (vehicle_id = 1) succeeded", "Redirected to: {$urlDzire}");

// 9. Test Booking Submission with Maruti Suzuki Ertiga (vehicle_id = 2)
$postErtiga = [
    '_token' => $csrfToken,
    'customer_name' => 'Rahul Sharma',
    'mobile' => '9244784443',
    'trip_type' => 'One-Way',
    'pickup_location' => 'Railway Station, Bilaspur',
    'destination' => 'Korba Commercial Hub',
    'travel_date' => date('Y-m-d', strtotime('+2 days')),
    'travel_time' => '14:30',
    'vehicle_id' => '2',
    'terms_accepted' => '1',
];

$ch = curl_init('http://127.0.0.1:8000/booking');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postErtiga));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resErtiga = curl_exec($ch);
$urlErtiga = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);
@unlink($cookieFile);

$ertigaSuccess = (strpos($urlErtiga, 'booking-success') !== false) || (strpos($resErtiga, 'Booking request received') !== false) || (strpos($resErtiga, 'VT-') !== false);
assertTest($ertigaSuccess, "Booking submission with Maruti Suzuki Ertiga (vehicle_id = 2) succeeded", "Redirected to: {$urlErtiga}");

echo "\n-----------------------------------------------------\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "-----------------------------------------------------\n";

exit($failCount > 0 ? 1 : 0);
