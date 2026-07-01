<!DOCTYPE html>
<html lang="es">
<head>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/register.css">
    <title>Registro</title>
</head>
<body>

    <div class="Registrohtml">

    <h2>Crear usuario</h2>

    <?php if (!empty($error)): ?>
        <p class="msg-error" style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
        <p class="msg-exito" style="color:green;"><?= $exito ?></p>
    <?php endif; ?>

    <form method="POST" action="http://localhost/proyecto-contabilidad/public/register">
        <label>Nombre<br>
            <input type="text" name="nombre" required
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </label>
        <br><br>
        <label>Email<br>
            <input type="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </label>
        <br><br>
        <label>Contraseña<br>
            <input type="password" name="password" required>
        </label>
        <br><br>
        <label>Rol<br>
            <select name="rol">
                <option value="visor">Visor</option>
                <option value="contador">Contador</option>
                <option value="admin">Admin</option>
            </select>
        </label>
        <br><br>
        <button type="submit">Crear usuario</button>
    </form>
</div>

</body>
</html>
