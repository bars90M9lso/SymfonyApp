<?php
// Minimal PHPUnit bootstrap for this project
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "Autoload file not found: $autoload\n");
    exit(1);
}
require $autoload;

// Ensure test environment
if (!isset($_SERVER['APP_ENV'])) {
    $_SERVER['APP_ENV'] = 'test';
}
putenv('APP_ENV=test');
