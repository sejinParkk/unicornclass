<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class SmsVerificationRepository
{
    /**
     * OTP 생성 및 저장. 생성된 코드 반환.
     * 동일 phone+purpose의 기존 미사용 코드는 만료 처리.
     */
    public function create(string $phone, string $purpose, ?string $mbId = null): string
    {
        // 기존 코드 만료
        DB::execute(
            'UPDATE lc_sms_verification SET expires_at = NOW()
             WHERE phone = ? AND purpose = ? AND is_used = 0',
            [$phone, $purpose]
        );

        $code      = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+3 minutes'));

        DB::execute(
            'INSERT INTO lc_sms_verification (phone, code, purpose, mb_id, expires_at)
             VALUES (?, ?, ?, ?, ?)',
            [$phone, $code, $purpose, $mbId, $expiresAt]
        );

        return $code;
    }

    /**
     * 인증 시도. 성공 시 true, 실패 시 false.
     * fail_count 5회 초과 시 즉시 무효화.
     */
    public function verify(string $phone, string $code, string $purpose): bool
    {
        $row = DB::selectOne(
            'SELECT * FROM lc_sms_verification
             WHERE phone = ? AND purpose = ? AND is_used = 0
               AND expires_at > NOW()
             ORDER BY idx DESC LIMIT 1',
            [$phone, $purpose]
        );

        if (!$row) {
            return false;
        }

        if ($row['fail_count'] >= 5) {
            // 무효화
            DB::execute(
                'UPDATE lc_sms_verification SET is_used = 1 WHERE idx = ?',
                [$row['idx']]
            );
            return false;
        }

        if ($row['code'] !== $code) {
            DB::execute(
                'UPDATE lc_sms_verification SET fail_count = fail_count + 1 WHERE idx = ?',
                [$row['idx']]
            );
            return false;
        }

        // 성공 — 사용 처리
        DB::execute(
            'UPDATE lc_sms_verification SET is_used = 1 WHERE idx = ?',
            [$row['idx']]
        );
        return true;
    }

    /** 오늘 동일 번호 요청 횟수 (일 5회 제한) */
    public function countTodayRequests(string $phone, string $purpose): int
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM lc_sms_verification
             WHERE phone = ? AND purpose = ? AND DATE(created_at) = CURDATE()',
            [$phone, $purpose]
        );
        return (int)($row['cnt'] ?? 0);
    }

    /** 가장 최근 미사용 OTP 조회 (개발용 — 화면에 코드 표시) */
    public function findLatestCode(string $phone, string $purpose): ?string
    {
        $row = DB::selectOne(
            'SELECT code FROM lc_sms_verification
             WHERE phone = ? AND purpose = ? AND is_used = 0 AND expires_at > NOW()
             ORDER BY idx DESC LIMIT 1',
            [$phone, $purpose]
        );
        return $row ? $row['code'] : null;
    }
}
