<?php declare(strict_types=1);

// DEBUG
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/core/App.php';
require_once BASE_PATH . '/config/Conexion.php';

spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/controllers/',
        BASE_PATH . '/models/',
        BASE_PATH . '/services/',
        BASE_PATH . '/middleware/',
        BASE_PATH . '/core/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$url = $_GET['url'] ?? '';
$url = trim($url, '/');

$app = new App();
$app->run($url);

var_dump($_GET['url'] ?? 'NO URL'); die;