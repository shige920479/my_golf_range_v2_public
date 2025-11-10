<?php

use App\Exceptions\BadRequestException;
use App\Services\Core\RequestHandler;
use App\Services\Core\RequestValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RequestHandlerTest extends TestCase
{
  private RequestHandler $handler;

  protected function setUp(): void
  {
    $_SERVER = [];
    $_SESSION = [];
    $_POST = [];
    $_GET = [];
    $_FILES = [];
    $this->handler = new RequestHandler(new RequestValidator([]));
  }
  
  protected function tearDown(): void
  {
    $_SERVER = [];
    $_SESSION = [];
    $_POST = [];
    $_GET = [];
    $_FILES = [];
  }

  #[Test]
  public function getRequest_only_get(): void
  {
    $_GET = ['name' => 'taro'];
    $_SERVER['REQUEST_METHOD'] = 'GET';

    $result = $this->handler->getRequest();
    
    $this->assertSame(['name' => 'taro'], $result);
  }

  #[Test]
  public function getRequest_only_post(): void
  {
    $_POST = ['name' => 'taro', 'token' => 'token123'];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['token'] = 'token123';

    $result = $this->handler->getRequest();

    $this->assertSame(['name' => 'taro', 'token' => 'token123'], $result);
  }

  #[Test]
  public function getRequest_invalid_token(): void
  {
    $_POST = ['name' => 'taro', 'token' => 'invalid'];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['token'] = 'token123';

    $this->expectException(BadRequestException::class);
    $this->handler->getRequest();
  }

  #[Test]
  public function getRequest_with_files(): void
  {
    $_POST = ['name' => 'taro', 'token' => 'token123'];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['token'] = 'token123';
    $_FILES = ['image' => [
      'name' => 'test.png', 'size' => 123
    ]];
    
    $result = $this->handler->getRequest();

    $this->assertArrayHasKey('image', $result);
    $this->assertSame('test.png', $result['image']['name']);
    $this->assertSame([
      'name' => 'taro',
      'token' => 'token123',
      'image' => [
        'name' => 'test.png', 'size' => 123
      ]
    ], $result);
  }

  #[Test]
  public function getRequest_get_and_post_merge(): void
  {
    $_GET = ['page' => 8, 'sort' => 'high'];
    $_POST = ['search' => 'abc', 'token' => 'token123'];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['token'] = 'token123';

    $result = $this->handler->getRequest();

    $this->assertSame([
      'page' => 8,
      'sort' => 'high',
      'search' => 'abc',
      'token' => 'token123'
    ], $result);
  }

  
}