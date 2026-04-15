<?php

declare(strict_types=1);

namespace App\Core;

/**
 * 세션 기반 인증 헬퍼.
 * 관리자(lc_admin)와 일반 회원(lc_member)의 세션을 완전히 분리합니다.
 *
 * 세션 키:
 *   $_SESSION['_admin']  — 관리자
 *   $_SESSION['_member'] — 일반 회원
 *
 * 보안: isMember() / isAdmin() 호출 시 매 요청마다 DB PK 조회로
 * 삭제·정지된 계정을 즉시 차단합니다. (PK 조회라 성능 영향 미미)
 */
class Auth
{
    // =========================================================================
    // 관리자
    // =========================================================================

    /** 관리자 로그인 처리: 세션 재생성 후 관리자 정보 저장 */
    public static function loginAdmin(array $admin): void
    {
        session_regenerate_id(true);
        $_SESSION['_admin'] = [
            'admin_idx' => (int) $admin['admin_idx'],
            'login_id'  => $admin['login_id'],
            'name'      => $admin['name'],
        ];
    }

    /** 관리자 로그아웃: 관리자 세션만 제거 */
    public static function logoutAdmin(): void
    {
        unset($_SESSION['_admin']);
    }

    /** 관리자 세션 데이터 반환. 미로그인 시 null */
    public static function admin(): ?array
    {
        return $_SESSION['_admin'] ?? null;
    }

    /** 관리자 로그인 여부 — DB로 존재·활성 상태 실시간 검증 */
    public static function isAdmin(): bool
    {
        if (!isset($_SESSION['_admin'])) {
            return false;
        }

        $adminIdx = $_SESSION['_admin']['admin_idx'] ?? 0;
        $row = DB::selectOne(
            'SELECT is_active FROM lc_admin WHERE admin_idx = ? LIMIT 1',
            [$adminIdx]
        );

        if (!$row || !$row['is_active']) {
            unset($_SESSION['_admin']);
            return false;
        }

        return true;
    }

    /** 관리자 전용 미들웨어: 미로그인 시 관리자 로그인 페이지로 리다이렉트 */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header('Location: /admin/login');
            exit;
        }
    }

    // =========================================================================
    // 일반 회원
    // =========================================================================

    /** 회원 로그인 처리: 세션 재생성 후 회원 정보 저장 */
    public static function loginMember(array $member): void
    {
        session_regenerate_id(true);
        $_SESSION['_member'] = [
            'member_idx'  => (int) $member['member_idx'],
            'mb_id'       => $member['mb_id'],
            'mb_name'     => $member['mb_name'],
            'mb_email'    => $member['mb_email'] ?? '',
            'mb_phone'    => $member['mb_phone'] ?? '',
            'signup_type' => $member['signup_type'] ?? 'email',
            'is_active'   => (int) ($member['is_active'] ?? 1),
        ];
    }

    /** 회원 로그아웃: 회원 세션만 제거 */
    public static function logoutMember(): void
    {
        unset($_SESSION['_member']);
    }

    /** 회원 세션 데이터 반환. 미로그인 시 null */
    public static function member(): ?array
    {
        return $_SESSION['_member'] ?? null;
    }

    /** 회원 로그인 여부 — DB로 존재·활성 상태 실시간 검증 */
    public static function isMember(): bool
    {
        if (!isset($_SESSION['_member'])) {
            return false;
        }

        $memberIdx = $_SESSION['_member']['member_idx'] ?? 0;
        $row = DB::selectOne(
            'SELECT is_active FROM lc_member WHERE member_idx = ? LIMIT 1',
            [$memberIdx]
        );

        if (!$row || !$row['is_active']) {
            // 탈퇴·정지 계정 → 세션 즉시 제거
            unset($_SESSION['_member']);
            return false;
        }

        return true;
    }

    /** 회원 전용 미들웨어: 미로그인 시 로그인 페이지로 리다이렉트 */
    public static function requireLogin(): void
    {
        if (!self::isMember()) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/');
            header("Location: /login?redirect={$redirect}");
            exit;
        }
    }
}
