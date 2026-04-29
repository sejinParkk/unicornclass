<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class MemberRepository
{
    /** 휴대폰으로 회원 조회 */
    public function findByPhone(string $phone): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_member WHERE mb_phone = ? LIMIT 1',
            [$phone]
        );
    }

    /** 휴대폰 + mb_id 일치 여부 (비밀번호 찾기 선검증) */
    public function findByIdAndPhone(string $mbId, string $phone): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_member WHERE mb_id = ? AND mb_phone = ? LIMIT 1',
            [$mbId, $phone]
        );
    }

    /** 휴대폰 중복 여부 */
    public function existsByPhone(string $phone): bool
    {
        $row = DB::selectOne(
            'SELECT 1 FROM lc_member WHERE mb_phone = ? LIMIT 1',
            [$phone]
        );
        return $row !== null;
    }

    /** mb_id로 회원 조회 */
    public function findByMbId(string $mbId): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_member WHERE mb_id = ? LIMIT 1',
            [$mbId]
        );
    }

    /** 이메일로 회원 조회 */
    public function findByEmail(string $email): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_member WHERE mb_email = ? LIMIT 1',
            [$email]
        );
    }

    /** mb_id 중복 여부 */
    public function existsById(string $mbId): bool
    {
        $row = DB::selectOne(
            'SELECT 1 FROM lc_member WHERE mb_id = ? LIMIT 1',
            [$mbId]
        );
        return $row !== null;
    }

    /** 이메일 중복 여부 */
    public function existsByEmail(string $email): bool
    {
        $row = DB::selectOne(
            'SELECT 1 FROM lc_member WHERE mb_email = ? LIMIT 1',
            [$email]
        );
        return $row !== null;
    }

    /** 회원 신규 등록. 생성된 member_idx 반환 */
    public function create(array $data): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_member
                (mb_id, mb_password, mb_name, mb_phone, mb_email, signup_type, mb_mailling, mb_sms)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['mb_id'],
                $data['mb_password'],
                $data['mb_name'],
                $data['mb_phone'] ?? null,
                $data['mb_email'],
                $data['signup_type'] ?? 'email',
                $data['mb_mailling'] ?? 0,
                $data['mb_sms'] ?? 0,
            ]
        );
    }

    // =========================================================================
    // 회원 정보 수정
    // =========================================================================

    /** 기본 정보 수정 (이름·이메일·연락처·수신 동의) */
    public function updateProfile(int $memberIdx, array $data): void
    {
        DB::execute(
            'UPDATE lc_member
             SET mb_name = ?, mb_email = ?, mb_phone = ?, mb_mailling = ?, mb_sms = ?
             WHERE member_idx = ?',
            [
                $data['mb_name'],
                $data['mb_email'] ?: null,
                $data['mb_phone'] ?: null,
                (int) $data['mb_mailling'],
                (int) $data['mb_sms'],
                $memberIdx,
            ]
        );
    }

    /** 비밀번호 변경 */
    public function updatePassword(int $memberIdx, string $hashedPassword): void
    {
        DB::execute(
            'UPDATE lc_member SET mb_password = ? WHERE member_idx = ?',
            [$hashedPassword, $memberIdx]
        );
    }

    /** 이메일 중복 여부 (자신 제외) */
    public function existsByEmailExcept(string $email, int $exceptMemberIdx): bool
    {
        $row = DB::selectOne(
            'SELECT 1 FROM lc_member WHERE mb_email = ? AND member_idx != ? LIMIT 1',
            [$email, $exceptMemberIdx]
        );
        return $row !== null;
    }

    /** 연락처 중복 여부 (자신 제외) */
    public function existsByPhoneExcept(string $phone, int $exceptMemberIdx): bool
    {
        $row = DB::selectOne(
            'SELECT 1 FROM lc_member WHERE mb_phone = ? AND member_idx != ? LIMIT 1',
            [$phone, $exceptMemberIdx]
        );
        return $row !== null;
    }

    // =========================================================================
    // 로그인 실패 카운트
    // =========================================================================

    /** 실패 횟수 +1 후 새 값 반환 */
    public function incrementLoginFail(int $memberIdx): int
    {
        DB::execute(
            'UPDATE lc_member SET login_fail_count = login_fail_count + 1 WHERE member_idx = ?',
            [$memberIdx]
        );
        return (int) (DB::selectOne(
            'SELECT login_fail_count FROM lc_member WHERE member_idx = ? LIMIT 1',
            [$memberIdx]
        )['login_fail_count'] ?? 0);
    }

    /** 로그인 성공 시 실패 횟수 초기화 */
    public function resetLoginFail(int $memberIdx): void
    {
        DB::execute(
            'UPDATE lc_member SET login_fail_count = 0 WHERE member_idx = ?',
            [$memberIdx]
        );
    }

    /** 실패 5회 도달 시 계정 잠금 */
    public function lockByLoginFail(int $memberIdx): void
    {
        DB::execute(
            'UPDATE lc_member SET is_active = 0, leave_at = NULL WHERE member_idx = ?',
            [$memberIdx]
        );
    }

    // =========================================================================
    // 관리자 전용
    // =========================================================================

    /**
     * 관리자 회원 목록 (검색·필터·페이지네이션)
     *
     * @return array{list: list<array>, total: int}
     */
    public function getAdminList(array $filters, int $page, int $limit): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = '(m.mb_id LIKE ? OR m.mb_name LIKE ? OR m.mb_email LIKE ?)';
            $like     = '%' . $filters['q'] . '%';
            $params   = array_merge($params, [$like, $like, $like]);
        }

        if (($filters['status'] ?? '') === 'active') {
            $where[] = 'm.is_active = 1';
        } elseif (($filters['status'] ?? '') === 'dormant') {
            $where[] = 'm.is_active = 0 AND m.leave_at IS NULL';
        } elseif (($filters['status'] ?? '') === 'withdrawn') {
            $where[] = 'm.is_active = 0 AND m.leave_at IS NOT NULL';
        }

        if (!empty($filters['signup_type'])) {
            $where[]  = 'm.signup_type = ?';
            $params[] = $filters['signup_type'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) (DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM lc_member m $whereSql",
            $params
        )['cnt'] ?? 0);

        $offset = ($page - 1) * $limit;
        $list   = DB::select(
            "SELECT m.*,
                    (SELECT COUNT(*) FROM lc_enroll e WHERE e.member_idx = m.member_idx) AS enroll_count,
                    (SELECT COALESCE(SUM(o.amount), 0) FROM lc_order o
                     WHERE o.member_idx = m.member_idx AND o.status = 'paid') AS total_paid
             FROM lc_member m
             $whereSql
             ORDER BY m.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );

        return ['list' => $list, 'total' => $total];
    }

    /** member_idx로 회원 조회 */
    public function findByIdx(int $memberIdx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_member WHERE member_idx = ? LIMIT 1',
            [$memberIdx]
        );
    }

    /**
     * 회원 상태 변경
     *
     * @param string $status 'active' | 'dormant' | 'withdrawn'
     */
    public function updateStatus(int $memberIdx, string $status): void
    {
        if ($status === 'active') {
            DB::execute(
                'UPDATE lc_member SET is_active = 1, leave_at = NULL, login_fail_count = 0 WHERE member_idx = ?',
                [$memberIdx]
            );
        } elseif ($status === 'dormant') {
            DB::execute(
                'UPDATE lc_member SET is_active = 0, leave_at = NULL WHERE member_idx = ?',
                [$memberIdx]
            );
        } elseif ($status === 'withdrawn') {
            DB::execute(
                'UPDATE lc_member SET is_active = 0, leave_at = NOW() WHERE member_idx = ?',
                [$memberIdx]
            );
        }
    }

    /** 회원의 수강 이력 */
    public function getEnrollList(int $memberIdx): array
    {
        return DB::select(
            "SELECT e.*, c.title AS class_title, c.type AS class_type
             FROM lc_enroll e
             JOIN lc_class c ON c.class_idx = e.class_idx
             WHERE e.member_idx = ?
             ORDER BY e.enrolled_at DESC",
            [$memberIdx]
        );
    }

    /** 회원의 결제 이력 */
    public function getOrderList(int $memberIdx): array
    {
        return DB::select(
            "SELECT o.*, c.title AS class_title
             FROM lc_order o
             JOIN lc_class c ON c.class_idx = o.class_idx
             WHERE o.member_idx = ?
             ORDER BY o.created_at DESC",
            [$memberIdx]
        );
    }
}
