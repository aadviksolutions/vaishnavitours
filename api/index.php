<?php

/**
 * Vercel Serverless Function Entrypoint for Vaishnavi Tours (Laravel)
 *
 * This entrypoint is used when deploying via Vercel Serverless Functions.
 * Note: When using Dockerfile.vercel, FrankenPHP serves requests directly via /app/public/index.php.
 */

// 1. Normalize server script paths so Symfony/Laravel handles root routing correctly
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

// 2. Enforce APP_KEY requirement (do not generate or use hardcoded secrets in production)
$hasAppKey = !empty(getenv('APP_KEY')) || !empty($_ENV['APP_KEY']) || !empty($_SERVER['APP_KEY']);
if (!$hasAppKey && (getenv('APP_ENV') === 'production' || ($_ENV['APP_ENV'] ?? null) === 'production')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Configuration Error: APP_KEY is required in production. Please set APP_KEY in your Vercel Project Environment Variables.\n";
    exit(1);
}

// 3. Fallback serverless-safe drivers if not specified in environment
if (empty(getenv('SESSION_DRIVER')) && empty($_ENV['SESSION_DRIVER']) && empty($_SERVER['SESSION_DRIVER'])) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
    $_SERVER['SESSION_DRIVER'] = 'cookie';
}

if (empty(getenv('LOG_CHANNEL')) && empty($_ENV['LOG_CHANNEL']) && empty($_SERVER['LOG_CHANNEL'])) {
    putenv('LOG_CHANNEL=stderr');
    $_ENV['LOG_CHANNEL'] = 'stderr';
    $_SERVER['LOG_CHANNEL'] = 'stderr';
}

// 4. Ensure writable /tmp storage directories exist on serverless filesystem
$storagePath = '/tmp/storage';
$dirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    $storagePath . '/app',
    '/tmp/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

// 5. Dispatch through public index.php
require __DIR__ . '/../public/index.php';
