<?php declare(strict_types=1);
$pageTitle = 'Colaboradores';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Colaboradores</h2>

<?php if (!empty($errores)): ?>
<div class="error">
    <?php foreach ($errores as $campo => $msg): ?>
    <p><?= htmlspecialchars($msg) ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['ok'])): ?>
<p class="ok">Colaborador guardado correctamente.</p>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/colaborador/guardar">
    <table style="width:auto; border:none">
        <tr>
            <td><label for="nombre_completo">Nombre completo</label></td>
            <td>
                <input type="text" id="nombre_completo" name="nombre_completo"
                    value="<?= htmlspecialchars($valores['nombre_completo'] ?? '') ?>" required>
                <?php if (isset($errores['nombre_completo'])): ?>
                <span class="error"><?= htmlspecialchars($errores['nombre_completo']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><label for="cedula">Cedula</label></td>
            <td>
                <input type="text" id="cedula" name="cedula" placeholder="8-123-4567"
                    value="<?= htmlspecialchars($valores['cedula'] ?? '') ?>">
                <?php if (isset($errores['cedula'])): ?>
                <span class="error"><?= htmlspecialchars($errores['cedula']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><label for="estado_civil">Estado civil</label></td>
            <td>
                <select id="estado_civil" name="estado_civil">
                    <?php foreach (['soltero' => 'Soltero/a', 'casado' => 'Casado/a', 'unido' => 'Unido/a'] as $val => $label): ?>
                    <option value="<?= $val ?>"
                        <?= ($valores['estado_civil'] ?? '') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="cargo">Cargo</label></td>
            <td>
                <input type="text" id="cargo" name="cargo"
                    value="<?= htmlspecialchars($valores['cargo'] ?? '') ?>" required>
            </td>
        </tr>
        <tr>
            <td><label for="salario_base">Salario base (mensual B/.)</label></td>
            <td>
                <input type="number" id="salario_base" name="salario_base"
                    step="0.01" min="0.01"
                    value="<?= htmlspecialchars($valores['salario_base'] ?? '') ?>" required>
                <?php if (isset($errores['salario_base'])): ?>
                <span class="error"><?= htmlspecialchars($errores['salario_base']) ?></span>
                <?php endif; ?>
                <?php if (isset($errores['salario_base_aviso'])): ?>
                <span style="color:orange"><?= htmlspecialchars($errores['salario_base_aviso']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><label for="tipo_salario">Tipo de salario</label></td>
            <td>
                <select id="tipo_salario" name="tipo_salario">
                    <?php foreach (['fijo' => 'Fijo', 'comisiones' => 'Comisiones', 'dietas' => 'Dietas', 'prima_produccion' => 'Prima de produccion'] as $val => $label): ?>
                    <option value="<?= $val ?>"
                        <?= ($valores['tipo_salario'] ?? '') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="anio_inicio">Año de inicio</label></td>
            <td>
                <input type="number" id="anio_inicio" name="anio_inicio"
                    min="1900" max="<?= date('Y') ?>"
                    value="<?= htmlspecialchars($valores['anio_inicio'] ?? date('Y')) ?>" required>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><button type="submit">Guardar</button></td>
        </tr>
    </table>
</form>

<h3 style="margin-top:24px">Lista de colaboradores</h3>
<?php if (empty($colaboradores)): ?>
<p>No hay colaboradores registrados.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Cedula</th>
            <th>Estado civil</th>
            <th>Cargo</th>
            <th>Salario base</th>
            <th>Tipo salario</th>
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
            <td><?= htmlspecialchars($c['tipo_salario'] ?? '-') ?></td>
            <td><?= htmlspecialchars($c['anio_inicio']) ?></td>
            <td style="text-align:center;white-space:nowrap">
                <button type="button"
                    onclick="abrirEditar(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode([
                        'nombre_completo' => $c['nombre_completo'],
                        'cedula'          => $c['cedula'],
                        'estado_civil'    => $c['estado_civil'],
                        'cargo'           => $c['cargo'],
                        'salario_base'    => $c['salario_base'],
                        'anio_inicio'     => $c['anio_inicio'],
                    ]), ENT_QUOTES) ?>)">
                    Editar
                </button>
                <button type="button" style="color:#c00"
                    onclick="abrirEliminar(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['nombre_completo']), ENT_QUOTES) ?>)">
                    Eliminar
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php
require BASE_PATH . '/views/partials/_modal_editar.php';
require BASE_PATH . '/views/partials/_modal_eliminar.php';
?>

<script>
function abrirEditar(id, datos) {
    document.getElementById('edit-id').value          = id;
    document.getElementById('edit-nombre').value      = datos.nombre_completo;
    document.getElementById('edit-cedula').value      = datos.cedula;
    document.getElementById('edit-ecivil').value      = datos.estado_civil;
    document.getElementById('edit-cargo').value       = datos.cargo;
    document.getElementById('edit-anio').value        = datos.anio_inicio;
    document.getElementById('dlg-editar').showModal();
}

function abrirEliminar(id, nombre) {
    _nombreEliminar = nombre;
    document.getElementById('del-id').value               = id;
    document.getElementById('del-nombre-display').textContent = nombre;
    document.getElementById('del-confirmacion').value     = '';
    document.getElementById('btn-confirmar-eliminar').disabled = true;
    document.getElementById('dlg-eliminar').showModal();
}
</script>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
