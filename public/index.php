<?php
declare(strict_types=1);
ob_start();

require_once __DIR__ . '/../bootstrap/app.php';

use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\Router;

session_start();

$logger = new Logger(BASE_PATH . '/app/log/app.log', $_ENV['APP_ENV'] !== 'production');
$errorHandler = new ErrorHandler($logger, $_ENV['APP_ENV'] !== 'production');

try {
  $router = new Router();
  require BASE_PATH . '/routes/web.php';
  if ($_ENV["APP_ENV"] !== 'production' && file_exists(BASE_PATH . '/routes/dev.php')) {
    require BASE_PATH . '/routes/dev.php';
  }
  $router->dispatch();

} catch (Throwable $e) {
  $errorHandler->handle($e);
}

ob_end_flush();