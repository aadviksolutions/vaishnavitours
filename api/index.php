<?php

// Vercel Serverless Entry Point for Laravel
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $storagePath = '/tmp/storage';
    if (!is_dir($storagePath . '/framework/views')) {
        @mkdir($storagePath . '/framework/views', 0755, true);
    }
    if (!is_dir($storagePath . '/framework/cache/data')) {
        @mkdir($storagePath . '/framework/cache/data', 0755, true);
    }
    if (!is_dir($storagePath . '/framework/sessions')) {
        @mkdir($storagePath . '/framework/sessions', 0755, true);
    }
    if (!is_dir($storagePath . '/logs')) {
        @mkdir($storagePath . '/logs', 0755, true);
    }
    putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
}

require __DIR__ . '/../public/index.php';
