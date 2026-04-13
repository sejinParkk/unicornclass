<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class InstructorRepository
{
    // =========================================================================
    // 강사 목록 / 조회
    // =========================================================================

    /** 드롭다운용 활성 강사 목록 */
    public function getActiveList(): array
    {
        return DB::select(
            'SELECT instructor_idx, name, field FROM lc_instructor
             WHERE is_active = 1 AND deleted_at IS NULL
             ORDER BY sort_order ASC, name ASC'
        );
    }

    public function findById(int $instructorIdx): ?array
    {
        $row = DB::selectOne(
            'SELECT i.*, ic.name AS category_name
             FROM lc_instructor i
             LEFT JOIN lc_instructor_category ic ON ic.category_idx = i.category_idx
             WHERE i.instructor_idx = ? AND i.deleted_at IS NULL',
            [$instructorIdx]
        );
        if (!$row) return null;

        $row['intros']  = $this->getIntros($instructorIdx);
        $row['careers'] = $this->getCareers($instructorIdx);

        return $row;
    }

    public function getIntros(int $instructorIdx): array
    {
        return DB::select(
            'SELECT intro_idx, content FROM lc_instructor_intro
             WHERE instructor_idx = ? ORDER BY sort_order ASC',
            [$instructorIdx]
        );
    }

    public function getCareers(int $instructorIdx): array
    {
        return DB::select(
            'SELECT career_idx, content FROM lc_instructor_career
             WHERE instructor_idx = ? ORDER BY sort_order ASC',
            [$instructorIdx]
        );
    }

    public function saveIntros(int $instructorIdx, array $items): void
    {
        DB::execute('DELETE FROM lc_instructor_intro WHERE instructor_idx = ?', [$instructorIdx]);
        foreach ($items as $i => $content) {
            $content = trim($content);
            if ($content === '') continue;
            DB::execute(
                'INSERT INTO lc_instructor_intro (instructor_idx, content, sort_order) VALUES (?, ?, ?)',
                [$instructorIdx, $content, $i + 1]
            );
        }
    }

    public function saveCareers(int $instructorIdx, array $items): void
    {
        DB::execute('DELETE FROM lc_instructor_career WHERE instructor_idx = ?', [$instructorIdx]);
        foreach ($items as $i => $content) {
            $content = trim($content);
            if ($content === '') continue;
            DB::execute(
                'INSERT INTO lc_instructor_career (instructor_idx, content, sort_order) VALUES (?, ?, ?)',
                [$instructorIdx, $content, $i + 1]
            );
        }
    }

    /** 관리자용 강사 목록 (검색/페이지네이션) */
    public function getAdminList(array $filters = [], int $page = 1, int $limit = 15): array
    {
        $where  = ['i.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = '(i.name LIKE ? OR i.field LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[]  = 'i.is_active = ?';
            $params[] = (int) $filters['is_active'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $total = (int) DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM lc_instructor i WHERE {$whereStr}",
            $params
        )['cnt'];

        $list = DB::select(
            "SELECT i.*, ic.name AS category_name,
                    (SELECT COUNT(*) FROM lc_class c WHERE c.instructor_idx = i.instructor_idx AND c.deleted_at IS NULL) AS class_count
             FROM lc_instructor i
             LEFT JOIN lc_instructor_category ic ON ic.category_idx = i.category_idx
             WHERE {$whereStr}
             ORDER BY i.sort_order ASC, i.instructor_idx DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return ['list' => $list, 'total' => $total];
    }

    /** 담당 강의(미삭제) 없으면 소프트 삭제, 있으면 blocked */
    public function delete(int $instructorIdx): string
    {
        if ($this->hasClasses($instructorIdx)) {
            return 'blocked';
        }
        DB::execute('UPDATE lc_instructor SET deleted_at = NOW() WHERE instructor_idx = ?', [$instructorIdx]);
        return 'deleted';
    }

    public function hasClasses(int $instructorIdx): bool
    {
        $row = DB::selectOne(
            'SELECT 1 FROM lc_class WHERE instructor_idx = ? AND deleted_at IS NULL LIMIT 1',
            [$instructorIdx]
        );
        return $row !== null;
    }

    // =========================================================================
    // 강사 등록 / 수정
    // =========================================================================

    public function create(array $data): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_instructor
                (category_idx, name, field, photo,
                 sns_youtube, sns_instagram, sns_facebook, sort_order, is_active)
             VALUES (?,?,?,?,?,?,?,?,?)',
            [
                $data['category_idx'] ?: null,
                $data['name'],
                $data['field'] ?? '',
                $data['photo'] ?? null,
                $data['sns_youtube'] ?? null,
                $data['sns_instagram'] ?? null,
                $data['sns_facebook'] ?? null,
                (int) ($data['sort_order'] ?? 0),
                (int) ($data['is_active'] ?? 1),
            ]
        );
    }

    public function update(int $instructorIdx, array $data): void
    {
        $sets   = [];
        $params = [];

        $fields = [
            'category_idx', 'name', 'field', 'photo',
            'sns_youtube', 'sns_instagram', 'sns_facebook', 'sort_order', 'is_active',
        ];
        foreach ($fields as $f) {
            if (!array_key_exists($f, $data)) continue;
            $sets[]   = "`{$f}` = ?";
            $params[] = $data[$f];
        }
        if (!$sets) return;

        $params[] = $instructorIdx;
        DB::execute(
            'UPDATE lc_instructor SET ' . implode(', ', $sets) . ' WHERE instructor_idx = ?',
            $params
        );
    }

    // =========================================================================
    // 강사 지원
    // =========================================================================

    public function getApplyList(array $filters = [], int $page = 1, int $limit = 15): array
    {
        $where  = ['1=1'];
        $params = [];

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(name LIKE ? OR email LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $total = (int) DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM lc_instructor_apply WHERE {$whereStr}",
            $params
        )['cnt'];

        $list = DB::select(
            "SELECT * FROM lc_instructor_apply
             WHERE {$whereStr}
             ORDER BY created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return ['list' => $list, 'total' => $total];
    }

    public function findApplyById(int $applyIdx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_instructor_apply WHERE apply_idx = ?',
            [$applyIdx]
        );
    }

    public function updateApplyStatus(int $applyIdx, string $status, ?string $rejectReason = null): void
    {
        DB::execute(
            'UPDATE lc_instructor_apply SET status = ?, reject_reason = ? WHERE apply_idx = ?',
            [$status, $rejectReason, $applyIdx]
        );
    }

    public function createApply(array $data): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_instructor_apply
                (name, phone, email, teach_field, teach_exp, bio, curriculum, teach_format,
                 sns_instagram, sns_youtube, sns_blog, sns_other, portfolio_link)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['name'],
                $data['phone'],
                $data['email'],
                $data['teach_field'],
                $data['teach_exp'],
                $data['bio'],
                $data['curriculum'],
                $data['teach_format'],
                $data['sns_instagram'] ?: null,
                $data['sns_youtube']   ?: null,
                $data['sns_blog']      ?: null,
                $data['sns_other']     ?: null,
                $data['portfolio_link'] ?: null,
            ]
        );
    }

    public function createApplyFile(int $applyIdx, string $filePath, string $originalName, int $fileSize): void
    {
        DB::execute(
            'INSERT INTO lc_instructor_apply_file (apply_idx, file_path, original_name, file_size)
             VALUES (?, ?, ?, ?)',
            [$applyIdx, $filePath, $originalName, $fileSize]
        );
    }

    /** 공개 목록 (페이지네이션, 활성 강사만) */
    public function getPublicList(int $page = 1, int $limit = 12): array
    {
        $offset = ($page - 1) * $limit;
        $list = DB::select(
            'SELECT i.*, ic.name AS category_name
             FROM lc_instructor i
             LEFT JOIN lc_instructor_category ic ON ic.category_idx = i.category_idx
             WHERE i.is_active = 1 AND i.deleted_at IS NULL
             ORDER BY i.sort_order ASC, i.instructor_idx ASC
             LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM lc_instructor WHERE is_active = 1 AND deleted_at IS NULL'
        )['cnt'] ?? 0);

        return ['list' => $list, 'total' => $total];
    }

    /** 강사 공개 상세 (담당 강의 포함) */
    public function findPublicById(int $instructorIdx): ?array
    {
        $row = $this->findById($instructorIdx);
        if (!$row || !$row['is_active']) return null;

        $row['classes'] = DB::select(
            'SELECT class_idx, title, type, thumbnail, summary
             FROM lc_class
             WHERE instructor_idx = ? AND is_active = 1 AND deleted_at IS NULL
             ORDER BY sort_order ASC, class_idx DESC',
            [$instructorIdx]
        );

        return $row;
    }
}
