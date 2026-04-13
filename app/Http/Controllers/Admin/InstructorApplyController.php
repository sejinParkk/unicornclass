<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\InstructorRepository;

class InstructorApplyController
{
    private InstructorRepository $instructorRepo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->instructorRepo = new InstructorRepository();
    }

    // =========================================================================
    // GET /admin/instructor-apply
    // =========================================================================
    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'q'      => trim($_GET['q'] ?? ''),
        ];
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;

        $result     = $this->instructorRepo->getApplyList($filters, $page, $limit);
        $applies    = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $pageTitle  = '강사 지원 관리';
        $activeMenu = 'instructor-apply';
        ob_start();
        require VIEW_PATH . '/pages/admin/instructor-apply/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/instructor-apply/{apply_idx}
    // =========================================================================
    public function show(string $applyIdx): void
    {
        $apply = $this->instructorRepo->findApplyById((int) $applyIdx);
        if (!$apply) { http_response_code(404); exit; }

        $csrfToken  = Csrf::token();
        $pageTitle  = '강사 지원 상세';
        $activeMenu = 'instructor-apply';
        ob_start();
        require VIEW_PATH . '/pages/admin/instructor-apply/show.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/instructor-apply/{apply_idx}/approve
    // =========================================================================
    public function approve(string $applyIdx): void
    {
        Csrf::verify();
        $idx = (int) $applyIdx;

        $apply = $this->instructorRepo->findApplyById($idx);
        if (!$apply) { http_response_code(404); exit; }

        $this->instructorRepo->updateApplyStatus($idx, 'approved');
        header('Location: /admin/instructor-apply/' . $idx . '?approved=1');
        exit;
    }

    // =========================================================================
    // POST /admin/instructor-apply/{apply_idx}/reject
    // =========================================================================
    public function reject(string $applyIdx): void
    {
        Csrf::verify();
        $idx = (int) $applyIdx;

        $apply = $this->instructorRepo->findApplyById($idx);
        if (!$apply) { http_response_code(404); exit; }

        $rejectReason = trim($_POST['reject_reason'] ?? '');
        $this->instructorRepo->updateApplyStatus($idx, 'rejected', $rejectReason ?: null);
        header('Location: /admin/instructor-apply/' . $idx . '?rejected=1');
        exit;
    }
}
