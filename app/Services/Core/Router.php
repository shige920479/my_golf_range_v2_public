<?php
namespace App\Services\Core;

use App\Exceptions\NotFoundException;
use RuntimeException;

class Router
{
  private array $routes = [];
  private Container $container;

  public function __construct(?Container $container = null)
  {
    $this->container = $container ?? new Container();
  }

  public function get(string $path, string $controller, string $action): void
  {
    $this->routes['GET'][$path] = [$controller, $action];
  }

  public function post(string $path, string $controller, string $action): void
  {
    $this->routes['POST'][$path] = [$controller, $action];
  }

  public function dispatch(): void
  {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = normalizeUri(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    foreach($this->routes[$method] ?? [] as $route => [$controller, $action]) {
      $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([0-9a-zA-Z_-]+)', $route);
      $pattern = '#^' . $pattern . '$#';

      if (preg_match($pattern, $uri, $matches)) {
        array_shift($matches);
       
        if (class_exists($controller)) {
          $controllerClass = $controller;
        } else {
          $controllerClass = '\\App\\Controller\\' . str_replace('/', '\\', $controller);
        }

        if (!class_exists($controllerClass)) {
          throw new RuntimeException("コントローラークラスが存在しません: {$controllerClass}");
        }
        
        $instance = $this->container->get($controllerClass);

        if (! method_exists($instance, $action)) {
          throw new RuntimeException("メソッドが見つかりません: {$controllerClass}::{$action}()");
        }

        $instance->$action(...$matches);
        return;
      }
    }
    throw new NotFoundException("ページが見つかりません: URI = {$uri}, METHOD = {$method}");
    return;
  }

}