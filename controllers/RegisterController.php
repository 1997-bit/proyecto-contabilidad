<?php

class RegisterController
{
  public function index(): void
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->guardar();
      return;
    }
    require BASE_PATH . '/views/register/register.php';
  }

  private function guardar(): void
  {
    if (!SessionHelper::verificarCsrf($_POST['csrf_token'] ?? '')) {
      $error = 'Token inválido. Recarga la página e intenta de nuevo.';
      require BASE_PATH . '/views/register/register.php';
      return;
    }

    $nombre = trim($_POST['nombre']   ?? '');
    $email = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol = 'visor'; // registro público siempre crea usuarios visor; escalar rol requiere un admin

    // Validación básica
    if (empty($nombre) || empty($email) || empty($password)) {
      $error = 'Todos los campos son requeridos.';
      require BASE_PATH . '/views/register/register.php';
      return;
    }

    $cifrado = new CifradoService();
    $emailHash = CifradoService::hash($email);

    // Verificar que el email no exista ya
    $model = new UsuarioModel();
    if ($model->existeEmailHash($emailHash)) {
      $error = 'Ya existe un usuario con ese email.';
      require BASE_PATH . '/views/register/register.php';
      return;
    }

    $model->insertar([
      'nombre' => $cifrado->cifrar($nombre),
      'email' => $cifrado->cifrar($email),
      'email_hash' => $emailHash,
      'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
      'rol' => $rol,
    ]);

    $exito = 'Usuario creado. <a href="' . BASE_URL . '/login">Ir al login</a>';
    require BASE_PATH . '/views/register/register.php';
  }
}
