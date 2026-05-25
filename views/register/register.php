<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>
    <h2>Crear usuario</h2>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
        <p style="color:green;"><?= $exito ?></p>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/register">
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
</body>
</html>