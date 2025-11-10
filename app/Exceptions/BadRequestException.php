<?php
namespace App\Exceptions;

class BadRequestException extends AppException
{
  public function __construct(string $message = '不正なリクエストです')
  {
    parent::__construct($message, 400);
  }
}