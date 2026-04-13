<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class NoticeRepository
{
    public function getAdminList(array $filters, int $page, int $limit): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = 'title LIKE ?';
            $params[] = '%' . $filters['q'] . '%';
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[]  = 'is_active = ?';
            $params[] = (int) $filters['is_active'];
        }

        $whereStr = implode(' AND ', $where);
        $countSql = "SELECT COUNT(*) AS cnt FROM lc_notice WHERE {$whereStr}";
        $total    = (int) (DB::selectOne($countSql, $params)['cnt'] ?? 0);

        $offset  = ($page - 1) * $limit;
        $listSql = "SELECT * FROM lc_notice WHERE {$whereStr}
                    ORDER BY is_pinned DESC, created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
        $list = DB::select($listSql, $params);

        return ['list' => $list, 'total' => $total];
    }

    public function findByIdx(int $noticeIdx): ?array
    {
        return DB::selectOne('SELECT * FROM lc_notice WHERE notice_idx = ? AND deleted_at IS NULL', [$noticeIdx]);
    }

    public function create(array $data): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_notice (title, content, is_pinned, is_active) VALUES (?, ?, ?, ?)',
            [$data['title'], $data['content'], (int) ($data['is_pinned'] ?? 0), (int) ($data['is_active'] ?? 1)]
        );
    }

    public function update(int $noticeIdx, array $data): void
    {
        DB::execute(
            'UPDATE lc_notice SET title = ?, content = ?, is_pinned = ?, is_active = ? WHERE notice_idx = ?',
            [$data['title'], $data['content'], (int) ($data['is_pinned'] ?? 0), (int) ($data['is_active'] ?? 1), $noticeIdx]
        );
    }

    public function delete(int $noticeIdx): void
    {
        DB::execute('UPDATE lc_notice SET is_active = 0 WHERE notice_idx = ?', [$noticeIdx]);
    }

    // =========================================================================
    // 공개 페이지용
    // =========================================================================

    public function getPublicList(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        $list = DB::select(
            'SELECT * FROM lc_notice WHERE is_active = 1
             ORDER BY is_pinned DESC, created_at DESC
             LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM lc_notice WHERE is_active = 1'
        )['cnt'] ?? 0);

        return ['list' => $list, 'total' => $total];
    }

    public function findPublicByIdx(int $noticeIdx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_notice WHERE notice_idx = ? AND is_active = 1',
            [$noticeIdx]
        );
    }

    public function getPrevNext(int $noticeIdx): array
    {
        $prev = DB::selectOne(
            'SELECT notice_idx, title, created_at FROM lc_notice
             WHERE notice_idx < ? AND is_active = 1
             ORDER BY notice_idx DESC LIMIT 1',
            [$noticeIdx]
        );
        $next = DB::selectOne(
            'SELECT notice_idx, title, created_at FROM lc_notice
             WHERE notice_idx > ? AND is_active = 1
             ORDER BY notice_idx ASC LIMIT 1',
            [$noticeIdx]
        );
        return ['prev' => $prev, 'next' => $next];
    }

    public function incrementViews(int $noticeIdx): void
    {
        DB::execute(
            'UPDATE lc_notice SET views = views + 1 WHERE notice_idx = ?',
            [$noticeIdx]
        );
    }
}
