<?php
/**
 * views/planilla/index.php
 *
 * Variables esperadas del PlanillaController:
 *   $filas   array   - filas acumuladas en sesión
 *   $totales array   - suma de cada columna numérica
 *   $errores array   - errores de validación del último POST
 */

// Helper local de formato
function planilla_fmt(float $v): string {
  return 'B/.' . number_format($v, 2, '.', ',');
}
?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<!doctype html>
<html lang="es">
	<head>
		<meta charset="UTF-8" />
		<title>Planilla de Servicios - Prueba</title>
		<style>
			body {
				font-family: Arial, sans-serif;
				font-size: 13px;
				margin: 20px;
			}
			h2 {
				margin-bottom: 6px;
			}
			p.sub {
				color: #555;
				margin-top: 0;
			}

			fieldset {
				margin-bottom: 14px;
				padding: 10px 14px;
			}
			legend {
				font-weight: bold;
			}
			label {
				display: inline-block;
				width: 170px;
			}
			input[type="text"],
			input[type="number"],
			select {
				width: 190px;
				padding: 2px 4px;
				margin-bottom: 4px;
			}
			button {
				margin: 4px 4px 4px 0;
				padding: 4px 10px;
				cursor: pointer;
			}

			.error {
				color: red;
				font-weight: bold;
				margin-bottom: 10px;
			}

			/* Filas ingresos dinámicos */
			#ingresos-container .ing-row {
				margin-bottom: 4px;
			}
			#ingresos-container .ing-row select {
				width: 130px;
			}
			#ingresos-container .ing-row input {
				width: 90px;
			}

			/* Tabla planilla */
			table.planilla {
				border-collapse: collapse;
				font-size: 12px;
				margin-top: 16px;
				width: 100%;
			}
			table.planilla th,
			table.planilla td {
				border: 1px solid #888;
				padding: 3px 6px;
				white-space: nowrap;
			}
			table.planilla th {
				background: #ddd;
				text-align: center;
			}
			table.planilla .num {
				text-align: right;
			}
			table.planilla tfoot td {
				background: #f5f5c0;
				font-weight: bold;
			}
			table.planilla tfoot .num {
				text-align: right;
			}
			tr.alerta {
				background: #fdd !important;
			}
			.badge-alerta {
				color: red;
				font-size: 10px;
				display: block;
			}

			form.inline {
				display: inline;
				margin: 0;
			}
		</style>
	</head>
	<body>
		<h2>Planilla de Servicios - Vista Prueba</h2>
		<p class="sub">
			Usa <code>PlanillaService</code> e <code>ISRService</code>
		</p>

		<?php if (!empty($errores)): ?>
		<div class="error">
			<?php foreach ($errores as $e): ?>
			<p>⚠ <?= htmlspecialchars($e) ?></p>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

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

				<label for="nombre">Nombre completo:</label>
				<input type="text" id="nombre" name="nombre" required /><br />

				<label for="cargo">Cargo:</label>
				<input type="text" id="cargo" name="cargo" /><br />

				<label for="salario">Salario base (mensual B/.):</label>
				<input
					type="number"
					id="salario"
					name="salario"
					step="0.01"
					min="0.01"
					required
				/><br />

				<label for="estado_civil">Estado civil:</label>
				<select id="estado_civil" name="estado_civil">
					<option value="soltero">Soltero/a</option>
					<option value="casado">Casado/a</option>
					<option value="unido">Unido/a</option></select
				><br />
				<label for="cedula">Cédula:</label>
				<input
					type="text"
					id="cedula"
					name="cedula"
					placeholder="8-888-8888"
					required
				/><br />

				<label for="anio_inicio">Año de inicio:</label>
				<input
					type="number"
					id="anio_inicio"
					name="anio_inicio"
					min="1900"
					max="2100"
					value="<?= date('Y') ?>"
					required
				/><br />

				<label for="otros_descuentos">Otros descuentos (B/.):</label>
				<input
					type="number"
					id="otros_descuentos"
					name="otros_descuentos"
					step="0.01"
					min="0"
					value="0"
				/>
				<small>(mueblería, adelantos, ahorros, etc.)</small>
			</fieldset>

			<fieldset>
				<legend>Otros ingresos del período</legend>
        <small>
          <strong>bonificacion</strong>: gravable 100%, Art.140 CT &nbsp;|&nbsp;
					<strong>comision</strong>: 100% gravable &nbsp;|&nbsp;
					<strong>dietas</strong>: exento hasta 25% sal. mensual
					&nbsp;|&nbsp; <strong>prima</strong>: exento hasta 50%
					&nbsp;|&nbsp; <strong>horas_extra</strong>: ingresar
					cantidad de horas
				</small>

				<div id="ingresos-container" style="margin-top: 6px">
					<div class="ing-row">
						<select name="ing_tipo[]" onchange="toggleHoras(this)">
							<option value="">-- ninguno --</option>
              <option value="bonificacion">bonificacion</option>             
              <option value="comision">comision</option>
							<option value="dietas">dietas</option>
							<option value="prima">prima</option>
							<option value="horas_extra">horas_extra</option>
						</select>
						<input
							type="number"
							name="ing_monto[]"
							step="0.01"
							min="0"
							placeholder="Monto B/."
							value="0"
						/>
						<input
							type="number"
							name="ing_horas[]"
							step="0.01"
							min="0"
							placeholder="Horas"
							value="0"
							class="campo-horas"
							style="display: none"
						/>
						<button type="button" onclick="eliminarFilaIng(this)">
							✕
						</button>
					</div>
				</div>
				<button type="button" onclick="agregarFilaIng()">
					+ Ingreso
				</button>
			</fieldset>

			<fieldset>
				<legend>Período</legend>
				<label for="empresa_id">Empresa:</label>
				<select id="empresa_id" name="empresa_id" required>
					<option value="">-- seleccionar --</option>
					<?php foreach ($empresas as $emp): ?>
					<option value="<?= $emp['id'] ?>">
						<?= htmlspecialchars($emp['nombre']) ?>
					</option>
					<?php endforeach; ?></select
				><br />

				<label for="periodo">Quincena:</label>
				<select id="periodo" name="periodo">
					<option value="1ra_quincena">1ra quincena</option>
					<option value="2da_quincena">2da quincena</option></select
				><br />

				<label for="mes">Mes:</label>
				<select id="mes" name="mes">
                  <?php foreach (['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $i => $nm): ?>
            <option value="<?= $i+1 ?>" <?= ($i+1 == (int)date('n')) ? 'selected' : '' ?>><?= $nm ?></option>
        <?php endforeach; ?>
          
          </select
				><br />

				<label for="anio">Año:</label>
				<input
					type="number"
					id="anio"
					name="anio"
					min="2000"
					max="2100"
					value="<?= date('Y') ?>"
					required
				/><br />
			</fieldset>

			<button type="submit">Agregar</button>
</form>


<!-- TABLA -->
<?php if (!empty($filas)): ?>

  <div style="margin-top:10px;">
    <form class="inline" method="POST" action="<?= BASE_URL ?>/planilla/limpiar"
          onsubmit="return confirm('¿Limpiar toda la planilla?')">
      <button type="submit">Limpiar tabla</button>
    </form>
  </div>

  <table class="planilla">
    <thead>
      <tr>
        <th>#</th>
        <th>Nombre</th>
        <th>Cargo</th>
        <th>Est. Civil</th>
        <th>Salario Base</th>
        <th>Otros Ingresos</th>
        <th>Total Bruto</th>
        <th>CSS (9.75%)</th>
        <th>Seg. Educativo (1.25%)</th>
        <th>ISR</th>
        <th>Otros Descuentos</th>
        <th>Total Descuentos</th>
        <th>Otros Ing. sin Desc.</th>
        <th>Salario Neto</th>
        <th>% Desc.</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($filas as $i => $f):
    $c      = $f['calc'];
    $alerta = (bool) $c['alerta_desc_excede'];
    ?>
          <tr <?= $alerta ? 'class="alerta"' : '' ?>>
            <td><?= $i + 1 ?></td>
            <td>
              <?= htmlspecialchars($f['nombre']) ?>
              <?php if ($alerta): ?>
                <span class="badge-alerta">⚠ Art.161 - desc. excede tope (35%)</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($f['cargo']) ?></td>
            <td><?= htmlspecialchars($f['estado_civil']) ?></td>
            <td class="num"><?= planilla_fmt($c['salario_base_quincena']) ?></td>
            <td class="num"><?= planilla_fmt($c['otros_ingresos']) ?></td>
            <td class="num"><?= planilla_fmt($c['salario_bruto']) ?></td>
            <td class="num"><?= planilla_fmt($c['desc_seguro_social']) ?></td>
            <td class="num"><?= planilla_fmt($c['desc_seguro_educativo']) ?></td>
            <td class="num"><?= planilla_fmt($c['desc_isr']) ?></td>
            <td class="num"><?= planilla_fmt($c['otros_descuentos']) ?></td>
            <td class="num"><?= planilla_fmt($c['total_descuentos']) ?></td>
            <td class="num">
    <?= $c['otros_ingresos_sin_descuento'] > 0
    ? planilla_fmt($c['otros_ingresos_sin_descuento'])
    : '-' ?>
            </td>
            <td class="num"><?= planilla_fmt($c['salario_neto']) ?></td>
            <td class="num"><?= $c['pct_descuentos'] ?>%</td>
            <td>
              <form class="inline" method="POST" action="<?= BASE_URL ?>/planilla/eliminar">
                <input type="hidden" name="idx" value="<?= $i ?>">
                <button type="submit" title="Eliminar fila">✕</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4"><strong>TOTALES</strong></td>
            <td class="num"><?= planilla_fmt($totales['salario_base_quincena']) ?></td>
            <td class="num"><?= planilla_fmt($totales['otros_ingresos']) ?></td>
            <td class="num"><?= planilla_fmt($totales['salario_bruto']) ?></td>
            <td class="num"><?= planilla_fmt($totales['desc_seguro_social']) ?></td>
            <td class="num"><?= planilla_fmt($totales['desc_seguro_educativo']) ?></td>
            <td class="num"><?= planilla_fmt($totales['desc_isr']) ?></td>
            <td class="num"><?= planilla_fmt($totales['otros_descuentos']) ?></td>
            <td class="num"><?= planilla_fmt($totales['total_descuentos']) ?></td>
            <td class="num">
    <?= $totales['otros_ingresos_sin_descuento'] > 0
    ? planilla_fmt($totales['otros_ingresos_sin_descuento'])
    : '-' ?>
            </td>
            <td class="num"><?= planilla_fmt($totales['salario_neto']) ?></td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>

      <p>
        <strong>Filas:</strong> <?= count($filas) ?> &nbsp;|&nbsp;
        <strong>Bruto total:</strong> <?= planilla_fmt($totales['salario_bruto']) ?> &nbsp;|&nbsp;
        <strong>Neto total:</strong> <?= planilla_fmt($totales['salario_neto']) ?>
      </p>

    <?php else: ?>
      <p><em>No hay colaboradores. Agrégalos usando el formulario.</em></p>
    <?php endif; ?>


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

				function eliminarFilaIng(btn) {
					const rows = document.querySelectorAll(".ing-row");
					if (rows.length > 1) btn.parentElement.remove();
        }

        function toggleHoras(sel) {
          const row   = sel.parentElement;
          const horas = row.querySelector('.campo-horas');
          if (sel.value === 'horas_extra') {
            horas.style.display = 'inline';
          } else {
            horas.style.display = 'none';
          }
        }
        </script>
    </form>
  </body>
</html>
