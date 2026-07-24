<?php declare(strict_types=1);

class App
{
  private const SESSION_TIMEOUT = 1800;

  public static function run(): void
  {
    self::iniciarSesion();
    self::definirRutas();
    self::cargarNucleo();
    (new Router())->dispatch();
  }

  private static function iniciarSesion(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (isset($_SESSION['LAST_ACTIVITY'])) {
      if (time() - $_SESSION['LAST_ACTIVITY'] > self::SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: /login?error=sesion');
        exit;
      }
    }

    $_SESSION['LAST_ACTIVITY'] = time();
  }

  private static function definirRutas(): void
  {
    define('BASE_PATH', realpath(__DIR__ . '/..'));
  }

  private static function cargarNucleo(): void
  {
    spl_autoload_register(function (string $class): void {
      $directorios = [
        BASE_PATH . '/controllers/',
        BASE_PATH . '/models/',
        BASE_PATH . '/services/',
        BASE_PATH . '/middleware/',
        BASE_PATH . '/core/',
        BASE_PATH . '/helpers/',
      ];

      foreach ($directorios as $dir) {
        $archivo = $dir . $class . '.php';
        if (file_exists($archivo)) {
          require_once $archivo;
          return;
        }
      }
    });

    require_once BASE_PATH . '/config/Config.php';
    Config::cargarEnv(BASE_PATH . '/.env');

    require_once BASE_PATH . '/config/Conexion.php';
    require_once BASE_PATH . '/core/Router.php';
  }
}
