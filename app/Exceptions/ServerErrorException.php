<?php
namespace App\Exceptions;

class ServerErrorException extends AppException
{
  public function __construct(string $message = "システムエラーが発生しました")
  {
    parent::__construct($message, 500);
  }
}