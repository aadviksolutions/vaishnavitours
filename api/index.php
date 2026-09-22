<?php

/**
 * Vercel Serverless Function Entrypoint for Vaishnavi Tours (Laravel)
 *
 * This entrypoint is used when deploying via Vercel Serverless Functions.
 * Note: When using Dockerfile.vercel, FrankenPHP serves requests directly via /app/public/index.php.
 */

// 1. Normalize server script paths so Symfony/Laravel handles root routing correctly
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';

// 2. Synchronize and enforce APP_KEY requirement with safe fallback
$appKey = getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? ($_SERVER['APP_KEY'] ?? 'base64:iu3vhq+8ihfTUUGzz5XkCyYRaCCeSKOTpsvJyc7RDjw='));

if (! empty($appKey)) {
    putenv("APP_KEY={$appKey}");
    $_ENV['APP_KEY'] = $appKey;
    $_SERVER['APP_KEY'] = $appKey;
}

$appUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? ($_SERVER['APP_URL'] ?? 'https://vaishnavitours.vercel.app'));
putenv("APP_URL={$appUrl}");
$_ENV['APP_URL'] = $appUrl;
$_SERVER['APP_URL'] = $appUrl;

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
        if (! getenv($varKey)) {
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
    $storagePath.'/framework/views',
    $storagePath.'/framework/cache/data',
    $storagePath.'/framework/sessions',
    $storagePath.'/logs',
    $storagePath.'/app',
    '/tmp/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

// 5. Initialize writable SQLite database on serverless environment if no external database is configured
$dbConn = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? ($_SERVER['DB_CONNECTION'] ?? ''));
$dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? ''));
$isExternalDb = ! empty($dbHost) && $dbHost !== '127.0.0.1' && $dbHost !== 'localhost';

if (! $isExternalDb) {
    $tmpDb = '/tmp/database.sqlite';
    $bundledDb = __DIR__.'/../database/database.sqlite';

    if (! file_exists($tmpDb) || filesize($tmpDb) === 0) {
        if (file_exists($bundledDb)) {
            @copy($bundledDb, $tmpDb);
        } else {
            @touch($tmpDb);
        }
        @chmod($tmpDb, 0666);
    }

    putenv('DB_CONNECTION=sqlite');
    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_SERVER['DB_CONNECTION'] = 'sqlite';

    putenv("DB_DATABASE={$tmpDb}");
    $_ENV['DB_DATABASE'] = $tmpDb;
    $_SERVER['DB_DATABASE'] = $tmpDb;
}

// 6. Dispatch through public index.php
require __DIR__.'/../public/index.php';
