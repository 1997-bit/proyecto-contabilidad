<?php

class SessionHelper
{
  public static function iniciar(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  public static function set(string $key, mixed $value): void
  {
    $_SESSION[$key] = $value;
  }

  public static function get(string $key, mixed $default = null): mixed
  {
    return $_SESSION[$key] ?? $default;
  }

  public static function existe(string $key): bool
  {
    return isset($_SESSION[$key]);
  }

  public static function destruir(): void
  {
    session_unset();
    session_destroy();
  }

  public static function requerir(string $rol = null): void
  {
    self::iniciar();

    if (!self::existe('usuario_id')) {
      header('Location: ' . BASE_URL . '/login');
      exit;
    }

    if ($rol && self::get('usuario_rol') !== $rol) {
      http_response_code(403);
      require BASE_PATH . '/views/403.php';
      exit;
    }
  }
  public static function generarCsrf(): string
  {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
  }

  public static function verificarCsrf(string $token): bool
  {
    return isset($_SESSION['csrf_token'])
      && hash_equals($_SESSION['csrf_token'], $token);
  }
}
