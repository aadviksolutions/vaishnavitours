<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureCustomer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'customer' => EnsureCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// Support serverless environments where root filesystem is read-only except /tmp
$isServerless = isset($_ENV['VERCEL']) ||
    isset($_SERVER['VERCEL']) ||
    getenv('VERCEL') ||
    env('APP_STORAGE_PATH') ||
    ! empty(getenv('AWS_LAMBDA_FUNCTION_NAME')) ||
    ! empty(getenv('LAMBDA_TASK_ROOT')) ||
    (PHP_SAPI !== 'cli' && DIRECTORY_SEPARATOR === '/' && is_dir('/tmp') && ! is_writable(base_path('storage')));

if ($isServerless) {
    $storagePath = env('APP_STORAGE_PATH', '/tmp/storage');
    $app->useStoragePath($storagePath);

    $requiredDirs = [
        $storagePath.'/framework/views',
        $storagePath.'/framework/cache/data',
        $storagePath.'/framework/sessions',
        $storagePath.'/logs',
        $storagePath.'/app',
        '/tmp/bootstrap/cache',
    ];
    foreach ($requiredDirs as $dir) {
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    $tmpDb = '/tmp/database.sqlite';
    $bundledDb = base_path('database/database.sqlite');
    if ((! file_exists($tmpDb) || filesize($tmpDb) === 0) && file_exists($bundledDb)) {
        @copy($bundledDb, $tmpDb);
        @chmod($tmpDb, 0666);
    }
}

return $app;
