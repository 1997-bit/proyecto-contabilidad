<?php declare(strict_types=1);
$pageTitle = 'Settings';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Settings</h2>

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

<fieldset>
    <legend>Empresa activa</legend>
    <?php if (!empty($_SESSION['ctx'])): ?>
    <p>Activa: <strong><?= htmlspecialchars($_SESSION['ctx']['empresa']) ?></strong></p>
    <?php else: ?>
    <p class="error">Sin empresa seleccionada.</p>
    <?php endif; ?>
    <form method="POST" action="<?= BASE_URL ?>/settings/seleccionar">
        <select name="empresa_id" required>
            <option value="">-- seleccionar --</option>
            <?php foreach ($empresas as $e): ?>
            <?php if (!$e['activo']) continue; ?>
            <option value="<?= $e['id'] ?>" <?= ($empresaActiva === (int)$e['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Activar</button>
    </form>
</fieldset>

<fieldset style="margin-top:16px">
    <legend>Nueva empresa</legend>
    <form method="POST" action="<?= BASE_URL ?>/settings/crearEmpresa">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br>
        <label for="ruc">RUC:</label>
        <input type="text" id="ruc" name="ruc" placeholder="123-456-1-2023"><br>
        <button type="submit">Crear</button>
    </form>
</fieldset>

<fieldset style="margin-top:16px">
    <legend>Empresas registradas</legend>
    <?php if (empty($empresas)): ?>
    <p>Sin empresas.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>RUC</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($empresas as $i => $e): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($e['nombre']) ?></td>
                <td><?= htmlspecialchars($e['ruc'] ?? '-') ?></td>
                <td><?= $e['activo'] ? 'Activa' : 'Inactiva' ?></td>
                <td>
                    <?php if ($e['activo']): ?>
                    <form method="POST" action="<?= BASE_URL ?>/settings/desactivarEmpresa"
                        style="display:inline"
                        onsubmit="return confirm('Desactivar <?= htmlspecialchars($e['nombre']) ?>?')">
                        <input type="hidden" name="empresa_id" value="<?= $e['id'] ?>">
                        <button type="submit">Desactivar</button>
                    </form>
                    <?php else: ?>
                    <span style="color:#aaa">Inactiva</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</fieldset>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
