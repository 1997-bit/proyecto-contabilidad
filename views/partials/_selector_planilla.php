<?php declare(strict_types=1);
/**
 * Selector de planilla especifica, dependiente de los filtros de _selector_periodo.php.
 *
 * Variables esperadas:
 *   $accionUrl   string  URL de destino del <form> (GET)
 *   $planillas   array   filas de PlanillaModel::listarPlanillas()
 *   $planillaId  int     0 = ninguna seleccionada
 *   $empresaId, $mes, $anio, $periodo  int|string  filtros activos (se reenvian como hidden)
 */
?>
<?php if (!empty($planillas)): ?>
<div class="form-row">
    <div class="form-group">
        <label for="f_planilla">Planilla</label>
        <select id="f_planilla" name="planilla_id"
            onchange="this.form.submit()"
            form="form-planilla">
            <option value="0">-- seleccionar --</option>
            <?php foreach ($planillas as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $planillaId === (int) $p['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['empresa_nombre']) ?> /
                <?= $p['periodo'] ?> / mes <?= $p['mes'] ?> / <?= $p['anio'] ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<form id="form-planilla" method="GET" action="<?= $accionUrl ?>">
    <input type="hidden" name="empresa_id" value="<?= $empresaId ?>">
    <input type="hidden" name="mes" value="<?= $mes ?>">
    <input type="hidden" name="anio" value="<?= $anio ?>">
    <input type="hidden" name="periodo" value="<?= htmlspecialchars($periodo) ?>">
</form>
<?php endif; ?>
