<?php declare(strict_types=1);
$pageTitle = 'Reporte P - Personal';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Reporte P - Planilla grupal</h2>
<p style="color:#888">[pendiente - conectar con PlanillaModel]</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Periodo</th>
            <th>Salario bruto</th>
            <th>Total descuentos</th>
            <th>Salario neto</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($filas)): ?>
        <tr><td colspan="7">Sin datos.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
