<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class SearchRepository
{
    // =========================================================================
    // 통합 검색
    // =========================================================================

    /**
     * 키워드로 클래스 + 강사를 통합 검색한다.
     *
     * 클래스 검색 로직 (UNION, 중복 제거):
     *   ① 클래스 제목 일치: lc_class.title LIKE '%q%'
     *   ② 강사명 일치:      lc_instructor.name LIKE '%q%' → 해당 강사의 전체 클래스
     *
     * 강사 검색 로직:
     *   ① 강사명 LIKE 매칭
     *   ② 클래스 검색 결과에 포함된 instructor_idx (중복 제거)
     *
     * @return array{classes: list<array>, instructors: list<array>, total: int}
     */
    public function search(string $q): array
    {
        $like = '%' . $q . '%';

        // ── 클래스 검색 ───────────────────────────────────────────────────────
        // ① 제목 일치
        // ② 강사명 일치 → 해당 강사의 클래스 (is_active=1 필수)
        // UNION으로 합산 후 중복 제거, 강사명 완전일치 우선 정렬
        $classes = DB::select(
            "SELECT c.class_idx, c.type, c.title, c.thumbnail,
                    c.price, c.price_origin, c.badge_hot, c.badge_new,
                    c.instructor_idx,
                    i.name  AS instructor_name,
                    cat.name AS category_name,
                    -- 관련도: 강사명 완전일치(2) > 제목 포함(1)
                    MAX(rel) AS relevance
             FROM (
                 -- ① 제목 일치
                 SELECT c2.class_idx, 1 AS rel
                 FROM lc_class c2
                 WHERE c2.title LIKE ? AND c2.is_active = 1 AND c2.deleted_at IS NULL

                 UNION

                 -- ② 강사명 일치 → 해당 강사의 클래스
                 SELECT c3.class_idx, 2 AS rel
                 FROM lc_class c3
                 JOIN lc_instructor i2 ON i2.instructor_idx = c3.instructor_idx
                 WHERE i2.name LIKE ? AND c3.is_active = 1 AND c3.deleted_at IS NULL
             ) AS sub
             JOIN lc_class c          ON c.class_idx      = sub.class_idx
             JOIN lc_instructor i     ON i.instructor_idx = c.instructor_idx
             LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
             GROUP BY c.class_idx
             ORDER BY MAX(rel) DESC, c.sort_order ASC, c.created_at DESC",
            [$like, $like]
        );

        // ── 강사 검색 ─────────────────────────────────────────────────────────
        // ① 강사명 LIKE 매칭
        // ② 위 클래스 결과에 등장한 instructor_idx (클래스 검색 시 관련 강사 노출)
        $classInstructorIds = array_unique(
            array_column($classes, 'instructor_idx')
        );

        if (!empty($classInstructorIds)) {
            $placeholders = implode(',', array_fill(0, count($classInstructorIds), '?'));
            $instructors  = DB::select(
                "SELECT i.instructor_idx, i.name, i.photo,
                        i.sns_youtube, i.sns_instagram, i.sns_facebook,
                        (SELECT COUNT(*) FROM lc_class c
                         WHERE c.instructor_idx = i.instructor_idx
                           AND c.is_active = 1 AND c.deleted_at IS NULL) AS class_count
                 FROM lc_instructor i
                 WHERE (i.name LIKE ? OR i.instructor_idx IN ({$placeholders}))
                   AND i.is_active = 1
                 ORDER BY
                     CASE WHEN i.name LIKE ? THEN 0 ELSE 1 END,
                     i.sort_order ASC",
                array_merge([$like], $classInstructorIds, [$like])
            );
        } else {
            $instructors = DB::select(
                "SELECT i.instructor_idx, i.name, i.photo,
                        i.sns_youtube, i.sns_instagram, i.sns_facebook,
                        (SELECT COUNT(*) FROM lc_class c
                         WHERE c.instructor_idx = i.instructor_idx
                           AND c.is_active = 1 AND c.deleted_at IS NULL) AS class_count
                 FROM lc_instructor i
                 WHERE i.name LIKE ? AND i.is_active = 1
                 ORDER BY i.sort_order ASC",
                [$like]
            );
        }

        // 강사 소개 첫 항목 일괄 매핑
        if (!empty($instructors)) {
            $ids          = array_column($instructors, 'instructor_idx');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $intros       = DB::select(
                "SELECT instructor_idx, content FROM lc_instructor_intro
                 WHERE instructor_idx IN ({$placeholders})
                 ORDER BY instructor_idx ASC, sort_order ASC",
                $ids
            );
            $introMap = [];
            foreach ($intros as $row) {
                $idx = $row['instructor_idx'];
                if (!isset($introMap[$idx])) $introMap[$idx] = $row['content'];
            }
            foreach ($instructors as &$ins) {
                $ins['intro'] = $introMap[$ins['instructor_idx']] ?? '';
            }
            unset($ins);
        }

        $total = count($classes) + count($instructors);

        return [
            'classes'     => $classes,
            'instructors' => $instructors,
            'total'       => $total,
        ];
    }

    // =========================================================================
    // 검색 로그
    // =========================================================================

    public function logSearch(string $keyword, int $resultCount): void
    {
        DB::insert(
            'INSERT INTO lc_search_log (keyword, result_count) VALUES (?, ?)',
            [$keyword, $resultCount]
        );
    }

    // =========================================================================
    // 추천 검색어
    // =========================================================================

    /** @return list<array{suggest_idx: int, keyword: string}> */
    public function getSuggestions(): array
    {
        return DB::select(
            'SELECT suggest_idx, keyword
             FROM lc_search_suggest
             WHERE is_active = 1
             ORDER BY sort_order ASC, suggest_idx ASC'
        );
    }
}
