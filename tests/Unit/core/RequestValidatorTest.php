<?php

use App\Exceptions\BadRequestException;
use App\Services\Core\RequestValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RequestValidatorTest extends TestCase
{
  private RequestValidator $validator;

  protected function setUp(): void
  {
    $_SESSION = [];
    $_SERVER = [];
    $this->validator = new RequestValidator(['skip1', 'skip2']);
  }

  protected function tearDown(): void
  {
    $_SESSION = [];
    $_SERVER = [];
  }

  #[Test]
  public function validateToken_mode_in_skipModes(): void
  {
    $post = ['mode' => 'skip1'];
    $result = $this->validator->validateToken($post);

    $this->assertNull($result);
  }

  #[Test]
  public function validateToken_valid_post_token(): void
  {
    $post = ['token' => 'token123', 'mode' => 'not_skip'];
    $_SESSION['token'] = 'token123';

    $this->assertNull($this->validator->validateToken($post));
  }

  #[Test]
  public function validatetoken_valid_header_token(): void
  {
    $post = [];
    $_SESSION['token'] = 'token123';
    $_SERVER['HTTP_X_CSRF_TOKEN'] = 'token123';

    $this->assertNull($this->validator->validateToken($post));

  }

  #[Test]
  public function validateToken_invalid_post_token(): void
  {
    $post = ['token' => 'invalidtoken'];
    $_SESSION['token'] = 'validtoken';

    $this->expectException(BadRequestException::class);

    $this->validator->validateToken($post);
  }
  #[Test]
  public function validateToken_empty_session_token(): void
  {
    $post = ['token' => 'token123'];

    $this->expectException(BadRequestException::class);

    $this->validator->validateToken($post);
  }

  #[Test]
  public function validateToken_post_token_priority(): void
  {
    $post = ['token' => 'post123'];
    $_SERVER['HTTP_X_CSRF_TOKEN'] = 'header123';
    $_SESSION['token'] = 'post123';

    $this->assertNull($this->validator->validateToken($post));
    $this->assertSame('post123', $_SESSION['token']);
  }



}