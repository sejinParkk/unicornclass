<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\AdminRepository;

class AuthController
{
    private AdminRepository $repo;

    public function __construct()
    {
        $this->repo = new AdminRepository();
    }

    // -------------------------------------------------------------------------
    // GET /admin/login
    // -------------------------------------------------------------------------
    public function loginForm(): void
    {
        // 이미 로그인된 관리자는 대시보드로
        if (Auth::isAdmin()) {
            header('Location: /admin');
            exit;
        }

        $csrfToken = Csrf::token();
        $error     = null;

        require VIEW_PATH . '/pages/admin/login.php';
    }

    // -------------------------------------------------------------------------
    // POST /admin/login
    // -------------------------------------------------------------------------
    public function login(): void
    {
        Csrf::verify();

        $loginId  = trim($_POST['mb_id'] ?? '');
        $password = $_POST['mb_password'] ?? '';

        $admin = $this->repo->findByLoginId($loginId);

        if (!$admin || !password_verify($password, $admin['password'])) {
            $csrfToken = Csrf::token();
            $error     = '아이디 또는 비밀번호가 올바르지 않습니다.';

            require VIEW_PATH . '/pages/admin/login.php';
            return;
        }

        Auth::loginAdmin($admin);
        $this->repo->updateLastLogin((int) $admin['admin_idx']);

        header('Location: /admin');
        exit;
    }

    // -------------------------------------------------------------------------
    // GET /admin/logout
    // -------------------------------------------------------------------------
    public function logout(): void
    {
        Auth::logoutAdmin();
        header('Location: /admin/login');
        exit;
    }
}
