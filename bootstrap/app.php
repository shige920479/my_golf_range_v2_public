<?php
date_default_timezone_set('Asia/Tokyo');
define('BASE_PATH', realpath(__DIR__ . '/..'));

require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

require_once BASE_PATH . '/app/config/config.php';
require_once __DIR__ . '/../app/functions/common.php';
Carbon\Carbon::setLocale('ja');


$env = $_ENV["APP_ENV"] ?? 'production';

if ($env === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/app/log/error.log');
} else {
    ini_set('display_errors', '1');
    ini_set('log_errors', '0');
    ini_set('error_log', BASE_PATH . '/app/log/error.log');
    error_reporting(E_ALL);
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });
}