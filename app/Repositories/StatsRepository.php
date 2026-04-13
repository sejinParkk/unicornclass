<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class StatsRepository
{
    // =========================================================================
    // 검색 로그
    // =========================================================================

    /** 키워드 빈도 순위 (기간 필터) */
    public function getSearchRanking(string $dateFrom = '', string $dateTo = '', int $limit = 50): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($dateFrom !== '') {
            $where[]  = 'DATE(searched_at) >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[]  = 'DATE(searched_at) <= ?';
            $params[] = $dateTo;
        }

        $whereStr = implode(' AND ', $where);
        return DB::select(
            "SELECT keyword, COUNT(*) AS search_count,
                    SUM(CASE WHEN result_count = 0 THEN 1 ELSE 0 END) AS no_result_count
             FROM lc_search_log
             WHERE {$whereStr}
             GROUP BY keyword
             ORDER BY search_count DESC
             LIMIT {$limit}",
            $params
        );
    }

    /** 날짜별 검색 건수 (최근 30일) */
    public function getSearchByDay(string $dateFrom = '', string $dateTo = ''): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($dateFrom !== '') {
            $where[]  = 'DATE(searched_at) >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[]  = 'DATE(searched_at) <= ?';
            $params[] = $dateTo;
        }

        $whereStr = implode(' AND ', $where);
        return DB::select(
            "SELECT DATE(searched_at) AS day, COUNT(*) AS cnt
             FROM lc_search_log
             WHERE {$whereStr}
             GROUP BY day
             ORDER BY day DESC
             LIMIT 30",
            $params
        );
    }

    /** 검색 총 건수 */
    public function getSearchTotal(string $dateFrom = '', string $dateTo = ''): int
    {
        $where  = ['1=1'];
        $params = [];
        if ($dateFrom !== '') { $where[] = 'DATE(searched_at) >= ?'; $params[] = $dateFrom; }
        if ($dateTo   !== '') { $where[] = 'DATE(searched_at) <= ?'; $params[] = $dateTo; }
        $row = DB::selectOne("SELECT COUNT(*) AS cnt FROM lc_search_log WHERE " . implode(' AND ', $where), $params);
        return (int) ($row['cnt'] ?? 0);
    }

    // =========================================================================
    // 오픈채팅 로그
    // =========================================================================

    /** 강의별 오픈채팅 클릭 수 */
    public function getOpenchatByClass(string $dateFrom = '', string $dateTo = ''): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($dateFrom !== '') {
            $where[]  = 'DATE(ol.clicked_at) >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[]  = 'DATE(ol.clicked_at) <= ?';
            $params[] = $dateTo;
        }

        $whereStr = implode(' AND ', $where);
        return DB::select(
            "SELECT c.class_idx, c.title AS class_title, c.type AS class_type,
                    COUNT(ol.log_idx) AS click_count
             FROM lc_openchat_log ol
             JOIN lc_class c ON c.class_idx = ol.class_idx
             WHERE {$whereStr}
             GROUP BY ol.class_idx
             ORDER BY click_count DESC",
            $params
        );
    }

    /** 날짜별 오픈채팅 클릭 수 (최근 30일) */
    public function getOpenchatByDay(string $dateFrom = '', string $dateTo = ''): array
    {
        $where  = ['1=1'];
        $params = [];
        if ($dateFrom !== '') { $where[] = 'DATE(clicked_at) >= ?'; $params[] = $dateFrom; }
        if ($dateTo   !== '') { $where[] = 'DATE(clicked_at) <= ?'; $params[] = $dateTo; }
        $whereStr = implode(' AND ', $where);
        return DB::select(
            "SELECT DATE(clicked_at) AS day, COUNT(*) AS cnt
             FROM lc_openchat_log WHERE {$whereStr}
             GROUP BY day ORDER BY day DESC LIMIT 30",
            $params
        );
    }

    /** 오픈채팅 총 클릭 수 */
    public function getOpenchatTotal(string $dateFrom = '', string $dateTo = ''): int
    {
        $where  = ['1=1'];
        $params = [];
        if ($dateFrom !== '') { $where[] = 'DATE(clicked_at) >= ?'; $params[] = $dateFrom; }
        if ($dateTo   !== '') { $where[] = 'DATE(clicked_at) <= ?'; $params[] = $dateTo; }
        $row = DB::selectOne("SELECT COUNT(*) AS cnt FROM lc_openchat_log WHERE " . implode(' AND ', $where), $params);
        return (int) ($row['cnt'] ?? 0);
    }
}
