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

// 2. Synchronize and enforce APP_KEY requirement
$appKey = getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? ($_SERVER['APP_KEY'] ?? null));

if (!empty($appKey)) {
    putenv("APP_KEY={$appKey}");
    $_ENV['APP_KEY'] = $appKey;
    $_SERVER['APP_KEY'] = $appKey;
} elseif (getenv('APP_ENV') === 'production' || ($_ENV['APP_ENV'] ?? null) === 'production' || ($_SERVER['APP_ENV'] ?? null) === 'production') {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Configuration Error: APP_KEY is required in production. Please set APP_KEY in your Vercel Project Environment Variables.\n";
    exit(1);
}

// Synchronize other project variables passed via $_SERVER into getenv and $_ENV
foreach ($_SERVER as $varKey => $varVal) {
    if (is_string($varVal) && (
        str_starts_with($varKey, 'APP_') ||
        str_starts_with($varKey, 'DB_') ||
        str_starts_with($varKey, 'MAIL_') ||
        str_starts_with($varKey, 'SESSION_') ||
        str_starts_with($varKey, 'CACHE_') ||
        str_starts_with($varKey, 'QUEUE_') ||
        str_starts_with($varKey, 'FILESYSTEM_')
    )) {
        if (!getenv($varKey)) {
            putenv("{$varKey}={$varVal}");
            $_ENV[$varKey] = $varVal;
        }
    }
}

// 3. Fallback serverless-safe environment flags & drivers
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

putenv('APP_STORAGE_PATH=/tmp/storage');
$_ENV['APP_STORAGE_PATH'] = '/tmp/storage';
$_SERVER['APP_STORAGE_PATH'] = '/tmp/storage';

if (empty(getenv('SESSION_DRIVER')) && empty($_ENV['SESSION_DRIVER']) && empty($_SERVER['SESSION_DRIVER'])) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
    $_SERVER['SESSION_DRIVER'] = 'cookie';
}

if (empty(getenv('CACHE_STORE')) && empty($_ENV['CACHE_STORE']) && empty($_SERVER['CACHE_STORE'])) {
    putenv('CACHE_STORE=array');
    $_ENV['CACHE_STORE'] = 'array';
    $_SERVER['CACHE_STORE'] = 'array';
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
