<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class TermsRepository
{
    // =========================================================================
    // 공개 페이지용
    // =========================================================================

    /** 타입의 현재 버전 조회 */
    public function getCurrentByType(string $type): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_terms WHERE type = ? AND is_current = 1 AND deleted_at IS NULL LIMIT 1',
            [$type]
        );
    }

    /** 타입의 특정 버전 조회 (terms_idx 지정) */
    public function getByIdx(int $termsIdx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_terms WHERE terms_idx = ? AND deleted_at IS NULL',
            [$termsIdx]
        );
    }

    /** 타입의 모든 버전 목록 (시행일 DESC) — 공개 버전 셀렉터용 */
    public function getVersionsByType(string $type): array
    {
        return DB::select(
            'SELECT terms_idx, title, effective_at, is_current
             FROM lc_terms WHERE type = ? AND deleted_at IS NULL
             ORDER BY effective_at DESC, terms_idx DESC',
            [$type]
        );
    }

    // =========================================================================
    // 관리자용
    // =========================================================================

    /** 모든 타입의 현재 버전 요약 (관리자 인덱스) */
    public function getAllCurrentSummary(): array
    {
        $rows = DB::select(
            'SELECT t1.type, t1.terms_idx, t1.title, t1.effective_at, t1.updated_at,
                    (SELECT COUNT(*) FROM lc_terms t2 WHERE t2.type = t1.type AND t2.deleted_at IS NULL) AS version_count
             FROM lc_terms t1
             WHERE t1.is_current = 1 AND t1.deleted_at IS NULL
             ORDER BY t1.terms_idx ASC'
        );

        // type => row 맵으로 변환
        $map = [];
        foreach ($rows as $row) {
            $map[$row['type']] = $row;
        }
        return $map;
    }

    /** 특정 타입의 버전 목록 전체 (관리자) */
    public function getAdminVersions(string $type): array
    {
        return DB::select(
            'SELECT * FROM lc_terms WHERE type = ? AND deleted_at IS NULL
             ORDER BY effective_at DESC, terms_idx DESC',
            [$type]
        );
    }

    /** 새 버전 생성 */
    public function createVersion(string $type, string $title, string $content, string $effectiveAt, bool $setCurrent): int
    {
        if ($setCurrent) {
            DB::execute(
                'UPDATE lc_terms SET is_current = 0 WHERE type = ?',
                [$type]
            );
        }

        return (int) DB::insert(
            'INSERT INTO lc_terms (type, title, content, effective_at, is_current)
             VALUES (?, ?, ?, ?, ?)',
            [$type, $title, $content, $effectiveAt, $setCurrent ? 1 : 0]
        );
    }

    /** 버전 내용 수정 */
    public function updateVersion(int $termsIdx, string $title, string $content, string $effectiveAt): void
    {
        DB::execute(
            'UPDATE lc_terms SET title = ?, content = ?, effective_at = ? WHERE terms_idx = ?',
            [$title, $content, $effectiveAt, $termsIdx]
        );
    }

    /** 현재 버전 변경 (같은 타입의 기존 current 해제 후 지정) */
    public function setCurrent(int $termsIdx, string $type): void
    {
        DB::execute('UPDATE lc_terms SET is_current = 0 WHERE type = ?', [$type]);
        DB::execute('UPDATE lc_terms SET is_current = 1 WHERE terms_idx = ?', [$termsIdx]);
    }

    /** 버전 삭제 (현재 버전이면서 유일한 버전이면 삭제 불가 → 'blocked' 반환) */
    public function deleteVersion(int $termsIdx): string
    {
        $row = DB::selectOne('SELECT type, is_current FROM lc_terms WHERE terms_idx = ? AND deleted_at IS NULL', [$termsIdx]);
        if (!$row) return 'not_found';

        if ($row['is_current']) {
            $count = (int) (DB::selectOne(
                'SELECT COUNT(*) AS cnt FROM lc_terms WHERE type = ? AND deleted_at IS NULL',
                [$row['type']]
            )['cnt'] ?? 0);
            if ($count <= 1) return 'blocked';
        }

        DB::execute('UPDATE lc_terms SET deleted_at = NOW() WHERE terms_idx = ? AND deleted_at IS NULL', [$termsIdx]);
        return 'deleted';
    }
}
