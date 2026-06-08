<?php /** @var array $c  colaborador actual (ya descifrado) */ ?>
<dialog id="dlg-editar" onclick="if (event.target === this) this.close();">
	<h3 style="margin-top: 0">Editar colaborador</h3>

	<?php if (!empty($editarError)): ?>
	<div class="error" style="margin-bottom: 8px">
		<?php foreach ($editarError as $msg): ?>
		<p style="margin: 2px 0"><?= htmlspecialchars($msg) ?></p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<form method="POST" action="<?= BASE_URL ?>/colaborador/editar">
		<input type="hidden" name="id" id="edit-id" />
		<table style="width: auto; border: none">
			<tr>
				<td><label for="edit-nombre">Nombre completo</label></td>
				<td>
					<input
						type="text"
						id="edit-nombre"
						name="nombre_completo"
						required
						style="width: 220px"
					/>
				</td>
			</tr>
			<tr>
				<td><label for="edit-cedula">Cédula</label></td>
				<td>
					<input
						type="text"
						id="edit-cedula"
						name="cedula"
						placeholder="8-123-4567"
					/>
				</td>
			</tr>
			<tr>
				<td><label for="edit-ecivil">Estado civil</label></td>
				<td>
					<select id="edit-ecivil" name="estado_civil">
						<option value="soltero">Soltero/a</option>
						<option value="casado">Casado/a</option>
						<option value="unido">Unido/a</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="edit-cargo">Cargo</label></td>
				<td>
					<input
						type="text"
						id="edit-cargo"
						name="cargo"
						required
						style="width: 220px"
					/>
				</td>
			</tr>
			<tr>
				<td><label for="edit-salario">Salario base (B/.)</label></td>
				<td>
					<input
						type="number"
						id="edit-salario"
						name="salario_base"
						step="0.01"
						min="0.01"
						required
					/>
				</td>
			</tr>

			<tr>
				<td><label for="edit-anio">Año de inicio</label></td>
				<td>
					<input
						type="number"
						id="edit-anio"
						name="anio_inicio"
						min="1900"
						max="<?= date('Y') ?>"
						required
					/>
				</td>
			</tr>
			<tr>
				<td></td>
				<td style="display: flex; gap: 8px; padding-top: 8px">
					<button type="submit">Guardar cambios</button>
					<button
						type="button"
						onclick="document.getElementById('dlg-editar').close()"
					>
						Cancelar
					</button>
				</td>
			</tr>
		</table>
	</form>
</dialog>
