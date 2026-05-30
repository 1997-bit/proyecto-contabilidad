<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!doctype html>
<html lang="es">
	<head>
		<meta charset="UTF-8" />
		<title>Login</title>
	</head>
	<body>
		<h2>Iniciar sesión</h2>

		<?php if (!empty($error)): ?>
		<p style="color: red"><?= htmlspecialchars($error) ?></p>
		<?php endif; ?>

		<form method="POST" action="<?= BASE_URL ?>/login">
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
		</form>
	</body>
</html>