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

<h2>Nueva Planilla</h2>

<?php if (!empty($errores)): ?>
<div class="error">
    <?php foreach ($errores as $e): ?>
    <p><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
<p class="ok"><?= htmlspecialchars($exito) ?></p>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/planilla/agregar">

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
        <legend>Periodo</legend>

        <label for="empresa_id">Empresa:</label>
        <select id="empresa_id" name="empresa_id" required>
            <option value="">-- seleccionar --</option>
            <?php foreach ($empresas as $emp): ?>
            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['nombre']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="periodo">Quincena:</label>
        <select id="periodo" name="periodo">
            <option value="1ra_quincena">1ra quincena</option>
            <option value="2da_quincena">2da quincena</option>
        </select><br>

        <label for="mes">Mes:</label>
        <select id="mes" name="mes">
            <?php foreach (['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $i => $nm): ?>
            <option value="<?= $i+1 ?>" <?= ($i+1 == (int)date('n')) ? 'selected' : '' ?>><?= $nm ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="anio">Ano:</label>
        <input type="number" id="anio" name="anio" min="2000" max="2100" value="<?= date('Y') ?>" required><br>
    </fieldset>

    <fieldset>
        <legend>Datos del colaborador</legend>

        <label for="nombre">Nombre completo:</label>
        <input type="text" id="nombre" name="nombre" required><br>

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

function toggleHoras(sel) {
    const horas = sel.parentElement.querySelector('.campo-horas');
    horas.style.display = sel.value === 'horas_extra' ? 'inline' : 'none';
}
</script>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
