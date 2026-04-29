<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;

class CategoryController
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    // 테이블 매핑
    private function table(string $type): string
    {
        return $type === 'instructor' ? 'lc_instructor_category' : 'lc_class_category';
    }

    // =========================================================================
    // GET /admin/categories?type=class|instructor
    // =========================================================================
    public function index(): void
    {
        $type = in_array($_GET['type'] ?? '', ['class', 'instructor']) ? ($_GET['type'] ?? 'class') : 'class';
        $tbl  = $this->table($type);

        $classCategories      = DB::select('SELECT * FROM lc_class_category ORDER BY sort_order ASC, category_idx ASC');
        $instructorCategories = DB::select('SELECT * FROM lc_instructor_category ORDER BY sort_order ASC, category_idx ASC');

        $csrfToken  = Csrf::token();
        $pageTitle  = '카테고리 관리';
        $activeMenu = 'categories';
        ob_start();
        require VIEW_PATH . '/pages/admin/categories/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/categories/create
    // =========================================================================
    public function create(): void
    {
        $type     = in_array($_GET['type'] ?? '', ['class', 'instructor']) ? ($_GET['type']) : 'class';
        $category = null;

        $csrfToken  = Csrf::token();
        $pageTitle  = '카테고리 추가';
        $activeMenu = 'categories';
        ob_start();
        require VIEW_PATH . '/pages/admin/categories/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/categories  (등록 폼 submit)
    // =========================================================================
    public function storeForm(): void
    {
        Csrf::verify();

        $type      = $_POST['type'] ?? '';
        $name      = trim($_POST['name'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if (!in_array($type, ['class', 'instructor']) || $name === '') {
            header("Location: /admin/categories/create?type={$type}&error=1");
            exit;
        }

        $tbl = $this->table($type);
        $idx = (int) DB::insert(
            "INSERT INTO {$tbl} (name, sort_order, is_active) VALUES (?, ?, 1)",
            [$name, $sortOrder]
        );

        header("Location: /admin/categories/{$idx}/edit?type={$type}&saved=1");
        exit;
    }

    // =========================================================================
    // GET /admin/categories/{idx}/edit
    // =========================================================================
    public function edit(string $idx): void
    {
        $intIdx = (int) $idx;
        $type   = in_array($_GET['type'] ?? '', ['class', 'instructor']) ? ($_GET['type']) : 'class';
        $tbl    = $this->table($type);

        $category = DB::selectOne("SELECT * FROM {$tbl} WHERE category_idx = ?", [$intIdx]);
        if (!$category) {
            header('Location: /admin/categories?type=' . $type);
            exit;
        }

        $csrfToken  = Csrf::token();
        $pageTitle  = '카테고리 수정';
        $activeMenu = 'categories';
        ob_start();
        require VIEW_PATH . '/pages/admin/categories/form.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/categories/{idx}  (폼 submit)
    // =========================================================================
    public function updateForm(string $idx): void
    {
        Csrf::verify();

        $intIdx    = (int) $idx;
        $type      = $_POST['type'] ?? '';
        $name      = trim($_POST['name'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 0;

        if (!in_array($type, ['class', 'instructor']) || $name === '') {
            header("Location: /admin/categories/{$intIdx}/edit?type={$type}&error=1");
            exit;
        }

        $tbl = $this->table($type);
        DB::execute(
            "UPDATE {$tbl} SET name = ?, sort_order = ?, is_active = ? WHERE category_idx = ?",
            [$name, $sortOrder, $isActive, $intIdx]
        );

        header("Location: /admin/categories/{$intIdx}/edit?type={$type}&saved=1");
        exit;
    }

    // =========================================================================
    // POST /admin/categories/store  (AJAX)
    // =========================================================================
    public function store(): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $type = $_POST['type'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if (!in_array($type, ['class', 'instructor'])) {
            echo json_encode(['ok' => false, 'error' => '잘못된 요청입니다.']);
            exit;
        }
        if ($name === '') {
            echo json_encode(['ok' => false, 'error' => '카테고리명을 입력해주세요.']);
            exit;
        }

        $tbl = $this->table($type);
        $idx = (int) DB::insert(
            "INSERT INTO {$tbl} (name, sort_order, is_active) VALUES (?, ?, 1)",
            [$name, $sortOrder]
        );

        echo json_encode(['ok' => true, 'category_idx' => $idx, 'name' => $name, 'sort_order' => $sortOrder]);
        exit;
    }

    // =========================================================================
    // POST /admin/categories/{idx}/update  (AJAX)
    // =========================================================================
    public function update(string $idx): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $type      = $_POST['type'] ?? '';
        $name      = trim($_POST['name'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if (!in_array($type, ['class', 'instructor'])) {
            echo json_encode(['ok' => false, 'error' => '잘못된 요청입니다.']);
            exit;
        }
        if ($name === '') {
            echo json_encode(['ok' => false, 'error' => '카테고리명을 입력해주세요.']);
            exit;
        }

        $tbl = $this->table($type);
        DB::execute(
            "UPDATE {$tbl} SET name = ?, sort_order = ?, is_active = ? WHERE category_idx = ?",
            [$name, $sortOrder, $isActive, (int) $idx]
        );

        echo json_encode(['ok' => true]);
        exit;
    }

    // =========================================================================
    // POST /admin/categories/{idx}/delete  (AJAX)
    // =========================================================================
    public function destroy(string $idx): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $type = $_POST['type'] ?? '';
        if (!in_array($type, ['class', 'instructor'])) {
            echo json_encode(['ok' => false, 'error' => '잘못된 요청입니다.']);
            exit;
        }

        $intIdx = (int) $idx;

        // 사용 중인지 확인
        if ($type === 'class') {
            $inUse = DB::selectOne(
                'SELECT 1 FROM lc_class WHERE category_idx = ? AND deleted_at IS NULL LIMIT 1',
                [$intIdx]
            );
        } else {
            $inUse = DB::selectOne(
                'SELECT 1 FROM lc_instructor WHERE category_idx = ? AND deleted_at IS NULL LIMIT 1',
                [$intIdx]
            );
        }

        if ($inUse) {
            echo json_encode(['ok' => false, 'error' => '사용 중인 카테고리는 삭제할 수 없습니다.']);
            exit;
        }

        $tbl = $this->table($type);
        DB::execute("DELETE FROM {$tbl} WHERE category_idx = ?", [$intIdx]);

        echo json_encode(['ok' => true]);
        exit;
    }
}
