<?php declare(strict_types=1);
function css_fmt(mixed $v): string {
    return 'B/.' . number_format((float) $v, 2, '.', ',');
}
$pageTitle = 'Reporte C: CSS';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Reporte C: Caja de Seguro Social</h2>

<?php
$accionUrl = BASE_URL . '/reporte/css';
require BASE_PATH . '/views/partials/_selector_periodo.php';
?>

<?php
$accionUrl = BASE_URL . '/reporte/css';
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
$totBruto = 0; $totEmpCss = 0; $totEmpEdu = 0; $totPatCss = 0; $totPatEdu = 0;
foreach ($filas as $f) {
    $totBruto  += $f['salario_bruto'];
    $totEmpCss += $f['desc_seguro_social'];
    $totEmpEdu += $f['desc_seguro_educativo'];
    $totPatCss += $f['aporte_patronal_css'];
    $totPatEdu += $f['aporte_patronal_edu'];
}
?>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cedula</th>
            <th>Nombre</th>
            <th>Salario bruto quincena</th>
            <th>CSS empleado (9.75%)</th>
            <th>CSS patronal (13.25%)</th>
            <th>Seg. edu. empleado (1.25%)</th>
            <th>Seg. edu. patronal (1.5%)</th>
            <th>Total aporte</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($filas as $i => $f): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($f['cedula']) ?></td>
            <td><?= htmlspecialchars($f['nombre_completo']) ?></td>
            <td class="num"><?= css_fmt($f['salario_bruto']) ?></td>
            <td class="num"><?= css_fmt($f['desc_seguro_social']) ?></td>
            <td class="num"><?= css_fmt($f['aporte_patronal_css']) ?></td>
            <td class="num"><?= css_fmt($f['desc_seguro_educativo']) ?></td>
            <td class="num"><?= css_fmt($f['aporte_patronal_edu']) ?></td>
            <td class="num"><?= css_fmt($f['desc_seguro_social'] + $f['desc_seguro_educativo'] + $f['aporte_patronal_css'] + $f['aporte_patronal_edu']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTALES</strong></td>
            <td class="num"><?= css_fmt($totBruto) ?></td>
            <td class="num"><?= css_fmt($totEmpCss) ?></td>
            <td class="num"><?= css_fmt($totPatCss) ?></td>
            <td class="num"><?= css_fmt($totEmpEdu) ?></td>
            <td class="num"><?= css_fmt($totPatEdu) ?></td>
            <td class="num"><?= css_fmt($totEmpCss + $totEmpEdu + $totPatCss + $totPatEdu) ?></td>
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
