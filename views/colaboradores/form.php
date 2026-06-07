<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<!-- CREADO: Formulario para creación y edición de colaboradores (2024) -->
<div class="colaborador-container">
    <h2><?= isset($valores['id']) ? 'Editar Colaborador' : 'Nuevo Colaborador' ?></h2>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <?php foreach ($errores as $key => $error): ?>
                <?php if (is_string($key) && strpos($key, '_aviso') !== false): ?>
                    <p class="alert-warning">⚠ <?= htmlspecialchars($error) ?></p>
                <?php else: ?>
                    <p>• <?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= isset($valores['id']) ? BASE_URL . '/colaborador/actualizar?id=' . $valores['id'] : BASE_URL . '/colaborador/guardar' ?>" class="form">
        
        <div class="form-group">
            <label>Nombre Completo *</label>
            <input type="text" name="nombre_completo" value="<?= htmlspecialchars($valores['nombre_completo'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Cédula (Formato: 8-123-4567) *</label>
            <input type="text" name="cedula" value="<?= htmlspecialchars($valores['cedula'] ?? '') ?>" placeholder="8-123-4567" required>
        </div>

        <div class="form-group">
            <label>Salario Base (B/.) *</label>
            <input type="number" step="0.01" name="salario_base" value="<?= htmlspecialchars($valores['salario_base'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Estado Civil *</label>
            <select name="estado_civil" required>
                <option value="">-- Selecciona --</option>
                <option value="soltero" <?= (($valores['estado_civil'] ?? '') === 'soltero') ? 'selected' : '' ?>>Soltero</option>
                <option value="casado" <?= (($valores['estado_civil'] ?? '') === 'casado') ? 'selected' : '' ?>>Casado</option>
                <option value="unido" <?= (($valores['estado_civil'] ?? '') === 'unido') ? 'selected' : '' ?>>Unido</option>
            </select>
        </div>

        <div class="form-group">
            <label>Cargo *</label>
            <input type="text" name="cargo" value="<?= htmlspecialchars($valores['cargo'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Año de Inicio de Labores *</label>
            <input type="number" name="anio_inicio" value="<?= htmlspecialchars($valores['anio_inicio'] ?? date('Y')) ?>" min="1900" max="<?= date('Y') ?>" required>
        </div>

        <div class="form-group">
            <label>Tipo de Salario *</label>
            <select name="tipo_salario" required>
                <option value="">-- Selecciona --</option>
                <option value="fijo" <?= (($valores['tipo_salario'] ?? '') === 'fijo') ? 'selected' : '' ?>>Fijo</option>
                <option value="comisiones" <?= (($valores['tipo_salario'] ?? '') === 'comisiones') ? 'selected' : '' ?>>Comisiones</option>
                <option value="dietas" <?= (($valores['tipo_salario'] ?? '') === 'dietas') ? 'selected' : '' ?>>Dietas</option>
                <option value="prima_produccion" <?= (($valores['tipo_salario'] ?? '') === 'prima_produccion') ? 'selected' : '' ?>>Prima Producción</option>
            </select>
        </div>

        <?php if (isset($valores['id'])): ?>
            <input type="hidden" name="id" value="<?= $valores['id'] ?>">
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <?= isset($valores['id']) ? 'Actualizar' : 'Guardar' ?>
            </button>
            <a href="<?= BASE_URL ?>/colaborador" class="btn btn-secondary">Cancelar</a>
        </div>

        <p class="form-note">* Campo obligatorio</p>
    </form>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
