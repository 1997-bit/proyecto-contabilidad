<?php declare(strict_types=1);

class SettingsController
{
  private PDO $db;

  public function __construct()
  {
    SessionHelper::requerir();
    $this->db = Conexion::conectar();
  }

  public function index(): void
  {
    $empresas = $this->listarEmpresas();
    $empresaActiva = $_SESSION['ctx']['empresa_id'] ?? null;
    $errores = SessionHelper::getFlash('settings_errores', []);
    $exito = SessionHelper::getFlash('settings_exito', '');
    require BASE_PATH . '/views/settings.php';
  }

  public function seleccionar(): void
  {
    SessionHelper::exigirPost(BASE_URL . '/settings');
    SessionHelper::exigirCsrf(BASE_URL . '/settings', 'settings_errores');

    $id = (int) ($_POST['empresa_id'] ?? 0);
    $stmt = $this->db->prepare("SELECT id, nombre FROM empresas WHERE id = :id AND activo = 1 LIMIT 1");
    $stmt->execute([':id' => $id]);
    $emp = $stmt->fetch();
    if (!$emp) {
      SessionHelper::flash('settings_errores', ['Empresa no valida.']);
      header('Location: ' . BASE_URL . '/settings');
      exit;
    }
    $_SESSION['ctx'] = [
      'empresa_id' => (int) $emp['id'],
      'empresa'    => $emp['nombre'],
    ];
    SessionHelper::flash('settings_exito', 'Empresa activa: ' . $emp['nombre']);
    header('Location: ' . BASE_URL . '/settings');
    exit;
  }

  public function crearEmpresa(): void
  {
    SessionHelper::exigirPost(BASE_URL . '/settings');
    SessionHelper::exigirCsrf(BASE_URL . '/settings', 'settings_errores');

    $nombre = trim($_POST['nombre'] ?? '');
    $ruc    = trim($_POST['ruc'] ?? '');
    if ($nombre === '') {
      SessionHelper::flash('settings_errores', ['Nombre de empresa requerido.']);
      header('Location: ' . BASE_URL . '/settings');
      exit;
    }
    $stmt = $this->db->prepare("INSERT INTO empresas (nombre, ruc) VALUES (:nombre, :ruc)");
    $stmt->execute([':nombre' => $nombre, ':ruc' => $ruc ?: null]);
    SessionHelper::flash('settings_exito', "Empresa '{$nombre}' creada.");
    header('Location: ' . BASE_URL . '/settings');
    exit;
  }

  public function desactivarEmpresa(): void
  {
    SessionHelper::exigirPost(BASE_URL . '/settings');
    SessionHelper::exigirCsrf(BASE_URL . '/settings', 'settings_errores');

    $id = (int) ($_POST['empresa_id'] ?? 0);
    $stmt = $this->db->prepare("UPDATE empresas SET activo = 0 WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if (($_SESSION['ctx']['empresa_id'] ?? null) === $id) {
      unset($_SESSION['ctx']);
    }
    SessionHelper::flash('settings_exito', 'Empresa desactivada.');
    header('Location: ' . BASE_URL . '/settings');
    exit;
  }

  private function listarEmpresas(): array
  {
    return $this->db
                ->query("SELECT id, nombre, ruc, activo FROM empresas ORDER BY activo DESC, nombre ASC")
                ->fetchAll();
  }
}
