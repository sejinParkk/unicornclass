<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\AdminReviewRepository;

class ReviewController
{
    private AdminReviewRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new AdminReviewRepository();
    }

    // =========================================================================
    // GET /admin/reviews
    // =========================================================================
    public function index(): void
    {
        $filters = [
            'q'         => trim($_GET['q'] ?? ''),
            'is_active' => $_GET['is_active'] ?? '',
            'rating'    => $_GET['rating'] ?? '',
        ];
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;

        $result     = $this->repo->getAdminList($filters, $page, $limit);
        $reviews    = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $csrfToken  = Csrf::token();
        $pageTitle  = '후기 관리';
        $activeMenu = 'reviews';
        ob_start();
        require VIEW_PATH . '/pages/admin/reviews/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/reviews/{review_idx}
    // =========================================================================
    public function show(string $reviewIdx): void
    {
        $review = $this->repo->findByIdx((int) $reviewIdx);
        if (!$review) { http_response_code(404); exit; }

        $images     = $this->repo->getImages((int) $reviewIdx);
        $csrfToken  = Csrf::token();
        $pageTitle  = '후기 상세';
        $activeMenu = 'reviews';
        ob_start();
        require VIEW_PATH . '/pages/admin/reviews/show.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/reviews/{review_idx}/active
    // =========================================================================
    public function toggleActive(string $reviewIdx): void
    {
        Csrf::verify();

        $review = $this->repo->findByIdx((int) $reviewIdx);
        if (!$review) { http_response_code(404); exit; }

        $newActive = $review['is_active'] ? 0 : 1;
        $this->repo->setActive((int) $reviewIdx, $newActive);

        header('Location: /admin/reviews/' . (int) $reviewIdx . '?saved=1');
        exit;
    }

    // =========================================================================
    // POST /admin/reviews/{review_idx}/delete
    // =========================================================================
    public function destroy(string $reviewIdx): void
    {
        Csrf::verify();

        $review = $this->repo->findByIdx((int) $reviewIdx);
        if (!$review) { http_response_code(404); exit; }

        $this->repo->delete((int) $reviewIdx);

        header('Location: /admin/reviews?deleted=1');
        exit;
    }
}
