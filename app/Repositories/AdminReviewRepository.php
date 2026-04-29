<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class AdminReviewRepository
{
    public function getAdminList(array $filters, int $page, int $limit): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($filters['q'] !== '') {
            $where[]  = '(r.title LIKE ? OR r.content LIKE ? OR m.mb_name LIKE ? OR c.title LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q]);
        }

        if ($filters['is_active'] !== '') {
            $where[]  = 'r.is_active = ?';
            $params[] = (int) $filters['is_active'];
        }

        if ($filters['rating'] !== '') {
            $where[]  = 'r.rating = ?';
            $params[] = (int) $filters['rating'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $total = (int) (DB::selectOne(
            "SELECT COUNT(*) AS cnt
             FROM lc_review r
             JOIN lc_class  c ON c.class_idx  = r.class_idx
             JOIN lc_member m ON m.member_idx = r.member_idx
             WHERE {$whereStr}",
            $params
        )['cnt'] ?? 0);

        $list = DB::select(
            "SELECT r.review_idx, r.rating, r.title, r.content, r.is_active, r.created_at,
                    c.title AS class_title,
                    m.mb_name AS member_name
             FROM lc_review r
             JOIN lc_class  c ON c.class_idx  = r.class_idx
             JOIN lc_member m ON m.member_idx = r.member_idx
             WHERE {$whereStr}
             ORDER BY r.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return ['list' => $list, 'total' => $total];
    }

    public function findByIdx(int $reviewIdx): ?array
    {
        return DB::selectOne(
            "SELECT r.*,
                    c.title AS class_title, c.thumbnail AS class_thumbnail,
                    m.mb_name AS member_name, m.mb_email AS member_email
             FROM lc_review r
             JOIN lc_class  c ON c.class_idx  = r.class_idx
             JOIN lc_member m ON m.member_idx = r.member_idx
             WHERE r.review_idx = ?",
            [$reviewIdx]
        );
    }

    public function getImages(int $reviewIdx): array
    {
        return DB::select(
            "SELECT image_path FROM lc_review_image
             WHERE review_idx = ? AND deleted_at IS NULL
             ORDER BY sort_order ASC",
            [$reviewIdx]
        );
    }

    public function setActive(int $reviewIdx, int $isActive): void
    {
        DB::execute(
            "UPDATE lc_review SET is_active = ? WHERE review_idx = ?",
            [$isActive, $reviewIdx]
        );
    }

    public function delete(int $reviewIdx): void
    {
        DB::execute("DELETE FROM lc_review_image WHERE review_idx = ?", [$reviewIdx]);
        DB::execute("DELETE FROM lc_review WHERE review_idx = ?", [$reviewIdx]);
    }
}
