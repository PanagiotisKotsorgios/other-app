<?php

declare(strict_types=1);

define('ROOT_PATH_BOOTSTRAP', dirname(__DIR__));

// Load Composer autoloader
require ROOT_PATH_BOOTSTRAP . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH_BOOTSTRAP);
$dotenv->safeLoad();

// App config (defines constants)
require ROOT_PATH_BOOTSTRAP . '/config/app.php';

// Database class (not autoloaded, loaded explicitly)
require ROOT_PATH_BOOTSTRAP . '/config/database.php';

// Start session
App\Core\Session::start();

// Dispatch route
$uri    = $_SERVER['REQUEST_URI'];
$base   = parse_url(APP_URL, PHP_URL_PATH);
if ($base && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}
if (empty($uri)) $uri = '/';

$method = $_SERVER['REQUEST_METHOD'];

/** @var App\Core\Router $router */
$router = require ROOT_PATH_BOOTSTRAP . '/app/routes.php';

// Root redirect
if ($uri === '/' || $uri === '') {
    header('Location: ' . APP_URL . '/auth/login');
    exit;
}

$router->dispatch($method, $uri);
