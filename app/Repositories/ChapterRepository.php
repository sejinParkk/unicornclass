<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class ChapterRepository
{
    public function findByClassIdx(int $classIdx): array
    {
        return DB::select(
            'SELECT * FROM lc_class_chapter
             WHERE class_idx = ? AND is_active = 1
             ORDER BY sort_order ASC, chapter_idx ASC',
            [$classIdx]
        );
    }

    public function findById(int $chapterIdx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_class_chapter WHERE chapter_idx = ?',
            [$chapterIdx]
        );
    }

    /** 초 단위 → "MM:SS" 변환 */
    public static function secondsToDisplay(int $seconds): string
    {
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;
        return sprintf('%d:%02d', $m, $s);
    }

    /** "MM:SS" → 초 단위 변환 */
    public static function displayToSeconds(string $display): int
    {
        $parts = explode(':', trim($display));
        if (count($parts) === 2) {
            return (int)$parts[0] * 60 + (int)$parts[1];
        }
        return (int)$parts[0];
    }

    public function create(int $classIdx, array $data): int
    {
        $nextOrder = $this->nextSortOrder($classIdx);

        return (int) DB::insert(
            'INSERT INTO lc_class_chapter (class_idx, title, vimeo_url, duration, sort_order)
             VALUES (?, ?, ?, ?, ?)',
            [
                $classIdx,
                $data['title'],
                $data['vimeo_url'] ?? '',
                self::displayToSeconds($data['duration'] ?? '0:00'),
                $nextOrder,
            ]
        );
    }

    public function update(int $chapterIdx, array $data): void
    {
        DB::execute(
            'UPDATE lc_class_chapter
             SET title = ?, vimeo_url = ?, duration = ?, sort_order = ?
             WHERE chapter_idx = ?',
            [
                $data['title'],
                $data['vimeo_url'] ?? '',
                self::displayToSeconds($data['duration'] ?? '0:00'),
                (int) ($data['sort_order'] ?? 0),
                $chapterIdx,
            ]
        );
    }

    /** 소프트 삭제 */
    public function delete(int $chapterIdx): void
    {
        DB::execute(
            'UPDATE lc_class_chapter SET is_active = 0 WHERE chapter_idx = ?',
            [$chapterIdx]
        );
    }

    /** sort_order 배열 일괄 업데이트: [[chapter_idx, sort_order], ...] */
    public function updateSortOrders(array $orders): void
    {
        foreach ($orders as $i => [$chapterIdx, $sortOrder]) {
            DB::execute(
                'UPDATE lc_class_chapter SET sort_order = ? WHERE chapter_idx = ?',
                [(int) $sortOrder, (int) $chapterIdx]
            );
        }
    }

    private function nextSortOrder(int $classIdx): int
    {
        $row = DB::selectOne(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order
             FROM lc_class_chapter WHERE class_idx = ? AND is_active = 1',
            [$classIdx]
        );
        return (int) ($row['next_order'] ?? 1);
    }
}
