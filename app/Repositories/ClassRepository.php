<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class ClassRepository
{
    // =========================================================================
    // 관리자 목록 조회
    // =========================================================================

    /**
     * 관리자용 강의 목록 (비활성 포함, 수강자 수 / 후기 수 포함)
     *
     * @return array{list: list<array>, total: int}
     */
    public function getAdminList(array $filters = [], int $page = 1, int $limit = 15): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = 'c.title LIKE ?';
            $params[] = '%' . $filters['q'] . '%';
        }
        if (isset($filters['type']) && $filters['type'] !== '') {
            $where[]  = 'c.type = ?';
            $params[] = $filters['type'];
        }
        if (isset($filters['category_idx']) && $filters['category_idx'] !== '') {
            $where[]  = 'c.category_idx = ?';
            $params[] = (int) $filters['category_idx'];
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[]  = 'c.is_active = ?';
            $params[] = (int) $filters['is_active'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $total = (int) DB::selectOne(
            "SELECT COUNT(*) AS cnt
             FROM lc_class c
             JOIN lc_instructor i ON i.instructor_idx = c.instructor_idx
             WHERE {$whereStr} AND c.deleted_at IS NULL",
            $params
        )['cnt'];

        $list = DB::select(
            "SELECT c.*,
                    i.name                AS instructor_name,
                    cat.name              AS category_name,
                    (SELECT COUNT(*) FROM lc_enroll e WHERE e.class_idx = c.class_idx)  AS enroll_count,
                    (SELECT COUNT(*) FROM lc_review r WHERE r.class_idx = c.class_idx)  AS review_count
             FROM lc_class c
             JOIN lc_instructor i   ON i.instructor_idx  = c.instructor_idx
             LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
             WHERE {$whereStr} AND c.deleted_at IS NULL
             ORDER BY c.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return ['list' => $list, 'total' => $total];
    }

    // =========================================================================
    // 단건 조회
    // =========================================================================

    public function findById(int $classIdx): ?array
    {
        return DB::selectOne(
            'SELECT c.*, i.name AS instructor_name, cat.name AS category_name
             FROM lc_class c
             JOIN lc_instructor i ON i.instructor_idx = c.instructor_idx
             LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
             WHERE c.class_idx = ? AND c.deleted_at IS NULL',
            [$classIdx]
        );
    }

    // =========================================================================
    // 등록 / 수정 / 삭제
    // =========================================================================

    public function create(array $data): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_class
                (category_idx, instructor_idx, type, title, summary, description,
                 thumbnail, price, price_origin, duration_days, kakao_url, vimeo_url,
                 badge_hot, badge_new, sale_end_at, is_active, sort_order)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['category_idx'] ?: null,
                $data['instructor_idx'],
                $data['type'],
                $data['title'],
                $data['summary'] ?? null,
                $data['description'] ?? null,
                $data['thumbnail'] ?? null,
                (int) ($data['price'] ?? 0),
                (int) ($data['price_origin'] ?? 0),
                (int) ($data['duration_days'] ?? 180),
                $data['kakao_url'] ?? null,
                $data['vimeo_url'] ?? null,
                (int) ($data['badge_hot'] ?? 0),
                (int) ($data['badge_new'] ?? 0),
                $data['sale_end_at'] ?: null,
                (int) ($data['is_active'] ?? 0),
                (int) ($data['sort_order'] ?? 0),
            ]
        );
    }

    public function update(int $classIdx, array $data): void
    {
        $sets   = [];
        $params = [];

        $fields = [
            'category_idx', 'instructor_idx', 'title', 'summary', 'description',
            'thumbnail', 'price', 'price_origin', 'duration_days',
            'kakao_url', 'vimeo_url', 'badge_hot', 'badge_new',
            'sale_end_at', 'is_active', 'sort_order',
        ];

        foreach ($fields as $f) {
            if (!array_key_exists($f, $data)) continue;
            $sets[]   = "`{$f}` = ?";
            $params[] = $data[$f];
        }

        if (!$sets) return;

        $params[] = $classIdx;
        DB::execute(
            'UPDATE lc_class SET ' . implode(', ', $sets) . ' WHERE class_idx = ?',
            $params
        );
    }

    /** 수강자 있으면 삭제 불가(blocked), 없으면 소프트 삭제(deleted_at) */
    public function delete(int $classIdx): string
    {
        if ($this->hasEnrollments($classIdx)) {
            return 'blocked';
        }

        DB::execute('UPDATE lc_class SET deleted_at = NOW() WHERE class_idx = ?', [$classIdx]);
        return 'deleted';
    }

    public function hasEnrollments(int $classIdx): bool
    {
        $row = DB::selectOne(
            'SELECT 1 FROM lc_enroll WHERE class_idx = ? LIMIT 1',
            [$classIdx]
        );
        return $row !== null;
    }

    // =========================================================================
    // 공개 페이지용
    // =========================================================================

    /** 카테고리 목록 (활성 강의가 1개 이상 있는 것만) */
    public function getPublicCategories(): array
    {
        return DB::select(
            'SELECT cat.category_idx, cat.name
             FROM lc_class_category cat
             WHERE cat.is_active = 1
               AND EXISTS (
                   SELECT 1 FROM lc_class c
                   WHERE c.category_idx = cat.category_idx
                     AND c.is_active = 1 AND c.deleted_at IS NULL
               )
             ORDER BY cat.sort_order ASC, cat.category_idx ASC'
        );
    }

    /**
     * 공개 강의 목록
     * @param string $type       'free'|'premium'|'' (전체)
     * @param int    $categoryIdx 0=전체
     */
    public function getPublicList(int $page, int $limit, string $type = '', int $categoryIdx = 0): array
    {
        $where  = ['c.is_active = 1', 'c.deleted_at IS NULL'];
        $params = [];

        if ($type !== '') {
            $where[]  = 'c.type = ?';
            $params[] = $type;
        }
        if ($categoryIdx > 0) {
            $where[]  = 'c.category_idx = ?';
            $params[] = $categoryIdx;
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $total = (int) (DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM lc_class c WHERE {$whereStr}",
            $params
        )['cnt'] ?? 0);

        $list = DB::select(
            "SELECT c.class_idx, c.title, c.type, c.thumbnail, c.summary,
                    c.badge_hot, c.badge_new, c.price, c.price_origin,
                    i.name AS instructor_name, cat.name AS category_name
             FROM lc_class c
             JOIN lc_instructor i ON i.instructor_idx = c.instructor_idx
             LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
             WHERE {$whereStr}
             ORDER BY c.sort_order ASC, c.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return ['list' => $list, 'total' => $total];
    }

    /** 공개 강의 단건 (활성·미삭제 확인, 챕터·파일·강사 소개 포함) */
    public function findPublicById(int $classIdx): ?array
    {
        $row = DB::selectOne(
            'SELECT c.*, i.name AS instructor_name, i.instructor_idx,
                    i.photo AS instructor_photo, i.field AS instructor_field,
                    i.sns_youtube, i.sns_instagram, i.sns_facebook,
                    cat.name AS category_name
             FROM lc_class c
             JOIN lc_instructor i ON i.instructor_idx = c.instructor_idx
             LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
             WHERE c.class_idx = ? AND c.is_active = 1 AND c.deleted_at IS NULL',
            [$classIdx]
        );
        if (!$row) return null;

        $row['chapters'] = DB::select(
            'SELECT chapter_idx, title, duration
             FROM lc_class_chapter
             WHERE class_idx = ? AND is_active = 1
             ORDER BY sort_order ASC',
            [$classIdx]
        );

        $row['files'] = DB::select(
            'SELECT file_idx, file_type, title, file_path, file_size, external_url
             FROM lc_class_file
             WHERE class_idx = ? AND is_active = 1
             ORDER BY sort_order ASC',
            [$classIdx]
        );

        $row['instructor_intros'] = DB::select(
            'SELECT content FROM lc_instructor_intro
             WHERE instructor_idx = ? ORDER BY sort_order ASC',
            [$row['instructor_idx']]
        );

        $row['instructor_careers'] = DB::select(
            'SELECT content FROM lc_instructor_career
             WHERE instructor_idx = ? ORDER BY sort_order ASC',
            [$row['instructor_idx']]
        );

        return $row;
    }

    /** 수강 여부 확인 */
    public function findEnroll(int $memberIdx, int $classIdx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_enroll WHERE member_idx = ? AND class_idx = ?',
            [$memberIdx, $classIdx]
        );
    }

    /** 무료 수강 등록 */
    public function createFreeEnroll(int $memberIdx, int $classIdx, ?string $kakaoUrl, ?string $vimeoUrl): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_enroll (member_idx, class_idx, type, kakao_url, vimeo_url)
             VALUES (?, ?, \'free\', ?, ?)',
            [$memberIdx, $classIdx, $kakaoUrl, $vimeoUrl]
        );
    }

    /** 유료 수강 등록 (주문 연동) */
    public function createPremiumEnroll(int $memberIdx, int $classIdx, int $orderIdx, ?string $kakaoUrl, ?string $vimeoUrl, ?string $expireAt): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_enroll (member_idx, class_idx, order_idx, type, kakao_url, vimeo_url, expire_at)
             VALUES (?, ?, ?, \'premium\', ?, ?, ?)',
            [$memberIdx, $classIdx, $orderIdx, $kakaoUrl, $vimeoUrl, $expireAt]
        );
    }

    /** 주문 생성 (결제 성공 후) */
    public function createOrder(int $memberIdx, int $classIdx, int $amount, int $amountOrigin): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_order (member_idx, class_idx, amount, amount_origin, status, paid_at)
             VALUES (?, ?, ?, ?, \'paid\', NOW())',
            [$memberIdx, $classIdx, $amount, $amountOrigin]
        );
    }

    /** 찜 여부 확인 */
    public function findWish(int $memberIdx, int $classIdx): ?array
    {
        return DB::selectOne(
            'SELECT wish_idx FROM lc_wishlist WHERE member_idx = ? AND class_idx = ?',
            [$memberIdx, $classIdx]
        );
    }

    /** 찜 추가 */
    public function createWish(int $memberIdx, int $classIdx): void
    {
        DB::insert(
            'INSERT IGNORE INTO lc_wishlist (member_idx, class_idx) VALUES (?, ?)',
            [$memberIdx, $classIdx]
        );
    }

    /** 찜 삭제 */
    public function deleteWish(int $memberIdx, int $classIdx): void
    {
        DB::execute(
            'DELETE FROM lc_wishlist WHERE member_idx = ? AND class_idx = ?',
            [$memberIdx, $classIdx]
        );
    }

    /** 오픈채팅 클릭 로그 */
    public function logOpenchatClick(int $classIdx, ?int $memberIdx): void
    {
        DB::insert(
            'INSERT INTO lc_openchat_log (class_idx, member_idx) VALUES (?, ?)',
            [$classIdx, $memberIdx]
        );
    }

    /** total_episodes 카운트 갱신 */
    public function syncTotalEpisodes(int $classIdx): void
    {
        DB::execute(
            'UPDATE lc_class
             SET total_episodes = (
                 SELECT COUNT(*) FROM lc_class_chapter
                 WHERE class_idx = ? AND is_active = 1
             )
             WHERE class_idx = ?',
            [$classIdx, $classIdx]
        );
    }
}
