<?php
use Dotenv\Dotenv;

if (! defined('BASE_PATH')) define('BASE_PATH', realpath(__DIR__ . '/../'));
require_once __DIR__ . '/../vendor/autoload.php';

$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'dev';
$envFile = $appEnv === 'testing' ? '.env.testing' : '.env';

$dotenv = Dotenv::createImmutable(BASE_PATH, $envFile);
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

Carbon\Carbon::setLocale('ja');

// テスト用に上書き
if (!defined('MAX_RESERVE_DATE')) define('MAX_RESERVE_DATE', 3);
if (!defined('OPEN_TIME')) define('OPEN_TIME', '08:00:00');
if (!defined('CLOSE_TIME')) define('CLOSE_TIME', '09:30:00');

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/functions/common.php';
