<?php declare(strict_types=1);

class PlanillaController
{
    private PlanillaService $planillaService;
    private PlanillaModel $planillaModel;
    private CifradoService $cifrado;

    public function __construct()
    {
        require_once BASE_PATH . '/config/Config.php';
        Config::cargarEnv(BASE_PATH . '/.env');

        $db = Conexion::conectar();
        $this->planillaService = new PlanillaService(new ISRService());
        $this->planillaModel   = new PlanillaModel($db);
        $this->cifrado         = new CifradoService();
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

public function agregar(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/planilla'); exit;
    }

    SessionHelper::iniciar();

    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $salario = (float) ($_POST['salario'] ?? 0);
    $estadoCivil = $_POST['estado_civil'] ?? 'soltero';
    $otrosDesc = (float) ($_POST['otros_descuentos']?? 0);
    $anioInicio = (int)   ($_POST['anio_inicio'] ?? date('Y'));
    $empresaId = (int)   ($_POST['empresa_id'] ?? 0);
    $periodo = $_POST['periodo'] ?? '1ra_quincena';
    $mes = (int)   ($_POST['mes'] ?? (int) date('n'));
    $anio = (int)   ($_POST['anio'] ?? (int) date('Y'));

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
    if ($empresaId <= 0) $errores[] = 'Debe seleccionar una empresa.';
    if (!in_array($estadoCivil, ['soltero','casado','unido'], true)) $errores[] = 'Estado civil inválido.';
    if (!in_array($periodo, ['1ra_quincena','2da_quincena'], true))  $errores[] = 'Período inválido.';
    if ($mes < 1 || $mes > 12) $errores[] = 'Mes inválido.';
    if ($anio < 2000 || $anio > 2100) $errores[] = 'Año inválido.';

    if (!empty($errores)) {
        $_SESSION['planilla_errores'] = $errores;
        header('Location: ' . BASE_URL . '/planilla'); exit;
    }

    $empresa = $this->planillaModel->buscarEmpresa($empresaId);
    if (!$empresa) {
        $_SESSION['planilla_errores'] = ['Empresa no encontrada.'];
        header('Location: ' . BASE_URL . '/planilla'); exit;
    }

    $calc = $this->planillaService->calcularQuincena([
        'salario_base' => $salario,
        'estado_civil' => $estadoCivil,
        'horas_semanales' => (float) $empresa['horas_semanales'],
        'semanas_mes' => (float) $empresa['semanas_mes'],
    ], ['ingresos' => $ingresos, 'otros_descuentos' => $otrosDesc]);

    $_SESSION['planilla_prueba'][] = [
        'nombre' => $nombre, 'cedula'  => $cedula,
        'cargo' => $cargo,  'estado_civil' => $estadoCivil,
        'salario' => $salario,'empresa_id' => $empresaId,
        'periodo' => $periodo,'mes' => $mes, 'anio' => $anio,
        'anio_inicio' => $anioInicio, 'ingresos' => $ingresos, 'calc' => $calc,
    ];

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
            $_SESSION['planilla_errores'] = ["Este colaborador ya tiene detalle en {$periodo} {$mes}/{$anio}."];
            header('Location: ' . BASE_URL . '/planilla'); exit;
        }

        $this->planillaModel->insertarDetalle($planillaId, $colaboradorId, $calc, $_SESSION['usuario_id'] ?? null);
        $pdo->commit();

        $_SESSION['planilla_exito'] = "Guardado — Planilla #{$planillaId} · Colaborador #{$colaboradorId}";

    } catch (\Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['planilla_errores'] = ['Error BD: ' . $e->getMessage()];
    }

    header('Location: ' . BASE_URL . '/planilla'); exit;
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

        header('Location: ' . BASE_URL . '/planilla');
        exit;
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
