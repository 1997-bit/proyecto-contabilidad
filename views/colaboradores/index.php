<?php declare(strict_types=1);
$pageTitle = 'Colaboradores';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Colaboradores</h2>

<?php if (!empty($errores)): ?>
<div class="error">
    <?php foreach ($errores as $campo => $msg): ?>
    <p><?= Icons::svg('alert-triangle') ?> <?= htmlspecialchars($msg) ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['ok'])): ?>
<p class="ok"><?= Icons::svg('check-circle') ?> Colaborador guardado correctamente.</p>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/colaborador/guardar">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="form-row">
        <div class="form-group">
            <label for="nombre_completo">Nombre completo</label>
            <input type="text" id="nombre_completo" name="nombre_completo"
                value="<?= htmlspecialchars($valores['nombre_completo'] ?? '') ?>" required>
            <?php if (isset($errores['nombre_completo'])): ?>
            <span class="error"><?= htmlspecialchars($errores['nombre_completo']) ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="cedula">Cédula</label>
            <input type="text" id="cedula" name="cedula" placeholder="8-123-4567"
                value="<?= htmlspecialchars($valores['cedula'] ?? '') ?>">
            <?php if (isset($errores['cedula'])): ?>
            <span class="error"><?= htmlspecialchars($errores['cedula']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="estado_civil">Estado civil</label>
            <select id="estado_civil" name="estado_civil">
                <?php foreach (['soltero' => 'Soltero/a', 'casado' => 'Casado/a', 'unido' => 'Unido/a'] as $val => $label): ?>
                <option value="<?= $val ?>"
                    <?= ($valores['estado_civil'] ?? '') === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="cargo">Cargo</label>
            <input type="text" id="cargo" name="cargo"
                value="<?= htmlspecialchars($valores['cargo'] ?? '') ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="salario_base">Salario base (mensual B/.)</label>
            <input type="number" id="salario_base" name="salario_base"
                step="0.01" min="0.01"
                value="<?= htmlspecialchars($valores['salario_base'] ?? '') ?>" required>
            <?php if (isset($errores['salario_base'])): ?>
            <span class="error"><?= htmlspecialchars($errores['salario_base']) ?></span>
            <?php endif; ?>
            <?php if (isset($errores['salario_base_aviso'])): ?>
            <span class="warning"><?= htmlspecialchars($errores['salario_base_aviso']) ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="anio_inicio">Año de inicio</label>
            <input type="number" id="anio_inicio" name="anio_inicio"
                min="1900" max="<?= date('Y') ?>"
                value="<?= htmlspecialchars($valores['anio_inicio'] ?? date('Y')) ?>" required>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit"><?= Icons::svg('save') ?> Guardar</button>
    </div>
</form>

<input type="text" id="buscador" placeholder="Buscar por nombre o cédula..."
       oninput="filtrarTabla()"
       style="padding:4px 8px;width:260px">

<h3>Lista de colaboradores</h3>
<?php if (empty($colaboradores)): ?>
<p>No hay colaboradores registrados.</p>
<?php else: ?>
<div class="table-wrap">
<table id="tabla-colaboradores">
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Cedula</th>
            <th>Estado civil</th>
            <th>Cargo</th>
            <th>Salario base</th>
            <th>Año inicio</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($colaboradores as $i => $c): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($c['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($c['cedula']) ?></td>
            <td><?= htmlspecialchars($c['estado_civil']) ?></td>
            <td><?= htmlspecialchars($c['cargo']) ?></td>
            <td class="num">B/. <?= number_format((float)$c['salario_base'], 2) ?></td>
            <td><?= htmlspecialchars($c['anio_inicio']) ?></td>
            <td style="text-align:center;white-space:nowrap">
                <button type="button"
                    onclick="abrirEditar(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode([
                        'nombre_completo' => $c['nombre_completo'],
                        'cedula' => $c['cedula'],
                        'estado_civil' => $c['estado_civil'],
                        'cargo' => $c['cargo'],
                        'salario_base' => $c['salario_base'],
                        'anio_inicio' => $c['anio_inicio'],
                    ]), ENT_QUOTES) ?>)">
                    <?= Icons::svg('edit') ?> Editar
                </button>
                <button type="button" class="btn-danger"
                    onclick="abrirEliminar(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['nombre_completo']), ENT_QUOTES) ?>)">
                    <?= Icons::svg('trash') ?> Eliminar
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php
require BASE_PATH . '/views/partials/_modal_editar.php';
require BASE_PATH . '/views/partials/_modal_eliminar.php';
?>

<script>
function abrirEditar(id, datos) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nombre').value = datos.nombre_completo;
    document.getElementById('edit-cedula').value = datos.cedula;
    document.getElementById('edit-ecivil').value = datos.estado_civil;
    document.getElementById('edit-cargo').value = datos.cargo;
    document.getElementById('edit-anio').value = datos.anio_inicio;
    document.getElementById('dlg-editar').showModal();
}

let _nombreEliminar = '';

function abrirEliminar(id, nombre) {
    _nombreEliminar = nombre;
    document.getElementById('del-id').value = id;
    document.getElementById('del-nombre-display').textContent = nombre;
    document.getElementById('del-confirmacion').value = '';
    document.getElementById('btn-confirmar-eliminar').disabled = true;
    document.getElementById('dlg-eliminar').showModal();
}

function cerrarEliminar() {
    document.getElementById('dlg-eliminar').close();
}

function verificarNombreEliminar() {
    const valor = document.getElementById('del-confirmacion').value;
    document.getElementById('btn-confirmar-eliminar').disabled = valor !== _nombreEliminar;
}

function filtrarTabla() {
    const q = document.getElementById('buscador').value.toLowerCase();
    document.querySelectorAll('#tabla-colaboradores tbody tr').forEach(function(fila) {
        const nombre = fila.cells[1] ? fila.cells[1].textContent.toLowerCase() : '';
        const cedula = fila.cells[2] ? fila.cells[2].textContent.toLowerCase() : '';
        fila.style.display = (nombre.includes(q) || cedula.includes(q)) ? '' : 'none';
    });
}
</script>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
