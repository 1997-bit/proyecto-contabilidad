<?php declare(strict_types=1);
function expediente_fmt(mixed $v): string {
    return 'B/.' . number_format((float) $v, 2, '.', ',');
}
$pageTitle = 'Reporte E: Expediente';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Reporte E: Expediente individual</h2>

<form method="GET" action="<?= BASE_URL ?>/reporte/expediente">
    <div class="form-row">
        <div class="form-group">
            <label for="f_empresa">Empresa</label>
            <select id="f_empresa" name="empresa_id" onchange="this.form.submit()">
                <?php foreach ($empresas as $emp): ?>
                <option value="<?= $emp['id'] ?>" <?= $empresaId === (int) $emp['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="f_colaborador">Colaborador</label>
            <select id="f_colaborador" name="id" onchange="this.form.submit()">
                <option value="">-- seleccionar --</option>
                <?php foreach ($colaboradores as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $colaboradorId === (int) $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre_completo']) ?> | <?= htmlspecialchars($c['cedula']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit"><?= Icons::svg('search') ?> Ver</button>
    </div>
</form>

<?php if (empty($empresas)): ?>
<div class="error"><p><?= Icons::svg('alert-triangle') ?> No hay empresas registradas. Crea una en Settings primero.</p></div>
<?php elseif (empty($colaboradores)): ?>
<div class="error"><p><?= Icons::svg('alert-triangle') ?> Esta empresa no tiene colaboradores registrados.</p></div>
<?php elseif ($colaborador): ?>
<p>
    <strong><?= htmlspecialchars($colaborador['nombre_completo']) ?></strong> |
    <?= htmlspecialchars($colaborador['cedula']) ?> |
    <?= htmlspecialchars($colaborador['cargo']) ?>
</p>
<?php elseif ($colaboradorId > 0): ?>
<p class="error"><?= Icons::svg('alert-triangle') ?> Colaborador no encontrado en esta empresa.</p>
<?php else: ?>
<p>Seleccione un colaborador.</p>
<?php endif; ?>

<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>Periodo</th>
            <th>Mes</th>
            <th>Año</th>
            <th class="num">Salario bruto</th>
            <th class="num">CSS</th>
            <th class="num">Seg. educativo</th>
            <th class="num">ISR</th>
            <th class="num">Otros desc.</th>
            <th class="num">Salario neto</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($detalles)): ?>
        <tr><td colspan="9">Sin datos.</td></tr>
        <?php else: ?>
        <?php foreach ($detalles as $d): ?>
        <tr>
            <td><?= $d['periodo'] === '1ra_quincena' ? '1ra quincena' : '2da quincena' ?></td>
            <td><?= $d['mes'] ?></td>
            <td><?= $d['anio'] ?></td>
            <td class="num"><?= expediente_fmt($d['salario_bruto']) ?></td>
            <td class="num"><?= expediente_fmt($d['desc_seguro_social']) ?></td>
            <td class="num"><?= expediente_fmt($d['desc_seguro_educativo']) ?></td>
            <td class="num"><?= expediente_fmt($d['desc_isr']) ?></td>
            <td class="num"><?= expediente_fmt($d['otros_descuentos']) ?></td>
            <td class="num"><?= expediente_fmt($d['salario_neto']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>
<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
