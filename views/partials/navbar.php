<?php declare(strict_types=1);
if (!SessionHelper::existe('usuario_id')) {
  header('Location: ' . BASE_URL . '/login');
  exit;
}
$csrf = SessionHelper::generarCsrf();
?>
<nav>
    <a href="<?= BASE_URL ?>/home">Home</a> |
    <a href="<?= BASE_URL ?>/colaborador">Colaboradores</a> |
    <a href="<?= BASE_URL ?>/planilla">Planilla</a> |
    <a href="<?= BASE_URL ?>/reporte">Reportes</a>
    &nbsp;&nbsp;
    <span>[ <?= htmlspecialchars(SessionHelper::get('usuario_nombre')) ?> ]</span>
    <form method="POST" action="<?= BASE_URL ?>/login/logout" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <button type="submit">Salir</button>
    </form>
</nav>
<hr>
