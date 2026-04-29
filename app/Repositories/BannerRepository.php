<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class BannerRepository
{
    // =========================================================================
    // 목록
    // =========================================================================

    /** @return array{list: list<array>, total: int} */
    public function getAdminList(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;

        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM lc_banner WHERE deleted_at IS NULL'
        )['cnt'] ?? 0);

        $list = DB::select(
            "SELECT banner_idx, image_path, link_url, link_target,
                    alt_text, start_date, end_date, is_active, sort_order, created_at
             FROM lc_banner
             WHERE deleted_at IS NULL
             ORDER BY sort_order ASC, banner_idx DESC
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
            'SELECT * FROM lc_banner WHERE banner_idx = ? AND deleted_at IS NULL LIMIT 1',
            [$idx]
        );
    }

    // =========================================================================
    // 생성
    // =========================================================================

    public function create(array $data): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_banner
                (image_path, link_url, link_target, alt_text,
                 start_date, end_date, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['image_path']  ?? null,
                $data['link_url']    ?: null,
                $data['link_target'] ?? '_blank',
                $data['alt_text']    ?? '',
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
            'image_path', 'link_url', 'link_target', 'alt_text',
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
            'UPDATE lc_banner SET ' . implode(', ', $sets) . ' WHERE banner_idx = ?',
            $params
        );
    }

    // =========================================================================
    // 삭제
    // =========================================================================

    public function delete(int $idx): void
    {
        DB::execute('UPDATE lc_banner SET deleted_at = NOW() WHERE banner_idx = ? AND deleted_at IS NULL', [$idx]);
    }
}
