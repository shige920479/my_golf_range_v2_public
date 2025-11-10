<?php
namespace App\Services\Core;

use Carbon\Carbon;

class Logger
{
  public function __construct(
    private string $logfile,
    private bool $debug
  ){}

  public function log(string $message, ?string $type = 'LOG'): void
  {
    $log =  "[" . date('Y-m-d H:i:s') . "] {$type}: {$message}";
    file_put_contents($this->logfile, $log . PHP_EOL, FILE_APPEND);
  }

  public function error(string $message, array $context = []): void
  {
    $this->writeLog('ERROR', $message, $context);
  }
  
  public function warning(string $message, array $context = []): void
  {
    $this->writeLog('WARNING', $message, $context);
  }

  public function info(string $message, array $context = []): void
  {
    if ($this->debug) {
      $this->writeLog('INFO', $message, $context);
    }
  }

  public function debug(string $message, array $context = []): void
  {
      if ($this->debug) {
          $this->writeLog('DEBUG', $message, $context);
      }
  }

  private function writeLog(string $level, string $message, array $context = []): void
  {
    $log = "[" . date('Y-m-d H:i:s') . "] {$level}: {$message}";
    if (! empty($context)) {
      $log .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
    file_put_contents($this->logfile, $log . PHP_EOL, FILE_APPEND);
  }
}