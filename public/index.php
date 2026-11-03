<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Auto-initialize .env if missing on production deployment
$baseDir = dirname(__DIR__);
if (! file_exists($baseDir.'/.env') && file_exists($baseDir.'/.env.example')) {
    @copy($baseDir.'/.env.example', $baseDir.'/.env');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $baseDir.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
if (! file_exists(__DIR__.'/../vendor/autoload.php')) {
    // Attempt automatic composer recovery if shell exec is enabled
    if (function_exists('exec')) {
        @exec("cd {$baseDir} && composer install --no-dev --optimize-autoloader --ignore-platform-reqs 2>&1");
    }
}

if (! file_exists(__DIR__.'/../vendor/autoload.php')) {
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Vaishnavi Tours — Setup in Progress</title>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box}';
    echo '.card{background:#1e293b;border:1px solid #334155;border-radius:16px;padding:32px;max-width:580px;width:100%;box-shadow:0 20px 25px -5px rgba(0,0,0,0.5)}';
    echo 'h1{font-size:1.35rem;margin:0 0 12px 0;color:#38bdf8;display:flex;align-items:center;gap:10px}';
    echo 'p{font-size:0.92rem;line-height:1.6;color:#94a3b8;margin:0 0 14px 0}';
    echo '.box{background:#0b1120;border:1px solid #1e293b;border-left:4px solid #38bdf8;padding:14px 16px;border-radius:8px;margin:14px 0;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:12.5px;line-height:1.7;color:#f1f5f9;overflow-x:auto}';
    echo '.step{margin-top:14px;padding:12px;background:#1e293b;border:1px solid #334155;border-radius:8px;font-size:0.88rem;color:#cbd5e1}';
    echo '</style></head><body>';
    echo '<div class="card">';
    echo '<h1><span>🚕</span> Vaishnavi Tours — Initial Setup</h1>';
    echo '<p>The PHP Composer autoloader (<code>vendor/autoload.php</code>) is not yet installed on this server.</p>';
    echo '<div class="step"><strong>Option A: In aaPanel GUI (Recommended - No Terminal needed)</strong><br>';
    echo '1. Go to aaPanel &rarr; <strong>Websites</strong> &rarr; Click <strong>vaishnavitours.com</strong><br>';
    echo '2. Click the <strong>Composer</strong> tab &rarr; click <strong>Install</strong>.</div>';
    echo '<div class="step"><strong>Option B: Via Terminal / SSH</strong><br>';
    echo '<div class="box">cd /www/wwwroot/vaishnavitours.com<br>composer install --no-dev --optimize-autoloader</div></div>';
    echo '<p style="text-align:center;margin-top:16px;"><a href="/setup.php" style="display:inline-block;background:#0284c7;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">Open Setup &amp; Diagnostic Desk &rarr;</a></p>';
    echo '<p style="margin-top:12px;font-size:12px;color:#64748b;text-align:center">After installation completes, refresh this page to access Vaishnavi Tours.</p>';
    echo '</div></body></html>';
    exit;
}

require __DIR__.'/../vendor/autoload.php';

try {
    // Bootstrap Laravel and handle the request...
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><head><title>500 Application Error — Vaishnavi Tours</title>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0f172a;color:#f8fafc;padding:30px 16px;margin:0;display:flex;justify-content:center}';
    echo '.card{max-width:820px;width:100%;background:#1e293b;border:1px solid #ef4444;border-radius:12px;padding:24px;box-shadow:0 10px 25px rgba(0,0,0,0.5)}';
    echo 'h1{color:#f87171;font-size:1.25rem;margin:0 0 12px 0}p{font-size:13.5px;color:#cbd5e1;line-height:1.6}';
    echo 'pre{background:#0b1120;border:1px solid #334155;padding:14px;border-radius:8px;overflow-x:auto;font-size:12px;color:#e2e8f0;white-space:pre-wrap}';
    echo '.btn{display:inline-block;background:#0284c7;color:#fff;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;margin-top:10px}';
    echo '</style></head><body><div class="card">';
    echo '<h1>🚨 Laravel Error 500: '.htmlspecialchars($e->getMessage()).'</h1>';
    echo '<p><strong>File:</strong> <code>'.htmlspecialchars($e->getFile()).'</code> on line <strong>'.htmlspecialchars($e->getLine()).'</strong></p>';
    echo '<p><a href="/setup.php" class="btn">🛠️ Open Setup &amp; Auto-Fix Desk &rarr;</a></p>';
    echo '<pre>'.htmlspecialchars($e->getTraceAsString()).'</pre>';
    echo '</div></body></html>';
}
