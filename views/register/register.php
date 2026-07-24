<?php declare(strict_types=1);
$pageTitle = 'Registro';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Crear usuario</h2>

<?php if (!empty($error)): ?>
<div class="error"><p><?= Icons::svg('alert-triangle') ?> <?= htmlspecialchars($error) ?></p></div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
<p class="ok"><?= Icons::svg('check-circle') ?> <?= $exito ?></p>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/register" class="form-narrow">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <div class="form-row">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" required
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit"><?= Icons::svg('plus') ?> Crear usuario</button>
    </div>
</form>

<p>¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/login">Inicia sesión</a></p>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
