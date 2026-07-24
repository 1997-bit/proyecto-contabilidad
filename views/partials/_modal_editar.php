<?php
$_editarAbierto = !empty($editarError);
$_ev = $editarValores ?? [];
?>
<dialog id="dlg-editar" aria-labelledby="dlg-editar-titulo" onclick="if (event.target === this) this.close();">
    <h3 id="dlg-editar-titulo">Editar colaborador</h3>

    <?php if (!empty($editarError)): ?>
    <div class="error">
        <?php foreach ($editarError as $msg): ?>
        <p><?= Icons::svg('alert-triangle') ?> <?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/colaborador/editar">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="id" id="edit-id" value="<?= htmlspecialchars((string)($editarId ?? '')) ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="edit-nombre">Nombre completo</label>
                <input type="text" id="edit-nombre" name="nombre_completo" required autofocus
                    value="<?= htmlspecialchars($_ev['nombre_completo'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="edit-cedula">Cédula</label>
                <input type="text" id="edit-cedula" name="cedula" placeholder="8-123-4567"
                    value="<?= htmlspecialchars($_ev['cedula'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="edit-ecivil">Estado civil</label>
                <select id="edit-ecivil" name="estado_civil">
                    <?php foreach (['soltero' => 'Soltero/a', 'casado' => 'Casado/a', 'unido' => 'Unido/a'] as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= ($_ev['estado_civil'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit-cargo">Cargo</label>
                <input type="text" id="edit-cargo" name="cargo" required
                    value="<?= htmlspecialchars($_ev['cargo'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="edit-salario">Salario base (B/.)</label>
                <input type="number" id="edit-salario" name="salario_base" step="0.01" min="0.01" required
                    value="<?= htmlspecialchars($_ev['salario_base'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="edit-anio">Año de inicio</label>
                <input type="number" id="edit-anio" name="anio_inicio" min="1900" max="<?= date('Y') ?>" required
                    value="<?= htmlspecialchars($_ev['anio_inicio'] ?? '') ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit"><?= Icons::svg('save') ?> Guardar cambios</button>
            <button type="button" class="btn-secondary" onclick="document.getElementById('dlg-editar').close()"><?= Icons::svg('x') ?> Cancelar</button>
        </div>
    </form>
</dialog>

<?php if ($_editarAbierto): ?>
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('dlg-editar').showModal());</script>
<?php endif; ?>
