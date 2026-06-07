<?php declare(strict_types=1);
function css_fmt(mixed $v): string {
    return 'B/.' . number_format((float) $v, 2, '.', ',');
}
$pageTitle = 'Reporte C - CSS';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Reporte C - Caja de Seguro Social</h2>

<form method="GET" action="<?= BASE_URL ?>/reporte/css" style="margin-bottom:12px">
    <label for="f_empresa">Empresa:</label>
    <select id="f_empresa" name="empresa_id">
        <option value="0">Todas</option>
        <?php foreach ($empresas as $emp): ?>
        <option value="<?= $emp['id'] ?>" <?= $empresaId === (int)$emp['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($emp['nombre']) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <label for="f_periodo">Quincena:</label>
    <select id="f_periodo" name="periodo">
        <option value="">Todas</option>
        <option value="1ra_quincena" <?= $periodo === '1ra_quincena' ? 'selected' : '' ?>>1ra quincena</option>
        <option value="2da_quincena" <?= $periodo === '2da_quincena' ? 'selected' : '' ?>>2da quincena</option>
    </select>

    <label for="f_mes">Mes:</label>
    <select id="f_mes" name="mes">
        <option value="0">Todos</option>
        <?php foreach (['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $i => $nm): ?>
        <option value="<?= $i+1 ?>" <?= $mes === $i+1 ? 'selected' : '' ?>><?= $nm ?></option>
        <?php endforeach; ?>
    </select>

    <label for="f_anio">Año:</label>

    <input type="number" id="f_anio" name="anio" min="2000" max="2100" value="<?= $anio ?>" style="width:70px">

     <button type="submit">Filtrar</button>
</form>

<?php if (!empty($planillas)): ?>
<label for="f_planilla">Planilla:</label>
<select id="f_planilla" name="planilla_id"
    onchange="this.form.submit()"
    form="form-planilla">
    <option value="0">-- seleccionar --</option>
    <?php foreach ($planillas as $p): ?>
    <option value="<?= $p['id'] ?>" <?= $planillaId === (int)$p['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($p['empresa_nombre']) ?> /
        <?= $p['periodo'] ?> / mes <?= $p['mes'] ?> / <?= $p['anio'] ?>
    </option>
    <?php endforeach; ?>
</select>
<form id="form-planilla" method="GET" action="<?= BASE_URL ?>/reporte/css">
    <input type="hidden" name="empresa_id" value="<?= $empresaId ?>">
    <input type="hidden" name="mes" value="<?= $mes ?>">
    <input type="hidden" name="anio" value="<?= $anio ?>">
    <input type="hidden" name="periodo" value="<?= htmlspecialchars($periodo) ?>">
</form>
<?php endif; ?>

<?php if ($planilla): ?>
<p style="margin-top:10px">
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
<h3 style="color:red;">Falta reacomodar y organizar bien el reporte.</h3>
<table style="margin-top:10px">
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
<?php elseif ($planillaId > 0): ?>
<p>Sin datos para esta planilla.</p>
<?php else: ?>
<p>Seleccione una planilla para ver el reporte.</p>
<?php endif; ?>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
