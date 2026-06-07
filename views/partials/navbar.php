<?php declare(strict_types=1);
if (!SessionHelper::existe('usuario_id')) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
$csrf = SessionHelper::generarCsrf();
$ctxEmpresa = $_SESSION['ctx']['empresa'] ?? null;
?>
<nav>
    <a href="<?= BASE_URL ?>/home">Home</a> |
    <a href="<?= BASE_URL ?>/colaborador">Colaboradores</a> |
    <a href="<?= BASE_URL ?>/planilla">Planilla</a> |
    <a href="<?= BASE_URL ?>/reporte">Reportes</a> |
    <a href="<?= BASE_URL ?>/settings">Settings</a>
    &nbsp;&nbsp;
    <?php if ($ctxEmpresa): ?>
    <strong>[<?= htmlspecialchars($ctxEmpresa) ?>]</strong>
    <?php else: ?>
    <a href="<?= BASE_URL ?>/settings" style="color:red">[sin empresa]</a>
    <?php endif; ?>
    &nbsp;&nbsp;
    <span>[ <?= htmlspecialchars(SessionHelper::get('usuario_nombre')) ?> ]</span>
    <form method="POST" action="<?= BASE_URL ?>/login/logout" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <button type="submit">Salir</button>
    </form>
</nav>
<hr>
