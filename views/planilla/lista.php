<?php declare(strict_types=1);
function lista_fmt(float $v): string {
  return 'B/.' . number_format($v, 2, '.', ',');
}
$pageTitle = 'Planillas';
require BASE_PATH . '/views/partials/layout_head.php';
?>
<h2>Planillas registradas</h2>

<?php
$accionUrl = BASE_URL . '/planilla/lista';
require BASE_PATH . '/views/partials/_selector_periodo.php';
?>

<?php if (empty($planillas)): ?>
<p>No hay planillas para los filtros seleccionados.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Empresa</th>
            <th>Quincena</th>
            <th>Mes</th>
            <th>Año</th>
            <th>Estado</th>
            <th>Colaboradores</th>
            <th class="num">Bruto total</th>
            <th class="num">Neto total</th>
            <th>Creada por</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($planillas as $i => $p): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($p['empresa_nombre']) ?></td>
            <td><?= $p['periodo'] === '1ra_quincena' ? '1ra quincena' : '2da quincena' ?></td>
            <td><?= $p['mes'] ?></td>
            <td><?= $p['anio'] ?></td>
            <td><?= htmlspecialchars($p['estado']) ?></td>
            <td><?= (int) $p['total_colaboradores'] ?></td>
            <td class="num"><?= lista_fmt((float) $p['bruto_total']) ?></td>
            <td class="num"><?= lista_fmt((float) $p['neto_total']) ?></td>
            <td><?= htmlspecialchars($p['creada_por'] ?? '-') ?></td>
            <td><a href="<?= BASE_URL ?>/reporte/css?planilla_id=<?= $p['id'] ?>">Ver CSS</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php require BASE_PATH . '/views/partials/layout_foot.php'; ?>
