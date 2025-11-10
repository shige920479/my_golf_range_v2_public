<?php

use App\Exceptions\BadRequestException;
use App\Services\Core\ExceptionMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExceptionMapperTest extends TestCase
{
  #[Test]
  public function resolve_app_exception():void
  {
    $e = new BadRequestException('不正なリクエストです');
    [$message, $code] = ExceptionMapper::resolve($e);

    $this->assertSame('不正なリクエストです', $message);
    $this->assertSame(400, $code);
  }
  #[Test]
  public function resolve_exception(): void
  {
    $e = new Exception('標準のexception');
    [$message, $code] = ExceptionMapper::resolve($e);

    $this->assertSame('システムエラーが発生しました', $message);
    $this->assertSame(500, $code);
  }

  
}