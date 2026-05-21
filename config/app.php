<?php

define('APP_NAME', 'SoftSystems Partnership Portal');
define('APP_ENV',   $_ENV['APP_ENV']   ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL',   $_ENV['APP_URL']   ?? 'http://localhost');

define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/uploads');
define('COMMISSION_RATE', (float)($_ENV['COMMISSION_RATE'] ?? 10));
define('UPLOAD_MAX_SIZE', (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760));

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

date_default_timezone_set('Europe/Athens');
