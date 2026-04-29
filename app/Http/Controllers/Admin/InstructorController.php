<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Repositories\InstructorRepository;
use App\Support\FileUploader;

class InstructorController
{
    private InstructorRepository $instructorRepo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->instructorRepo = new InstructorRepository();
    }

    // =========================================================================
    // GET /admin/instructors
    // =========================================================================
    public function index(): void
    {
        $filters = [
            'q'         => trim($_GET['q'] ?? ''),
            'is_active' => $_GET['is_active'] ?? '',
        ];
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;

        $result     = $this->instructorRepo->getAdminList($filters, $page, $limit);
        $instructors = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $csrfToken  = Csrf::token();
        $pageTitle  = '강사 관리';
        $activeMenu = 'instructors';
        ob_start();
        require VIEW_PATH . '/pages/admin/instructors/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/instructors/create
    // =========================================================================
    public function create(): void
    {
        $instructor = null;
        $categories = DB::select('SELECT * FROM lc_instructor_category WHERE is_active=1 ORDER BY sort_order');
        $errors     = [];
        $csrfToken  = Csrf::token();

        $pageTitle  = '강사 등록';
        $activeMenu = 'instructors';
        ob_start();
        require VIEW_PATH . '/pages/admin/instructors/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/instructors
    // =========================================================================
    public function store(): void
    {
        Csrf::verify();

        header('Content-Type: application/json; charset=utf-8');

        $errors = $this->validate($_POST);
        if ($errors) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        $data = $this->buildData($_POST);

        if (!empty($_FILES['photo']['tmp_name'])) {
            try {
                $data['photo'] = FileUploader::uploadInstructorPhoto($_FILES['photo']);
            } catch (\RuntimeException $e) {
                echo json_encode(['ok' => false, 'errors' => ['photo' => $e->getMessage()]]);
                exit;
            }
        }

        $instructorIdx = $this->instructorRepo->create($data);

        $intros  = json_decode($_POST['intros_json']  ?? '[]', true) ?? [];
        $careers = json_decode($_POST['careers_json'] ?? '[]', true) ?? [];
        $this->instructorRepo->saveIntros($instructorIdx, $intros);
        $this->instructorRepo->saveCareers($instructorIdx, $careers);

        echo json_encode(['ok' => true, 'redirect' => '/admin/instructors?created=1']);
        exit;
    }

    // =========================================================================
    // GET /admin/instructors/{instructor_idx}/edit
    // =========================================================================
    public function edit(string $instructorIdx): void
    {
        $instructor = $this->instructorRepo->findById((int) $instructorIdx);
        if (!$instructor) { http_response_code(404); exit; }

        $categories = DB::select('SELECT * FROM lc_instructor_category WHERE is_active=1 ORDER BY sort_order');
        $errors     = [];
        $csrfToken  = Csrf::token();

        $pageTitle  = '강사 수정';
        $activeMenu = 'instructors';
        ob_start();
        require VIEW_PATH . '/pages/admin/instructors/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/instructors/{instructor_idx}
    // =========================================================================
    public function update(string $instructorIdx): void
    {
        Csrf::verify();
        $idx = (int) $instructorIdx;

        $instructor = $this->instructorRepo->findById($idx);
        if (!$instructor) { http_response_code(404); exit; }

        header('Content-Type: application/json; charset=utf-8');

        $errors = $this->validate($_POST);
        if ($errors) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        $data = $this->buildData($_POST);

        if (!empty($_FILES['photo']['tmp_name'])) {
            try {
                $newPhoto = FileUploader::uploadInstructorPhoto($_FILES['photo']);
                FileUploader::deleteInstructorPhoto($instructor['photo']);
                $data['photo'] = $newPhoto;
            } catch (\RuntimeException $e) {
                echo json_encode(['ok' => false, 'errors' => ['photo' => $e->getMessage()]]);
                exit;
            }
        }

        $this->instructorRepo->update($idx, $data);

        $intros  = json_decode($_POST['intros_json']  ?? '[]', true) ?? [];
        $careers = json_decode($_POST['careers_json'] ?? '[]', true) ?? [];
        $this->instructorRepo->saveIntros($idx, $intros);
        $this->instructorRepo->saveCareers($idx, $careers);

        echo json_encode(['ok' => true, 'redirect' => '/admin/instructors?updated=1']);
        exit;
    }

    // =========================================================================
    // POST /admin/instructors/{instructor_idx}/delete
    // =========================================================================
    public function destroy(string $instructorIdx): void
    {
        Csrf::verify();
        $idx = (int) $instructorIdx;

        $instructor = $this->instructorRepo->findById($idx);
        if (!$instructor) { http_response_code(404); exit; }

        $result = $this->instructorRepo->delete($idx);

        if ($result === 'blocked') {
            header('Location: /admin/instructors?error=' . urlencode('담당 강의가 있는 강사는 삭제할 수 없습니다. 강의를 먼저 삭제해주세요.'));
            exit;
        }

        header('Location: /admin/instructors?deleted=1');
        exit;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function validate(array $post): array
    {
        $errors = [];
        if (empty(trim($post['name'] ?? ''))) {
            $errors['name'] = '강사명을 입력해주세요.';
        }
        return $errors;
    }

    private function buildData(array $post): array
    {
        return [
            'category_idx'  => !empty($post['category_idx']) ? (int) $post['category_idx'] : null,
            'name'          => trim($post['name'] ?? ''),
            'sns_youtube'   => trim($post['sns_youtube'] ?? '') ?: null,
            'sns_instagram' => trim($post['sns_instagram'] ?? '') ?: null,
            'sns_facebook'  => trim($post['sns_facebook'] ?? '') ?: null,
            'sort_order'    => (int) ($post['sort_order'] ?? 0),
            'is_active'     => isset($post['is_active']) ? 1 : 0,
        ];
    }
}
