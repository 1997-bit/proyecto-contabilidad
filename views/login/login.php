<?php declare(strict_types=1);
$pageTitle = 'Iniciar sesión';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Iniciar sesión</h2>

<?php if (!empty($error)): ?>
<div class="error"><p><?= Icons::svg('alert-triangle') ?> <?= htmlspecialchars($error) ?></p></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/login" class="form-narrow">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

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
        <button type="submit"><?= Icons::svg('unlock') ?> Entrar</button>
    </div>
</form>

<p>¿No tienes cuenta? <a href="<?= BASE_URL ?>/register">Regístrate</a></p>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
