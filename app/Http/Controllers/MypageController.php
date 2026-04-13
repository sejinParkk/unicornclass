<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\MemberRepository;

class MypageController
{
    private MemberRepository $memberRepo;

    public function __construct()
    {
        Auth::requireLogin();
        $this->memberRepo = new MemberRepository();
    }

    // =========================================================================
    // GET /mypage/profile
    // =========================================================================
    public function profileForm(): void
    {
        $session    = Auth::member();
        $member     = $this->memberRepo->findByIdx((int) $session['member_idx']);
        if (!$member) { http_response_code(404); exit; }

        $csrfToken  = Csrf::token();
        $errors     = [];
        $pwErrors   = [];
        $saved      = isset($_GET['saved']);
        $pwChanged  = isset($_GET['pw_changed']);

        require VIEW_PATH . '/pages/mypage/profile.php';
    }

    // =========================================================================
    // POST /mypage/profile
    // =========================================================================
    public function profileUpdate(): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];
        $member    = $this->memberRepo->findByIdx($memberIdx);
        if (!$member) { http_response_code(404); exit; }

        $action = $_POST['_action'] ?? 'profile';

        if ($action === 'password') {
            $this->handlePasswordChange($memberIdx, $member);
            return;
        }

        // ── 기본 정보 수정 ──
        $name      = trim($_POST['mb_name'] ?? '');
        $email     = trim($_POST['mb_email'] ?? '');
        $mailling  = isset($_POST['mb_mailling']) ? 1 : 0;
        $sms       = isset($_POST['mb_sms'])      ? 1 : 0;

        $errors = $this->validateProfile($name, $email, $memberIdx);

        if ($errors) {
            $csrfToken = Csrf::token();
            $saved     = false;
            $pwChanged = false;
            $pwErrors  = [];
            require VIEW_PATH . '/pages/mypage/profile.php';
            return;
        }

        $this->memberRepo->updateProfile($memberIdx, [
            'mb_name'    => $name,
            'mb_email'   => $email,
            'mb_mailling'=> $mailling,
            'mb_sms'     => $sms,
        ]);

        // 세션 갱신
        $_SESSION['_member']['mb_name']  = $name;
        $_SESSION['_member']['mb_email'] = $email;

        header('Location: /mypage/profile?saved=1');
        exit;
    }

    // =========================================================================
    // 비밀번호 변경 처리
    // =========================================================================
    private function handlePasswordChange(int $memberIdx, array $member): void
    {
        // 소셜 전용 계정은 비밀번호 없음
        if (empty($member['mb_password'])) {
            header('Location: /mypage/profile?pw_error=' . urlencode('소셜 계정은 비밀번호를 변경할 수 없습니다.'));
            exit;
        }

        $currentPw = $_POST['current_password'] ?? '';
        $newPw     = $_POST['new_password']     ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';

        $pwErrors = $this->validatePassword($member, $currentPw, $newPw, $confirmPw);

        if ($pwErrors) {
            $session   = Auth::member();
            $member    = $this->memberRepo->findByIdx($memberIdx);
            $csrfToken = Csrf::token();
            $errors    = [];
            $saved     = false;
            $pwChanged = false;
            require VIEW_PATH . '/pages/mypage/profile.php';
            return;
        }

        $hashed = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->memberRepo->updatePassword($memberIdx, $hashed);

        header('Location: /mypage/profile?pw_changed=1');
        exit;
    }

    // =========================================================================
    // 유효성 검사
    // =========================================================================
    private function validateProfile(string $name, string $email, int $memberIdx): array
    {
        $errors = [];

        if ($name === '') {
            $errors['mb_name'] = '이름을 입력해주세요.';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 20) {
            $errors['mb_name'] = '이름은 2~20자로 입력해주세요.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['mb_email'] = '이메일 형식이 올바르지 않습니다.';
        } elseif ($email !== '' && $this->memberRepo->existsByEmailExcept($email, $memberIdx)) {
            $errors['mb_email'] = '이미 사용 중인 이메일입니다.';
        }

        return $errors;
    }

    private function validatePassword(array $member, string $current, string $new, string $confirm): array
    {
        $errors = [];

        if ($current === '') {
            $errors['current_password'] = '현재 비밀번호를 입력해주세요.';
        } elseif (!password_verify($current, $member['mb_password'])) {
            $errors['current_password'] = '현재 비밀번호가 올바르지 않습니다.';
        }

        if ($new === '') {
            $errors['new_password'] = '새 비밀번호를 입력해주세요.';
        } elseif (strlen($new) < 8) {
            $errors['new_password'] = '새 비밀번호는 8자 이상이어야 합니다.';
        }

        if ($confirm === '') {
            $errors['confirm_password'] = '비밀번호 확인을 입력해주세요.';
        } elseif ($new !== '' && $new !== $confirm) {
            $errors['confirm_password'] = '새 비밀번호가 일치하지 않습니다.';
        }

        return $errors;
    }

    // =========================================================================
    // 미구현 마이페이지 라우트 (stub)
    // =========================================================================
    public function myClass(): void    { http_response_code(501); echo '준비 중'; }
    public function wishlist(): void   { http_response_code(501); echo '준비 중'; }
    public function orders(): void     { http_response_code(501); echo '준비 중'; }
    public function orderShow(): void  { http_response_code(501); echo '준비 중'; }
    public function qnaList(): void    { http_response_code(501); echo '준비 중'; }
    public function qnaShow(): void    { http_response_code(501); echo '준비 중'; }
    public function reviews(): void    { http_response_code(501); echo '준비 중'; }
    public function reviewForm(): void { http_response_code(501); echo '준비 중'; }
    public function reviewStore(): void{ http_response_code(501); echo '준비 중'; }
    public function withdrawForm(): void{ http_response_code(501); echo '준비 중'; }
    public function withdraw(): void   { http_response_code(501); echo '준비 중'; }
}
