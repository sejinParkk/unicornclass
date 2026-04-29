<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\FaqRepository;

class FaqController
{
    private FaqRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new FaqRepository();
    }

    // =========================================================================
    // GET /admin/faqs
    // =========================================================================
    public function index(): void
    {
        $category   = $_GET['category'] ?? '';
        $faqs       = $this->repo->getList($category);
        $categories = $this->repo->getCategories();
        $csrfToken  = Csrf::token();

        $pageTitle  = 'FAQ';
        $activeMenu = 'faqs';
        ob_start();
        require VIEW_PATH . '/pages/admin/faqs/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/faqs/create
    // =========================================================================
    public function create(): void
    {
        $faq        = null;
        $categories = $this->repo->getCategories();
        $csrfToken  = Csrf::token();

        $pageTitle  = 'FAQ 등록';
        $activeMenu = 'faqs';
        ob_start();
        require VIEW_PATH . '/pages/admin/faqs/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/faqs/{faq_idx}/edit
    // =========================================================================
    public function edit(string $faqIdx): void
    {
        $idx = (int) $faqIdx;
        $faq = $this->repo->findByIdx($idx);
        if (!$faq) {
            http_response_code(404); exit;
        }

        $categories = $this->repo->getCategories();
        $csrfToken  = Csrf::token();

        $pageTitle  = 'FAQ 수정 #' . $idx;
        $activeMenu = 'faqs';
        ob_start();
        require VIEW_PATH . '/pages/admin/faqs/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/faqs
    // =========================================================================
    public function store(): void
    {
        Csrf::verify();

        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');

        header('Content-Type: application/json; charset=utf-8');

        if ($question === '' || $answer === '') {
            echo json_encode(['ok' => false, 'message' => '질문과 답변을 모두 입력해주세요.']);
            exit;
        }

        $this->repo->create([
            'category'   => $_POST['category'] ?? 'etc',
            'question'   => $question,
            'answer'     => $answer,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active'  => (int) ($_POST['is_active'] ?? 1),
        ]);

        echo json_encode(['ok' => true, 'redirect' => '/admin/faqs?saved=1']);
        exit;
    }

    // =========================================================================
    // POST /admin/faqs/{faq_idx}
    // =========================================================================
    public function update(string $faqIdx): void
    {
        Csrf::verify();

        $idx = (int) $faqIdx;
        $faq = $this->repo->findByIdx($idx);
        if (!$faq) {
            http_response_code(404); exit;
        }

        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');

        header('Content-Type: application/json; charset=utf-8');

        if ($question === '' || $answer === '') {
            echo json_encode(['ok' => false, 'message' => '질문과 답변을 모두 입력해주세요.']);
            exit;
        }

        $this->repo->update($idx, [
            'category'   => $_POST['category'] ?? $faq['category'],
            'question'   => $question,
            'answer'     => $answer,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active'  => (int) ($_POST['is_active'] ?? 1),
        ]);

        echo json_encode(['ok' => true, 'redirect' => "/admin/faqs/{$idx}/edit?saved=1"]);
        exit;
    }

    // =========================================================================
    // POST /admin/faqs/{faq_idx}/delete
    // =========================================================================
    public function destroy(string $faqIdx): void
    {
        Csrf::verify();

        $idx = (int) $faqIdx;
        $faq = $this->repo->findByIdx($idx);
        if (!$faq) {
            http_response_code(404); exit;
        }

        $this->repo->delete($idx);
        header('Location: /admin/faqs?deleted=1');
        exit;
    }
}
