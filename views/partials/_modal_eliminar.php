<dialog id="dlg-eliminar" aria-labelledby="dlg-eliminar-titulo" onclick="if (event.target === this) { cerrarEliminar(); }">
  <h3 id="dlg-eliminar-titulo" style="color:#c62828">Eliminar colaborador</h3>
  <p><strong>Esta acción es irreversible.</strong><br>
    El colaborador será desactivado y dejará de aparecer en el sistema.</p>
  <p>Para confirmar, escribe exactamente el nombre del colaborador:</p>
  <p><strong id="del-nombre-display" style="font-size:14px"></strong></p>

  <form method="POST" action="<?= BASE_URL ?>/colaborador/eliminar" id="form-eliminar">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="id" id="del-id">

    <div class="form-row">
        <div class="form-group">
            <label for="del-confirmacion">Nombre para confirmar</label>
            <input type="text" id="del-confirmacion" placeholder="Escribe el nombre aquí"
                oninput="verificarNombreEliminar()" autofocus>
        </div>
    </div>

    <div class="form-actions">
      <button type="submit" id="btn-confirmar-eliminar" disabled class="btn-danger">
        <?= Icons::svg('trash') ?> Confirmar eliminación
      </button>
      <button type="button" class="btn-secondary" onclick="cerrarEliminar()"><?= Icons::svg('x') ?> Cancelar</button>
    </div>
  </form>
</dialog>
