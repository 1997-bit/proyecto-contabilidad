<?php declare(strict_types=1);

class PlanillaController
{
    private PlanillaService $planillaService;

    public function __construct()
    {
        $this->planillaService = new PlanillaService(new ISRService());
    }

    /**
     * GET  /planilla -> muestra formulario + tabla (sesión)
     * POST /planilla/agregar -> agrega fila y redirige
     * POST /planilla/eliminar -> elimina fila por índice y redirige
     * POST /planilla/limpiar -> vacía la sesión y redirige
     */
    public function index(): void
    {
        SessionHelper::iniciar();
        $filas   = $_SESSION['planilla_prueba'] ?? [];
        $totales = $this->calcularTotales($filas);
        $errores = $_SESSION['planilla_errores'] ?? [];
        unset($_SESSION['planilla_errores']);

        require BASE_PATH . '/views/planilla/index.php';
    }

    public function agregar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/planilla');
            exit;
        }

        SessionHelper::iniciar();

        $nombre = trim($_POST['nombre'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $salario = (float) ($_POST['salario'] ?? 0);
        $estadoCivil = $_POST['estado_civil'] ?? 'soltero';
        $otrosDesc = (float) ($_POST['otros_descuentos'] ?? 0);

        // Construir array de ingresos variables
        $ingresos = [];
        foreach ($_POST['ing_tipo'] ?? [] as $i => $tipo) {
            if (empty($tipo)) continue;
            $entry = [
                'tipo'  => $tipo,
                'monto' => (float) ($_POST['ing_monto'][$i] ?? 0),
            ];
            if ($tipo === 'horas_extra') {
                $entry['horas'] = (float) ($_POST['ing_horas'][$i] ?? 0);
            }
            $ingresos[] = $entry;
        }

        $errores = [];
        if ($nombre === '') $errores[] = 'El nombre es requerido.';
        if ($salario <= 0) $errores[] = 'El salario base debe ser mayor a 0.';
        if (!in_array($estadoCivil, ['soltero', 'casado', 'unido'], true))
            $errores[] = 'Estado civil inválido.';

        if (!empty($errores)) {
            $_SESSION['planilla_errores'] = $errores;
            header('Location: ' . BASE_URL . '/planilla');
            exit;
        }

        $colaborador = [
            'salario_base' => $salario,
            'estado_civil' => $estadoCivil,
            'horas_semanales' => 48,
            'semanas_mes' => 4.3333,
        ];
        $extras = [
            'ingresos' => $ingresos,
            'otros_descuentos' => $otrosDesc,
        ];

        $calc = $this->planillaService->calcularQuincena($colaborador, $extras);

        $_SESSION['planilla_prueba'][] = [
            'nombre' => $nombre,
            'cargo' => $cargo,
            'estado_civil'=> $estadoCivil,
            'salario' => $salario,
            'calc' => $calc,
        ];

        header('Location: ' . BASE_URL . '/planilla');
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
}
