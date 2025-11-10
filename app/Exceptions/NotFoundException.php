<?php
namespace App\Exceptions;

class NotFoundException extends AppException
{
  public function __construct(string $message = "ページが見つかりません")
  {
    parent::__construct($message, 404);
  }
}