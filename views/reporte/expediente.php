<?php declare(strict_types=1);
$pageTitle = 'Reporte E - Expediente';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Reporte E - Expediente individual</h2>
<p style="color:#888">[pendiente - conectar con ColaboradorModel + PlanillaModel]</p>

<?php if ($colaborador): ?>
<p><strong>Colaborador:</strong> <?= htmlspecialchars($colaborador['nombre_completo']) ?></p>
<?php else: ?>
<p>Seleccione un colaborador.</p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Periodo</th>
            <th>Mes</th>
            <th>Ano</th>
            <th>Salario bruto</th>
            <th>CSS</th>
            <th>Seg. educativo</th>
            <th>ISR</th>
            <th>Otros desc.</th>
            <th>Salario neto</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($detalles)): ?>
        <tr><td colspan="9">Sin datos.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
