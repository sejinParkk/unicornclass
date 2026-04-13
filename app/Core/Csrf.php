<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /** 세션에서 CSRF 토큰을 가져오거나 새로 생성합니다. */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /** POST 요청에서 토큰을 검증합니다 (form-data 또는 X-CSRF-Token 헤더). 실패 시 403으로 종료합니다. */
    public static function verify(): void
    {
        // form-data 우선, 없으면 X-CSRF-Token 헤더에서 추출
        $submitted = $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';
        $stored = $_SESSION[self::SESSION_KEY] ?? '';

        if (!$stored || !hash_equals($stored, $submitted)) {
            http_response_code(403);
            $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
            if ($isApi) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '잘못된 요청입니다. 다시 시도해주세요.']);
            } else {
                echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8"><title>403</title></head>'
                   . '<body style="text-align:center;padding:60px;font-family:sans-serif">'
                   . '<h2>잘못된 요청입니다.</h2><p><a href="javascript:history.back()">← 돌아가기</a></p>'
                   . '</body></html>';
            }
            exit;
        }
    }

    /** 뷰에서 사용할 hidden input HTML을 반환합니다. */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token()) . '">';
    }
}
