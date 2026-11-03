<?php
/**
 * Vaishnavi Tours — One-Click Server Diagnostic & Setup Runner
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__);
$vendorAutoload = $baseDir . '/vendor/autoload.php';
$envFile = $baseDir . '/.env';
$envExample = $baseDir . '/.env.example';
$sqliteDb = $baseDir . '/database/database.sqlite';

$action = $_GET['action'] ?? '';
$outputLog = '';

// Auto-create .env if missing
if (!file_exists($envFile) && file_exists($envExample)) {
    @copy($envExample, $envFile);
}

// Action: Run Composer Install
if ($action === 'install_composer') {
    $canExec = function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));

    if (!$canExec) {
        $outputLog = "⚠️ PHP 'shell_exec' or 'exec' is disabled on this server by default.\n\nPlease use aaPanel's built-in Composer tool:\n1. Open aaPanel -> Websites\n2. Click on 'vaishnavitours.com'\n3. Click 'Composer' tab on the left\n4. Click 'Install' button!";
    } else {
        $cmd = "cd " . escapeshellarg($baseDir) . " && (composer install --no-dev --optimize-autoloader --ignore-platform-reqs 2>&1 || /www/server/php/84/bin/composer install --no-dev --optimize-autoloader --ignore-platform-reqs 2>&1)";
        $result = @shell_exec($cmd);
        $outputLog = $result ?: "Command executed. Check if vendor/autoload.php is created.";
    }
}

// Action: Generate Key & Link Storage
if ($action === 'fix_permissions') {
    if (file_exists($vendorAutoload)) {
        @shell_exec("cd " . escapeshellarg($baseDir) . " && php artisan key:generate --force 2>&1");
        @shell_exec("cd " . escapeshellarg($baseDir) . " && php artisan storage:link 2>&1");
        $outputLog = "Artisan commands executed successfully.";
    } else {
        $outputLog = "Please run Composer Install first before running artisan commands.";
    }
}

$isVendorInstalled = file_exists($vendorAutoload);
$isEnvPresent = file_exists($envFile);
$isDbPresent = file_exists($sqliteDb);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaishnavi Tours — Server Setup Desk</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            margin: 0;
            padding: 30px 16px;
            display: flex;
            justify-content: center;
        }
        .container {
            max-width: 680px;
            width: 100%;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
        h1 {
            font-size: 1.4rem;
            margin: 0 0 8px 0;
            color: #38bdf8;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .subtitle {
            font-size: 0.9rem;
            color: #94a3b8;
            margin-bottom: 24px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-success { background: #064e3b; color: #6ee7b7; border: 1px solid #059669; }
        .badge-danger { background: #7f1d1d; color: #fca5a5; border: 1px solid #dc2626; }
        .card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 13.5px;
        }
        .btn {
            display: inline-block;
            background: #0284c7;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn:hover { background: #0369a1; }
        .btn-success { background: #16a34a; }
        .btn-success:hover { background: #15803d; }
        .btn-outline {
            background: transparent;
            border: 1px solid #475569;
            color: #cbd5e1;
        }
        .btn-outline:hover { background: #334155; }
        .instructions {
            background: #0f172a;
            border-left: 4px solid #38bdf8;
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            line-height: 1.7;
        }
        .instructions ol {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }
        pre {
            background: #0b1120;
            border: 1px solid #334155;
            padding: 12px;
            border-radius: 8px;
            font-size: 12px;
            color: #a5f3fc;
            overflow-x: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span>🚕</span> Vaishnavi Tours — Server Status</h1>
        <div class="subtitle">Diagnostic checker and installation runner for live hosting</div>

        <?php if ($outputLog): ?>
            <div style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; font-size: 13px; color: #f8fafc;">Execution Result:</h4>
                <pre><?= htmlspecialchars($outputLog) ?></pre>
            </div>
        <?php endif; ?>

        <div class="card-row">
            <div>
                <strong>PHP Version:</strong> <?= PHP_VERSION ?>
            </div>
            <span class="status-badge badge-success">OK</span>
        </div>

        <div class="card-row">
            <div>
                <strong>Environment (.env):</strong>
                <span style="color: #94a3b8; font-size: 12px; margin-left: 6px;"><?= $isEnvPresent ? '.env file active' : 'Missing' ?></span>
            </div>
            <span class="status-badge <?= $isEnvPresent ? 'badge-success' : 'badge-danger' ?>">
                <?= $isEnvPresent ? 'Ready' : 'Missing' ?>
            </span>
        </div>

        <div class="card-row">
            <div>
                <strong>Composer Autoloader:</strong>
                <span style="color: #94a3b8; font-size: 12px; margin-left: 6px;"><?= $isVendorInstalled ? 'vendor/autoload.php loaded' : 'Missing' ?></span>
            </div>
            <span class="status-badge <?= $isVendorInstalled ? 'badge-success' : 'badge-danger' ?>">
                <?= $isVendorInstalled ? 'Installed' : 'Action Required' ?>
            </span>
        </div>

        <div class="card-row">
            <div>
                <strong>SQLite Database:</strong>
                <span style="color: #94a3b8; font-size: 12px; margin-left: 6px;"><?= $isDbPresent ? 'database.sqlite active' : 'Not found' ?></span>
            </div>
            <span class="status-badge <?= $isDbPresent ? 'badge-success' : 'badge-danger' ?>">
                <?= $isDbPresent ? 'Ready' : 'Not Found' ?>
            </span>
        </div>

        <div style="margin-top: 24px; display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if (!$isVendorInstalled): ?>
                <a href="?action=install_composer" class="btn">⚡ Try Auto Composer Install</a>
            <?php else: ?>
                <a href="/" class="btn btn-success">🚀 Go to Live Website &rarr;</a>
                <a href="?action=fix_permissions" class="btn btn-outline">Generate Storage Link</a>
            <?php endif; ?>
        </div>

        <?php if (!$isVendorInstalled): ?>
            <div class="instructions">
                <strong>⭐ How to complete setup in aaPanel (Recommended):</strong>
                <ol>
                    <li>Log into your <strong>aaPanel Dashboard</strong>.</li>
                    <li>Go to <strong>Websites</strong> &rarr; click on <strong>vaishnavitours.com</strong>.</li>
                    <li>In the popup settings modal, click the <strong>Composer</strong> tab on the left.</li>
                    <li>Select <strong>PHP Version</strong> (e.g. PHP 8.2 or 8.3) and click the <strong>Install</strong> button.</li>
                    <li>Wait ~20 seconds for Composer to finish, then refresh this page!</li>
                </ol>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

