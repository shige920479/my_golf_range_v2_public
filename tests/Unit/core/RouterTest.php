<?php

use App\Dev\Controller\DummyController;
use App\Exceptions\NotFoundException;
use App\Services\Core\Router;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

class RouterTest extends TestCase
{
  private Router $router;

  protected function setUp(): void
  {
    $this->router = new Router();
    $_SERVER = [];
  }

  #[Test]
  public function get_dispatch_get_route(): void
  {
    $this->router->get('/dummy', DummyController::class, 'index');
    $_SERVER['REQUEST_METHOD'] = "GET";
    $_SERVER['REQUEST_URI'] = '/dummy';

    ob_start();
    $this->router->dispatch();
    $output = ob_get_clean();

    $this->assertSame('dummy called', $output);
  }

  #[Test]
  public function get_dispatch_get_route_with_param(): void
  {
    $this->router->get('/dummy/{id}', DummyController::class, 'show');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/dummy/123';

    ob_start();
    $this->router->dispatch();
    $output = ob_get_clean();

    $this->assertSame("show called with id=123", $output);
  }

  #[Test]
  public function post_dispatch_post_route(): void
  {
    $this->router->post('/dummy', DummyController::class, 'store');
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/dummy';
    $_POST = ['name' => 'taro'];

    ob_start();
    $this->router->dispatch();
    $output = ob_get_clean();

    $this->assertSame('stored: name=taro', $output);
  }

  #[Test]
  public function dispatch_throws_not_found_exception_for_unknown_route(): void
  {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/not-found-route';

    $this->expectException(NotFoundException::class);
    $this->router->dispatch();
  }
  
  #[Test]
  public function dispatch_throws_not_found_exception_for_not_exists_controller(): void
  {
    $this->router->get('/not-exists-controller', 'NotExistsController', 'notExists');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/not-exists-controller';

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessageMatches('/コントローラークラスが存在しません/');
    $this->router->dispatch();
  }

  #[Test]
  public function dispatch_throws_not_found_exception_for_not_exists_action(): void
  {
    $this->router->get('/dummy', DummyController::class, 'notExist');
    $_SERVER['REQUEST_METHOD'] = "GET";
    $_SERVER['REQUEST_URI'] = '/dummy';

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessageMatches('/メソッドが見つかりません/');
    $this->router->dispatch();
  }

  #[Test]
  public function dispatch_multi_parameter(): void
  {
    $this->router->get("/dummy/{id}/item/{itemId}", DummyController::class, "multiParams");
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = "/dummy/12/item/345";

    ob_start();
    $this->router->dispatch();
    $output = ob_get_clean();

    var_dump($output);

    $this->assertSame('called id=12:item=345', $output);
  }

  #[Test]
  public function dispatch_not_matched_for_regex(): void
  {
    $this->router->get("/dummy/{id}/item/{itemId}", DummyController::class, "show");
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = "/dummy/12/item/@345";

    $this->expectException(NotFoundException::class);
    $this->router->dispatch();
  }


}