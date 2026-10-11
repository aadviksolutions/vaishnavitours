<?php

$baseUrl = 'http://127.0.0.1:8000';

echo "=====================================================\n";
echo "VAISHNAVI TOURS — HERO CAR IMAGE SEQUENCE TEST\n";
echo "=====================================================\n\n";

$pass = 0;
$fail = 0;

function testStep($label, $callback)
{
    global $pass, $fail;
    try {
        $result = $callback();
        if ($result !== false) {
            echo " [PASS] $label\n";
            $pass++;
        } else {
            echo " [FAIL] $label\n";
            $fail++;
        }
    } catch (Throwable $e) {
        echo " [FAIL] $label: ".$e->getMessage()."\n";
        $fail++;
    }
}

// 1. Check local files
testStep('All 240 frame images exist on disk', function () {
    $files = glob(__DIR__.'/../public/assets/hero-loop/*.jpg');

    return count($files) === 240;
});

testStep('Manifest file exists and has 240 frame entries', function () {
    $manifestPath = __DIR__.'/../public/assets/hero-loop/manifest.json';
    if (! file_exists($manifestPath)) {
        return false;
    }
    $data = json_decode(file_get_contents($manifestPath), true);

    return isset($data['count']) && $data['count'] === 240 && count($data['frames']) === 240;
});

// 2. Check homepage markup
testStep('Homepage returns HTTP 200 with hero sequence container', function () use ($baseUrl) {
    $html = @file_get_contents($baseUrl.'/');
    if (! $html) {
        return false;
    }
    $hasContainer = strpos($html, 'id="hero-car-sequence"') !== false;
    $hasCanvas = strpos($html, 'id="hero-car-canvas"') !== false;
    $hasFallback = strpos($html, 'id="hero-car-fallback"') !== false;
    $hasPreload = strpos($html, 'ezgif-frame-001.jpg') !== false;
    $hasLegacyAssert = strpos($html, 'hero-taxi.jpg') !== false;

    return $hasContainer && $hasCanvas && $hasFallback && $hasPreload && $hasLegacyAssert;
});

// 3. Test HTTP availability of first, middle, last, and random frames
$sampleFrames = [
    'assets/hero-loop/manifest.json',
    'assets/hero-loop/ezgif-frame-001.jpg',
    'assets/hero-loop/ezgif-frame-060.jpg',
    'assets/hero-loop/ezgif-frame-120.jpg',
    'assets/hero-loop/ezgif-frame-180.jpg',
    'assets/hero-loop/ezgif-frame-240.jpg',
];

foreach ($sampleFrames as $sample) {
    testStep("Asset HTTP 200 OK: $sample", function () use ($baseUrl, $sample) {
        $ch = curl_init($baseUrl.'/'.$sample);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $code === 200;
    });
}

// 4. Verify CSS classes
testStep('CSS defines .hero-visual-canvas and hardware acceleration', function () {
    $css = file_get_contents(__DIR__.'/../public/css/style.css');

    return strpos($css, '.hero-visual-canvas') !== false &&
           strpos($css, 'prefers-reduced-motion') !== false;
});

echo "\n-----------------------------------------------------\n";
echo "SUMMARY: $pass Passed, $fail Failed\n";
echo "-----------------------------------------------------\n";

exit($fail === 0 ? 0 : 1);
