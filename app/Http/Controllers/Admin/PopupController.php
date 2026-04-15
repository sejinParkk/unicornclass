<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\PopupRepository;
use App\Support\FileUploader;

class PopupController
{
    private PopupRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new PopupRepository();
    }

    // =========================================================================
    // GET /admin/popups
    // =========================================================================
    public function index(): void
    {
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $limit      = 20;
        $result     = $this->repo->getAdminList($page, $limit);
        $popups     = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $csrfToken  = Csrf::token();
        $pageTitle  = '팝업 관리';
        $activeMenu = 'popups';
        ob_start();
        require VIEW_PATH . '/pages/admin/popups/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/popups/create
    // =========================================================================
    public function create(): void
    {
        $popup      = null;
        $csrfToken  = Csrf::token();
        $pageTitle  = '팝업 등록';
        $activeMenu = 'popups';
        ob_start();
        require VIEW_PATH . '/pages/admin/popups/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/popups
    // =========================================================================
    public function store(): void
    {
        Csrf::verify();

        try {
            $imagePath = FileUploader::uploadPopupImage($_FILES['image'] ?? []);
        } catch (\RuntimeException $e) {
            header('Location: /admin/popups/create?error=' . urlencode($e->getMessage()));
            exit;
        }

        $idx = $this->repo->create([
            'image_path'  => $imagePath,
            'link_url'    => trim($_POST['link_url']    ?? ''),
            'link_target' => $_POST['link_target']      ?? '_blank',
            'start_date'  => $_POST['start_date']       ?: null,
            'end_date'    => $_POST['end_date']          ?: null,
            'is_active'   => (int) ($_POST['is_active']  ?? 1),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ]);

        header("Location: /admin/popups/{$idx}/edit?saved=1");
        exit;
    }

    // =========================================================================
    // GET /admin/popups/{popup_idx}/edit
    // =========================================================================
    public function edit(string $popupIdx): void
    {
        $popup = $this->repo->findByIdx((int) $popupIdx);
        if (!$popup) { http_response_code(404); exit; }

        $csrfToken  = Csrf::token();
        $pageTitle  = '팝업 수정';
        $activeMenu = 'popups';
        ob_start();
        require VIEW_PATH . '/pages/admin/popups/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/popups/{popup_idx}
    // =========================================================================
    public function update(string $popupIdx): void
    {
        Csrf::verify();

        $idx   = (int) $popupIdx;
        $popup = $this->repo->findByIdx($idx);
        if (!$popup) { http_response_code(404); exit; }

        $data = [
            'link_url'    => trim($_POST['link_url']    ?? ''),
            'link_target' => $_POST['link_target']      ?? '_blank',
            'start_date'  => $_POST['start_date']       ?: null,
            'end_date'    => $_POST['end_date']          ?: null,
            'is_active'   => (int) ($_POST['is_active']  ?? 1),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ];

        // 새 이미지 업로드 시 기존 파일 삭제 후 교체
        try {
            $newImage = FileUploader::uploadPopupImage($_FILES['image'] ?? []);
            if ($newImage) {
                FileUploader::deletePopupImage($popup['image_path']);
                $data['image_path'] = $newImage;
            }
        } catch (\RuntimeException $e) {
            header("Location: /admin/popups/{$idx}/edit?error=" . urlencode($e->getMessage()));
            exit;
        }

        $this->repo->update($idx, $data);
        header("Location: /admin/popups/{$idx}/edit?saved=1");
        exit;
    }

    // =========================================================================
    // POST /admin/popups/{popup_idx}/delete
    // =========================================================================
    public function destroy(string $popupIdx): void
    {
        Csrf::verify();

        $idx   = (int) $popupIdx;
        $popup = $this->repo->findByIdx($idx);
        if (!$popup) { http_response_code(404); exit; }

        FileUploader::deletePopupImage($popup['image_path']);
        $this->repo->delete($idx);

        header('Location: /admin/popups?deleted=1');
        exit;
    }
}
