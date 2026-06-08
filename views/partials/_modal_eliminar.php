<dialog
	id="dlg-eliminar"
	onclick="
		if (event.target === this) {
			cerrarEliminar();
		}
	"
>
	<h3 style="margin-top: 0; color: #ef0606">Eliminar colaborador</h3>
	<p>
		<strong>Esta acción es irreversible.</strong><br />
		El colaborador será desactivado y dejará de aparecer en el sistema.
	</p>
	<p>Para confirmar, escribe exactamente el nombre del colaborador:</p>
	<p>
		<strong id="del-nombre-display" style="font-size: 14px"></strong>
	</p>

	<input
		type="text"
		id="del-confirmacion"
		placeholder="Escribe el nombre aquí"
		oninput="verificarNombre()"
		style="width: 100%; box-sizing: border-box; margin-bottom: 8px"
	/>

	<form
		method="POST"
		action="<?= BASE_URL ?>/colaborador/eliminar"
		id="form-eliminar"
	>
		<input type="hidden" name="id" id="del-id" />
		<div style="display: flex; gap: 8px">
			<button
				type="submit"
				id="btn-confirmar-eliminar"
				disabled
				style="
					background: #ef0606;
					color: #fff;
					border: none;
					padding: 4px 12px;
					cursor: pointer;
				"
			>
				Confirmar eliminación
			</button>
			<button type="button" onclick="cerrarEliminar()">Cancelar</button>
		</div>
	</form>
</dialog>

<script>
	let _nombreEliminar = "";

	function verificarNombre() {
		const val = document.getElementById("del-confirmacion").value;
		document.getElementById("btn-confirmar-eliminar").disabled =
			val !== _nombreEliminar;
	}

	function cerrarEliminar() {
		document.getElementById("dlg-eliminar").close();
		document.getElementById("del-confirmacion").value = "";
		document.getElementById("btn-confirmar-eliminar").disabled = true;
	}
</script>
