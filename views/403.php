<?php declare(strict_types=1);
$pageTitle = 'Acceso denegado';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Acceso denegado</h2>
<div class="error">
    <p><?= Icons::svg('alert-triangle') ?> No tienes permisos para acceder a esta sección.</p>
</div>
<p><a href="<?= BASE_URL ?>/home">Volver al inicio</a></p>
<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
