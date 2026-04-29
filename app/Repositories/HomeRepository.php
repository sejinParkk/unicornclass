<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class HomeRepository
{
    // =========================================================================
    // 강의
    // =========================================================================

    /**
     * 홈 섹션용 강의 목록 (무료 or 프리미엄)
     *
     * @param  string $type  'free' | 'premium'
     * @param  int    $limit 최대 반환 수
     * @return list<array>
     */
    public function getHomeClasses(string $type, int $limit = 6): array
    {
        return DB::select(
            "SELECT c.class_idx, c.title, c.type, c.thumbnail,
                    c.price, c.price_origin, c.badge_hot, c.badge_new,
                    i.name AS instructor_name,
                    cat.name AS category_name
             FROM lc_class c
             JOIN lc_instructor i ON i.instructor_idx = c.instructor_idx
             LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
             WHERE c.type = ? AND c.is_active = 1 AND c.deleted_at IS NULL
             ORDER BY c.badge_hot DESC, c.sort_order ASC, c.created_at DESC
             LIMIT {$limit}",
            [$type]
        );
    }

    // =========================================================================
    // 강사
    // =========================================================================

    /**
     * 홈 섹션용 강사 목록 (소개 첫 항목 포함)
     *
     * @return list<array>
     */
    public function getHomeInstructors(int $limit = 8): array
    {
        $instructors = DB::select(
            "SELECT i.instructor_idx, i.name, i.field, i.photo,
                    i.sns_youtube, i.sns_instagram, i.sns_facebook
             FROM lc_instructor i
             WHERE i.is_active = 1
             ORDER BY i.sort_order ASC, i.created_at ASC
             LIMIT {$limit}",
        );

        if (empty($instructors)) {
            return [];
        }

        // 각 강사의 소개 첫 항목 일괄 조회
        $ids          = array_column($instructors, 'instructor_idx');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $intros       = DB::select(
            "SELECT instructor_idx, content
             FROM lc_instructor_intro
             WHERE instructor_idx IN ({$placeholders})
             ORDER BY instructor_idx, sort_order ASC",
            $ids
        );

        // 강사별 첫 번째 소개 항목 매핑
        $introMap = [];
        foreach ($intros as $intro) {
            $idx = $intro['instructor_idx'];
            if (!isset($introMap[$idx])) {
                $introMap[$idx] = $intro['content'];
            }
        }

        foreach ($instructors as &$ins) {
            $ins['intro'] = $introMap[$ins['instructor_idx']] ?? '';
        }
        unset($ins);

        return $instructors;
    }

    // =========================================================================
    // 수강생 후기
    // =========================================================================

    /**
     * 홈 섹션용 최신 후기 (활성, 강의명·작성자 포함)
     *
     * @return list<array>
     */
    public function getHomeReviews(int $limit = 6): array
    {
        $reviews = DB::select(
            "SELECT r.review_idx, r.rating, r.content, r.created_at,
                    c.title AS class_title, c.thumbnail AS class_thumbnail,
                    m.mb_name AS member_name
             FROM lc_review r
             JOIN lc_class   c ON c.class_idx   = r.class_idx
             JOIN lc_member  m ON m.member_idx  = r.member_idx
             WHERE r.is_active = 1
             ORDER BY r.created_at DESC
             LIMIT {$limit}",
        );

        if (empty($reviews)) return [];

        $ids          = array_column($reviews, 'review_idx');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $imgRows      = DB::select(
            "SELECT review_idx, image_path FROM lc_review_image
             WHERE review_idx IN ({$placeholders}) AND deleted_at IS NULL ORDER BY review_idx, sort_order",
            $ids
        );

        $imageMap = [];
        foreach ($imgRows as $row) {
            $imageMap[$row['review_idx']][] = $row['image_path'];
        }
        foreach ($reviews as &$rv) {
            $rv['images'] = $imageMap[$rv['review_idx']] ?? [];
        }
        unset($rv);

        return $reviews;
    }

    // =========================================================================
    // 이벤트/공지 배너
    // =========================================================================

    /**
     * 노출 기간 + is_active=1 인 배너 목록
     *
     * @return list<array>
     */
    public function getActiveBanners(): array
    {
        return DB::select(
            "SELECT banner_idx, image_path, link_url, link_target, alt_text
             FROM lc_banner
             WHERE is_active = 1
               AND deleted_at IS NULL
               AND (start_date IS NULL OR start_date <= CURDATE())
               AND (end_date   IS NULL OR end_date   >= CURDATE())
             ORDER BY sort_order ASC, banner_idx ASC"
        );
    }

    // =========================================================================
    // 메인 팝업
    // =========================================================================

    /**
     * 노출 기간 + is_active=1 인 팝업 전체 (슬라이더로 표시)
     *
     * @return list<array>
     */
    public function getActivePopups(): array
    {
        return DB::select(
            "SELECT popup_idx, image_path, link_url, link_target
             FROM lc_popup
             WHERE is_active = 1
               AND deleted_at IS NULL
               AND (start_date IS NULL OR start_date <= CURDATE())
               AND (end_date   IS NULL OR end_date   >= CURDATE())
             ORDER BY sort_order ASC, popup_idx ASC"
        );
    }

    // =========================================================================
    // 사이트 설정 (홈 필요 키만)
    // =========================================================================

    /**
     * @param  list<string> $keys
     * @return array<string, string|null>
     */
    public function getSiteSettings(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $rows = DB::select(
            "SELECT config_key, config_value
             FROM lc_site_config
             WHERE config_key IN ({$placeholders})",
            $keys
        );
        $map = array_fill_keys($keys, null);
        foreach ($rows as $row) {
            $map[$row['config_key']] = $row['config_value'];
        }
        return $map;
    }
}
