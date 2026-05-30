<?php
define('BASE_PATH', dirname(__DIR__));
$_ENV['DB_ENCRYPTION_KEY'] = str_repeat('ab', 32);
require_once BASE_PATH . '/services/CifradoService.php';