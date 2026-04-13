<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('VIEW_PATH', ROOT_PATH . '/resources/views');

// Composer 오토로드
require ROOT_PATH . '/vendor/autoload.php';

// .env 로드
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

// 에러 표시 설정 (운영: Off)
$debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : 0);
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/storage/logs/error.log');

// 세션 시작
require ROOT_PATH . '/bootstrap/session.php';
