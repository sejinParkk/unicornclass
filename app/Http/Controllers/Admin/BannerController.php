<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\BannerRepository;
use App\Support\FileUploader;

class BannerController
{
    private BannerRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new BannerRepository();
    }

    // =========================================================================
    // GET /admin/banners
    // =========================================================================
    public function index(): void
    {
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $limit      = 20;
        $result     = $this->repo->getAdminList($page, $limit);
        $banners    = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $csrfToken  = Csrf::token();
        $pageTitle  = '이벤트 배너';
        $activeMenu = 'banners';
        ob_start();
        require VIEW_PATH . '/pages/admin/banners/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/banners/create
    // =========================================================================
    public function create(): void
    {
        $banner     = null;
        $csrfToken  = Csrf::token();
        $pageTitle  = '배너 등록';
        $activeMenu = 'banners';
        ob_start();
        require VIEW_PATH . '/pages/admin/banners/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/banners
    // =========================================================================
    public function store(): void
    {
        Csrf::verify();

        try {
            $imagePath = FileUploader::uploadBannerImage($_FILES['image'] ?? []);
        } catch (\RuntimeException $e) {
            header('Location: /admin/banners/create?error=' . urlencode($e->getMessage()));
            exit;
        }

        $idx = $this->repo->create([
            'image_path'  => $imagePath,
            'link_url'    => trim($_POST['link_url']   ?? ''),
            'link_target' => $_POST['link_target']     ?? '_blank',
            'alt_text'    => trim($_POST['alt_text']   ?? ''),
            'start_date'  => $_POST['start_date']      ?: null,
            'end_date'    => $_POST['end_date']         ?: null,
            'is_active'   => (int) ($_POST['is_active'] ?? 1),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ]);

        header("Location: /admin/banners/{$idx}/edit?saved=1");
        exit;
    }

    // =========================================================================
    // GET /admin/banners/{banner_idx}/edit
    // =========================================================================
    public function edit(string $bannerIdx): void
    {
        $banner = $this->repo->findByIdx((int) $bannerIdx);
        if (!$banner) { http_response_code(404); exit; }

        $csrfToken  = Csrf::token();
        $pageTitle  = '배너 수정';
        $activeMenu = 'banners';
        ob_start();
        require VIEW_PATH . '/pages/admin/banners/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/banners/{banner_idx}
    // =========================================================================
    public function update(string $bannerIdx): void
    {
        Csrf::verify();

        $idx    = (int) $bannerIdx;
        $banner = $this->repo->findByIdx($idx);
        if (!$banner) { http_response_code(404); exit; }

        $data = [
            'link_url'    => trim($_POST['link_url']    ?? ''),
            'link_target' => $_POST['link_target']      ?? '_blank',
            'alt_text'    => trim($_POST['alt_text']    ?? ''),
            'start_date'  => $_POST['start_date']       ?: null,
            'end_date'    => $_POST['end_date']          ?: null,
            'is_active'   => (int) ($_POST['is_active']  ?? 1),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ];

        // 새 이미지 업로드 시 기존 파일 삭제 후 교체
        try {
            $newImage = FileUploader::uploadBannerImage($_FILES['image'] ?? []);
            if ($newImage) {
                FileUploader::deleteBannerImage($banner['image_path']);
                $data['image_path'] = $newImage;
            }
        } catch (\RuntimeException $e) {
            header("Location: /admin/banners/{$idx}/edit?error=" . urlencode($e->getMessage()));
            exit;
        }

        $this->repo->update($idx, $data);
        header("Location: /admin/banners/{$idx}/edit?saved=1");
        exit;
    }

    // =========================================================================
    // POST /admin/banners/{banner_idx}/delete
    // =========================================================================
    public function destroy(string $bannerIdx): void
    {
        Csrf::verify();

        $idx    = (int) $bannerIdx;
        $banner = $this->repo->findByIdx($idx);
        if (!$banner) { http_response_code(404); exit; }

        FileUploader::deleteBannerImage($banner['image_path']);
        $this->repo->delete($idx);

        header('Location: /admin/banners?deleted=1');
        exit;
    }
}
