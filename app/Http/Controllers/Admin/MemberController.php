<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\MemberRepository;

class MemberController
{
    private MemberRepository $memberRepo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->memberRepo = new MemberRepository();
    }

    // =========================================================================
    // GET /admin/members
    // =========================================================================
    public function index(): void
    {
        $filters = [
            'q'           => trim($_GET['q'] ?? ''),
            'status'      => $_GET['status'] ?? '',
            'signup_type' => $_GET['signup_type'] ?? '',
        ];
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;

        $result     = $this->memberRepo->getAdminList($filters, $page, $limit);
        $members    = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $pageTitle  = '회원 관리';
        $activeMenu = 'members';
        ob_start();
        require VIEW_PATH . '/pages/admin/members/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/members/{mb_idx}
    // =========================================================================
    public function show(string $mbIdx): void
    {
        $memberIdx = (int) $mbIdx;
        $member    = $this->memberRepo->findByIdx($memberIdx);
        if (!$member) {
            http_response_code(404);
            exit;
        }

        $enrolls   = $this->memberRepo->getEnrollList($memberIdx);
        $orders    = $this->memberRepo->getOrderList($memberIdx);
        $csrfToken = Csrf::token();

        $pageTitle  = '회원 상세 — ' . htmlspecialchars($member['mb_name']);
        $activeMenu = 'members';
        ob_start();
        require VIEW_PATH . '/pages/admin/members/show.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/members/{mb_idx}/status
    // =========================================================================
    public function updateStatus(string $mbIdx): void
    {
        Csrf::verify();

        $memberIdx = (int) $mbIdx;
        $member    = $this->memberRepo->findByIdx($memberIdx);
        if (!$member) {
            http_response_code(404);
            exit;
        }

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['active', 'dormant', 'withdrawn'], true)) {
            http_response_code(400);
            exit;
        }

        $this->memberRepo->updateStatus($memberIdx, $status);
        header("Location: /admin/members/{$memberIdx}?status_updated=1");
        exit;
    }

    // =========================================================================
    // POST /admin/members/{mb_idx}/profile
    // =========================================================================
    public function updateProfile(string $mbIdx): void
    {
        Csrf::verify();

        $memberIdx = (int) $mbIdx;
        $member    = $this->memberRepo->findByIdx($memberIdx);
        if (!$member) { http_response_code(404); exit; }

        $name      = trim($_POST['mb_name']  ?? '');
        $email     = trim($_POST['mb_email'] ?? '');
        $phoneDigits = preg_replace('/[^0-9]/', '', $_POST['mb_phone'] ?? '');
        $phone       = $this->formatPhone($phoneDigits);
        $mailling  = isset($_POST['mb_mailling']) ? 1 : 0;
        $sms       = isset($_POST['mb_sms'])      ? 1 : 0;
        $newPw     = $_POST['new_password']     ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';

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

        if ($phoneDigits !== '' && strlen($phoneDigits) !== 11) {
            $errors['mb_phone'] = '연락처는 11자리 숫자로 입력해주세요.';
        } elseif ($phone !== '' && $this->memberRepo->existsByPhoneExcept($phone, $memberIdx)) {
            $errors['mb_phone'] = '이미 사용 중인 연락처입니다.';
        }

        if ($newPw !== '') {
            if (strlen($newPw) < 8) {
                $errors['new_password'] = '비밀번호는 8자 이상이어야 합니다.';
            } elseif ($newPw !== $confirmPw) {
                $errors['confirm_password'] = '비밀번호가 일치하지 않습니다.';
            }
        }

        header('Content-Type: application/json; charset=utf-8');

        if ($errors) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        $this->memberRepo->updateProfile($memberIdx, [
            'mb_name'    => $name,
            'mb_email'   => $email,
            'mb_phone'   => $phone ?: null,
            'mb_mailling'=> $mailling,
            'mb_sms'     => $sms,
        ]);

        if ($newPw !== '') {
            $hashed = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->memberRepo->updatePassword($memberIdx, $hashed);
        }

        echo json_encode(['ok' => true, 'redirect' => "/admin/members/{$memberIdx}?profile_updated=1"]);
        exit;
    }

    private function formatPhone(string $digits): string
    {
        if (strlen($digits) === 11) {
            return substr($digits, 0, 3) . '-' . substr($digits, 3, 4) . '-' . substr($digits, 7);
        }
        if (strlen($digits) === 10) {
            return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }
        return $digits;
    }
}
