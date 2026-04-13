<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class ClassMaterialRepository
{
    public function findByClassIdx(int $classIdx): array
    {
        return DB::select(
            'SELECT * FROM lc_class_file
             WHERE class_idx = ? AND is_active = 1
             ORDER BY sort_order ASC, file_idx ASC',
            [$classIdx]
        );
    }

    public function findById(int $fileIdx): ?array
    {
        return DB::selectOne(
            'SELECT * FROM lc_class_file WHERE file_idx = ?',
            [$fileIdx]
        );
    }

    public function createFile(int $classIdx, string $title, string $filePath, int $fileSize): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_class_file (class_idx, file_type, title, file_path, file_size)
             VALUES (?, ?, ?, ?, ?)',
            [$classIdx, 'file', $title, $filePath, $fileSize]
        );
    }

    public function createLink(int $classIdx, string $title, string $url): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_class_file (class_idx, file_type, title, external_url)
             VALUES (?, ?, ?, ?)',
            [$classIdx, 'link', $title, $url]
        );
    }

    /** 소프트 삭제 */
    public function delete(int $fileIdx): void
    {
        DB::execute(
            'UPDATE lc_class_file SET is_active = 0 WHERE file_idx = ?',
            [$fileIdx]
        );
    }
}
