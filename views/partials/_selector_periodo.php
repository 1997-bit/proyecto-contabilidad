<?php declare(strict_types=1);
/**
 * Filtro reusable de empresa + quincena + mes + año.
 *
 * Variables esperadas:
 *   $accionUrl   string  URL de destino del <form> (GET)
 *   $periodo     string  '' | '1ra_quincena' | '2da_quincena'
 *   $mes         int     0 = todos
 *   $anio        int
 *
 * Opcionales (si $empresas viene definido, se agrega el selector de empresa):
 *   $empresas    array   filas con 'id' y 'nombre'
 *   $empresaId   int     0 = todas
 */
?>
<form method="GET" action="<?= $accionUrl ?>">
    <div class="form-row">
        <?php if (isset($empresas)): ?>
        <div class="form-group">
            <label for="f_empresa">Empresa</label>
            <select id="f_empresa" name="empresa_id">
                <option value="0">Todas</option>
                <?php foreach ($empresas as $emp): ?>
                <option value="<?= $emp['id'] ?>" <?= ($empresaId ?? 0) === (int) $emp['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="f_periodo">Quincena</label>
            <select id="f_periodo" name="periodo">
                <option value="">Todas</option>
                <option value="1ra_quincena" <?= $periodo === '1ra_quincena' ? 'selected' : '' ?>>1ra quincena</option>
                <option value="2da_quincena" <?= $periodo === '2da_quincena' ? 'selected' : '' ?>>2da quincena</option>
            </select>
        </div>

        <div class="form-group">
            <label for="f_mes">Mes</label>
            <select id="f_mes" name="mes">
                <option value="0">Todos</option>
                <?php foreach (['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $i => $nm): ?>
                <option value="<?= $i+1 ?>" <?= $mes === $i+1 ? 'selected' : '' ?>><?= $nm ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="f_anio">Año</label>
            <input type="number" id="f_anio" name="anio" min="2000" max="2100" value="<?= $anio ?>">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit"><?= Icons::svg('search') ?> Filtrar</button>
    </div>
</form>
