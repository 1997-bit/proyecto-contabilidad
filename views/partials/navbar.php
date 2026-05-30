<?php
SessionHelper::iniciar();
if (!SessionHelper::existe('usuario_id')) {
  header('Location: ' . BASE_URL . '/login');
  exit;
}
$csrf = SessionHelper::generarCsrf();
?>
<nav>
    <span>Bienvenido, <?= htmlspecialchars(SessionHelper::get('usuario_nombre')) ?></span>
    <form method="POST" action="<?= BASE_URL ?>/login/logout">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <button type="submit">Cerrar sesión</button>
    </form>
</nav>
