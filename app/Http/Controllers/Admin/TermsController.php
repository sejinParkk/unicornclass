<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\TermsRepository;

class TermsController
{
    private TermsRepository $repo;

    public const TYPES = [
        'terms'         => '이용약관',
        'privacy'       => '개인정보처리방침',
        'refund'        => '환불 정책',
        'youth'         => '청소년 보호정책',
        'instructor'    => '강사 이용약관',
        'copyright'     => '저작권 정책',
        'cookie'        => '쿠키 정책',
        'marketing'     => '마케팅 수신 동의',
        'disclaimer'    => '면책조항',
        'purchase'      => '구매 조건 동의',
        'ecommerce'     => '전자금융거래 이용약관',
        'privacy_third' => '개인정보 제3자 제공 동의 (PG사)',
    ];

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new TermsRepository();
    }

    // =========================================================================
    // GET /admin/terms  — 약관 유형 목록 (현재 버전 요약)
    // =========================================================================
    public function index(): void
    {
        $summary    = $this->repo->getAllCurrentSummary();
        $termTypes  = self::TYPES;
        $csrfToken  = Csrf::token();
        $pageTitle  = '약관 관리';
        $activeMenu = 'terms';

        ob_start();
        require VIEW_PATH . '/pages/admin/terms/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/terms/{type}/versions  — 버전 목록
    // =========================================================================
    public function versions(string $type): void
    {
        $this->requireValidType($type);

        $versions   = $this->repo->getAdminVersions($type);
        $typeName   = self::TYPES[$type];
        $csrfToken  = Csrf::token();
        $pageTitle  = $typeName . ' 버전 관리';
        $activeMenu = 'terms';

        ob_start();
        require VIEW_PATH . '/pages/admin/terms/versions.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/terms/{type}/create  — 새 버전 등록 폼
    // =========================================================================
    public function createForm(string $type): void
    {
        $this->requireValidType($type);

        $typeName   = self::TYPES[$type];
        $csrfToken  = Csrf::token();
        $pageTitle  = $typeName . ' 새 버전 등록';
        $activeMenu = 'terms';
        $version    = null; // 신규

        ob_start();
        require VIEW_PATH . '/pages/admin/terms/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/terms/{type}  — 새 버전 저장
    // =========================================================================
    public function store(string $type): void
    {
        Csrf::verify();
        $this->requireValidType($type);

        $title        = trim($_POST['title'] ?? '');
        $content      = $_POST['content'] ?? '';
        $effectiveAt  = trim($_POST['effective_at'] ?? '');
        $setCurrent   = isset($_POST['is_current']);

        header('Content-Type: application/json; charset=utf-8');

        if ($title === '') {
            echo json_encode(['ok' => false, 'message' => '제목을 입력해주세요.']);
            exit;
        }
        if ($effectiveAt === '' || !strtotime($effectiveAt)) {
            echo json_encode(['ok' => false, 'message' => '시행일을 입력해주세요.']);
            exit;
        }

        $this->repo->createVersion($type, $title, $content, $effectiveAt, $setCurrent);
        echo json_encode(['ok' => true, 'redirect' => "/admin/terms/{$type}/versions?saved=1"]);
        exit;
    }

    // =========================================================================
    // GET /admin/terms/v/{idx}/edit  — 버전 수정 폼
    // =========================================================================
    public function editForm(string $idx): void
    {
        $version = $this->repo->getByIdx((int) $idx);
        if (!$version) { http_response_code(404); exit; }

        $type       = $version['type'];
        $typeName   = self::TYPES[$type] ?? $type;
        $csrfToken  = Csrf::token();
        $pageTitle  = $typeName . ' 버전 수정';
        $activeMenu = 'terms';

        ob_start();
        require VIEW_PATH . '/pages/admin/terms/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/terms/v/{idx}  — 버전 수정 저장
    // =========================================================================
    public function update(string $idx): void
    {
        Csrf::verify();

        $termsIdx = (int) $idx;
        $version  = $this->repo->getByIdx($termsIdx);
        if (!$version) { http_response_code(404); exit; }

        $title       = trim($_POST['title'] ?? '');
        $content     = $_POST['content'] ?? '';
        $effectiveAt = trim($_POST['effective_at'] ?? '');

        header('Content-Type: application/json; charset=utf-8');

        if ($title === '') {
            echo json_encode(['ok' => false, 'message' => '제목을 입력해주세요.']);
            exit;
        }
        if ($effectiveAt === '' || !strtotime($effectiveAt)) {
            echo json_encode(['ok' => false, 'message' => '시행일을 입력해주세요.']);
            exit;
        }

        $this->repo->updateVersion($termsIdx, $title, $content, $effectiveAt);
        echo json_encode(['ok' => true, 'redirect' => "/admin/terms/{$version['type']}/versions?saved=1"]);
        exit;
    }

    // =========================================================================
    // POST /admin/terms/v/{idx}/current  — 현재 버전으로 설정
    // =========================================================================
    public function setCurrent(string $idx): void
    {
        Csrf::verify();

        $termsIdx = (int) $idx;
        $version  = $this->repo->getByIdx($termsIdx);
        if (!$version) { http_response_code(404); exit; }

        $this->repo->setCurrent($termsIdx, $version['type']);
        header("Location: /admin/terms/{$version['type']}/versions?current=1");
        exit;
    }

    // =========================================================================
    // POST /admin/terms/v/{idx}/delete  — 버전 삭제
    // =========================================================================
    public function destroy(string $idx): void
    {
        Csrf::verify();

        $termsIdx = (int) $idx;
        $version  = $this->repo->getByIdx($termsIdx);
        if (!$version) { http_response_code(404); exit; }

        $type   = $version['type'];
        $result = $this->repo->deleteVersion($termsIdx);

        if ($result === 'blocked') {
            header("Location: /admin/terms/{$type}/versions?error=" . urlencode('현재 버전이 유일한 버전이면 삭제할 수 없습니다.'));
        } else {
            header("Location: /admin/terms/{$type}/versions?deleted=1");
        }
        exit;
    }

    // =========================================================================
    private function requireValidType(string $type): void
    {
        if (!array_key_exists($type, self::TYPES)) {
            http_response_code(404);
            exit;
        }
    }
}
