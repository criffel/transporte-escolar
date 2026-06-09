<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

define('APP_NAME',       $_ENV['APP_NAME'] ?? 'Motorista Check');
define('APP_ENV',        $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG',      filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL',        $_ENV['APP_URL'] ?? 'http://localhost:8080');

define('DB_HOST', $_ENV['DB_HOST'] ?? 'db');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'motorista_check');
define('DB_USER', $_ENV['DB_USER'] ?? 'motorista_user');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 120));
define('SESSION_SECURE', filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN));

define('QR_CODE_EXPIRY_MINUTES', (int)($_ENV['QR_CODE_EXPIRY_MINUTES'] ?? 5));
define('QR_CODE_LINK_EXPIRY_HOURS', (int)($_ENV['QR_CODE_LINK_EXPIRY_HOURS'] ?? 2));

define('SMTP_HOST',     $_ENV['SMTP_HOST'] ?? '');
define('SMTP_PORT',     (int)($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_USER',     $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS',     $_ENV['SMTP_PASS'] ?? '');
define('SMTP_FROM',     $_ENV['SMTP_FROM'] ?? 'noreply@motoristacheck.com.br');
define('SMTP_FROM_NAME',$_ENV['SMTP_FROM_NAME'] ?? 'Motorista Check');

define('UPLOAD_DIR', __DIR__ . '/../public/assets/img/upload/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set('America/Sao_Paulo');
