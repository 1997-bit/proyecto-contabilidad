<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<!-- CREADO: Vista principal para listar colaboradores (2024) -->
<div class="colaborador-container">
    <h2>Gestión de Colaboradores</h2>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <?php foreach ($errores as $error): ?>
                <p>• <?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success">
            ✓ Operación realizada correctamente.
        </div>
    <?php endif; ?>

    <p>
        <a href="<?= BASE_URL ?>/colaborador/nuevo" class="btn btn-primary">
            + Nuevo Colaborador
        </a>
    </p>

    <?php if (empty($colaboradores)): ?>
        <p class="text-muted">No hay colaboradores registrados.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Cargo</th>
                    <th class="text-right">Salario</th>
                    <th>Estado Civil</th>
                    <th>Año Inicio</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($colaboradores as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($c['cedula']) ?></td>
                    <td><?= htmlspecialchars($c['cargo']) ?></td>
                    <td class="text-right">B/. <?= number_format((float)$c['salario_base'], 2) ?></td>
                    <td><?= htmlspecialchars($c['estado_civil']) ?></td>
                    <td><?= htmlspecialchars($c['anio_inicio']) ?></td>
                    <td class="text-center">
                        <a href="<?= BASE_URL ?>/colaborador/editar?id=<?= $c['id'] ?>" class="link-edit">Editar</a>
                        <form method="post" action="<?= BASE_URL ?>/colaborador/eliminar" class="form-inline" onsubmit="return confirm('¿Deseas eliminar este colaborador?');">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="link-delete">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
