<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\MemberRepository;

class AuthController
{
    private MemberRepository $repo;

    public function __construct()
    {
        $this->repo = new MemberRepository();
    }

    // -------------------------------------------------------------------------
    // GET /login
    // -------------------------------------------------------------------------
    public function loginForm(): void
    {
        if (Auth::isMember()) {
            header('Location: /');
            exit;
        }

        $csrfToken = Csrf::token();
        $error     = null;

        require VIEW_PATH . '/pages/auth/login.php';
    }

    // -------------------------------------------------------------------------
    // POST /login
    // -------------------------------------------------------------------------
    public function login(): void
    {
        Csrf::verify();

        $mbId     = trim($_POST['mb_id'] ?? '');
        $password = $_POST['mb_password'] ?? '';
        $redirect = $_POST['redirect'] ?? '/';

        $member = $this->repo->findByMbId($mbId);

        if (!$member) {
            $this->renderLoginError('아이디 또는 비밀번호가 올바르지 않습니다.');
            return;
        }

        if (!$member['mb_password'] || !password_verify($password, $member['mb_password'])) {
            $this->renderLoginError('아이디 또는 비밀번호가 올바르지 않습니다.');
            return;
        }

        if (!$member['is_active']) {
            $this->renderLoginError('탈퇴 또는 정지된 계정입니다.');
            return;
        }

        Auth::loginMember($member);

        // 안전한 리다이렉트 (외부 URL 방지)
        $redirect = $this->safeRedirect($redirect);
        header("Location: {$redirect}");
        exit;
    }

    // -------------------------------------------------------------------------
    // POST /logout
    // -------------------------------------------------------------------------
    public function logout(): void
    {
        Csrf::verify();
        Auth::logoutMember();
        header('Location: /');
        exit;
    }

    // -------------------------------------------------------------------------
    // GET /register  — STEP 1: 휴대폰 인증 | STEP 2: 정보 입력
    // -------------------------------------------------------------------------
    public function registerForm(): void
    {
        if (Auth::isMember()) {
            header('Location: /');
            exit;
        }

        $csrfToken     = Csrf::token();
        $errors        = [];
        $old           = [];
        $verifiedPhone = $_SESSION['sms_verified_register'] ?? null;

        require VIEW_PATH . '/pages/auth/register.php';
    }

    // -------------------------------------------------------------------------
    // POST /register
    // -------------------------------------------------------------------------
    public function register(): void
    {
        Csrf::verify();

        // 전화번호 인증 세션 확인
        $verifiedPhone = $_SESSION['sms_verified_register'] ?? null;
        if (!$verifiedPhone) {
            header('Location: /register');
            exit;
        }

        $data = [
            'mb_id'        => trim($_POST['mb_id'] ?? ''),
            'mb_password'  => $_POST['mb_password'] ?? '',
            'mb_password2' => $_POST['mb_password2'] ?? '',
            'mb_name'      => trim($_POST['mb_name'] ?? ''),
            'mb_email'     => trim($_POST['mb_email'] ?? ''),
            'agree_terms'  => ($_POST['agree_terms'] ?? '0') === '1',
            'agree_marketing' => ($_POST['agree_marketing'] ?? '0') === '1',
        ];

        $errors = $this->validateRegister($data);

        if ($errors) {
            $csrfToken = Csrf::token();
            $old       = $data;
            require VIEW_PATH . '/pages/auth/register.php';
            return;
        }

        $this->repo->create([
            'mb_id'       => $data['mb_id'],
            'mb_password' => password_hash($data['mb_password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'mb_name'     => $data['mb_name'],
            'mb_email'    => $data['mb_email'],
            'mb_phone'    => $verifiedPhone,
            'signup_type' => 'email',
            'mb_sms'      => $data['agree_marketing'] ? 1 : 0,
        ]);

        unset($_SESSION['sms_verified_register']);

        $csrfToken     = Csrf::token();
        $errors        = [];
        $old           = [];
        $verifiedPhone = null;
        $registerDone  = true;
        require VIEW_PATH . '/pages/auth/register.php';
        exit;
    }

    // -------------------------------------------------------------------------
    // 소셜 로그인 (스텁 — Phase 2 확장 예정)
    // -------------------------------------------------------------------------
    public function kakaoRedirect(): void  { $this->stubSocial('kakao'); }
    public function kakaoCallback(): void  { $this->stubSocial('kakao'); }
    public function naverRedirect(): void  { $this->stubSocial('naver'); }
    public function naverCallback(): void  { $this->stubSocial('naver'); }

    // -------------------------------------------------------------------------
    // GET /find-id  — 휴대폰 SMS 인증으로 아이디 찾기 (JS 기반)
    // -------------------------------------------------------------------------
    public function findId(): void
    {
        $csrfToken = Csrf::token();
        require VIEW_PATH . '/pages/auth/find-id.php';
    }

    // -------------------------------------------------------------------------
    // GET /find-password  — 아이디+휴대폰 SMS 인증 → 비밀번호 재설정
    // -------------------------------------------------------------------------
    public function resetPassword(): void
    {
        $csrfToken    = Csrf::token();
        $verifiedInfo = $_SESSION['sms_verified_find_password'] ?? null;

        require VIEW_PATH . '/pages/auth/find-password.php';
    }

    // -------------------------------------------------------------------------
    // POST /find-password/reset  — 실제 비밀번호 변경 처리
    // -------------------------------------------------------------------------
    public function doResetPassword(): void
    {
        Csrf::verify();

        $verifiedInfo = $_SESSION['sms_verified_find_password'] ?? null;
        if (!$verifiedInfo) {
            header('Location: /find-password');
            exit;
        }

        $mbId  = $verifiedInfo['mb_id'];
        $newPw = $_POST['mb_password']  ?? '';
        $newPw2= $_POST['mb_password2'] ?? '';

        $pwRegex = '/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*\-_]).{8,}$/';
        if (!preg_match($pwRegex, $newPw)) {
            $_SESSION['reset_pw_error'] = '영문 + 숫자 + 특수문자 포함 8자 이상이어야 합니다.';
            header('Location: /find-password');
            exit;
        }
        if ($newPw !== $newPw2) {
            $_SESSION['reset_pw_error'] = '비밀번호가 일치하지 않습니다.';
            header('Location: /find-password');
            exit;
        }

        $member = $this->repo->findByMbId($mbId);
        if ($member) {
            $this->repo->updatePassword(
                $member['member_idx'],
                password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12])
            );
            unset($_SESSION['sms_verified_find_password'], $_SESSION['reset_pw_error']);
            header('Location: /login?reset=1');
            exit;
        }

        $_SESSION['reset_pw_error'] = '처리 중 오류가 발생했습니다.';
        header('Location: /find-password');
        exit;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function renderLoginError(string $message): void
    {
        $csrfToken = Csrf::token();
        $error     = $message;
        require VIEW_PATH . '/pages/auth/login.php';
    }

    private function validateRegister(array $data): array
    {
        $errors = [];

        if (!preg_match('/^[a-z0-9]{4,20}$/', $data['mb_id'])) {
            $errors['mb_id'] = '아이디는 소문자 영문/숫자 4~20자여야 합니다.';
        } elseif ($this->repo->existsById($data['mb_id'])) {
            $errors['mb_id'] = '이미 사용 중인 아이디입니다.';
        }

        $pwRegex = '/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*\-_]).{8,}$/';
        if (!preg_match($pwRegex, $data['mb_password'])) {
            $errors['mb_password'] = '영문 + 숫자 + 특수문자 포함 8자 이상이어야 합니다.';
        } elseif ($data['mb_password'] !== $data['mb_password2']) {
            $errors['mb_password2'] = '비밀번호가 일치하지 않습니다.';
        }

        if (!preg_match('/^[가-힣a-zA-Z]{2,20}$/', $data['mb_name'])) {
            $errors['mb_name'] = '이름은 한글/영문 2~20자여야 합니다.';
        }

        if (!filter_var($data['mb_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['mb_email'] = '올바른 이메일 형식이 아닙니다.';
        } elseif ($this->repo->existsByEmail($data['mb_email'])) {
            $errors['mb_email'] = '이미 사용 중인 이메일입니다.';
        }

        if (!($data['agree_terms'] ?? false)) {
            $errors['agree_terms'] = '필수 약관에 동의해주세요.';
        }

        return $errors;
    }

    private function safeRedirect(string $url): string
    {
        // 같은 도메인 내 경로만 허용
        if (!str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return '/';
        }
        return $url;
    }

    private function stubSocial(string $provider): void
    {
        http_response_code(501);
        echo "{$provider} 소셜 로그인은 준비 중입니다.";
    }
}
