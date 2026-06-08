<?php declare(strict_types=1);
/*
 * Seeder - Grupo examen (1-4)
 * Uso: php database/grupos-seed.php [grupo]
 */

// CAMBIAR AQUI
define('GROUP_ID', (int) ($_SERVER['argv'][1] ?? getenv('GROUP_ID') ?: 3));

$baseDir = dirname(__DIR__);
require_once $baseDir . '/config/Config.php';
Config::cargarEnv($baseDir . '/.env');

spl_autoload_register(function (string $c) use ($baseDir): void {
    foreach (['controllers', 'models', 'services', 'helpers', 'core'] as $dir) {
        $f = "$baseDir/$dir/$c.php";
        if (file_exists($f)) { require_once $f; return; }
    }
});

require_once $baseDir . '/config/Conexion.php';

const PERIODO = '2da_quincena';
const MES = 6;
const ANIO = 2015;
const BONIF = 0.10;

$grupos = [
    1 => [
        'empleados' => [
            ['nombre' => 'Ruben Palacios',    'cedula' => '4-590-678',  'cargo' => 'Asistente de Gerencia', 'salario' => 900.00,  'estado_civil' => 'casado',  'anio_inicio' => 2015],
            ['nombre' => 'Alejandro Mirones', 'cedula' => '10-400-390', 'cargo' => 'Supervisora de Planta', 'salario' => 680.00,  'estado_civil' => 'soltero', 'anio_inicio' => 2015],
            ['nombre' => 'Jairo Fernandez',   'cedula' => '5-789-352',  'cargo' => 'Aseador',               'salario' => 654.55,  'estado_civil' => 'soltero', 'anio_inicio' => 2015],
        ],
        'ingresos' => [
            '4-590-678'  => [['tipo' => 'horas_extra', 'monto' => 32.31, 'horas' => 7.0]],
            '10-400-390' => [['tipo' => 'horas_extra', 'monto' => 24.41, 'horas' => 7.0]],
            '5-789-352'  => [['tipo' => 'horas_extra', 'monto' => 16.78, 'horas' => 5.0]],
        ],
        'descuentos' => ['4-590-678' => 100.00, '10-400-390' => 120.00, '5-789-352' => 25.00],
    ],
    2 => [
        'empleados' => [
            ['nombre' => 'Cesar Garcia',     'cedula' => '4-590-678',  'cargo' => 'Vendedor de Calle',   'salario' => 650.00,  'estado_civil' => 'casado',  'anio_inicio' => 2015],
            ['nombre' => 'Amanda Iglesias',  'cedula' => '10-400-390', 'cargo' => 'Vendedor de Agencia', 'salario' => 654.55,  'estado_civil' => 'soltero', 'anio_inicio' => 2015],
            ['nombre' => 'Vladimir Caceres', 'cedula' => '5-789-352',  'cargo' => 'Vendedor Supervisor', 'salario' => 800.00,  'estado_civil' => 'soltero', 'anio_inicio' => 2015],
        ],
        'ingresos' => [
            '4-590-678'  => [['tipo' => 'comision', 'monto' => 700.00]],
            '10-400-390' => [['tipo' => 'comision', 'monto' => 1200.00]],
            '5-789-352'  => [['tipo' => 'comision', 'monto' => 1100.00]],
        ],
        'descuentos' => ['4-590-678' => 125.00, '10-400-390' => 135.00, '5-789-352' => 25.00],
    ],
    3 => [
        'empleados' => [
            ['nombre' => 'Ruben Blades',       'cedula' => '4-590-678',  'cargo' => 'Asistente de Gerencia',        'salario' => 1200.00, 'estado_civil' => 'casado',  'anio_inicio' => 2015],
            ['nombre' => 'Alejandro Fernandez', 'cedula' => '10-400-390', 'cargo' => 'Supervisora de Planta',        'salario' => 1000.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015],
            ['nombre' => 'Vicente Fernandez',   'cedula' => '5-789-352',  'cargo' => 'Analista de Recursos Humanos', 'salario' =>  980.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015],
        ],
        'ingresos' => [
            '4-590-678'  => [['tipo' => 'dietas', 'monto' => 3000.00]],
            '10-400-390' => [['tipo' => 'dietas', 'monto' => 5000.00]],
            '5-789-352'  => [['tipo' => 'dietas', 'monto' =>  700.00]],
        ],
        'descuentos' => ['4-590-678' => 150.00, '10-400-390' => 250.00, '5-789-352' => 125.00],
    ],
    4 => [
        'empleados' => [
            ['nombre' => 'Jose Gonzalez', 'cedula' => '4-590-678',  'cargo' => 'Reparador de Calle',   'salario' => 690.00, 'estado_civil' => 'casado',  'anio_inicio' => 2015],
            ['nombre' => 'Julio Iglesias', 'cedula' => '10-400-390', 'cargo' => 'Supervisor de Planta', 'salario' => 800.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015],
            ['nombre' => 'Mariano Ramos',  'cedula' => '5-789-352',  'cargo' => 'Analista Supervisor',  'salario' => 900.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015],
        ],
        'ingresos' => [
            '4-590-678'  => [['tipo' => 'prima', 'monto' =>  600.00]],
            '10-400-390' => [['tipo' => 'prima', 'monto' => 3000.00]],
            '5-789-352'  => [['tipo' => 'prima', 'monto' => 1100.00]],
        ],
        'descuentos' => ['4-590-678' => 125.00, '10-400-390' => 310.00, '5-789-352' => 100.00],
    ],
];

if (!isset($grupos[GROUP_ID])) {
    die("Error: GROUP_ID debe ser 1-4. Actual: " . GROUP_ID . "\n");
}

$db = Conexion::conectar();
$cifrado = new CifradoService();
$colModel = new ColaboradorModel($db);
$grupo = $grupos[GROUP_ID];

$empresa = $db->query("SELECT * FROM empresas WHERE activo = 1 LIMIT 1")->fetch();
if (!$empresa) die("Error: No hay empresa activa en la BD.\n");

$empresa_id = (int) $empresa['id'];
$valor_hora_divisor = (float) $empresa['horas_semanales'] * (float) $empresa['semanas_mes'];

echo "Grupo " . GROUP_ID . " | " . $empresa['nombre'] . " | " . PERIODO . " " . MES . "/" . ANIO . "\n";

// 1. Colaboradores

$ids = [];

foreach ($grupo['empleados'] as $emp) {
    $cedulaHash = CifradoService::hash($emp['cedula']);
    $stmt = $db->prepare("SELECT id FROM colaboradores WHERE cedula_hash = ? LIMIT 1");
    $stmt->execute([$cedulaHash]);
    $existente = $stmt->fetchColumn();

    if ($existente) {
        $ids[$emp['cedula']] = (int) $existente;
        echo "  existe: {$emp['nombre']}\n";
        continue;
    }

    $colModel->insertar([
        ':empresa_id' => $empresa_id,
        ':nombre_completo' => $cifrado->cifrar($emp['nombre']),
        ':nombre_hash' => CifradoService::hash($emp['nombre']),
        ':cedula' => $cifrado->cifrar($emp['cedula']),
        ':cedula_hash' => $cedulaHash,
        ':estado_civil' => $emp['estado_civil'],
        ':cargo' => $emp['cargo'],
        ':salario_base' => $emp['salario'],
        ':anio_inicio' => $emp['anio_inicio'],
    ]);

    $ids[$emp['cedula']] = (int) $db->lastInsertId();
    echo "  insertado: {$emp['nombre']}\n";
}

// 2. Planilla

$db->prepare(
    "INSERT IGNORE INTO planillas (empresa_id, periodo, mes, anio, estado) VALUES (?, ?, ?, ?, 'borrador')"
)->execute([$empresa_id, PERIODO, MES, ANIO]);

$stmt = $db->prepare("SELECT id FROM planillas WHERE empresa_id = ? AND periodo = ? AND mes = ? AND anio = ? LIMIT 1");
$stmt->execute([$empresa_id, PERIODO, MES, ANIO]);
$planilla_id = (int) $stmt->fetchColumn();

echo "  planilla id: $planilla_id\n";

// 3. Detalle e ingresos

$insDetalle = $db->prepare("
    INSERT IGNORE INTO detalle_planilla
        (id_planilla, id_colaborador, salario_base_quincena, valor_hora,
         otros_ingresos, otros_ingresos_sin_descuento, salario_bruto,
         desc_seguro_social, desc_seguro_educativo, desc_isr,
         otros_descuentos, total_descuentos, salario_neto, pct_descuentos)
    VALUES (?,?,?,?, 0,0,0, 0,0,0, 0,0,0,0)
");

$insIngreso = $db->prepare("
    INSERT INTO detalle_ingresos (id_detalle, tipo, monto, gravable, sin_descuento, horas)
    VALUES (?,?,?,?,0,?)
");

$updDetalle = $db->prepare("
    UPDATE detalle_planilla SET
        otros_ingresos = ?,
        salario_bruto = ?,
        desc_seguro_social = ?,
        desc_seguro_educativo = ?,
        otros_descuentos = ?,
        total_descuentos = ?,
        salario_neto = ?,
        pct_descuentos = ?
    WHERE id = ?
");

foreach ($grupo['empleados'] as $emp) {
    $cedula = $emp['cedula'];
    $colab_id = $ids[$cedula];
    $salario = $emp['salario'];
    $quincena = $salario / 2;
    $valor_hora = $salario / $valor_hora_divisor;

    $insDetalle->execute([$planilla_id, $colab_id, $quincena, $valor_hora]);
    $detalle_id = (int) $db->lastInsertId();

    if ($detalle_id === 0) {
        echo "  detalle ya existe: {$emp['nombre']}\n";
        continue;
    }

    $bonif = $salario * BONIF;
    $insIngreso->execute([$detalle_id, 'bonificacion', $bonif, $bonif, null]);

    $otros = $bonif;
    foreach ($grupo['ingresos'][$cedula] ?? [] as $ing) {
        $insIngreso->execute([$detalle_id, $ing['tipo'], $ing['monto'], $ing['monto'], $ing['horas'] ?? null]);
        $otros += $ing['monto'];
    }

    $bruto = $quincena + $otros;
    $css = $bruto * Config::CSS_EMPLEADO;
    $edu = $bruto * Config::SEG_EDUCATIVO;
    $oDesc = $grupo['descuentos'][$cedula] ?? 0.0;
    $totalDesc = $css + $edu + $oDesc;
    $neto = $bruto - $totalDesc;
    $pct = $bruto > 0 ? ($totalDesc / $bruto) * 100 : 0;

    $updDetalle->execute([$otros, $bruto, $css, $edu, $oDesc, $totalDesc, $neto, $pct, $detalle_id]);

    echo "  {$emp['nombre']}: neto B/. " . number_format($neto, 2) . "\n";
}

