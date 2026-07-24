<?php declare(strict_types=1);
function planilla_fmt(float $v): string {
  return 'B/.' . number_format($v, 2, '.', ',');
}
$pageTitle = 'Nueva Planilla';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<style>
#ingresos-container .ing-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
#ingresos-container .ing-row select { width: 200px; }
#ingresos-container .ing-row input { width: 110px; }

.ing-header {
    display: flex;
    gap: 8px;
    margin-top: 10px;
    margin-bottom: 4px;
}
.ing-header span {
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: #1a2f5e;
}
.ing-header .col-tipo { width: 200px; }
.ing-header .col-valor { width: 110px; }

.info-tooltip {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #a8d5cc;
    color: #1a2f5e;
    font-size: 11px;
    font-weight: bold;
    cursor: help;
    margin-left: 6px;
    vertical-align: middle;
}
.info-tooltip .info-tooltip-content {
    display: none;
    position: absolute;
    left: 0;
    top: 22px;
    z-index: 20;
    background: #1a2f5e;
    color: #fff;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: normal;
    text-transform: none;
    letter-spacing: normal;
    width: 260px;
    line-height: 1.6;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
}
.info-tooltip:hover .info-tooltip-content,
.info-tooltip:focus .info-tooltip-content {
    display: block;
}
</style>

<h2>Planilla</h2>

<?php if (!empty($errores)): ?>
<div class="error">
    <?php foreach ($errores as $e): ?>
    <p><?= Icons::svg('alert-triangle') ?> <?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="GET" action="<?= BASE_URL ?>/planilla">
<fieldset>
        <legend>Periodo activo</legend>

        <div class="form-row">
            <div class="form-group">
                <label for="periodo">Quincena</label>
                <select id="periodo" name="periodo">
                    <option value="1ra_quincena" <?= $periodo === '1ra_quincena' ? 'selected' : '' ?>>1ra quincena</option>
                    <option value="2da_quincena" <?= $periodo === '2da_quincena' ? 'selected' : '' ?>>2da quincena</option>
                </select>
            </div>
            <div class="form-group">
                <label for="mes">Mes</label>
                <select id="mes" name="mes">
                    <?php foreach (['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $i => $nm): ?>
                    <option value="<?= $i+1 ?>" <?= $mes === $i+1 ? 'selected' : '' ?>><?= $nm ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="anio">Año</label>
                <input type="number" id="anio" name="anio" min="2000" max="2100" value="<?= $anio ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit"><?= Icons::svg('search') ?> Ver periodo</button>
        </div>
    </fieldset>
</form>
<form method="POST" action="<?= BASE_URL ?>/planilla/agregar">
<input type="hidden" name="periodo" value="<?= htmlspecialchars($periodo) ?>">
<input type="hidden" name="mes" value="<?= $mes ?>">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <?php if (!empty($colaboradores)): ?>
    <fieldset>
        <legend>Colaborador</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="colab_selector">Seleccionar existente</label>
                <select id="colab_selector">
                    <option value="">-- nuevo / manual --</option>
                    <?php foreach ($colaboradores as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        data-nombre="<?= htmlspecialchars($c['nombre_completo']) ?>"
                        data-cedula="<?= htmlspecialchars($c['cedula']) ?>"
                        data-cargo="<?= htmlspecialchars($c['cargo']) ?>"
                        data-estado="<?= htmlspecialchars($c['estado_civil']) ?>"
                        data-salario="<?= $c['salario_base'] ?>"
                        data-anio="<?= $c['anio_inicio'] ?>">
                        <?= htmlspecialchars($c['nombre_completo']) ?> | <?= htmlspecialchars($c['cedula']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <input type="hidden" id="colaborador_id" name="colaborador_id" value="">
    </fieldset>
    <?php endif; ?>

    <fieldset>
        <legend>Datos del colaborador</legend>

        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="cedula">Cédula</label>
                <input type="text" id="cedula" name="cedula" placeholder="8-888-8888" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="cargo">Cargo</label>
                <input type="text" id="cargo" name="cargo">
            </div>
            <div class="form-group">
                <label for="estado_civil">Estado civil</label>
                <select id="estado_civil" name="estado_civil">
                    <option value="soltero">Soltero/a</option>
                    <option value="casado">Casado/a</option>
                    <option value="unido">Unido/a</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="salario">Salario base (mensual B/.)</label>
                <input type="number" id="salario" name="salario" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="anio_inicio">Año de inicio</label>
                <input type="number" id="anio_inicio" name="anio_inicio" min="1900" max="2100" value="<?= date('Y') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="otros_descuentos">Otros descuentos (B/.)</label>
                <input type="number" id="otros_descuentos" name="otros_descuentos" step="0.01" min="0" value="0">
                <small>(mueblería, adelantos, ahorros)</small>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>
            Otros ingresos del periodo
            <span class="info-tooltip" tabindex="0">?
                <span class="info-tooltip-content">
                    <strong>Bonificación</strong>: gravable 100%<br>
                    <strong>Comisión</strong>: gravable 100%<br>
                    <strong>Dietas</strong>: exento hasta 25% del salario mensual<br>
                    <strong>Prima</strong>: exento hasta 50% del salario mensual<br>
                    <strong>Horas extra diurna</strong>: recargo 1.25x<br>
                    <strong>Horas extra nocturna</strong>: recargo 1.50x<br>
                    <strong>Horas extra dominical/feriado</strong>: recargo 1.75x
                </span>
            </span>
        </legend>

        <div class="ing-header">
            <span class="col-tipo">Tipo</span>
            <span class="col-valor">Monto / Horas</span>
        </div>
        <div id="ingresos-container">
            <div class="ing-row">
                <select name="ing_tipo[]" onchange="toggleHoras(this)">
                    <option value="">-- ninguno --</option>
                    <option value="bonificacion">bonificacion</option>
                    <option value="comision">comision</option>
                    <option value="dietas">dietas</option>
                    <option value="prima">prima</option>
                    <option value="horas_extra_diurna">horas extra diurna</option>
                    <option value="horas_extra_nocturna">horas extra nocturna</option>
                    <option value="horas_extra_dominical">horas extra dominical/feriado</option>
                </select>
                <input type="number" name="ing_monto[]" step="0.01" min="0" placeholder="Monto B/." value="0" class="campo-monto">
                <input type="number" name="ing_horas[]" step="0.01" min="0" placeholder="Horas" value="0" class="campo-horas" style="display:none">
                <button type="button" class="btn-secondary" onclick="eliminarFilaIng(this)"><?= Icons::svg('x') ?></button>
            </div>
        </div>
        <button type="button" class="btn-secondary" onclick="agregarFilaIng()"><?= Icons::svg('plus') ?> Ingreso</button>
    </fieldset>

    <div class="form-actions">
        <button type="submit"><?= Icons::svg('save') ?> Guardar</button>
    </div>
</form>


<?php if (!empty($filas)): ?>
<h3>
    Planilla: <?= $periodo === '1ra_quincena' ? '1ra Quincena' : '2da Quincena' ?>
    (<?= $mes ?>/<?= $anio ?>)
    &nbsp;<small style="color:#555">(<?= count($filas) ?> colaborador<?= count($filas) !== 1 ? 'es' : '' ?>)</small>
</h3>

<?php if (!empty($exito)): ?>
    <p class="ok"><?= Icons::svg('check-circle') ?> <?= htmlspecialchars($exito) ?></p>
<?php endif; ?>

<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th class="num">Sal. Bruto</th>
            <th class="num">Otros Ing.</th>
            <th class="num">Sal. Total</th>
            <th class="num">Seg. Social</th>
            <th class="num">Seg. Educ.</th>
            <th class="num">ISR</th>
            <th class="num">Otros Desc.</th>
            <th class="num">Total Desc.</th>
            <th class="num">Otros Ing. s/Desc.</th>
            <th class="num">Sal. Neto</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($filas as $i => $f): ?>
        <tr <?= $f['calc']['alerta_desc_excede'] ? 'style="background:#ffe0e0"' : '' ?>>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($f['cedula']) ?></td>
            <td><?= htmlspecialchars($f['nombre']) ?></td>
            <td><?= htmlspecialchars($f['cargo']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['salario_bruto']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['otros_ingresos']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['salario_base_quincena'] + $f['calc']['otros_ingresos']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['desc_seguro_social']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['desc_seguro_educativo']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['desc_isr']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['otros_descuentos']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['total_descuentos']) ?></td>
            <td class="num"><?= planilla_fmt($f['calc']['otros_ingresos_sin_descuento']) ?></td>
            <td class="num"><strong><?= planilla_fmt($f['calc']['salario_neto']) ?></strong>
                <?= $f['calc']['alerta_desc_excede'] ? ' *' : '' ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><strong>TOTALES</strong></td>
            <td class="num"><?= planilla_fmt($totales['salario_bruto']) ?></td>
            <td class="num"><?= planilla_fmt($totales['otros_ingresos']) ?></td>
            <td class="num"><?= planilla_fmt($totales['salario_base_quincena'] + $totales['otros_ingresos']) ?></td>
            <td class="num"><?= planilla_fmt($totales['desc_seguro_social']) ?></td>
            <td class="num"><?= planilla_fmt($totales['desc_seguro_educativo']) ?></td>
            <td class="num"><?= planilla_fmt($totales['desc_isr']) ?></td>
            <td class="num"><?= planilla_fmt($totales['otros_descuentos']) ?></td>
            <td class="num"><?= planilla_fmt($totales['total_descuentos']) ?></td>
            <td class="num"><?= planilla_fmt($totales['otros_ingresos_sin_descuento']) ?></td>
            <td class="num"><strong><?= planilla_fmt($totales['salario_neto']) ?></strong></td>
        </tr>
    </tfoot>
</table>
</div>

<?php elseif (!empty($exito)): ?>
    <p class="ok"><?= Icons::svg('check-circle') ?> <?= htmlspecialchars($exito) ?></p>
<?php endif; ?>
<script>
document.getElementById('colab_selector')?.addEventListener('change', function() {
  const opt = this.options[this.selectedIndex];
  const hiddenId = document.getElementById('colaborador_id');
  if (!opt.value) {
    hiddenId.value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('cedula').value = '';
    document.getElementById('cargo').value = '';
    document.getElementById('estado_civil').value = 'soltero';
    document.getElementById('salario').value = '';
    document.getElementById('anio_inicio').value = '<?= date('Y') ?>';
    return;
  }
  hiddenId.value = opt.value;
  document.getElementById('nombre').value = opt.dataset.nombre ?? '';
  document.getElementById('cedula').value = opt.dataset.cedula ?? '';
  document.getElementById('cargo').value = opt.dataset.cargo ?? '';
  document.getElementById('estado_civil').value = opt.dataset.estado ?? 'soltero';
  document.getElementById('salario').value = opt.dataset.salario ?? '';
  document.getElementById('anio_inicio').value = opt.dataset.anio ?? '';
});

function agregarFilaIng() {
  const c = document.getElementById('ingresos-container');
  const div = document.createElement('div');
  div.className = 'ing-row';
  div.innerHTML = `
  <select name="ing_tipo[]" onchange="toggleHoras(this)">
  <option value="">-- ninguno --</option>
  <option value="bonificacion">bonificacion</option>
  <option value="comision">comision</option>
  <option value="dietas">dietas</option>
  <option value="prima">prima</option>
  <option value="horas_extra_diurna">horas extra diurna</option>
  <option value="horas_extra_nocturna">horas extra nocturna</option>
  <option value="horas_extra_dominical">horas extra dominical/feriado</option>
  </select>
  <input type="number" name="ing_monto[]" step="0.01" min="0" placeholder="Monto B/." value="0" class="campo-monto">
  <input type="number" name="ing_horas[]" step="0.01" min="0" placeholder="Horas" value="0" class="campo-horas" style="display:none">
  <button type="button" class="btn-secondary" onclick="eliminarFilaIng(this)"><?= Icons::svg('x') ?></button>
`;
c.appendChild(div);
}

function eliminarFilaIng(btn) {
  const rows = document.querySelectorAll('.ing-row');
  if (rows.length > 1) btn.parentElement.remove();
}

function toggleHoras(sel) {
  const esHorasExtra = sel.value.startsWith('horas_extra_');
  const horas = sel.parentElement.querySelector('.campo-horas');
  const monto = sel.parentElement.querySelector('.campo-monto');
  horas.style.display = esHorasExtra ? 'inline' : 'none';
  monto.style.display = esHorasExtra ? 'none' : 'inline';
}
</script>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>

