<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!doctype html>
<html lang="es">
  <head>
    <link rel="stylesheet" href="../assets/css/login.css"/>
    <meta charset="UTF-8" />
    <title>Login</title>
  </head>
  <body>


    <?php if (!empty($error)): ?>
    <p style="color: red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form class="login" method="POST" action="<?= BASE_URL ?>/login">
<input type="hidden" name="csrf_token" value="<?= SessionHelper::generarCsrf() ?>">
        
                          <h2>Iniciar sesión</h2>

        <label
        >Email<br />
        <input
          type="email"
          name="email"
          required
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        />
      </label>
      <br /><br />
      <label
        >Contraseña<br />
        <input type="password" name="password" required />
      </label>
      <br /><br />
      <button type="submit">Entrar</button>
      <!-- CREADO: Enlace a registro (2024) - Permite usuarios nuevos auto-registrarse -->
      <br /><br />
      <p>¿No tienes cuenta? <a href="<?= BASE_URL ?>/register">Registrarse aquí</a></p>
    </form>
  </body>
</html>
