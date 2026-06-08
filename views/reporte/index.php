<?php declare(strict_types=1);
$pageTitle = 'Reportes';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Seleccion de reportes</h2>

<div class="cards">
    <div class="card">
        <a href="<?= BASE_URL ?>/reporte/personal">Reporte P</a>
        <p>Planilla grupal de colaboradores.</p>
    </div>
    <div class="card">
        <a href="<?= BASE_URL ?>/reporte/expediente">Reporte E</a>
        <p>Expediente individual por colaborador.</p>
    </div>
    <div class="card">
        <a href="<?= BASE_URL ?>/reporte/css">Reporte C</a>
        <p>Informe Caja de Seguro Social.</p>
    </div>
</div>
<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
