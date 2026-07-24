<?php declare(strict_types=1);
function personal_fmt(mixed $v): string {
    return 'B/.' . number_format((float) $v, 2, '.', ',');
}
$pageTitle = 'Reporte P: Personal';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Reporte P: Planilla grupal</h2>

<?php
$accionUrl = BASE_URL . '/reporte/personal';
require BASE_PATH . '/views/partials/_selector_periodo.php';
require BASE_PATH . '/views/partials/_selector_planilla.php';
?>

<?php if ($planilla): ?>
<p>
    <strong><?= htmlspecialchars($planilla['empresa_nombre']) ?></strong> /
    <?= $planilla['periodo'] ?> / mes <?= $planilla['mes'] ?> / <?= $planilla['anio'] ?>
</p>
<?php endif; ?>

<?php if (!empty($filas)): ?>
<?php
$totBruto = 0; $totDesc = 0; $totNeto = 0;
foreach ($filas as $f) {
    $totBruto += $f['salario_bruto'];
    $totDesc  += $f['total_descuentos'];
    $totNeto  += $f['salario_neto'];
}
?>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cedula</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th class="num">Salario base</th>
            <th class="num">Otros ingresos</th>
            <th class="num">Salario bruto</th>
            <th class="num">Total descuentos</th>
            <th class="num">Salario neto</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($filas as $i => $f): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($f['cedula']) ?></td>
            <td><?= htmlspecialchars($f['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($f['cargo']) ?></td>
            <td class="num"><?= personal_fmt($f['salario_base_quincena']) ?></td>
            <td class="num"><?= personal_fmt($f['otros_ingresos']) ?></td>
            <td class="num"><?= personal_fmt($f['salario_bruto']) ?></td>
            <td class="num"><?= personal_fmt($f['total_descuentos']) ?></td>
            <td class="num"><?= personal_fmt($f['salario_neto']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6"><strong>TOTALES</strong></td>
            <td class="num"><?= personal_fmt($totBruto) ?></td>
            <td class="num"><?= personal_fmt($totDesc) ?></td>
            <td class="num"><?= personal_fmt($totNeto) ?></td>
        </tr>
    </tfoot>
</table>
</div>
<?php elseif ($planillaId > 0): ?>
<p>Sin datos para esta planilla.</p>
<?php else: ?>
<p>Seleccione una planilla para ver el reporte.</p>
<?php endif; ?>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
