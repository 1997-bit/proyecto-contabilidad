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

  public static function requerir(?string $rol = null): void
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

  /**
   * Corta la petición si no es POST, redirigiendo a $redirectUrl.
   */
  public static function exigirPost(string $redirectUrl): void
  {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
      header('Location: ' . $redirectUrl);
      exit;
    }
  }

  /**
   * Corta la petición si el CSRF de $_POST no es válido, redirigiendo a $redirectUrl.
   * Si $flashKey se indica, deja un mensaje de error flash con esa clave.
   */
  public static function exigirCsrf(string $redirectUrl, ?string $flashKey = null): void
  {
    if (!self::verificarCsrf($_POST['csrf_token'] ?? '')) {
      if ($flashKey !== null) {
        self::flash($flashKey, ['Token inválido. Recarga la página e intenta de nuevo.']);
      }
      header('Location: ' . $redirectUrl);
      exit;
    }
  }

  /**
   * Indica si $empresaId corresponde a una de las empresas dadas.
   */
  public static function empresaIdValida(int $empresaId, array $empresas): bool
  {
    foreach ($empresas as $e) {
      if ((int) $e['id'] === $empresaId) return true;
    }
    return false;
  }

  /**
   * Resuelve la empresa activa: GET > contexto de sesión > primera empresa disponible.
   * Solo devuelve un id que exista realmente en $empresas (evita confiar en un
   * empresa_id de sesión desactualizado que ya no exista).
   */
  public static function empresaIdActiva(array $empresas): int
  {
    $empresaId = (int) ($_GET['empresa_id'] ?? 0);
    if (!self::empresaIdValida($empresaId, $empresas)) {
      $empresaId = (int) ($_SESSION['ctx']['empresa_id'] ?? 0);
    }
    if (!self::empresaIdValida($empresaId, $empresas) && !empty($empresas)) {
      $empresaId = (int) $empresas[0]['id'];
    }
    return $empresaId;
  }
  public static function flash(string $key, mixed $val): void
  {
    self::iniciar();
    $_SESSION['_flash'][$key] = $val;
  }

  public static function getFlash(string $key, mixed $default = null): mixed
  {
    self::iniciar();
    $val = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);
    return $val;
  }
}
