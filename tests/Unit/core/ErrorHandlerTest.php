<?php

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
  private string $logfile;

  protected function setUp(): void
  {
    $this->logfile = sys_get_temp_dir() . '/test.log';
    if (file_exists($this->logfile)) {
      unlink($this->logfile);
    }
    $_SERVER = [];
  }

  #[Test]
  public function handle_skips_logging_for_favicon_requests(): void
  {
    $logger = new Logger($this->logfile, true);
    $handler = new ErrorHandler($logger, true);
    $_SERVER['REQUEST_URI'] = '/favicon.ico';

    $e = new \RuntimeException('This should not be logged');

    try {
      $handler->handle($e);
    } catch(\Throwable $ex) {

    }
    $log = file_exists($this->logfile) ? file_get_contents($this->logfile) : '';
    $this->assertSame('', $log);
  }

  #[Test]
  public function handle_returns_json_for_ajax_requests(): void
  {
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
    $logger = new Logger($this->logfile, true);
    $handler = new ErrorHandler($logger, false);

    $e = new BadRequestException('Invalid token');
    $response = $this->invokeMethod($handler, 'renderResponse', [$e]);

    $this->assertStringContainsString('"success":false', $response);
    $this->assertStringContainsString('Invalid token', $response);
  }

  #[Test]
  public function handle_returns_debug_output_in_debug_mode(): void
  {
    $logger = new Logger($this->logfile, true);
    $handler = new ErrorHandler($logger, true);

    $e = new \RuntimeException('something broke');

    $response = $this->invokeMethod($handler, 'renderResponse', [$e]);
    $this->assertStringContainsString('Type:</strong> Error</p>', $response);
    $this->assertStringContainsString('something broke', $response);
  }

  #[Test]
  public function handle_returns_error_page_in_production(): void
  {
    $logger = new Logger($this->logfile, false);
    $handler = new ErrorHandler($logger, false);

    $e = new NotFoundException('page not found');

    $response = $this->invokeMethod($handler, 'renderResponse', [$e]);
    $this->assertStringContainsString('404', $response);
    $this->assertStringContainsString('page not found', $response);
  }

  #[Test]
  public function handle_maps_error_exception_levels(): void
  {
    $logger = new Logger($this->logfile, false);
    $handler = new ErrorHandler($logger, false);

    $e = new \ErrorException('warning', 0, E_WARNING);
    $this->assertSame('Warning', $this->invokeMethod($handler, 'mapSeverity', [$e]));

    $e = new \ErrorException('notice', 0, E_NOTICE);
    $this->assertSame('Notice', $this->invokeMethod($handler, 'mapSeverity', [$e]));

    $e = new \ErrorException('deprecated', 0, E_DEPRECATED);
    $this->assertSame('Deprecated', $this->invokeMethod($handler, 'mapSeverity', [$e]));

    $e = new \ErrorException('fatal', 0, E_ERROR);
    $this->assertSame('Fatal Error', $this->invokeMethod($handler, 'mapSeverity', [$e]));
  }

  public function handle_maps_throwable(): void
  {
    $logger = new Logger($this->logfile, false);
    $handler = new ErrorHandler($logger, false);

    $e = new \TypeError('type error', 0);
    $this->assertSame('Fatal Error', $this->invokeMethod($handler, 'mapSeverity', [$e]));

    $e = new \ParseError('parse error', 0);
    $this->assertSame('Fatal Error', $this->invokeMethod($handler, 'mapSeverity', [$e]));

    $e = new \Error('error', 0);
    $this->assertSame('Fatal Error', $this->invokeMethod($handler, 'mapSeverity', [$e]));
    
    $e = new \RuntimeException('runtime error', 0);
    $this->assertSame('Fatal Error', $this->invokeMethod($handler, 'mapSeverity', [$e]));
  }

  private function invokeMethod(object $object, string $methodName, array $params = [])
  {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invokeArgs($object, $params);
  }
}