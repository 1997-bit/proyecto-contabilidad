<?php declare(strict_types=1);

class Router
{
  public function dispatch(): void
  {
    $uri    = trim($_GET['url'] ?? '', '/');
    $partes = array_values(array_filter(explode('/', $uri), fn($s) => $s !== ''));

    $controllerName = ucfirst($partes[0] ?? 'login') . 'Controller';
    $metodo         = $partes[1] ?? 'index';
    $params         = array_slice($partes, 2);

    if (!class_exists($controllerName)) {
      $this->error404();
      return;
    }

    $controller = new $controllerName();

    if (!is_callable([$controller, $metodo])) {
      $this->error404();
      return;
    }

    call_user_func_array([$controller, $metodo], $params);
  }

  private function error404(): void
  {
    http_response_code(404);
    $vista = BASE_PATH . '/views/404.PHP';
    file_exists($vista) ? require $vista : print('<h1>Error 404: página no encontrada</h1>');
    exit;
  }
}
