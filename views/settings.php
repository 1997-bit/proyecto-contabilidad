<?php declare(strict_types=1);
$pageTitle = 'Settings';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Settings</h2>

<?php if (!empty($errores)): ?>
<div class="error">
    <?php foreach ($errores as $e): ?>
    <p><?= Icons::svg('alert-triangle') ?> <?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
<p class="ok"><?= Icons::svg('check-circle') ?> <?= htmlspecialchars($exito) ?></p>
<?php endif; ?>

<fieldset>
    <legend>Empresa activa</legend>
    <?php if (!empty($_SESSION['ctx'])): ?>
    <p>Activa: <strong><?= htmlspecialchars($_SESSION['ctx']['empresa']) ?></strong></p>
    <?php else: ?>
    <p class="error"><?= Icons::svg('alert-triangle') ?> Sin empresa seleccionada.</p>
    <?php endif; ?>
    <form method="POST" action="<?= BASE_URL ?>/settings/seleccionar">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="empresa_id">Empresa</label>
                <select id="empresa_id" name="empresa_id" required>
                    <option value="">-- seleccionar --</option>
                    <?php foreach ($empresas as $e): ?>
                    <?php if (!$e['activo']) continue; ?>
                    <option value="<?= $e['id'] ?>" <?= ($empresaActiva === (int)$e['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit"><?= Icons::svg('check-circle') ?> Activar</button>
        </div>
    </form>
</fieldset>

<fieldset>
    <legend>Nueva empresa</legend>
    <form method="POST" action="<?= BASE_URL ?>/settings/crearEmpresa">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="ruc">RUC</label>
                <input type="text" id="ruc" name="ruc" placeholder="123-456-1-2023">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit"><?= Icons::svg('plus') ?> Crear</button>
        </div>
    </form>
</fieldset>

<fieldset>
    <legend>Empresas registradas</legend>
    <?php if (empty($empresas)): ?>
    <p>Sin empresas.</p>
    <?php else: ?>
    <div class="table-wrap">
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
                <td><?= $e['activo'] ? Icons::svg('check-circle') . ' Activa' : Icons::svg('x-circle') . ' Inactiva' ?></td>
                <td>
                    <?php if ($e['activo']): ?>
                    <form method="POST" action="<?= BASE_URL ?>/settings/desactivarEmpresa"
                        style="display:inline"
                        onsubmit="return confirm('Desactivar <?= htmlspecialchars($e['nombre']) ?>?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="empresa_id" value="<?= $e['id'] ?>">
                        <button type="submit" class="btn-danger"><?= Icons::svg('trash') ?> Desactivar</button>
                    </form>
                    <?php else: ?>
                    <span style="color:#aaa">Inactiva</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</fieldset>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
