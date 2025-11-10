<?php
namespace App\Exceptions;

class ForbiddenException extends AppException
{
  public function __construct(string $message = "このページへのアクセス権限がありません")
  {
    parent::__construct($message, 403);
  }
}