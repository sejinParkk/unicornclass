<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\SettingRepository;

class TermsController
{
    private SettingRepository $repo;

    private const TYPES = [
        'terms'      => '이용약관',
        'privacy'    => '개인정보처리방침',
        'refund'     => '환불 정책',
        'youth'      => '청소년 보호정책',
        'instructor' => '강사 이용약관',
        'copyright'  => '저작권 정책',
        'cookie'     => '쿠키 정책',
        'marketing'     => '마케팅 수신 동의',
        'disclaimer'    => '면책조항',
        'purchase'      => '구매 조건 동의',
        'ecommerce'     => '전자금융거래 이용약관',
        'privacy_third' => '개인정보 제3자 제공 동의 (PG사)',
    ];

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new SettingRepository();
    }

    // =========================================================================
    // GET /admin/terms
    // =========================================================================
    public function index(): void
    {
        $termData  = [];
        foreach (array_keys(self::TYPES) as $type) {
            $termData[$type] = $this->repo->getTerm($type);
        }
        $termTypes = self::TYPES;
        $csrfToken = Csrf::token();
        $saved     = $_GET['saved'] ?? null;

        $pageTitle  = '약관 관리';
        $activeMenu = 'terms';
        ob_start();
        require VIEW_PATH . '/pages/admin/terms/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/terms/{type}
    // =========================================================================
    public function update(string $type): void
    {
        Csrf::verify();

        if (!array_key_exists($type, self::TYPES)) {
            http_response_code(404);
            exit;
        }

        $title   = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';

        if ($title === '') {
            header('Location: /admin/terms?error=' . urlencode('제목을 입력해주세요.'));
            exit;
        }

        $this->repo->saveTerm($type, $title, $content);
        header('Location: /admin/terms?saved=' . $type);
        exit;
    }
}
