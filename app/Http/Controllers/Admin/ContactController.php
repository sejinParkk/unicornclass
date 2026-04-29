<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\ContactRepository;

class ContactController
{
    private ContactRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new ContactRepository();
    }

    // =========================================================================
    // GET /admin/contacts
    // =========================================================================
    public function index(): void
    {
        $filters = [
            'q'        => trim($_GET['q'] ?? ''),
            'status'   => $_GET['status'] ?? '',
            'category' => $_GET['category'] ?? '',
        ];
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;

        $result     = $this->repo->getAdminList($filters, $page, $limit);
        $contacts   = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $pageTitle  = '1:1 문의';
        $activeMenu = 'contacts';
        ob_start();
        require VIEW_PATH . '/pages/admin/contacts/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/contacts/{contact_idx}
    // =========================================================================
    public function show(string $contactIdx): void
    {
        $idx     = (int) $contactIdx;
        $contact = $this->repo->findByIdx($idx);
        if (!$contact) {
            http_response_code(404); exit;
        }

        $csrfToken  = Csrf::token();
        $pageTitle  = '문의 상세 #' . $idx;
        $activeMenu = 'contacts';
        ob_start();
        require VIEW_PATH . '/pages/admin/contacts/show.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/contacts/{contact_idx}/answer
    // =========================================================================
    public function answer(string $contactIdx): void
    {
        Csrf::verify();

        $idx     = (int) $contactIdx;
        $contact = $this->repo->findByIdx($idx);
        if (!$contact) {
            http_response_code(404); exit;
        }

        $answer = trim($_POST['answer'] ?? '');
        if ($answer === '') {
            header("Location: /admin/contacts/{$idx}?error=" . urlencode('답변 내용을 입력해주세요.'));
            exit;
        }

        $adminIdx = (int) ($_SESSION['_admin']['admin_idx'] ?? 0);
        $this->repo->saveAnswer($idx, $answer, $adminIdx);
        header("Location: /admin/contacts/{$idx}?answered=1");
        exit;
    }
}
