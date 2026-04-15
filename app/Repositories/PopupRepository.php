<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class PopupRepository
{
    // =========================================================================
    // 목록
    // =========================================================================

    /** @return array{list: list<array>, total: int} */
    public function getAdminList(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;

        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM lc_popup'
        )['cnt'] ?? 0);

        $list = DB::select(
            "SELECT popup_idx, image_path, link_url, link_target,
                    start_date, end_date, is_active, sort_order, created_at
             FROM lc_popup
             ORDER BY sort_order ASC, popup_idx DESC
             LIMIT {$limit} OFFSET {$offset}"
        );

        return ['list' => $list, 'total' => $total];
    }

    // =========================================================================
    // 단건
    // =========================================================================

    public function findByIdx(int $idx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_popup WHERE popup_idx = ? LIMIT 1',
            [$idx]
        );
    }

    // =========================================================================
    // 생성
    // =========================================================================

    public function create(array $data): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_popup
                (image_path, link_url, link_target,
                 start_date, end_date, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['image_path']  ?? null,
                $data['link_url']    ?: null,
                $data['link_target'] ?? '_blank',
                $data['start_date']  ?: null,
                $data['end_date']    ?: null,
                (int) ($data['is_active']  ?? 1),
                (int) ($data['sort_order'] ?? 0),
            ]
        );
    }

    // =========================================================================
    // 수정
    // =========================================================================

    public function update(int $idx, array $data): void
    {
        $sets   = [];
        $params = [];

        $fields = [
            'image_path', 'link_url', 'link_target',
            'start_date', 'end_date', 'is_active', 'sort_order',
        ];
        foreach ($fields as $f) {
            if (!array_key_exists($f, $data)) continue;
            $sets[]   = "`{$f}` = ?";
            $params[] = $data[$f];
        }
        if (!$sets) return;

        $params[] = $idx;
        DB::execute(
            'UPDATE lc_popup SET ' . implode(', ', $sets) . ' WHERE popup_idx = ?',
            $params
        );
    }

    // =========================================================================
    // 삭제
    // =========================================================================

    public function delete(int $idx): void
    {
        DB::execute('DELETE FROM lc_popup WHERE popup_idx = ?', [$idx]);
    }
}
