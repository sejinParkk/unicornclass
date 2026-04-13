<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class AdminRepository
{
    /** login_id로 관리자 조회 */
    public function findByLoginId(string $loginId): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_admin WHERE login_id = ? AND is_active = 1 LIMIT 1',
            [$loginId]
        );
    }

    /** 마지막 로그인 시각 갱신 */
    public function updateLastLogin(int $adminIdx): void
    {
        DB::execute(
            'UPDATE lc_admin SET last_login_at = NOW() WHERE admin_idx = ?',
            [$adminIdx]
        );
    }

    /** admin_idx로 관리자 조회 */
    public function findByIdx(int $adminIdx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_admin WHERE admin_idx = ? LIMIT 1',
            [$adminIdx]
        );
    }

    /** 이름·이메일 수정 */
    public function updateProfile(int $adminIdx, string $name, ?string $email): void
    {
        DB::execute(
            'UPDATE lc_admin SET name = ?, email = ? WHERE admin_idx = ?',
            [$name, $email, $adminIdx]
        );
        // 세션 이름 즉시 반영
        if (isset($_SESSION['_admin'])) {
            $_SESSION['_admin']['name'] = $name;
        }
    }

    /** 비밀번호 변경 */
    public function updatePassword(int $adminIdx, string $hashedPassword): void
    {
        DB::execute(
            'UPDATE lc_admin SET password = ? WHERE admin_idx = ?',
            [$hashedPassword, $adminIdx]
        );
    }
}
