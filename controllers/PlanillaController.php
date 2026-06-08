<?php declare(strict_types=1);

class PlanillaController
{
  private PlanillaService $planillaService;
  private PlanillaModel $planillaModel;
  private CifradoService $cifrado;

  public function __construct()
  {

    $db = Conexion::conectar();
    $this->planillaService = new PlanillaService(new ISRService());
    $this->planillaModel = new PlanillaModel($db);
    $this->cifrado = new CifradoService();
  }

  /**
   * GET  /planilla -> muestra formulario + tabla (sesión)
   * POST /planilla/agregar -> agrega fila y redirige
   * POST /planilla/eliminar -> elimina fila por índice y redirige
   * POST /planilla/limpiar -> vacía la sesión y redirige
   */
  public function index(): void
  {
    SessionHelper::requerir();
    if (empty($_SESSION['ctx']['empresa_id'])) {
      header('Location: ' . BASE_URL . '/settings');
      exit;
    }

    /**
     * GET  /planilla -> muestra formulario + tabla (sesión)
     * POST /planilla/agregar -> agrega fila y redirige
     * POST /planilla/eliminar -> elimina fila por índice y redirige
     * POST /planilla/limpiar -> vacía la sesión y redirige
     * GET  /planilla/buscarEmpleado?cedula=X -> busca empleado por cedula
     */
    public function index(): void
    {
        SessionHelper::iniciar();

        $filas = $_SESSION['planilla_prueba'] ?? [];
        $totales = $this->calcularTotales($filas);
        $errores = $_SESSION['planilla_errores'] ?? [];
        $exito = $_SESSION['planilla_exito'] ?? '';
        unset($_SESSION['planilla_errores'], $_SESSION['planilla_exito']);

        $empresas = $this->planillaModel->listarEmpresas();
        $csrf = SessionHelper::generarCsrf();

        require BASE_PATH . '/views/planilla/index.php';
    }

    $filas = [];
    $totales = [];
    $planillaRow = $this->planillaModel->buscarPlanilla($empresaId, $periodo, $mes, $anio);

    if ($planillaRow) {
      $rawFilas = $this->planillaModel->listarDetallePlanilla((int) $planillaRow['id']);
      foreach ($rawFilas as $f) {
        try {
          $nombre = $cifrado->descifrar($f['nombre_completo'] ?? '');
          $cedula = $cifrado->descifrar($f['cedula'] ?? '');
        } catch (RuntimeException) {
          $nombre = '[error]';
          $cedula = '[error]';
        }
        $filas[] = [
          'nombre' => $nombre,
          'cedula' => $cedula,
          'cargo' => $f['cargo'],
          'estado_civil' => $f['estado_civil'],
          'calc' => [
            'salario_base_quincena' => (float) $f['salario_base_quincena'],
            'otros_ingresos' => (float) $f['otros_ingresos'],
            'otros_ingresos_sin_descuento' => (float) $f['otros_ingresos_sin_descuento'],
            'salario_bruto' => (float) $f['salario_bruto'],
            'desc_seguro_social' => (float) $f['desc_seguro_social'],
            'desc_seguro_educativo' => (float) $f['desc_seguro_educativo'],
            'desc_isr' => (float) $f['desc_isr'],
            'otros_descuentos' => (float) $f['otros_descuentos'],
            'total_descuentos' => (float) $f['total_descuentos'],
            'salario_neto' => (float) $f['salario_neto'],
            'pct_descuentos' => $f['pct_descuentos'],
            'alerta_desc_excede' => (bool)  $f['alerta_desc_excede'],
          ],
        ];
      }
      $totales = $this->calcularTotales($filas);
    }

    require BASE_PATH . '/views/planilla/index.php';
  }

  public function agregar(): void
  {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $qs = http_build_query(['periodo' => $periodo, 'mes' => $mes, 'anio' => $anio]);
      header('Location: ' . BASE_URL . '/planilla?' . $qs); exit;
    }

    if (!SessionHelper::verificarCsrf($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      exit('Token inválido.');
    }

    SessionHelper::requerir();

    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $salario = (float) ($_POST['salario'] ?? 0);
    $estadoCivil = $_POST['estado_civil'] ?? 'soltero';
    $otrosDesc = (float) ($_POST['otros_descuentos']?? 0);
    $anioInicio = (int) ($_POST['anio_inicio'] ?? date('Y'));
    $empresaId = (int) ($_SESSION['ctx']['empresa_id'] ?? 0);
    $periodo = $_POST['periodo'] ?? '1ra_quincena';
    $mes = (int) ($_POST['mes'] ?? (int) date('n'));
    $anio = (int) ($_POST['anio'] ?? (int) date('Y'));

    $ingresos = [];
    foreach ($_POST['ing_tipo'] ?? [] as $i => $tipo) {
      if (empty($tipo)) continue;
      $entry = ['tipo' => $tipo, 'monto' => (float) ($_POST['ing_monto'][$i] ?? 0)];
      if ($tipo === 'horas_extra') $entry['horas'] = (float) ($_POST['ing_horas'][$i] ?? 0);
      $ingresos[] = $entry;
    }

    $errores = [];
    if ($nombre === '') $errores[] = 'El nombre es requerido.';
    if ($cedula === '') $errores[] = 'La cédula es requerida.';
    if ($salario <= 0) $errores[] = 'El salario base debe ser mayor a 0.';
    if ($empresaId <= 0) {
      SessionHelper::flash('planilla_errores', ['Sin empresa activa. Configure en Settings.']);
      header('Location: ' . BASE_URL . '/settings');
      exit;
    }
    if (!in_array($estadoCivil, ['soltero','casado','unido'], true)) $errores[] = 'Estado civil inválido.';
    if (!in_array($periodo, ['1ra_quincena','2da_quincena'], true))  $errores[] = 'Período inválido.';
    if ($mes < 1 || $mes > 12) $errores[] = 'Mes inválido.';
    if ($anio < 2000 || $anio > 2100) $errores[] = 'Año inválido.';

    if (!empty($errores)) {
      SessionHelper::flash('planilla_errores', $errores);
      header('Location: ' . BASE_URL . '/planilla'); exit;
    }

    $empresa = $this->planillaModel->buscarEmpresa($empresaId);
    if (!$empresa) {
      SessionHelper::flash('planilla_errores', ['Empresa no encontrada.']);
      header('Location: ' . BASE_URL . '/planilla'); exit;
    }

    $calc = $this->planillaService->calcularQuincena([
      'salario_base' => $salario,
      'estado_civil' => $estadoCivil,
      'horas_semanales' => (float) $empresa['horas_semanales'],
      'semanas_mes' => (float) $empresa['semanas_mes'],
    ], ['ingresos' => $ingresos, 'otros_descuentos' => $otrosDesc]);

    try {
      $pdo = Conexion::conectar();
      $pdo->beginTransaction();

      $cedulaHash = CifradoService::hash($cedula);
      $colab = $this->planillaModel->buscarColaboradorPorCedulaHash($cedulaHash);

      if ($colab) {
        $colaboradorId = (int) $colab['id'];
      } else {
        $colaboradorId = $this->planillaModel->insertarColaborador([
          ':empresa_id' => $empresaId,
          ':nombre_completo' => $this->cifrado->cifrar($nombre),
          ':nombre_hash' => CifradoService::hash($nombre),
          ':cedula' => $this->cifrado->cifrar($cedula),
          ':cedula_hash' => $cedulaHash,
          ':estado_civil' => $estadoCivil,
          ':cargo' => $cargo,
          ':salario_base' => $salario,
          ':anio_inicio' => $anioInicio,
        ]);
      }

      $planillaRow = $this->planillaModel->buscarPlanilla($empresaId, $periodo, $mes, $anio);
      $planillaId  = $planillaRow
        ? (int) $planillaRow['id']
        : $this->planillaModel->crearPlanilla($empresaId, $periodo, $mes, $anio, $_SESSION['usuario_id'] ?? null);

      if ($this->planillaModel->existeDetalle($planillaId, $colaboradorId)) {
        $pdo->rollBack();
        SessionHelper::flash('planilla_errores', ["Este colaborador ya tiene detalle en {$periodo} {$mes}/{$anio}."]);
        header('Location: ' . BASE_URL . '/planilla'); exit;
      }

      $this->planillaModel->insertarDetalle($planillaId, $colaboradorId, $calc, $_SESSION['usuario_id'] ?? null);
      $pdo->commit();

      SessionHelper::flash('planilla_exito', "Guardado — Planilla #{$planillaId} · Colaborador #{$colaboradorId}");

    } catch (\Throwable $e) {
      if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
      SessionHelper::flash('planilla_errores', ['Error BD: ' . $e->getMessage()]);
    }

    $qs = http_build_query(['periodo' => $periodo, 'mes' => $mes, 'anio' => $anio]);
    header('Location: ' . BASE_URL . '/planilla?' . $qs);
    exit;
  }
  public function eliminar(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: ' . BASE_URL . '/planilla');
      exit;
    }

    SessionHelper::iniciar();
    $idx = (int) ($_POST['idx'] ?? -1);

    if (isset($_SESSION['planilla_prueba'][$idx])) {
      array_splice($_SESSION['planilla_prueba'], $idx, 1);
    }

    header('Location: ' . BASE_URL . '/planilla');
    exit;
  }

  public function limpiar(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: ' . BASE_URL . '/planilla');
      exit;
    }

    SessionHelper::iniciar();
    unset($_SESSION['planilla_prueba']);

    $periodo = $_POST['periodo'] ?? '1ra_quincena';
    $mes = (int) ($_POST['mes']  ?? (int) date('n'));
    $anio = (int) ($_POST['anio'] ?? (int) date('Y'));
    $qs = http_build_query(['periodo' => $periodo, 'mes' => $mes, 'anio' => $anio]);
    header('Location: ' . BASE_URL . '/planilla?' . $qs);    exit;
  }

  //Helpers privados

  private function calcularTotales(array $filas): array
  {
    $keys = [
      'salario_base_quincena', 'otros_ingresos', 'salario_bruto',
      'desc_seguro_social', 'desc_seguro_educativo', 'desc_isr',
      'otros_descuentos', 'total_descuentos',
      'otros_ingresos_sin_descuento', 'salario_neto',
    ];

    $t = array_fill_keys($keys, 0.0);
    foreach ($filas as $f) {
      foreach ($keys as $k) {
        $t[$k] += (float) ($f['calc'][$k] ?? 0);
      }
    }
    return $t;
  }
  public function test(): void
  {
    SessionHelper::iniciar();

    $filas = $_SESSION['planilla_prueba'] ?? [];
    $totales = $this->calcularTotales($filas);
    $errores = SessionHelper::getFlash('planilla_errores', []);
    $exito   = SessionHelper::getFlash('planilla_exito', '');

    $empresas = $this->planillaModel->listarEmpresas();
    $csrf = SessionHelper::generarCsrf();

        $t = array_fill_keys($keys, 0.0);
        foreach ($filas as $f) {
            foreach ($keys as $k) {
                $t[$k] += (float) ($f['calc'][$k] ?? 0);
            }
        }
        return $t;
    }

    /**
     * GET /planilla/buscar-empleado?cedula=8-123-4567
     * Retorna JSON con datos del empleado para auto-llenar formulario
     * CREADO - Issue #5: Captura de planilla - búsqueda de empleado
     */
    public function buscarEmpleado(): void
    {
        header('Content-Type: application/json');

        $cedula = trim($_GET['cedula'] ?? '');
        if (strlen($cedula) < 5) {
            http_response_code(400);
            echo json_encode(['error' => 'Cédula inválida']);
            exit;
        }

        $cedulaHash = CifradoService::hash($cedula);
        $colab = $this->planillaModel->buscarColaboradorPorCedulaHash($cedulaHash);

        if (!$colab) {
            http_response_code(404);
            echo json_encode(['error' => 'Empleado no encontrado']);
            exit;
        }

        // Descifrar datos sensibles
        $nombre = $this->cifrado->descifrar($colab['nombre_completo']);
        $cedulaDescifrada = $this->cifrado->descifrar($colab['cedula']);

        echo json_encode([
            'id' => (int) $colab['id'],
            'nombre_completo' => $nombre,
            'cedula' => $cedulaDescifrada,
            'cargo' => $colab['cargo'],
            'salario_base' => (float) $colab['salario_base'],
            'estado_civil' => $colab['estado_civil'],
            'anio_inicio' => (int) $colab['anio_inicio'],
        ]);
        exit;
    }
}
