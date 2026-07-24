<?php declare(strict_types=1);
$pageTitle = 'Home';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Panel principal</h2>
<p>Sistema de planilla de servicios.</p>

<div class="cards">
    <div class="card">
        <a href="<?= BASE_URL ?>/colaborador"><?= Icons::svg('users') ?> Colaboradores</a>
        <p>Agregar y listar colaboradores.</p>
    </div>
    <div class="card">
        <a href="<?= BASE_URL ?>/planilla"><?= Icons::svg('file-text') ?> Planilla</a>
        <p>Captura y calculo de quincena.</p>
    </div>
    <div class="card">
        <a href="<?= BASE_URL ?>/reporte"><?= Icons::svg('bar-chart') ?> Reportes</a>
        <p>Reportes P, E y CSS.</p>
    </div>
</div>
<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
