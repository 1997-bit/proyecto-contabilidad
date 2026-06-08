<?php
$csrf = SessionHelper::generarCsrf();
$_editarAbierto = !empty($editarError);
$_ev = $editarValores ?? [];
?>
<dialog id="dlg-editar" onclick="if (event.target === this) this.close();">
    <h3 style="margin-top:0">Editar colaborador</h3>

    <?php if (!empty($editarError)): ?>
    <div class="error" style="margin-bottom:8px">
        <?php foreach ($editarError as $msg): ?>
        <p style="margin:2px 0"><?= htmlspecialchars($msg) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/colaborador/editar">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="id" id="edit-id" value="<?= htmlspecialchars((string)($editarId ?? '')) ?>">
        <table style="width:auto;border:none">
            <tr>
                <td><label for="edit-nombre">Nombre completo</label></td>
                <td><input type="text" id="edit-nombre" name="nombre_completo" required style="width:220px"
                    value="<?= htmlspecialchars($_ev['nombre_completo'] ?? '') ?>"></td>
            </tr>
            <tr>
                <td><label for="edit-cedula">Cédula</label></td>
                <td><input type="text" id="edit-cedula" name="cedula" placeholder="8-123-4567"
                    value="<?= htmlspecialchars($_ev['cedula'] ?? '') ?>"></td>
            </tr>
            <tr>
                <td><label for="edit-ecivil">Estado civil</label></td>
                <td>
                    <select id="edit-ecivil" name="estado_civil">
                        <?php foreach (['soltero' => 'Soltero/a', 'casado' => 'Casado/a', 'unido' => 'Unido/a'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= ($_ev['estado_civil'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="edit-cargo">Cargo</label></td>
                <td><input type="text" id="edit-cargo" name="cargo" required style="width:220px"
                    value="<?= htmlspecialchars($_ev['cargo'] ?? '') ?>"></td>
            </tr>
            <tr>
                <td><label for="edit-salario">Salario base (B/.)</label></td>
                <td><input type="number" id="edit-salario" name="salario_base" step="0.01" min="0.01" required
                    value="<?= htmlspecialchars($_ev['salario_base'] ?? '') ?>"></td>
            </tr>
            <tr>
                <td><label for="edit-anio">Año de inicio</label></td>
                <td><input type="number" id="edit-anio" name="anio_inicio" min="1900" max="<?= date('Y') ?>" required
                    value="<?= htmlspecialchars($_ev['anio_inicio'] ?? '') ?>"></td>
            </tr>
            <tr>
                <td></td>
                <td style="display:flex;gap:8px;padding-top:8px">
                    <button type="submit">Guardar cambios</button>
                    <button type="button" onclick="document.getElementById('dlg-editar').close()">Cancelar</button>
                </td>
            </tr>
        </table>
    </form>
</dialog>

<?php if ($_editarAbierto): ?>
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('dlg-editar').showModal());</script>
<?php endif; ?>
