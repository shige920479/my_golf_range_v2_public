<?php
namespace App\Exceptions;

use Exception;
use Throwable;

class AppException extends Exception
{
  protected int $statusCode;

  public function __construct(string $message, int $statusCode = 500, ?Throwable $previous = null)
  {
    parent::__construct($message, $statusCode, $previous);
    $this->statusCode = $statusCode;
  }

  public function getStatusCode(): int
  {
    return $this->statusCode;
  }
}