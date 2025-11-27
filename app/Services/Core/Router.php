<?php
namespace App\Services\Core;

use App\Exceptions\ErrorHandler;

class Router
{
  private array $routes = [];

  public function get(string $path, string $controller, string $method): void
  {
    $this->routes['GET'][$path] = [$controller, $method];
  }

  public function post(string $path, string $controller, string $method): void
  {
    $this->routes['POST'][$path] = [$controller, $method];
  }

  public function dispatch(): void
  {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // 登録されたルートをすべて走査
    foreach ($this->routes[$method] ?? [] as $route => [$controller, $action]) {
      $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([0-9a-zA-Z_-]+)', $route);
      $pattern = '#^' . $pattern . '$#';

      if (preg_match($pattern, $uri, $matches)) {
        array_shift($matches); // 最初のマッチはフルURIなので除外

        if(class_exists($controller)) {
          $controllerClass = $controller;
        } else {
          $controllerClass = '\\App\\Controller\\' . str_replace('/', '\\', $controller);
        }
        
        if (!class_exists($controllerClass)) {
            ErrorHandler::log("コントローラークラスが存在しません: {$controllerClass}");
            ErrorHandler::redirectWithCode('500');
            return;
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $action)) {
            ErrorHandler::log("メソッドが見つかりません: {$controllerClass}::{$action}()");
            ErrorHandler::redirectWithCode('500');
            return;
        }

        // 可変引数を渡す
        $instance->$action(...$matches);
        return;
      }
    }

    // 該当ルートなし
    ErrorHandler::log("ページが見つかりません: URI = {$uri}, METHOD = {$method}");
    ErrorHandler::redirectWithCode('404');
  }
}