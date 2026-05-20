<?php

declare(strict_types=1);

define('ROOT_PATH_BOOTSTRAP', dirname(__DIR__));

// Always log PHP errors to the error_log (Apache stderr → docker logs)
ini_set('log_errors', '1');
ini_set('display_errors', '0'); // never show raw errors to users

// Global exception + error handler — catches everything not caught by controllers
set_exception_handler(function (\Throwable $e): void {
    $debug = defined('APP_DEBUG') && APP_DEBUG;
    $msg   = sprintf('[%s] Uncaught %s: %s in %s:%d',
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    error_log($msg);
    error_log($e->getTraceAsString());

    if (ob_get_level() > 0) {
        ob_clean();
    }
    http_response_code(500);
    if ($debug) {
        echo '<!DOCTYPE html><html><body>'
            . '<h1>500 — Internal Server Error</h1>'
            . '<pre>' . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString()) . '</pre>'
            . '</body></html>';
    } else {
        echo '<!DOCTYPE html><html><body>'
            . '<h1>500 — Internal Server Error</h1>'
            . '<p>Something went wrong. Please try again later.</p>'
            . '</body></html>';
    }
    exit;
});

// Load Composer autoloader
require ROOT_PATH_BOOTSTRAP . '/vendor/autoload.php';

// Load .env (safeLoad never throws — missing/unreadable file is silently skipped)
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH_BOOTSTRAP);
$dotenv->safeLoad();

// Auto-detect public URL from the incoming request.
// This makes the app work transparently behind ngrok, any reverse proxy,
// or direct localhost access without manual configuration.
// Overrides whatever APP_URL was in .env so form actions / redirects always
// use the correct scheme+host the browser is actually on.
if (!empty($_SERVER['HTTP_HOST'])) {
    // X-Forwarded-Proto is sent by ngrok and other proxies to indicate the
    // original scheme (https) even though Apache receives plain HTTP.
    $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'http';
    $scheme = ($scheme === 'https') ? 'https' : 'http';
    $_ENV['APP_URL'] = $scheme . '://' . $_SERVER['HTTP_HOST'];
}

// App config (defines constants — reads APP_URL from $_ENV set above)
require ROOT_PATH_BOOTSTRAP . '/config/app.php';

// Database class (not autoloaded, loaded explicitly)
require ROOT_PATH_BOOTSTRAP . '/config/database.php';

// Start session
App\Core\Session::start();

// Dispatch route
$uri    = $_SERVER['REQUEST_URI'];
$base   = parse_url(APP_URL, PHP_URL_PATH);
if ($base && $base !== '/' && str_starts_with($uri, $base)) {
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
