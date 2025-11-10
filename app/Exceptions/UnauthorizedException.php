<?php
namespace App\Exceptions;

class UnauthorizedException extends AppException
{
  public function __construct(string $message = '認証が必要です')
  {
    parent::__construct($message, 401);
  }
}