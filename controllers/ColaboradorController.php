<?php declare(strict_types=1);

class ColaboradorController
{
  private PDO $db;
  private CifradoService $cifrado;
  private ColaboradorModel $model;

  public function __construct()
  {
    $this->db = Conexion::conectar();
    $this->cifrado = new CifradoService();
    $this->model = new ColaboradorModel($this->db);
  }

  public function index(): void
  {
    SessionHelper::requerir();
    $colaboradores = $this->listar();
    $errores = [];
    $valores = [];
    require BASE_PATH . '/views/colaboradores/index.php';
  }

  public function guardar(): void
  {
    SessionHelper::requerir();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: /colaborador');
      exit;
    }

    if (!SessionHelper::verificarCsrf($_POST['csrf_token'] ?? '')) {
      header('Location: /colaborador');
      exit;
    }

    $valores = [
      'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
      'cedula'          => trim($_POST['cedula'] ?? ''),
      'estado_civil'    => $_POST['estado_civil'] ?? '',
      'cargo'           => trim($_POST['cargo'] ?? ''),
      'salario_base'    => $_POST['salario_base'] ?? '',
      'anio_inicio'     => $_POST['anio_inicio'] ?? '',
    ];

    $errores = $this->validar($valores);

    if (!empty($errores)) {
      $colaboradores = $this->listar();
      require BASE_PATH . '/views/colaboradores/index.php';
      return;
    }

    try {
      $this->model->insertar([
        ':empresa_id'      => $_SESSION['ctx']['empresa_id'],
        ':nombre_completo' => $this->cifrado->cifrar($valores['nombre_completo']),
        ':nombre_hash'     => CifradoService::hash($valores['nombre_completo']),
        ':cedula'          => $this->cifrado->cifrar($valores['cedula']),
        ':cedula_hash'     => CifradoService::hash($valores['cedula']),
        ':estado_civil'    => $valores['estado_civil'],
        ':cargo'           => $valores['cargo'],
        ':salario_base'    => (float) $valores['salario_base'],
        ':anio_inicio'     => (int) $valores['anio_inicio'],
      ]);
    } catch (PDOException $e) {
      if ($e->getCode() === '23000') {
        $errores['cedula'] = 'Ya existe un colaborador con esa cédula.';
        $colaboradores = $this->listar();
        require BASE_PATH . '/views/colaboradores/index.php';
        return;
      }
      throw $e;
    }

    header('Location: /colaborador?ok=1');
    exit;
  }

  public function editar(): void
  {
    SessionHelper::requerir();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: /colaborador');
      exit;
    }

    if (!SessionHelper::verificarCsrf($_POST['csrf_token'] ?? '')) {
      header('Location: /colaborador');
      exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
      header('Location: /colaborador');
      exit;
    }

    // IDOR: verificar que el colaborador pertenece a la empresa activa
    $colab = $this->model->buscarPorId($id);
    if (!$colab || (int) $colab['empresa_id'] !== (int) $_SESSION['ctx']['empresa_id']) {
      header('Location: /colaborador');
      exit;
    }

    $valores = [
      'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
      'cedula'          => trim($_POST['cedula'] ?? ''),
      'estado_civil'    => $_POST['estado_civil'] ?? '',
      'cargo'           => trim($_POST['cargo'] ?? ''),
      'salario_base'    => $_POST['salario_base'] ?? '',
      'anio_inicio'     => $_POST['anio_inicio'] ?? '',
    ];

    $errores = $this->validar($valores);

    if (!empty($errores)) {
      $colaboradores = $this->listar();
      $editarError = $errores;
      $editarId = $id;
      $editarValores = $valores;
      require BASE_PATH . '/views/colaboradores/index.php';
      return;
    }

    try {
      $this->model->actualizar([
        ':id'              => $id,
        ':nombre_completo' => $this->cifrado->cifrar($valores['nombre_completo']),
        ':nombre_hash'     => CifradoService::hash($valores['nombre_completo']),
        ':cedula'          => $this->cifrado->cifrar($valores['cedula']),
        ':cedula_hash'     => CifradoService::hash($valores['cedula']),
        ':estado_civil'    => $valores['estado_civil'],
        ':cargo'           => $valores['cargo'],
        ':salario_base'    => (float) $valores['salario_base'],
        ':anio_inicio'     => (int) $valores['anio_inicio'],
      ]);
    } catch (PDOException $e) {
      if ($e->getCode() === '23000') {
        $errores['cedula'] = 'Ya existe un colaborador con esa cédula.';
        $colaboradores = $this->listar();
        $editarError = $errores;
        $editarId = $id;
        $editarValores = $valores;
        require BASE_PATH . '/views/colaboradores/index.php';
        return;
      }
      throw $e;
    }

    header('Location: /colaborador?ok=1');
    exit;
  }

  public function eliminar(): void
  {
    SessionHelper::requerir();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: /colaborador');
      exit;
    }

    if (!SessionHelper::verificarCsrf($_POST['csrf_token'] ?? '')) {
      header('Location: /colaborador');
      exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
      header('Location: /colaborador');
      exit;
    }

    // IDOR: verificar que el colaborador pertenece a la empresa activa
    $colab = $this->model->buscarPorId($id);
    if (!$colab || (int) $colab['empresa_id'] !== (int) $_SESSION['ctx']['empresa_id']) {
      header('Location: /colaborador');
      exit;
    }

    $this->model->desactivar($id);

    header('Location: /colaborador?ok=1');
    exit;
  }

  private function validar(array $v): array
  {
    $e = [];

    if ($v['nombre_completo'] === '')
      $e['nombre_completo'] = 'El nombre es obligatorio.';

    if (!preg_match('/^\d{1,2}-\d{3,4}-\d{3,4}$/', $v['cedula']))
      $e['cedula'] = 'Formato inválido. Ej: 8-123-4567';

    if (!in_array($v['estado_civil'], ['soltero', 'casado', 'unido'], true))
      $e['estado_civil'] = 'Seleccione un estado civil.';

    if ($v['cargo'] === '')
      $e['cargo'] = 'El cargo es obligatorio.';

    $salario = (float) $v['salario_base'];
    if ($salario <= 0)
      $e['salario_base'] = 'El salario debe ser mayor a 0.';
    elseif ($salario < Config::SALARIO_MINIMO_MES)
      $e['salario_base_aviso'] = "Aviso: $salario está bajo el mínimo legal ($" . Config::SALARIO_MINIMO_MES . "). Se guardará igual.";

    $anio = (int) $v['anio_inicio'];
    if ($anio < 1900 || $anio > (int) date('Y'))
      $e['anio_inicio'] = 'Año inválido.';

    return $e;
  }

  private function listar(): array
  {
    $filas = $this->model->listarTodos();

    foreach ($filas as &$fila) {
      try {
        $fila['nombre_completo'] = $this->cifrado->descifrar($fila['nombre_completo']);
        $fila['cedula']          = $this->cifrado->descifrar($fila['cedula']);
      } catch (RuntimeException) {
        $fila['nombre_completo'] = '[error]';
        $fila['cedula']          = '[error]';
      }
    }

    return $filas;
  }
}
