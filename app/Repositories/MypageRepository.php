<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class MypageRepository
{
    // =========================================================================
    // 나의 강의 (수강 목록 + 진도율)
    // =========================================================================

    /**
     * 회원의 수강 목록 (진도율 포함)
     * type: 'all' | 'free' | 'premium'
     */
    public function getMyClasses(int $memberIdx, string $type = 'all'): array
    {
        $where  = ['e.member_idx = ?'];
        $params = [$memberIdx];

        if ($type === 'free' || $type === 'premium') {
            $where[]  = 'e.type = ?';
            $params[] = $type;
        }

        $whereStr = implode(' AND ', $where);

        return DB::select(
            "SELECT e.*,
                    c.title, c.thumbnail, c.type AS class_type, c.kakao_url,
                    c.total_episodes,
                    c.vimeo_url AS class_vimeo_url,
                    i.name AS instructor_name,
                    cat.name AS category_name,
                    (SELECT COUNT(*) FROM lc_progress p
                     WHERE p.member_idx = e.member_idx
                       AND p.class_idx  = e.class_idx
                       AND p.is_complete = 1) AS done_count
             FROM lc_enroll e
             JOIN lc_class   c ON c.class_idx       = e.class_idx
             LEFT JOIN lc_instructor    i   ON i.instructor_idx = c.instructor_idx
             LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
             WHERE {$whereStr}
             ORDER BY e.enrolled_at DESC",
            $params
        );
    }

    /**
     * 수강 탭별 카운트 (전체 / 무료 / 프리미엄)
     */
    public function getMyClassCounts(int $memberIdx): array
    {
        $rows = DB::select(
            "SELECT type, COUNT(*) AS cnt
             FROM lc_enroll
             WHERE member_idx = ?
             GROUP BY type",
            [$memberIdx]
        );

        $result = ['all' => 0, 'free' => 0, 'premium' => 0];
        foreach ($rows as $row) {
            $result[$row['type']] = (int) $row['cnt'];
            $result['all'] += (int) $row['cnt'];
        }
        return $result;
    }

    // =========================================================================
    // 찜목록
    // =========================================================================

    public function getWishlist(int $memberIdx): array
    {
        return DB::select(
            "SELECT w.wish_idx, w.created_at AS wished_at,
                    c.class_idx, c.title, c.thumbnail, c.type AS class_type,
                    c.price, c.price_origin, c.sale_end_at,
                    (SELECT 1 FROM lc_enroll e
                     WHERE e.member_idx = ? AND e.class_idx = c.class_idx
                     LIMIT 1) AS is_enrolled
             FROM lc_wishlist w
             JOIN lc_class c ON c.class_idx = w.class_idx
             WHERE w.member_idx = ?
               AND c.is_active = 1
             ORDER BY w.created_at DESC",
            [$memberIdx, $memberIdx]
        );
    }

    public function removeWish(int $wishIdx, int $memberIdx): void
    {
        DB::execute(
            "DELETE FROM lc_wishlist WHERE wish_idx = ? AND member_idx = ?",
            [$wishIdx, $memberIdx]
        );
    }

    // =========================================================================
    // 결제내역
    // =========================================================================

    public function getOrders(int $memberIdx, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $total = (int) (DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM lc_order WHERE member_idx = ?",
            [$memberIdx]
        )['cnt'] ?? 0);

        $list = DB::select(
            "SELECT o.*, c.title AS class_title, c.type AS class_type,
                    c.thumbnail, c.total_episodes,
                    (SELECT COUNT(*) FROM lc_progress p
                     WHERE p.member_idx = o.member_idx
                       AND p.class_idx  = o.class_idx
                       AND p.is_complete = 1) AS done_count
             FROM lc_order o
             JOIN lc_class c ON c.class_idx = o.class_idx
             WHERE o.member_idx = ?
             ORDER BY o.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            [$memberIdx]
        );

        return ['list' => $list, 'total' => $total];
    }

    public function getOrderDetail(int $orderIdx, int $memberIdx): ?array
    {
        return DB::selectOne(
            "SELECT o.*, c.title AS class_title, c.type AS class_type,
                    c.thumbnail, c.total_episodes,
                    (SELECT COUNT(*) FROM lc_progress p
                     WHERE p.member_idx = o.member_idx
                       AND p.class_idx  = o.class_idx
                       AND p.is_complete = 1) AS done_count
             FROM lc_order o
             JOIN lc_class c ON c.class_idx = o.class_idx
             WHERE o.order_idx = ? AND o.member_idx = ?",
            [$orderIdx, $memberIdx]
        );
    }

    /**
     * 환불 신청: paid → refund_req
     */
    public function requestRefund(int $orderIdx, int $memberIdx, string $reason): bool
    {
        $affected = DB::execute(
            "UPDATE lc_order
             SET status = 'refund_req', refund_reason = ?
             WHERE order_idx = ? AND member_idx = ? AND status = 'paid'",
            [$reason, $orderIdx, $memberIdx]
        );
        return $affected > 0;
    }

    // =========================================================================
    // 1:1 문의
    // =========================================================================

    public function getQnaList(int $memberIdx, string $status = ''): array
    {
        $where  = ['member_idx = ?'];
        $params = [$memberIdx];

        if ($status === 'wait' || $status === 'done') {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        $whereStr = implode(' AND ', $where);

        return DB::select(
            "SELECT qna_idx, category, title, status, created_at
             FROM lc_qna
             WHERE {$whereStr}
             ORDER BY created_at DESC",
            $params
        );
    }

    public function getQnaDetail(int $qnaIdx, int $memberIdx): ?array
    {
        return DB::selectOne(
            "SELECT * FROM lc_qna WHERE qna_idx = ? AND member_idx = ?",
            [$qnaIdx, $memberIdx]
        );
    }

    public function createQna(int $memberIdx, array $data): int
    {
        return (int) DB::insert(
            "INSERT INTO lc_qna (member_idx, category, title, content, file_path)
             VALUES (?, ?, ?, ?, ?)",
            [
                $memberIdx,
                $data['category'],
                $data['title'],
                $data['content'],
                $data['file_path'] ?? null,
            ]
        );
    }

    public function deleteQna(int $qnaIdx, int $memberIdx): bool
    {
        $affected = DB::execute(
            "DELETE FROM lc_qna WHERE qna_idx = ? AND member_idx = ? AND status = 'wait'",
            [$qnaIdx, $memberIdx]
        );
        return $affected > 0;
    }

    // =========================================================================
    // 후기
    // =========================================================================

    public function getReviews(int $memberIdx): array
    {
        return DB::select(
            "SELECT r.*, c.title AS class_title, c.thumbnail
             FROM lc_review r
             JOIN lc_class c ON c.class_idx = r.class_idx
             WHERE r.member_idx = ? AND r.is_active = 1
             ORDER BY r.created_at DESC",
            [$memberIdx]
        );
    }

    public function getReviewDetail(int $reviewIdx, int $memberIdx): ?array
    {
        return DB::selectOne(
            "SELECT r.*, c.title AS class_title
             FROM lc_review r
             JOIN lc_class c ON c.class_idx = r.class_idx
             WHERE r.review_idx = ? AND r.member_idx = ?",
            [$reviewIdx, $memberIdx]
        );
    }

    /**
     * 후기 탭용: 프리미엄 수강 강의 목록 + 후기 작성 여부
     * (후기 작성 가능 목록 + 수정 가능 목록 통합)
     */
    public function getReviewableClasses(int $memberIdx): array
    {
        return DB::select(
            "SELECT e.class_idx, c.title, c.thumbnail,
                    e.enrolled_at,
                    r.review_idx, r.rating, r.content, r.created_at AS review_at
             FROM lc_enroll e
             JOIN lc_class c ON c.class_idx = e.class_idx
             LEFT JOIN lc_review r ON r.class_idx = e.class_idx
                                   AND r.member_idx = e.member_idx
                                   AND r.is_active = 1
             WHERE e.member_idx = ?
               AND e.type = 'premium'
             ORDER BY e.enrolled_at DESC",
            [$memberIdx]
        );
    }

    /**
     * 이미 해당 강의에 후기를 작성했는지 확인
     */
    public function hasReview(int $memberIdx, int $classIdx): bool
    {
        $row = DB::selectOne(
            "SELECT 1 FROM lc_review WHERE member_idx = ? AND class_idx = ? AND is_active = 1 LIMIT 1",
            [$memberIdx, $classIdx]
        );
        return $row !== null;
    }

    public function createReview(int $memberIdx, array $data): int
    {
        return (int) DB::insert(
            "INSERT INTO lc_review (class_idx, member_idx, rating, content)
             VALUES (?, ?, ?, ?)",
            [$data['class_idx'], $memberIdx, $data['rating'], $data['content']]
        );
    }

    public function updateReview(int $reviewIdx, int $memberIdx, array $data): void
    {
        DB::execute(
            "UPDATE lc_review SET rating = ?, content = ?, updated_at = NOW()
             WHERE review_idx = ? AND member_idx = ?",
            [$data['rating'], $data['content'], $reviewIdx, $memberIdx]
        );
    }

    public function deleteReview(int $reviewIdx, int $memberIdx): bool
    {
        $affected = DB::execute(
            "UPDATE lc_review SET is_active = 0 WHERE review_idx = ? AND member_idx = ?",
            [$reviewIdx, $memberIdx]
        );
        return $affected > 0;
    }

    // =========================================================================
    // 회원탈퇴
    // =========================================================================

    public function withdraw(int $memberIdx): void
    {
        DB::execute(
            "UPDATE lc_member SET is_active = 0, leave_at = NOW() WHERE member_idx = ?",
            [$memberIdx]
        );
    }
}
