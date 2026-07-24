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

    $empresas = $this->planillaModel->listarEmpresas();
    $empresaId = (int) ($_SESSION['ctx']['empresa_id'] ?? 0);
    if (!SessionHelper::empresaIdValida($empresaId, $empresas)) {
      header('Location: ' . BASE_URL . '/settings');
      exit;
    }

    $periodo = $_GET['periodo'] ?? '1ra_quincena';
    $mes = (int) ($_GET['mes'] ?? (int) date('n'));
    $anio = (int) ($_GET['anio'] ?? (int) date('Y'));

    $errores = SessionHelper::getFlash('planilla_errores', []);
    $exito = SessionHelper::getFlash('planilla_exito', '');
    $csrf = SessionHelper::generarCsrf();

    $rawColabs = $this->planillaModel->listarColaboradoresActivos($empresaId);
    $colaboradores = [];
    foreach ($rawColabs as $c) {
      $c['nombre_completo'] = $this->cifrado->descifrarConFallback($c['nombre_completo']);
      $c['cedula'] = $this->cifrado->descifrarConFallback($c['cedula']);
      $colaboradores[] = $c;
    }

    $filas = [];
    $totales = [];
    $planillaRow = $this->planillaModel->buscarPlanilla($empresaId, $periodo, $mes, $anio);

    if ($planillaRow) {
      $rawFilas = $this->planillaModel->listarDetallePlanilla((int) $planillaRow['id']);
      foreach ($rawFilas as $f) {
        $nombre = $this->cifrado->descifrarConFallback($f['nombre_completo'] ?? '');
        $cedula = $this->cifrado->descifrarConFallback($f['cedula'] ?? '');
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
    SessionHelper::exigirPost(BASE_URL . '/planilla');

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

    if ($empresaId <= 0) {
      SessionHelper::flash('planilla_errores', ['Sin empresa activa. Configure en Settings.']);
      header('Location: ' . BASE_URL . '/settings');
      exit;
    }

    $ingresos = $this->parseIngresosPost();
    $errores = $this->validarNuevoDetalle($nombre, $cedula, $salario, $estadoCivil, $periodo, $mes, $anio);

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

    $this->persistirDetalle($empresaId, $periodo, $mes, $anio, [
      'nombre' => $nombre,
      'cedula' => $cedula,
      'cargo' => $cargo,
      'salario' => $salario,
      'estado_civil' => $estadoCivil,
      'anio_inicio' => $anioInicio,
    ], $calc);

    $qs = http_build_query(['periodo' => $periodo, 'mes' => $mes, 'anio' => $anio]);
    header('Location: ' . BASE_URL . '/planilla?' . $qs);
    exit;
  }

  private function parseIngresosPost(): array
  {
    $tiposHorasExtra = ['horas_extra_diurna', 'horas_extra_nocturna', 'horas_extra_dominical'];

    $ingresos = [];
    foreach ($_POST['ing_tipo'] ?? [] as $i => $tipo) {
      if (empty($tipo)) continue;
      $entry = ['tipo' => $tipo];
      if (in_array($tipo, $tiposHorasExtra, true)) {
        $entry['horas'] = (float) ($_POST['ing_horas'][$i] ?? 0);
      } else {
        $entry['monto'] = (float) ($_POST['ing_monto'][$i] ?? 0);
      }
      $ingresos[] = $entry;
    }
    return $ingresos;
  }

  private function validarNuevoDetalle(
    string $nombre,
    string $cedula,
    float $salario,
    string $estadoCivil,
    string $periodo,
    int $mes,
    int $anio
  ): array {
    $errores = [];
    if ($nombre === '') $errores[] = 'El nombre es requerido.';
    if ($cedula === '') $errores[] = 'La cédula es requerida.';
    if ($salario <= 0) $errores[] = 'El salario base debe ser mayor a 0.';
    if (!in_array($estadoCivil, ['soltero','casado','unido'], true)) $errores[] = 'Estado civil inválido.';
    if (!in_array($periodo, ['1ra_quincena','2da_quincena'], true))  $errores[] = 'Período inválido.';
    if ($mes < 1 || $mes > 12) $errores[] = 'Mes inválido.';
    if ($anio < 2000 || $anio > 2100) $errores[] = 'Año inválido.';
    return $errores;
  }

  /**
   * Busca o crea el colaborador y la planilla, e inserta el detalle dentro de una transacción.
   * En éxito o en error deja un flash y retorna; el caller es quien redirige.
   */
  private function persistirDetalle(int $empresaId, string $periodo, int $mes, int $anio, array $d, array $calc): void
  {
    try {
      $pdo = Conexion::conectar();
      $pdo->beginTransaction();

      $cedulaHash = CifradoService::hash($d['cedula']);
      $colab = $this->planillaModel->buscarColaboradorPorCedulaHash($cedulaHash);

      if ($colab) {
        $colaboradorId = (int) $colab['id'];
      } else {
        $colaboradorId = $this->planillaModel->insertarColaborador([
          ':empresa_id' => $empresaId,
          ':nombre_completo' => $this->cifrado->cifrar($d['nombre']),
          ':nombre_hash' => CifradoService::hash($d['nombre']),
          ':cedula' => $this->cifrado->cifrar($d['cedula']),
          ':cedula_hash' => $cedulaHash,
          ':estado_civil' => $d['estado_civil'],
          ':cargo' => $d['cargo'],
          ':salario_base' => $d['salario'],
          ':anio_inicio' => $d['anio_inicio'],
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

      SessionHelper::flash('planilla_exito', "Guardado. Planilla #{$planillaId}, colaborador #{$colaboradorId}");

    } catch (\Throwable $e) {
      if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
      SessionHelper::flash('planilla_errores', ['Error BD: ' . $e->getMessage()]);
    }
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
  public function lista(): void
  {
    SessionHelper::requerir();

    $empresas = $this->planillaModel->listarEmpresas();
    $empresaId = (int) ($_GET['empresa_id'] ?? 0);
    $mes = (int) ($_GET['mes'] ?? 0);
    $anio = (int) ($_GET['anio'] ?? (int) date('Y'));
    $periodo = $_GET['periodo'] ?? '';

    $planillas = $this->planillaModel->listarPlanillas($empresaId, $mes, $anio, $periodo);

    require BASE_PATH . '/views/planilla/lista.php';
  }
}
