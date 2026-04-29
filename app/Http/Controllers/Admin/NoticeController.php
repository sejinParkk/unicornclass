<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\NoticeRepository;

class NoticeController
{
    private NoticeRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new NoticeRepository();
    }

    // =========================================================================
    // GET /admin/notices
    // =========================================================================
    public function index(): void
    {
        $filters = [
            'q'              => trim($_GET['q'] ?? ''),
            'is_active'      => $_GET['is_active'] ?? '',
            'is_pinned'      => $_GET['is_pinned'] ?? '',
            'is_maintenance' => $_GET['is_maintenance'] ?? '',
        ];
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;

        $result     = $this->repo->getAdminList($filters, $page, $limit);
        $notices    = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $csrfToken  = Csrf::token();
        $pageTitle  = '공지사항';
        $activeMenu = 'notices';
        ob_start();
        require VIEW_PATH . '/pages/admin/notices/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/notices/create
    // =========================================================================
    public function create(): void
    {
        $notice     = null;
        $csrfToken  = Csrf::token();
        $pageTitle  = '공지사항 등록';
        $activeMenu = 'notices';
        ob_start();
        require VIEW_PATH . '/pages/admin/notices/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/notices
    // =========================================================================
    public function store(): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            echo json_encode(['ok' => false, 'message' => '제목을 입력해주세요.']);
            exit;
        }

        $noticeType = $_POST['notice_type'] ?? 'none';
        $idx = $this->repo->create([
            'title'          => $title,
            'content'        => $_POST['content'] ?? '',
            'is_pinned'      => $noticeType === 'pinned' ? 1 : 0,
            'is_maintenance' => $noticeType === 'maintenance' ? 1 : 0,
            'is_active'      => (int) ($_POST['is_active'] ?? 1),
        ]);

        echo json_encode(['ok' => true, 'redirect' => "/admin/notices/{$idx}/edit?saved=1"]);
        exit;
    }

    // =========================================================================
    // GET /admin/notices/{notice_idx}/edit
    // =========================================================================
    public function edit(string $noticeIdx): void
    {
        $notice = $this->repo->findByIdx((int) $noticeIdx);
        if (!$notice) {
            http_response_code(404); exit;
        }

        $csrfToken  = Csrf::token();
        $pageTitle  = '공지사항 수정';
        $activeMenu = 'notices';
        ob_start();
        require VIEW_PATH . '/pages/admin/notices/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/notices/{notice_idx}
    // =========================================================================
    public function update(string $noticeIdx): void
    {
        Csrf::verify();

        $idx    = (int) $noticeIdx;
        $notice = $this->repo->findByIdx($idx);
        if (!$notice) {
            http_response_code(404); exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            echo json_encode(['ok' => false, 'message' => '제목을 입력해주세요.']);
            exit;
        }

        $noticeType = $_POST['notice_type'] ?? 'none';
        $this->repo->update($idx, [
            'title'          => $title,
            'content'        => $_POST['content'] ?? '',
            'is_pinned'      => $noticeType === 'pinned' ? 1 : 0,
            'is_maintenance' => $noticeType === 'maintenance' ? 1 : 0,
            'is_active'      => (int) ($_POST['is_active'] ?? 1),
        ]);

        echo json_encode(['ok' => true, 'redirect' => "/admin/notices/{$idx}/edit?saved=1"]);
        exit;
    }

    // =========================================================================
    // POST /admin/notices/{notice_idx}/delete
    // =========================================================================
    public function destroy(string $noticeIdx): void
    {
        Csrf::verify();

        $idx    = (int) $noticeIdx;
        $notice = $this->repo->findByIdx($idx);
        if (!$notice) {
            http_response_code(404); exit;
        }

        $this->repo->delete($idx);
        header('Location: /admin/notices?deleted=1');
        exit;
    }
}
