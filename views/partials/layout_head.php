<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?? 'Próspera' ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background-color: #e8f5f3;
            color: #1a2f5e;
            min-height: 100vh;
        }

        /* NAV */
        nav {
            background-color: #1a2f5e;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        nav a {
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        nav a:hover {
            background-color: #3db8a0;
        }

        nav strong {
            color: #3db8a0;
            margin-left: 8px;
        }

        nav span {
            color: #a8d5cc;
            margin-left: auto;
        }

        nav form {
            display: inline;
        }

        nav button[type="submit"] {
            background-color: transparent;
            border: 1px solid #a8d5cc;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
        }

        nav button[type="submit"]:hover {
            background-color: #c62828;
            border-color: #c62828;
        }

        /* CONTENIDO */
        .contenido {
            padding: 24px;
        }

        /* Ritmo vertical: separacion consistente entre bloques de nivel superior,
           tanto en el contenido principal como dentro de los modales. */
        .contenido > * + *,
        dialog > * + * {
            margin-top: 16px;
        }

        p {
            margin-bottom: 8px;
        }

        h2 {
            color: #1a2f5e;
            margin-bottom: 12px;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h3 {
            color: #1a2f5e;
            margin-bottom: 10px;
        }

        hr {
            border: none;
            border-top: 2px solid #3db8a0;
            margin-bottom: 20px;
        }

        /* MENSAJES */
        .error {
            color: #c62828;
            background: #ffebee;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        .ok {
            color: #1a5e3a;
            background: #d0f0ea;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        .warning {
            color: #b25f00;
            font-weight: bold;
        }

        /* ICONOS */
        .icon {
            vertical-align: -3px;
            margin-right: 4px;
            flex-shrink: 0;
        }

        button .icon,
        a .icon {
            margin-right: 5px;
        }

        /* TABLA */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        th, td {
            border: 1px solid #c8e6e0;
            padding: 6px 10px;
        }

        th {
            background-color: #1a2f5e;
            color: #ffffff;
            text-align: center;
        }

        .num {
            text-align: right;
        }

        tfoot td {
            background-color: #d0f0ea;
            font-weight: bold;
            color: #1a2f5e;
        }

        tbody tr:hover {
            background-color: #f0faf8;
        }

        /* CARDS */
        .cards {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .card {
            border: 1px solid #a8d5cc;
            padding: 16px 24px;
            min-width: 160px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s;
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(61,184,160,0.2);
        }

        .card a {
            display: block;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            color: #1a2f5e;
        }

        .card p {
            margin: 4px 0 0;
            color: #555;
            font-size: 12px;
        }

        /* FORMULARIOS GLOBALES */
        fieldset {
            border: 1px solid #a8d5cc;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 14px;
            background: #ffffff;
        }

        legend {
            font-weight: bold;
            color: #1a2f5e;
            padding: 0 6px;
        }

        /* FORM SYSTEM */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            flex: 1 1 200px;
        }

        .form-group label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #1a2f5e;
            margin-bottom: 4px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
        }

        .form-actions {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }

        .form-narrow {
            max-width: 360px;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="password"],
        select {
            padding: 4px 8px;
            border: 1px solid #a8d5cc;
            border-radius: 6px;
            font-size: 13px;
            color: #333;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #3db8a0;
            box-shadow: 0 0 0 3px rgba(61,184,160,0.2);
        }

        button {
            padding: 5px 12px;
            background-color: #3db8a0;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
        }

        button:hover {
            background-color: #2e9e88;
        }

        .btn-secondary {
            background: transparent;
            color: #1a2f5e;
            border: 1px solid #a8d5cc;
        }

        .btn-secondary:hover {
            background: #e8f5f3;
        }

        .btn-danger {
            background-color: #c62828;
        }

        .btn-danger:hover {
            background-color: #a81f1f;
        }

        /* DIALOG */
        dialog {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            border: none;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.2);
            min-width: 300px;
            max-width: min(90vw, 640px);
            max-height: 85vh;
            overflow-y: auto;
        }

        dialog::backdrop {
            background: rgba(0,0,0,0.45);
        }
    </style>
</head>
<body>
<?php
$csrf = SessionHelper::generarCsrf();
require BASE_PATH . '/views/partials/navbar.php';
?>
<div class="contenido">