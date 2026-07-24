<?php declare(strict_types=1);
if (SessionHelper::existe('usuario_id')):
$ctxEmpresa = $_SESSION['ctx']['empresa'] ?? null;
?>
<nav>
    <a href="<?= BASE_URL ?>/home"><?= Icons::svg('home') ?> Home</a> |
    <a href="<?= BASE_URL ?>/colaborador"><?= Icons::svg('users') ?> Colaboradores</a> |
    <a href="<?= BASE_URL ?>/planilla"><?= Icons::svg('file-text') ?> Planilla</a> |
    <a href="<?= BASE_URL ?>/reporte"><?= Icons::svg('bar-chart') ?> Reportes</a> |
    <a href="<?= BASE_URL ?>/settings"><?= Icons::svg('settings') ?> Settings</a>
    <?php if (SessionHelper::get('usuario_rol') === 'admin'): ?>
    | <a href="<?= BASE_URL ?>/usuarios"><?= Icons::svg('user') ?> Usuarios</a>
    <?php endif; ?>
    &nbsp;&nbsp;
    <?php if ($ctxEmpresa): ?>
    <strong>[<?= htmlspecialchars($ctxEmpresa) ?>]</strong>
    <?php else: ?>
    <a href="<?= BASE_URL ?>/settings" class="warning"><?= Icons::svg('alert-triangle') ?> sin empresa</a>
    <?php endif; ?>
    &nbsp;&nbsp;
    <span><?= Icons::svg('user') ?> <?= htmlspecialchars(SessionHelper::get('usuario_nombre')) ?></span>
    <form method="POST" action="<?= BASE_URL ?>/login/logout" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <button type="submit"><?= Icons::svg('log-out') ?> Salir</button>
    </form>
</nav>
<hr>
<?php endif; ?>
