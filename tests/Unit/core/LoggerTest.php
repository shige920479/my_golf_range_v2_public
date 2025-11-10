<?php

use App\Services\Core\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
  private string $logfile;

  protected function setUp(): void
  {
    
    $this->logfile = sys_get_temp_dir() . '/test.log';
    if (file_exists($this->logfile)) {
      unlink($this->logfile);
    }
  }
 
  #[Test]
  public function log_writes_with_message_and_type(): void
  {
    $logger = new Logger($this->logfile, true);
    $logger->log('log-write-test', 'LOG-TEST');

    $log = file_get_contents($this->logfile);
    $this->assertStringContainsString('LOG-TEST: log-write-test', $log);
  }

  #[Test]
  public function error_wrietLog(): void
  {
    $logger = new Logger($this->logfile, false);
    $logger->error('error-test');

    $log = file_get_contents($this->logfile);
    $this->assertStringContainsString('ERROR: error-test', $log);
  }
  #[Test]
  public function waring_writeLog(): void
  {
    $logger = new Logger($this->logfile, false);
    $logger->warning('warning-test');

    $log = file_get_contents($this->logfile);
    $this->assertStringContainsString('WARNING: warning-test', $log);
  }
  #[Test]
  public function info_writeLog(): void
  {
    $logger = new Logger($this->logfile, true);
    $logger->info('info-test');

    $log = file_get_contents($this->logfile);
    $this->assertStringContainsString('INFO: info-test', $log);
  }
  #[Test]
  public function debug_writeLog(): void
  {
    $logger = new Logger($this->logfile, true);
    $logger->debug('debug-test');

    $log = file_get_contents($this->logfile);
    $this->assertStringContainsString('DEBUG: debug-test', $log);
  }
  #[Test]
  public function it_writes_error_log_with_context(): void
  {
    $logger = new Logger($this->logfile, false);
    $logger->error('with-context', ['foo' => 'bar', 'id' => 99]);

    $log = file_get_contents($this->logfile);
    
    $this->assertStringContainsString('ERROR: with-context', $log);
    $this->assertStringContainsString('"foo":"bar"', $log);
    $this->assertStringContainsString('"id":99', $log);
  }


}