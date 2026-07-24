<!doctype html>
<html lang="es">
	<head>
		<meta charset="UTF-8" />
		<link rel="stylesheet" href="../assets/css/404.css" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>404</title>
		<style></style>
	</head>
	<body>
		<div class="container">
			<div class="left">
				<div class="code">404</div>
				<p class="title">Página no encontrada</p>
				<p class="message">La página que buscas no existe.</p>
				<a
					href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>/home"
					class="back"
					>Volver al inicio</a
				>
			</div>
			<div class="right">
				<img
					src="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/img/404.webp"
					alt="404"
				/>
			</div>
		</div>
	</body>
</html>
