<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\AdminRepository;

class ProfileController
{
    private AdminRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new AdminRepository();
    }

    // =========================================================================
    // GET /admin/profile
    // =========================================================================
    public function index(): void
    {
        $adminIdx = (int) ($_SESSION['_admin']['admin_idx'] ?? 0);
        $admin    = $this->repo->findByIdx($adminIdx);
        if (!$admin) {
            http_response_code(404);
            exit;
        }

        $csrfToken    = Csrf::token();
        $saved        = isset($_GET['saved']);
        $pwChanged    = isset($_GET['pw_changed']);
        $errors       = [];

        $pageTitle  = '관리자 프로필';
        $activeMenu = 'profile';
        ob_start();
        require VIEW_PATH . '/pages/admin/profile/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/profile   (이름·이메일 수정)
    // =========================================================================
    public function update(): void
    {
        Csrf::verify();

        $adminIdx = (int) ($_SESSION['_admin']['admin_idx'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '') ?: null;

        if ($name === '') {
            header('Location: /admin/profile?error=' . urlencode('이름을 입력해주세요.'));
            exit;
        }

        $this->repo->updateProfile($adminIdx, $name, $email);
        header('Location: /admin/profile?saved=1');
        exit;
    }

    // =========================================================================
    // POST /admin/profile/password   (비밀번호 변경)
    // =========================================================================
    public function changePassword(): void
    {
        Csrf::verify();

        $adminIdx    = (int) ($_SESSION['_admin']['admin_idx'] ?? 0);
        $admin       = $this->repo->findByIdx($adminIdx);
        $currentPw   = $_POST['current_password'] ?? '';
        $newPw       = $_POST['new_password'] ?? '';
        $confirmPw   = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPw, $admin['password'])) {
            header('Location: /admin/profile?pw_error=' . urlencode('현재 비밀번호가 올바르지 않습니다.'));
            exit;
        }
        if (strlen($newPw) < 8) {
            header('Location: /admin/profile?pw_error=' . urlencode('새 비밀번호는 8자 이상이어야 합니다.'));
            exit;
        }
        if ($newPw !== $confirmPw) {
            header('Location: /admin/profile?pw_error=' . urlencode('새 비밀번호가 일치하지 않습니다.'));
            exit;
        }

        $hashed = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->repo->updatePassword($adminIdx, $hashed);
        header('Location: /admin/profile?pw_changed=1');
        exit;
    }
}
