<?php declare(strict_types=1);

class HomeController
{
  public function index()
  {
    SessionHelper::requerir(); // redirige a /login si no hay sesión
    require BASE_PATH . '/views/home.php';
  }
}
