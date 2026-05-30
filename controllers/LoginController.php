<?php

class LoginController
{
  private const MAX_INTENTOS = 5;

  public function index(): void
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->autenticar();
      return;
    }
    require BASE_PATH . '/views/login/login.php';
  }

  public function logout(): void
  {
    // Verifica CSRF en logout POST
    SessionHelper::iniciar();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!SessionHelper::verificarCsrf($_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . '/login');
        exit;
      }
    }

    // Destruye sesión
    SessionHelper::destruir();

    // Borra cookie de sesión del navegador
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
      );
    }

    header('Location: ' . BASE_URL . '/login');
    exit;
  }

  private function autenticar(): void
  {
    if (!SessionHelper::verificarCsrf($_POST['csrf_token'] ?? '')) {
      $error = 'Token inválido. Recarga la página e intenta de nuevo.';
      require BASE_PATH . '/views/login/login.php';
      return;
    }
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
      $error = 'Completa todos los campos.';
      require BASE_PATH . '/views/login/login.php';
      return;
    }

    $model   = new UsuarioModel();
    $usuario = $model->buscarPorEmailHash($email);

    // Usuario no existe
    if (!$usuario) {
      $error = 'Credenciales incorrectas.';
      require BASE_PATH . '/views/login/login.php';
      return;
    }

    // Cuenta bloqueada
    if ($usuario['locked_until'] && strtotime($usuario['locked_until']) > time()) {
      $error = 'Cuenta bloqueada temporalmente. Intenta más tarde.';
      require BASE_PATH . '/views/login/login.php';
      return;
    }

    // Contraseña incorrecta
    if (!password_verify($password, $usuario['password_hash'])) {
      $model->incrementarIntentos($usuario['id']);

      if ($usuario['login_attempts'] + 1 >= self::MAX_INTENTOS) {
        $model->bloquearCuenta($usuario['id']);
        $error = 'Demasiados intentos. Cuenta bloqueada 15 minutos.';
      } else {
        $error = 'Credenciales incorrectas.';
      }

      require BASE_PATH . '/views/login/login.php';
      return;
    }

    // Login exitoso
    SessionHelper::set('usuario_id',     $usuario['id']);
    SessionHelper::set('usuario_rol',    $usuario['rol']);
    SessionHelper::set('usuario_nombre', $usuario['nombre_plain']);

    session_regenerate_id(true);
    header('Location: ' . BASE_URL . '/home');
    exit;
  }
}
