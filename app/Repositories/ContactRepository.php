<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class ContactRepository
{
    // =========================================================================
    // 목록 조회 (관리자)
    // =========================================================================
    public function getAdminList(array $filters, int $page, int $limit): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $like     = '%' . $filters['q'] . '%';
            $where[]  = '(m.mb_id LIKE ? OR m.mb_name LIKE ? OR q.title LIKE ?)';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }

        if (!empty($filters['status'])) {
            $where[]  = 'q.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $where[]  = 'q.category = ?';
            $params[] = $filters['category'];
        }

        $whereStr = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) AS cnt
                     FROM lc_qna q
                     JOIN lc_member m ON m.member_idx = q.member_idx
                     WHERE {$whereStr}";
        $total = (int) (DB::selectOne($countSql, $params)['cnt'] ?? 0);

        $offset  = ($page - 1) * $limit;
        $listSql = "SELECT q.*, m.mb_id, m.mb_name
                    FROM lc_qna q
                    JOIN lc_member m ON m.member_idx = q.member_idx
                    WHERE {$whereStr}
                    ORDER BY q.status ASC, q.created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
        $list = DB::select($listSql, $params);

        return ['list' => $list, 'total' => $total];
    }

    // =========================================================================
    // 상세 조회
    // =========================================================================
    public function findByIdx(int $qnaIdx): ?array
    {
        return DB::selectOne(
            "SELECT q.*, m.mb_id, m.mb_name, m.mb_email, m.mb_phone
             FROM lc_qna q
             JOIN lc_member m ON m.member_idx = q.member_idx
             WHERE q.qna_idx = ?",
            [$qnaIdx]
        );
    }

    // =========================================================================
    // 답변 저장
    // =========================================================================
    public function saveAnswer(int $qnaIdx, string $answer, int $adminIdx): void
    {
        DB::execute(
            "UPDATE lc_qna SET answer = ?, status = 'done', answered_by = ?, answered_at = NOW() WHERE qna_idx = ?",
            [$answer, $adminIdx, $qnaIdx]
        );
    }

    // =========================================================================
    // 미답변 수 (헤더 배지용)
    // =========================================================================
    public function countPending(): int
    {
        $row = DB::selectOne("SELECT COUNT(*) AS cnt FROM lc_qna WHERE status = 'wait'");
        return (int) ($row['cnt'] ?? 0);
    }
}
