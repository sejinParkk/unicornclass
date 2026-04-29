<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\SettingRepository;
use App\Support\FileUploader;

class SettingController
{
    private SettingRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new SettingRepository();
    }

    // =========================================================================
    // GET /admin/settings
    // =========================================================================
    public function index(): void
    {
        $settings  = $this->repo->getAll();
        $csrfToken = Csrf::token();
        $saved     = isset($_GET['saved']);

        $pageTitle  = '사이트 설정';
        $activeMenu = 'settings';
        ob_start();
        require VIEW_PATH . '/pages/admin/settings/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/settings
    // =========================================================================
    public function update(): void
    {
        Csrf::verify();

        $textKeys = [
            'site_name', 'company_name', 'ceo_name', 'business_no',
            'phone', 'email', 'address', 'footer_copy',
            'sns_instagram', 'sns_youtube', 'sns_facebook', 'sns_blog',
            'kakao_channel_url',
        ];

        $data = [];
        foreach ($textKeys as $key) {
            $data[$key] = trim($_POST[$key] ?? '') ?: null;
        }

        // 로고 업로드
        if (!empty($_FILES['logo']['tmp_name'])) {
            try {
                $old = $this->repo->get('logo');
                $data['logo'] = FileUploader::uploadSiteImage($_FILES['logo']);
                FileUploader::deleteSiteImage($old);
            } catch (\RuntimeException $e) {
                $this->redirectWithError('/admin/settings', $e->getMessage());
                return;
            }
        }

        // 파비콘 업로드
        if (!empty($_FILES['favicon']['tmp_name'])) {
            try {
                $old = $this->repo->get('favicon');
                $data['favicon'] = FileUploader::uploadSiteImage($_FILES['favicon']);
                FileUploader::deleteSiteImage($old);
            } catch (\RuntimeException $e) {
                $this->redirectWithError('/admin/settings', $e->getMessage());
                return;
            }
        }

        // 히어로 배너 동영상 업로드
        if (!empty($_FILES['hero_video']['tmp_name'])) {
            try {
                $old = $this->repo->get('hero_video');
                $data['hero_video'] = FileUploader::uploadSiteVideo($_FILES['hero_video']);
                FileUploader::deleteSiteVideo($old);
            } catch (\RuntimeException $e) {
                $this->redirectWithError('/admin/settings', $e->getMessage());
                return;
            }
        }

        // 히어로 배너 동영상 삭제 요청
        if (!empty($_POST['delete_hero_video'])) {
            FileUploader::deleteSiteVideo($this->repo->get('hero_video'));
            $data['hero_video'] = null;
        }

        $this->repo->saveMany($data);
        header('Location: /admin/settings?saved=1');
        exit;
    }

    private function redirectWithError(string $url, string $msg): void
    {
        header('Location: ' . $url . '?error=' . urlencode($msg));
        exit;
    }
}
