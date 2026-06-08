<?php declare(strict_types=1);
function planilla_fmt(float $v): string {
  return 'B/.' . number_format($v, 2, '.', ',');
}
$pageTitle = 'Nueva Planilla';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<style>
fieldset { margin-bottom: 14px; padding: 10px 14px; }
legend { font-weight: bold; }
label { display: inline-block; width: 170px; }
input[type="text"], input[type="number"], select { width: 190px; padding: 2px 4px; margin-bottom: 4px; }
button { margin: 4px 4px 4px 0; padding: 4px 10px; cursor: pointer; }
#ingresos-container .ing-row { margin-bottom: 4px; }
#ingresos-container .ing-row select { width: 130px; }
#ingresos-container .ing-row input { width: 90px; }
#colab_selector { width: 340px; }
</style>

    <input type="hidden" name="anio" value="<?= $anio ?>">
<form method="GET" action="<?= BASE_URL ?>/planilla" style="margin-bottom:12px">
    
<fieldset>
        <legend>Periodo activo</legend>
        <p style="margin:0 0 6px">
            Empresa: <strong><?= htmlspecialchars($_SESSION['ctx']['empresa']) ?></strong>
            &nbsp;<a href="<?= BASE_URL ?>/settings" style="font-size:11px">(cambiar)</a>
        </p>

        <label for="periodo">Quincena:</label>
        <select id="periodo" name="periodo">
            <option value="1ra_quincena" <?= $periodo === '1ra_quincena' ? 'selected' : '' ?>>1ra quincena</option>
            <option value="2da_quincena" <?= $periodo === '2da_quincena' ? 'selected' : '' ?>>2da quincena</option>
        </select><br>

        <label for="mes">Mes:</label>
        <select id="mes" name="mes">
            <?php foreach (['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $i => $nm): ?>
            <option value="<?= $i+1 ?>" <?= $mes === $i+1 ? 'selected' : '' ?>><?= $nm ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="anio">Ano:</label>
        <input type="number" id="anio" name="anio" min="2000" max="2100" value="<?= $anio ?>" required><br>

        <button type="submit">Ver periodo</button>
    </fieldset>
</form>
<form method="POST" action="<?= BASE_URL ?>/planilla/agregar">
<input type="hidden" name="periodo" value="<?= htmlspecialchars($periodo) ?>">
<input type="hidden" name="mes" value="<?= $mes ?>">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <?php if (!empty($colaboradores)): ?>
    <fieldset>
        <legend>Colaborador</legend>
        <label for="colab_selector">Seleccionar existente:</label>
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
        <input type="hidden" id="colaborador_id" name="colaborador_id" value="">
    </fieldset>
    <?php endif; ?>

    <fieldset>
        <legend>Datos del colaborador</legend>

		<!-- FORMULARIO AGREGAR -->
		<form method="POST" action="<?= BASE_URL ?>/planilla/agregar">
			<!-- CREADO - Issue #5: Búsqueda de empleado para cargar datos automáticamente -->
			<fieldset>
				<legend>🔍 Buscar Empleado</legend>

				<label for="cedula_buscar">Cédula:</label>
				<input
					type="text"
					id="cedula_buscar"
					placeholder="8-888-8888"
					autocomplete="off"
				/>
				<button type="button" id="btn_buscar">Buscar</button>
				<span id="estatus_busqueda"></span><br /><br />

				<div id="empleado-info" style="display: none; padding: 10px; background: #e8f5e9; border: 1px solid #4caf50; border-radius: 4px; margin-top: 10px;">
					<strong>✓ Encontrado:</strong><br>
					Nombre: <span id="info-nombre"></span><br>
					Cargo: <span id="info-cargo"></span><br>
					Salario: <span id="info-salario"></span>
				</div>
			</fieldset>

			<fieldset>
				<legend>Datos del colaborador</legend>

        <label for="cedula">Cedula:</label>
        <input type="text" id="cedula" name="cedula" placeholder="8-888-8888" required><br>

        <label for="cargo">Cargo:</label>
        <input type="text" id="cargo" name="cargo"><br>

        <label for="estado_civil">Estado civil:</label>
        <select id="estado_civil" name="estado_civil">
            <option value="soltero">Soltero/a</option>
            <option value="casado">Casado/a</option>
            <option value="unido">Unido/a</option>
        </select><br>

        <label for="salario">Salario base (mensual B/.):</label>
        <input type="number" id="salario" name="salario" step="0.01" min="0.01" required><br>

        <label for="anio_inicio">Ano de inicio:</label>
        <input type="number" id="anio_inicio" name="anio_inicio" min="1900" max="2100" value="<?= date('Y') ?>" required><br>

        <label for="otros_descuentos">Otros descuentos (B/.):</label>
        <input type="number" id="otros_descuentos" name="otros_descuentos" step="0.01" min="0" value="0">
        <small>(muebleria, adelantos, ahorros)</small>
    </fieldset>

    <fieldset>
        <legend>Otros ingresos del periodo</legend>
        <small>
            <strong>bonificacion</strong>: gravable 100% &nbsp;|&nbsp;
            <strong>comision</strong>: 100% gravable &nbsp;|&nbsp;
            <strong>dietas</strong>: exento hasta 25% sal. mensual &nbsp;|&nbsp;
            <strong>prima</strong>: exento hasta 50% &nbsp;|&nbsp;
            <strong>horas_extra</strong>: ingresar cantidad de horas
        </small>

        <div id="ingresos-container" style="margin-top:6px">
            <div class="ing-row">
                <select name="ing_tipo[]" onchange="toggleHoras(this)">
                    <option value="">-- ninguno --</option>
                    <option value="bonificacion">bonificacion</option>
                    <option value="comision">comision</option>
                    <option value="dietas">dietas</option>
                    <option value="prima">prima</option>
                    <option value="horas_extra">horas_extra</option>
                </select>
                <input type="number" name="ing_monto[]" step="0.01" min="0" placeholder="Monto B/." value="0">
                <input type="number" name="ing_horas[]" step="0.01" min="0" placeholder="Horas" value="0" class="campo-horas" style="display:none">
                <button type="button" onclick="eliminarFilaIng(this)">x</button>
            </div>
        </div>
        <button type="button" onclick="agregarFilaIng()">+ Ingreso</button>
    </fieldset>

    <button type="submit">Guardar</button>
</form>


<?php if (!empty($filas)): ?>
<h3>
    Planilla: <?= $periodo === '1ra_quincena' ? '1ra Quincena' : '2da Quincena' ?>
    — <?= $mes ?>/<?= $anio ?>
    &nbsp;<small style="color:#555">(<?= count($filas) ?> colaborador<?= count($filas) !== 1 ? 'es' : '' ?>)</small>
</h3>

<?php if (!empty($exito)): ?>
    <p class="ok"><?= htmlspecialchars($exito) ?></p>
<?php endif; ?>

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

<?php elseif (!empty($exito)): ?>
    <p class="ok"><?= htmlspecialchars($exito) ?></p>
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
  <option value="horas_extra">horas_extra</option>
  </select>
  <input type="number" name="ing_monto[]" step="0.01" min="0" placeholder="Monto B/." value="0">
  <input type="number" name="ing_horas[]" step="0.01" min="0" placeholder="Horas" value="0" class="campo-horas" style="display:none">
  <button type="button" onclick="eliminarFilaIng(this)">x</button>
`;
c.appendChild(div);
}

function eliminarFilaIng(btn) {
  const rows = document.querySelectorAll('.ing-row');
  if (rows.length > 1) btn.parentElement.remove();
}

			<script>				// CREADO - Issue #5: Búsqueda de empleado con AJAX
				document.getElementById('btn_buscar').addEventListener('click', function() {
					const cedula = document.getElementById('cedula_buscar').value.trim();
					const estatus = document.getElementById('estatus_busqueda');

					if (cedula.length < 5) {
						estatus.innerHTML = '<span style="color: red;">⚠ Ingrese una cédula válida</span>';
						return;
					}

					estatus.innerHTML = '<span style="color: #666;">🔄 Buscando...</span>';

					fetch('<?= BASE_URL ?>/planilla/buscarEmpleado?cedula=' + encodeURIComponent(cedula))
						.then(r => r.json())
						.then(data => {
							if (data.error) {
								estatus.innerHTML = '<span style="color: red;">✗ ' + data.error + '</span>';
								document.getElementById('empleado-info').style.display = 'none';
								return;
							}

							// Llenar campos automáticamente
							document.getElementById('nombre').value = data.nombre_completo;
							document.getElementById('cedula').value = data.cedula;
							document.getElementById('cargo').value = data.cargo;
							document.getElementById('salario').value = data.salario_base;
							document.getElementById('estado_civil').value = data.estado_civil;
							document.getElementById('anio_inicio').value = data.anio_inicio;

							// Mostrar información de confirmación
							document.getElementById('info-nombre').textContent = data.nombre_completo;
							document.getElementById('info-cargo').textContent = data.cargo;
							document.getElementById('info-salario').textContent = 'B/. ' + parseFloat(data.salario_base).toFixed(2);
							document.getElementById('empleado-info').style.display = 'block';

							estatus.innerHTML = '<span style="color: green;">✓ Cargado</span>';
						})
						.catch(e => {
							estatus.innerHTML = '<span style="color: red;">✗ Error de conexión</span>';
							console.error(e);
						});
				});

				// Permitir búsqueda con Enter
				document.getElementById('cedula_buscar').addEventListener('keypress', function(e) {
					if (e.key === 'Enter') {
						document.getElementById('btn_buscar').click();
					}
				});

				function agregarFilaIng() {
					const c = document.getElementById("ingresos-container");
					const div = document.createElement("div");
					div.className = "ing-row";
					div.innerHTML = `
    <select name="ing_tipo[]" onchange="toggleHoras(this)">
          <option value="">-- ninguno --</option>
          <option value="bonificacion">bonificacion</option>
          <option value="comision">comision</option>
          <option value="dietas">dietas</option>
          <option value="prima">prima</option>
          <option value="horas_extra">horas_extra</option>
        </select>
        <input type="number" name="ing_monto[]" step="0.01" min="0"
               placeholder="Monto B/." value="0">
        <input type="number" name="ing_horas[]" step="0.01" min="0"
               placeholder="Horas trabajadas" value="0"
               class="campo-horas" style="display:none">
        <button type="button" onclick="eliminarFilaIng(this)">✕</button>
    `;
					c.appendChild(div);
				}

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>

