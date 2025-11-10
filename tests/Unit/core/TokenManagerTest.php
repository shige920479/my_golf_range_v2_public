<?php

use App\Services\Security\TokenManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TokenManagerTest extends TestCase
{
  private TokenManager $manager;

  protected function setUp(): void
  {
    $this->manager = new TokenManager();
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  }

  protected function tearDown(): void
  {
    $_SESSION = [];
  }

  #[Test]
  public function generateToken_generate_token(): void
  {
    $token = $this->manager->generateToken();

    $this->assertIsString($token);
    $this->assertSame(64, strlen($token));
  }

  #[Test]
  public function get_creates_token_if_not_exist(): void
  {
    $token = $this->manager->get();

    $this->assertSame($token, $_SESSION['token']);
    $this->assertArrayHasKey('token', $_SESSION);
  }

  #[Test]
  public function get_returns_existing_token(): void
  {
    $_SESSION['token'] = 'token_test123';
    $getToken = new TokenManager();
    $token = $getToken->get();

    $this->assertSame('token_test123', $token);
  }

  #[Test]
  public function destroy_can_delete_token(): void
  {
    $token = $this->manager->get();
    $this->manager->destroy();

    $this->assertArrayNotHasKey('token', $_SESSION);
  }
}