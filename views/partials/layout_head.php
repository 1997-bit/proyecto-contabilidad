<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="es">
		<style>
			dialog {
				border: none;
				border-radius: 20px;
				padding: 24px;
				box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
			}

      dialog::backdrop {
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(1px);
        -webkit-backdrop-filter: blur(4px);
			}
			body {
				font-family: Arial, sans-serif;
				font-size: 13px;
				margin: 20px;
			}
			nav a {
				margin-right: 8px;
			}
			nav {
				margin-bottom: 8px;
			}
			h2 {
				margin-bottom: 4px;
			}
			.error {
				color: red;
				font-weight: bold;
			}
			.ok {
				color: green;
				font-weight: bold;
			}
			table {
				border-collapse: collapse;
				width: 100%;
				font-size: 12px;
			}
			th,
			td {
				border: 1px solid #888;
				padding: 4px 8px;
			}
			th {
				background: #ddd;
				text-align: center;
			}
			.num {
				text-align: right;
			}
			tfoot td {
				background: #f5f5c0;
				font-weight: bold;
			}
			.cards {
				display: flex;
				gap: 16px;
				flex-wrap: wrap;
				margin-top: 16px;
			}
			.card {
				border: 1px solid #ccc;
				padding: 16px 24px;
				min-width: 160px;
			}
			.card a {
				display: block;
				font-size: 15px;
				font-weight: bold;
				text-decoration: none;
			}
			.card p {
				margin: 4px 0 0;
				color: #555;
				font-size: 12px;
			}
		</style>
	</head>
<body>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
