<?php
/**
 * ISSUE #4: Precarga COMPLETA de empleados + planilla por grupo
 * 
 * Instrucciones:
 * 1. Cuando sepas tu grupo (1, 2, 3 o 4), cambia GROUP_ID abajo
 * 2. Ejecuta: php database/grupos-seed.php
 * 3. Los 3 empleados + planilla de junio 2015 se cargarán con datos exactos del enunciado
 * 
 * Datos según enunciado del examen (Empresa PROSPERA, S.A.):
 *   Grupo 1: Rubén Palacios, Alejandro Mirones, Jairo Fernández (Horas Extras)
 *   Grupo 2: Cesar García, Amanda Iglesias, Vladimir Cáceres (Comisiones 2% ventas)
 *   Grupo 3: Rubén Blades, Alejandro Fernández, Vicente Fernández (Dietas)
 *   Grupo 4: José González, Julio Iglesias, Mariano Ramos (Prima producción)
 * 
 * Periodo: Junio 2015 - Segunda Quincena
 * Bonificación: 10% del salario para todos
 */ 

// ===== CAMBIAR AQUÍ =====
define('GROUP_ID', 3);  // Cambia a 1, 2, 3 o 4 cuando sepas tu grupo
// =======================

// Datos base de empleados
$empleados_por_grupo = [
    1 => [
        ['nombre' => 'Rubén Palacios', 'cedula' => '4-590-678', 'cargo' => 'Asistente de Gerencia', 'salario' => 900.00, 'estado_civil' => 'casado', 'anio_inicio' => 2015],
        ['nombre' => 'Alejandro Mirones', 'cedula' => '10-400-390', 'cargo' => 'Supervisora de Planta', 'salario' => 680.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015],
        ['nombre' => 'Jairo Fernández', 'cedula' => '5-789-352', 'cargo' => 'Aseador', 'salario' => 654.55, 'estado_civil' => 'soltero', 'anio_inicio' => 2015]
    ],
    2 => [
        ['nombre' => 'Cesar García', 'cedula' => '4-590-678', 'cargo' => 'Vendedor de Calle', 'salario' => 650.00, 'estado_civil' => 'casado', 'anio_inicio' => 2015],
        ['nombre' => 'Amanda Iglesias', 'cedula' => '10-400-390', 'cargo' => 'Vendedor de Agencia', 'salario' => 654.55, 'estado_civil' => 'soltero', 'anio_inicio' => 2015],
        ['nombre' => 'Vladimir Cáceres', 'cedula' => '5-789-352', 'cargo' => 'Vendedor Supervisor', 'salario' => 800.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015]
    ],
    3 => [
        ['nombre' => 'Rubén Blades', 'cedula' => '4-590-678', 'cargo' => 'Asistente de Gerencia', 'salario' => 1200.00, 'estado_civil' => 'casado', 'anio_inicio' => 2015],
        ['nombre' => 'Alejandro Fernández', 'cedula' => '10-400-390', 'cargo' => 'Supervisora de Planta', 'salario' => 1000.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015],
        ['nombre' => 'Vicente Fernández', 'cedula' => '5-789-352', 'cargo' => 'Analista de Recursos Humanos', 'salario' => 980.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015]
    ],
    4 => [
        ['nombre' => 'José González', 'cedula' => '4-590-678', 'cargo' => 'Reparador de Calle', 'salario' => 690.00, 'estado_civil' => 'casado', 'anio_inicio' => 2015],
        ['nombre' => 'Julio Iglesias', 'cedula' => '10-400-390', 'cargo' => 'Supervisor de Planta', 'salario' => 800.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015],
        ['nombre' => 'Mariano Ramos', 'cedula' => '5-789-352', 'cargo' => 'Analista Supervisor', 'salario' => 900.00, 'estado_civil' => 'soltero', 'anio_inicio' => 2015]
    ]
];

// Ingresos específicos por grupo - Junio 2015, Segunda Quincena
$ingresos_por_grupo = [
    1 => [ // HORAS EXTRAS
        ['cedula' => '4-590-678', 'tipo' => 'horas_extra', 'horas' => 7.0, 'monto' => 32.31], // Rubén: 2+2+3 horas
        ['cedula' => '10-400-390', 'tipo' => 'horas_extra', 'horas' => 7.0, 'monto' => 24.41], // Alejandro: 3+4 horas
        ['cedula' => '5-789-352', 'tipo' => 'horas_extra', 'horas' => 5.0, 'monto' => 16.78], // Jairo: 3+2 horas
    ],
    2 => [ // COMISIONES (2% de ventas)
        ['cedula' => '4-590-678', 'tipo' => 'comision', 'monto' => 700.00], // Cesar: 2% de $35,000
        ['cedula' => '10-400-390', 'tipo' => 'comision', 'monto' => 1200.00], // Amanda: 2% de $60,000
        ['cedula' => '5-789-352', 'tipo' => 'comision', 'monto' => 1100.00], // Vladimir: 2% de $55,000
    ],
    3 => [ // DIETAS
        ['cedula' => '4-590-678', 'tipo' => 'dietas', 'monto' => 3000.00], // Rubén: Congreso
        ['cedula' => '10-400-390', 'tipo' => 'dietas', 'monto' => 5000.00], // Alejandro: Capacitación
        ['cedula' => '5-789-352', 'tipo' => 'dietas', 'monto' => 700.00], // Vicente: Reuniones
    ],
    4 => [ // PRIMA DE PRODUCCIÓN
        ['cedula' => '4-590-678', 'tipo' => 'prima', 'monto' => 600.00], // José: Prima fija
        ['cedula' => '10-400-390', 'tipo' => 'prima', 'monto' => 3000.00], // Julio: 2% de $150,000
        ['cedula' => '5-789-352', 'tipo' => 'prima', 'monto' => 1100.00], // Mariano: 2% de $55,000
    ]
];

// Descuentos por grupo
$descuentos_por_grupo = [
    1 => [
        ['cedula' => '4-590-678', 'monto' => 100.00], // Rubén: Mueblería $200/2
        ['cedula' => '10-400-390', 'monto' => 120.00], // Alejandro: Adelanto $60 + Mueblería $60
        ['cedula' => '5-789-352', 'monto' => 25.00], // Jairo: Ahorro $50/2
    ],
    2 => [
        ['cedula' => '4-590-678', 'monto' => 125.00], // Cesar: Mueblería $250/2
        ['cedula' => '10-400-390', 'monto' => 135.00], // Amanda: Adelanto $50 + Mueblería $110
        ['cedula' => '5-789-352', 'monto' => 25.00], // Vladimir: Ahorro $50/2
    ],
    3 => [
        ['cedula' => '4-590-678', 'monto' => 150.00], // Rubén: Mueblería $300/2
        ['cedula' => '10-400-390', 'monto' => 250.00], // Alejandro: Mueblería $500/2
        ['cedula' => '5-789-352', 'monto' => 125.00], // Vicente: Ahorro $250/2
    ],
    4 => [
        ['cedula' => '4-590-678', 'monto' => 125.00], // José: Mueblería $250/2
        ['cedula' => '10-400-390', 'monto' => 310.00], // Julio: Adelanto $250 + Mueblería $160
        ['cedula' => '5-789-352', 'monto' => 100.00], // Mariano: Ahorro $200/2
    ]
];

if (!isset($empleados_por_grupo[GROUP_ID])) {
    die("❌ Error: GROUP_ID debe ser 1, 2, 3 o 4. Actual: " . GROUP_ID . "\n");
}

$baseDir = __DIR__ . '/..';
require_once $baseDir . '/config/Config.php';
Config::cargarEnv($baseDir . '/.env');
require_once $baseDir . '/vendor/autoload.php';
require_once $baseDir . '/core/App.php';
require_once $baseDir . '/helpers/SessionHelper.php';
require_once $baseDir . '/services/CifradoService.php';
require_once $baseDir . '/config/Conexion.php';
require_once $baseDir . '/models/PlanillaModel.php';

try {
    $db = Conexion::conectar();
    $cifrado = new CifradoService();
    $modelo = new PlanillaModel($db);
    
    $empresas = $modelo->listarEmpresas();
    if (empty($empresas)) {
        die("❌ Error: No hay empresas en la BD.\n");
    }
    $empresa_id = $empresas[0]['id'];
    $empresa = $empresas[0];
    
    echo "\n╔════════════════════════════════════════════════════════════════╗\n";
    echo "║ CARGANDO EMPLEADOS + PLANILLA - GRUPO " . GROUP_ID . "                        ║\n";
    echo "║ Junio 2015 - Segunda Quincena (Empresa PROSPERA)             ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    // PASO 1: Cargar empleados base
    echo "📥 Cargando empleados...\n";
    $colaboradores = [];
    foreach ($empleados_por_grupo[GROUP_ID] as $emp) {
        $cedulaHash = CifradoService::hash($emp['cedula']);
        $stmt = $db->prepare("SELECT id FROM colaboradores WHERE cedula_hash = ?");
        $stmt->execute([$cedulaHash]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            $colaboradores[$emp['cedula']] = $existe['id'];
            echo "⚠️  Existe: {$emp['nombre']}\n";
            continue;
        }
        
        $nombreCifrado = $cifrado->cifrar($emp['nombre']);
        $cedulaCifrada = $cifrado->cifrar($emp['cedula']);
        $nombreHash = CifradoService::hash($emp['nombre']);
        
        $stmt = $db->prepare(
            "INSERT INTO colaboradores 
            (empresa_id, nombre_completo, nombre_hash, cedula, cedula_hash, 
             estado_civil, cargo, salario_base, anio_inicio, activo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"
        );
        
        $stmt->execute([
            $empresa_id, $nombreCifrado, $nombreHash, $cedulaCifrada, $cedulaHash,
            $emp['estado_civil'], $emp['cargo'], $emp['salario'], $emp['anio_inicio']
        ]);
        
        $colaboradores[$emp['cedula']] = $db->lastInsertId();
        echo "✅ Cargado: {$emp['nombre']} ({$emp['cedula']})\n";
    }
    
    // PASO 2: Crear/obtener planilla para junio 2015, segunda quincena
    echo "\n📋 Creando planilla (junio 2015, segunda quincena)...\n";
    $stmt = $db->prepare(
        "INSERT IGNORE INTO planillas (empresa_id, periodo, mes, anio, estado, created_by)
         VALUES (?, '2da_quincena', 6, 2015, 'borrador', NULL)"
    );
    $stmt->execute([$empresa_id]);
    
    $stmt = $db->prepare(
        "SELECT id FROM planillas 
         WHERE empresa_id = ? AND periodo = '2da_quincena' AND mes = 6 AND anio = 2015"
    );
    $stmt->execute([$empresa_id]);
    $planilla = $stmt->fetch();
    $planilla_id = $planilla['id'];
    echo "✅ Planilla ID: $planilla_id\n";
    
    // PASO 3: Obtener datos completos de la empresa
    echo "\n💰 Cargando detalles de planilla e ingresos...\n";
    $stmt = $db->prepare("SELECT horas_semanales, semanas_mes FROM empresas WHERE id = ?");
    $stmt->execute([$empresa_id]);
    $emp_datos = $stmt->fetch();
    $horas_semanales = (float) $emp_datos['horas_semanales']; // 45
    $semanas_mes = (float) $emp_datos['semanas_mes']; // 4.3333
    $valor_hora_base = $horas_semanales * $semanas_mes; // 195.00
    
    foreach ($empleados_por_grupo[GROUP_ID] as $emp) {
        $colab_id = $colaboradores[$emp['cedula']];
        $salario_base = $emp['salario'];
        $salario_quincena = $salario_base / 2;
        $valor_hora = $salario_base / $valor_hora_base;
        
        // Verificar si ya existe detalle_planilla
        $stmt = $db->prepare(
            "SELECT id FROM detalle_planilla 
             WHERE id_planilla = ? AND id_colaborador = ?"
        );
        $stmt->execute([$planilla_id, $colab_id]);
        $existe_detalle = $stmt->fetch();
        
        if ($existe_detalle) {
            echo "⚠️  Ya existe detalle para: {$emp['nombre']}\n";
            continue;
        }
        
        // Crear detalle_planilla
        $stmt = $db->prepare(
            "INSERT INTO detalle_planilla 
            (id_planilla, id_colaborador, salario_base_quincena, valor_hora, otros_ingresos, 
             otros_ingresos_sin_descuento, salario_bruto, desc_seguro_social, desc_seguro_educativo, 
             desc_isr, otros_descuentos, total_descuentos, salario_neto, pct_descuentos)
            VALUES (?, ?, ?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)"
        );
        $stmt->execute([$planilla_id, $colab_id, $salario_quincena, $valor_hora]);
        $detalle_id = $db->lastInsertId();
        
        // Agregar BONIFICACIÓN (10% para todos)
        $bonificacion = $salario_base * 0.10;
        $stmt = $db->prepare(
            "INSERT INTO detalle_ingresos (id_detalle, tipo, monto, gravable, sin_descuento)
             VALUES (?, 'bonificacion', ?, ?, 0)"
        );
        $stmt->execute([$detalle_id, $bonificacion, $bonificacion]);
        
        // Agregar ingresos específicos del grupo
        $ingresos_emp = array_filter($ingresos_por_grupo[GROUP_ID], fn($i) => $i['cedula'] === $emp['cedula']);
        foreach ($ingresos_emp as $ingreso) {
            $monto = $ingreso['monto'];
            $horas = $ingreso['horas'] ?? null;
            
            $stmt = $db->prepare(
                "INSERT INTO detalle_ingresos (id_detalle, tipo, monto, gravable, sin_descuento, horas)
                 VALUES (?, ?, ?, ?, 0, ?)"
            );
            $stmt->execute([$detalle_id, $ingreso['tipo'], $monto, $monto, $horas]);
        }
        
        // Agregar otros_descuentos
        $desc_emp = array_filter($descuentos_por_grupo[GROUP_ID], fn($d) => $d['cedula'] === $emp['cedula']);
        $total_descuentos = 0;
        foreach ($desc_emp as $desc) {
            $total_descuentos += $desc['monto'];
        }
        
        // Actualizar totales en detalle_planilla
        $otros_ingresos = $bonificacion + array_sum(array_column($ingresos_emp, 'monto'));
        $salario_bruto = $salario_quincena + $otros_ingresos;
        $desc_css = $salario_bruto * 0.0975;
        $desc_edu = $salario_bruto * 0.0125;
        $desc_total = $desc_css + $desc_edu + $total_descuentos;
        $salario_neto = $salario_bruto - $desc_total;
        $pct_desc = ($salario_bruto > 0) ? ($desc_total / $salario_bruto) * 100 : 0;
        
        $stmt = $db->prepare(
            "UPDATE detalle_planilla SET 
            otros_ingresos = ?, salario_bruto = ?, desc_seguro_social = ?, 
            desc_seguro_educativo = ?, otros_descuentos = ?, total_descuentos = ?, 
            salario_neto = ?, pct_descuentos = ?
            WHERE id = ?"
        );
        $stmt->execute([
            $otros_ingresos, $salario_bruto, $desc_css, $desc_edu, $total_descuentos, 
            $desc_total, $salario_neto, $pct_desc, $detalle_id
        ]);
        
        echo "✅ {$emp['nombre']}: Salario Neto B/. " . number_format($salario_neto, 2) . "\n";
    }
    
    echo "\n╔════════════════════════════════════════════════════════════════╗\n";
    echo "║ ✅ COMPLETADO: Grupo " . GROUP_ID . " cargado con datos exactos del enunciado ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
} catch (Exception $e) {
    die("❌ Error: {$e->getMessage()}\nTrace: {$e->getTraceAsString()}\n");
}
