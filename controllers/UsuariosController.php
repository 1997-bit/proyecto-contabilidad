<?php declare(strict_types=1);

class UsuariosController
{
  private UsuarioModel $model;
  private CifradoService $cifrado;

  public function __construct()
  {
    SessionHelper::requerir('admin');
    $this->model = new UsuarioModel();
    $this->cifrado = new CifradoService();
  }

  public function index(): void
  {
    $rawUsuarios = $this->model->listarTodos();
    $usuarios = [];
    foreach ($rawUsuarios as $u) {
      $u['nombre'] = $this->cifrado->descifrarConFallback($u['nombre']);
      $u['email'] = $this->cifrado->descifrarConFallback($u['email']);
      $usuarios[] = $u;
    }

    $errores = SessionHelper::getFlash('usuarios_errores', []);
    $exito = SessionHelper::getFlash('usuarios_exito', '');
    $csrf = SessionHelper::generarCsrf();

    require BASE_PATH . '/views/usuarios/index.php';
  }

  public function crear(): void
  {
    SessionHelper::exigirPost(BASE_URL . '/usuarios');
    SessionHelper::exigirCsrf(BASE_URL . '/usuarios', 'usuarios_errores');

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol = $_POST['rol'] ?? '';

    if ($nombre === '' || $email === '' || $password === '') {
      SessionHelper::flash('usuarios_errores', ['Nombre, email y contraseña son requeridos.']);
      header('Location: ' . BASE_URL . '/usuarios');
      exit;
    }
    if (!in_array($rol, ['admin', 'contador', 'visor'], true)) {
      SessionHelper::flash('usuarios_errores', ['Rol inválido.']);
      header('Location: ' . BASE_URL . '/usuarios');
      exit;
    }

    $emailHash = CifradoService::hash($email);
    if ($this->model->existeEmailHash($emailHash)) {
      SessionHelper::flash('usuarios_errores', ['Ya existe un usuario con ese email.']);
      header('Location: ' . BASE_URL . '/usuarios');
      exit;
    }

    $this->model->insertar([
      'nombre' => $this->cifrado->cifrar($nombre),
      'email' => $this->cifrado->cifrar($email),
      'email_hash' => $emailHash,
      'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
      'rol' => $rol,
    ]);

    SessionHelper::flash('usuarios_exito', "Usuario '{$nombre}' creado correctamente.");
    header('Location: ' . BASE_URL . '/usuarios');
    exit;
  }

  public function cambiarRol(): void
  {
    SessionHelper::exigirPost(BASE_URL . '/usuarios');
    SessionHelper::exigirCsrf(BASE_URL . '/usuarios', 'usuarios_errores');

    $id = (int) ($_POST['id'] ?? 0);
    $rol = $_POST['rol'] ?? '';

    if ($id === (int) ($_SESSION['usuario_id'] ?? 0)) {
      SessionHelper::flash('usuarios_errores', ['No puedes cambiar tu propio rol. Pide a otro admin que lo haga.']);
      header('Location: ' . BASE_URL . '/usuarios');
      exit;
    }
    if (!in_array($rol, ['admin', 'contador', 'visor'], true)) {
      SessionHelper::flash('usuarios_errores', ['Rol inválido.']);
      header('Location: ' . BASE_URL . '/usuarios');
      exit;
    }
    if (!$this->model->buscarPorId($id)) {
      SessionHelper::flash('usuarios_errores', ['Usuario no encontrado.']);
      header('Location: ' . BASE_URL . '/usuarios');
      exit;
    }

    $this->model->actualizarRol($id, $rol);
    SessionHelper::flash('usuarios_exito', 'Rol actualizado correctamente.');
    header('Location: ' . BASE_URL . '/usuarios');
    exit;
  }

  public function toggleActivo(): void
  {
    SessionHelper::exigirPost(BASE_URL . '/usuarios');
    SessionHelper::exigirCsrf(BASE_URL . '/usuarios', 'usuarios_errores');

    $id = (int) ($_POST['id'] ?? 0);

    if ($id === (int) ($_SESSION['usuario_id'] ?? 0)) {
      SessionHelper::flash('usuarios_errores', ['No puedes activar/desactivar tu propia cuenta.']);
      header('Location: ' . BASE_URL . '/usuarios');
      exit;
    }

    $usuario = $this->model->buscarPorId($id);
    if (!$usuario) {
      SessionHelper::flash('usuarios_errores', ['Usuario no encontrado.']);
      header('Location: ' . BASE_URL . '/usuarios');
      exit;
    }

    $this->model->actualizarActivo($id, !$usuario['activo']);
    SessionHelper::flash('usuarios_exito', $usuario['activo'] ? 'Usuario desactivado.' : 'Usuario activado.');
    header('Location: ' . BASE_URL . '/usuarios');
    exit;
  }
}
