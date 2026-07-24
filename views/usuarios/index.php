<?php declare(strict_types=1);
$pageTitle = 'Usuarios';
require BASE_PATH . '/views/partials/layout_head.php';
$miId = (int) ($_SESSION['usuario_id'] ?? 0);
?>
<h2>Usuarios</h2>

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
    <legend>Crear usuario</legend>
    <form method="POST" action="<?= BASE_URL ?>/usuarios/crear">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="visor">Visor</option>
                    <option value="contador">Contador</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit"><?= Icons::svg('plus') ?> Crear usuario</button>
        </div>
    </form>
</fieldset>

<?php if (empty($usuarios)): ?>
<p>No hay usuarios registrados.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Último acceso</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $i => $u): ?>
        <?php $esYo = (int) $u['id'] === $miId; ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($u['nombre']) ?><?= $esYo ? ' (tú)' : '' ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <form method="POST" action="<?= BASE_URL ?>/usuarios/cambiarRol" style="display:flex;gap:4px;align-items:center">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <select name="rol" <?= $esYo ? 'disabled' : '' ?>>
                        <?php foreach (['visor' => 'Visor', 'contador' => 'Contador', 'admin' => 'Admin'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= $u['rol'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" <?= $esYo ? 'disabled' : '' ?>><?= Icons::svg('save') ?> Guardar</button>
                </form>
            </td>
            <td><?= $u['activo'] ? Icons::svg('check-circle') . ' Activo' : Icons::svg('x-circle') . ' Inactivo' ?></td>
            <td><?= $u['last_login'] ? htmlspecialchars($u['last_login']) : '-' ?></td>
            <td>
                <form method="POST" action="<?= BASE_URL ?>/usuarios/toggleActivo"
                    onsubmit="return confirm('<?= $u['activo'] ? 'Desactivar' : 'Activar' ?> a <?= htmlspecialchars(addslashes($u['nombre'])) ?>?')">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="<?= $u['activo'] ? 'btn-danger' : 'btn-secondary' ?>" <?= $esYo ? 'disabled' : '' ?>>
                        <?= $u['activo'] ? Icons::svg('trash') . ' Desactivar' : Icons::svg('check-circle') . ' Activar' ?>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
