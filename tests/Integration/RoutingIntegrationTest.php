<?php

use App\Dev\Controller\IntegrationTestController;
use App\Services\Core\Router;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RoutingIntegrationTest extends TestCase
{
  #[Test]
  public function it_dispatches_request_to_controller_and_returns_output(): void
  {
    $router = new Router();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/hello/Taro';

    $router->get('/hello/{name}', IntegrationTestController::class, 'hello');

    ob_start();
    $router->dispatch();
    $output = ob_get_clean();
    $_SERVER = [];

    $this->assertSame('Hello, Taro', $output);
  }

}