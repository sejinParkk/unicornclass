<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Repositories\ClassRepository;
use App\Repositories\ChapterRepository;
use App\Repositories\ClassMaterialRepository;
use App\Repositories\InstructorRepository;
use App\Support\FileUploader;

class ClassController
{
    private ClassRepository          $classRepo;
    private ChapterRepository        $chapterRepo;
    private ClassMaterialRepository  $materialRepo;
    private InstructorRepository     $instructorRepo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->classRepo      = new ClassRepository();
        $this->chapterRepo    = new ChapterRepository();
        $this->materialRepo   = new ClassMaterialRepository();
        $this->instructorRepo = new InstructorRepository();
    }

    // =========================================================================
    // GET /admin/classes
    // =========================================================================
    public function index(): void
    {
        $filters = [
            'q'            => trim($_GET['q'] ?? ''),
            'type'         => $_GET['type'] ?? '',
            'category_idx' => $_GET['category_idx'] ?? '',
            'is_active'    => $_GET['is_active'] ?? '',
        ];
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;

        $result     = $this->classRepo->getAdminList($filters, $page, $limit);
        $classes    = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);
        $categories = DB::select('SELECT * FROM lc_class_category WHERE is_active=1 ORDER BY sort_order');

        $csrfToken  = Csrf::token();
        $pageTitle  = '강의 관리';
        $activeMenu = 'classes';
        ob_start();
        require VIEW_PATH . '/pages/admin/classes/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/classes/create
    // =========================================================================
    public function create(): void
    {
        $instructors = $this->instructorRepo->getActiveList();
        $categories  = DB::select('SELECT * FROM lc_class_category WHERE is_active=1 ORDER BY sort_order');
        $class       = null;
        $chapters    = [];
        $materials   = [];
        $errors      = [];

        $pageTitle  = '강의 등록';
        $activeMenu = 'classes';
        $csrfToken  = Csrf::token();
        ob_start();
        require VIEW_PATH . '/pages/admin/classes/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/classes
    // =========================================================================
    public function store(): void
    {
        Csrf::verify();

        [$data, $errors] = $this->validateForm($_POST);

        if ($errors) {
            $this->renderForm(null, [], [], $errors);
            return;
        }

        // 썸네일 업로드
        if (!empty($_FILES['thumbnail']['tmp_name'])) {
            try {
                $data['thumbnail'] = FileUploader::uploadClassThumbnail($_FILES['thumbnail']);
            } catch (\RuntimeException $e) {
                $errors['thumbnail'] = $e->getMessage();
                $this->renderForm(null, [], [], $errors);
                return;
            }
        }

        $classIdx = $this->classRepo->create($data);

        // 챕터 저장
        $this->saveChapters($classIdx, null);

        // 강의 자료 저장
        $this->saveMaterials($classIdx);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'redirect' => '/admin/classes/' . $classIdx . '/edit?created=1']);
        exit;
    }

    // =========================================================================
    // GET /admin/classes/{class_idx}/edit
    // =========================================================================
    public function edit(string $classIdx): void
    {
        $class = $this->classRepo->findById((int) $classIdx);
        if (!$class) {
            http_response_code(404);
            echo '강의를 찾을 수 없습니다.';
            return;
        }

        $instructors = $this->instructorRepo->getActiveList();
        $categories  = DB::select('SELECT * FROM lc_class_category WHERE is_active=1 ORDER BY sort_order');
        $chapters    = $this->loadChaptersForForm((int) $classIdx);
        $materials   = $this->materialRepo->findByClassIdx((int) $classIdx);
        $errors      = [];

        $pageTitle  = '강의 수정';
        $activeMenu = 'classes';
        $csrfToken  = Csrf::token();
        ob_start();
        require VIEW_PATH . '/pages/admin/classes/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/classes/{class_idx}
    // =========================================================================
    public function update(string $classIdx): void
    {
        Csrf::verify();
        $classIdx = (int) $classIdx;

        $existing = $this->classRepo->findById($classIdx);
        if (!$existing) {
            http_response_code(404);
            echo '강의를 찾을 수 없습니다.';
            return;
        }

        [$data, $errors] = $this->validateForm($_POST, isUpdate: true);

        if ($errors) {
            $chapters  = $this->loadChaptersForForm($classIdx);
            $materials = $this->materialRepo->findByClassIdx($classIdx);
            $this->renderForm(array_merge($existing, $_POST), $chapters, $materials, $errors);
            return;
        }

        // 썸네일 교체
        if (!empty($_FILES['thumbnail']['tmp_name'])) {
            try {
                $newThumb = FileUploader::uploadClassThumbnail($_FILES['thumbnail']);
                FileUploader::deleteClassThumbnail($existing['thumbnail']);
                $data['thumbnail'] = $newThumb;
            } catch (\RuntimeException $e) {
                $errors['thumbnail'] = $e->getMessage();
                $chapters  = $this->loadChaptersForForm($classIdx);
                $materials = $this->materialRepo->findByClassIdx($classIdx);
                $this->renderForm(array_merge($existing, $_POST), $chapters, $materials, $errors);
                return;
            }
        }

        // type은 수강자 존재 시 변경 불가
        if ($this->classRepo->hasEnrollments($classIdx)) {
            unset($data['type']);
        }

        $this->classRepo->update($classIdx, $data);

        // 챕터 저장 (기존 챕터 목록 전달)
        $existingChapters = $this->chapterRepo->findByClassIdx($classIdx);
        $this->saveChapters($classIdx, $existingChapters);

        // 강의 자료: 삭제 처리
        $this->deleteMaterials($classIdx);

        // 강의 자료: 새 항목 저장
        $this->saveMaterials($classIdx);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'redirect' => '/admin/classes/' . $classIdx . '/edit?updated=1']);
        exit;
    }

    // =========================================================================
    // POST /admin/classes/{class_idx}/delete
    // =========================================================================
    public function destroy(string $classIdx): void
    {
        Csrf::verify();
        $classIdx = (int) $classIdx;

        $class = $this->classRepo->findById($classIdx);
        if (!$class) {
            http_response_code(404);
            echo '강의를 찾을 수 없습니다.';
            return;
        }

        $result = $this->classRepo->delete($classIdx);

        if ($result === 'blocked') {
            header('Location: /admin/classes?error=' . urlencode('수강자가 있는 강의는 삭제할 수 없습니다. 수정 페이지에서 비활성화를 이용해주세요.'));
            exit;
        }

        header('Location: /admin/classes?deleted=1');
        exit;
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function renderForm(?array $class, array $chapters, array $materials, array $errors): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    /** DB 챕터를 폼에서 쓰는 형식으로 변환 */
    private function loadChaptersForForm(int $classIdx): array
    {
        $rows = $this->chapterRepo->findByClassIdx($classIdx);
        foreach ($rows as &$ch) {
            $ch['duration_display'] = ChapterRepository::secondsToDisplay((int) $ch['duration']);
        }
        unset($ch);
        return $rows;
    }

    /**
     * chapters_json POST 필드를 파싱해 챕터를 저장합니다.
     *
     * @param int        $classIdx
     * @param array|null $existingChapters  null=신규(기존 없음), array=수정 시 기존 챕터 목록
     */
    private function saveChapters(int $classIdx, ?array $existingChapters): void
    {
        $json = trim($_POST['chapters_json'] ?? '');
        if ($json === '') return;

        $submitted = json_decode($json, true);
        if (!is_array($submitted)) return;

        if ($existingChapters !== null) {
            // 수정: 기존 챕터 중 제출되지 않은 것 삭제
            $submittedIds = array_filter(array_column($submitted, 'chapter_idx'));
            foreach ($existingChapters as $existing) {
                if (!in_array((int) $existing['chapter_idx'], array_map('intval', $submittedIds), true)) {
                    $this->chapterRepo->delete((int) $existing['chapter_idx']);
                }
            }
        }

        foreach ($submitted as $i => $ch) {
            $chapterIdx = (int) ($ch['chapter_idx'] ?? 0);
            $title      = trim($ch['title'] ?? '');
            if ($title === '') continue;

            $chData = [
                'title'      => $title,
                'vimeo_url'  => trim($ch['vimeo_url'] ?? ''),
                'duration'   => trim($ch['duration'] ?? '0:00'),
                'sort_order' => $i + 1,
            ];

            if ($chapterIdx > 0) {
                $this->chapterRepo->update($chapterIdx, $chData);
            } else {
                $this->chapterRepo->create($classIdx, $chData);
            }
        }

        $this->classRepo->syncTotalEpisodes($classIdx);
    }

    /** delete_file_ids POST 필드로 강의 자료 삭제 */
    private function deleteMaterials(int $classIdx): void
    {
        $raw = trim($_POST['delete_file_ids'] ?? '');
        if ($raw === '') return;

        $ids = array_filter(array_map('intval', explode(',', $raw)));
        foreach ($ids as $fileIdx) {
            $material = $this->materialRepo->findById($fileIdx);
            if (!$material || (int) $material['class_idx'] !== $classIdx) continue;

            if ($material['file_path']) {
                FileUploader::deleteClassMaterial($material['file_path']);
            }
            $this->materialRepo->delete($fileIdx);
        }
    }

    /** 새 파일 업로드 + 새 링크 저장 */
    private function saveMaterials(int $classIdx): void
    {
        // 파일 업로드
        $files  = $_FILES['new_files']  ?? [];
        $titles = $_POST['new_file_titles'] ?? [];

        if (!empty($files['tmp_name'])) {
            $count = is_array($files['tmp_name']) ? count($files['tmp_name']) : 1;
            for ($i = 0; $i < $count; $i++) {
                $singleFile = [
                    'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                    'error'    => is_array($files['error'])    ? $files['error'][$i]    : $files['error'],
                    'size'     => is_array($files['size'])     ? $files['size'][$i]     : $files['size'],
                    'name'     => is_array($files['name'])     ? $files['name'][$i]     : $files['name'],
                    'type'     => is_array($files['type'])     ? $files['type'][$i]     : $files['type'],
                ];
                if (empty($singleFile['tmp_name'])) continue;

                try {
                    $result = FileUploader::uploadClassMaterial($singleFile);
                    if (!empty($result)) {
                        $title = trim($titles[$i] ?? '') ?: ($singleFile['name'] ?? '첨부파일');
                        $this->materialRepo->createFile($classIdx, $title, $result['filename'], $result['size']);
                    }
                } catch (\RuntimeException $e) {
                    // 개별 파일 오류는 무시하고 계속 진행
                    error_log('Material upload error: ' . $e->getMessage());
                }
            }
        }

        // 링크 저장
        $linkTitles = $_POST['new_link_titles'] ?? [];
        $linkUrls   = $_POST['new_link_urls']   ?? [];

        foreach ($linkUrls as $i => $url) {
            $url   = trim($url);
            $title = trim($linkTitles[$i] ?? '');
            if ($url === '') continue;
            if ($title === '') $title = $url;
            $this->materialRepo->createLink($classIdx, $title, $url);
        }
    }

    private function validateForm(array $post, bool $isUpdate = false): array
    {
        $errors = [];
        $data   = [];

        $data['title'] = trim($post['title'] ?? '');
        if ($data['title'] === '') $errors['title'] = '강의명을 입력해주세요.';

        $data['instructor_idx'] = (int) ($post['instructor_idx'] ?? 0);
        if (!$data['instructor_idx']) $errors['instructor_idx'] = '강사를 선택해주세요.';

        if (!$isUpdate) {
            $data['type'] = $post['type'] ?? '';
            if (!in_array($data['type'], ['free', 'premium'], true)) {
                $errors['type'] = '강의 유형을 선택해주세요.';
            }
        }

        $data['category_idx']  = (int) ($post['category_idx'] ?? 0) ?: null;
        $data['summary']       = trim($post['summary'] ?? '') ?: null;
        $data['description']   = trim($post['description'] ?? '') ?: null;
        $data['kakao_url']     = trim($post['kakao_url'] ?? '') ?: null;
        $data['vimeo_url']     = trim($post['vimeo_url'] ?? '') ?: null;
        $data['duration_days'] = max(1, (int) ($post['duration_days'] ?? 180));
        $data['price']         = max(0, (int) str_replace(',', '', ($post['price'] ?? '0')));
        $data['price_origin']  = max(0, (int) str_replace(',', '', ($post['price_origin'] ?? '0')));
        $data['badge_hot']     = isset($post['badge_hot']) ? 1 : 0;
        $data['badge_new']     = isset($post['badge_new']) ? 1 : 0;
        $data['is_active']     = isset($post['is_active']) ? 1 : 0;
        $data['sort_order']    = (int) ($post['sort_order'] ?? 0);

        $saleEnd = trim($post['sale_end_at'] ?? '');
        if ($saleEnd === '') {
            $data['sale_end_at'] = null;
        } elseif (strtotime($saleEnd) !== false) {
            $data['sale_end_at'] = date('Y-m-d H:i:s', strtotime($saleEnd));
        } else {
            $errors['sale_end_at'] = '올바른 날짜 형식을 입력해주세요.';
        }

        $enrollStart = trim($post['enroll_start_at'] ?? '');
        if ($enrollStart === '') {
            $data['enroll_start_at'] = null;
        } elseif (strtotime($enrollStart) !== false) {
            $data['enroll_start_at'] = date('Y-m-d H:i:s', strtotime($enrollStart));
        } else {
            $errors['enroll_start_at'] = '올바른 날짜 형식을 입력해주세요.';
        }

        return [$data, $errors];
    }
}
