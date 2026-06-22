<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!doctype html>
<html lang="es">
  <head>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">

    <meta charset="UTF-8" />
    <title>Login</title>
  </head>
  <body>


    <?php if (!empty($error)): ?>
    <p style="color: red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

   <form class="login" method="POST" action="<?= BASE_URL ?>/index.php?url=login">
    <input type="hidden" name="csrf_token" value="<?= SessionHelper::generarCsrf() ?>">
    
    <h2>Iniciar sesión</h2>

    <label>Email
        <input type="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </label>

    <label>Contraseña
        <input type="password" name="password" required>
    </label>

    <button type="submit">Entrar</button>
</form>
  </body>
</html>
