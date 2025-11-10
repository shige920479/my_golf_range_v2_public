<?php
namespace App\Services\Core;

use App\Exceptions\AppException;
use Throwable;

class ExceptionMapper
{
  public static function resolve(Throwable $e): array
  {
    if ($e instanceof AppException) {
      return [$e->getMessage(), $e->getStatusCode()];
    }
    return ['システムエラーが発生しました', 500];
  }

}