<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class SettingRepository
{
    /** 전체 설정을 key => value 맵으로 반환 */
    public function getAll(): array
    {
        $rows = DB::select('SELECT config_key, config_value FROM lc_site_config ORDER BY config_key');
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['config_key']] = $row['config_value'];
        }
        return $map;
    }

    /** 단일 키 조회 */
    public function get(string $key): ?string
    {
        $row = DB::selectOne(
            'SELECT config_value FROM lc_site_config WHERE config_key = ? LIMIT 1',
            [$key]
        );
        return $row['config_value'] ?? null;
    }

    /**
     * 복수 키 일괄 저장 (upsert)
     * @param array<string, string|null> $data
     */
    public function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            DB::execute(
                'INSERT INTO lc_site_config (config_key, config_value)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)',
                [$key, $value]
            );
        }
    }

    // -------------------------------------------------------------------------
    // 약관 (lc_terms)
    // -------------------------------------------------------------------------

    /** 약관 단일 조회 */
    public function getTerm(string $type): ?array
    {
        return DB::selectOne(
            "SELECT * FROM lc_terms WHERE type = ? LIMIT 1",
            [$type]
        );
    }

    /** 약관 내용 저장 (upsert) */
    public function saveTerm(string $type, string $title, string $content): void
    {
        DB::execute(
            'INSERT INTO lc_terms (type, title, content)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content)',
            [$type, $title, $content]
        );
    }
}
