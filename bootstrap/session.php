<?php

declare(strict_types=1);

// 세션 보안 설정 (세션 시작 전 적용)
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', '7200');  // 2시간

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}
