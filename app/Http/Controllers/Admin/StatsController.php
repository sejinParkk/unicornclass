<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Repositories\StatsRepository;

class StatsController
{
    private StatsRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new StatsRepository();
    }

    // =========================================================================
    // GET /admin/search-logs
    // =========================================================================
    public function searchLogs(): void
    {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo   = $_GET['date_to']   ?? date('Y-m-d');

        $ranking    = $this->repo->getSearchRanking($dateFrom, $dateTo, 50);
        $byDay      = $this->repo->getSearchByDay($dateFrom, $dateTo);
        $totalCount = $this->repo->getSearchTotal($dateFrom, $dateTo);

        $pageTitle  = '검색 로그';
        $activeMenu = 'search-logs';
        ob_start();
        require VIEW_PATH . '/pages/admin/stats/search-logs.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/openchat-logs
    // =========================================================================
    public function openchatLogs(): void
    {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo   = $_GET['date_to']   ?? date('Y-m-d');

        $byClass    = $this->repo->getOpenchatByClass($dateFrom, $dateTo);
        $byDay      = $this->repo->getOpenchatByDay($dateFrom, $dateTo);
        $totalCount = $this->repo->getOpenchatTotal($dateFrom, $dateTo);

        $pageTitle  = '오픈채팅 통계';
        $activeMenu = 'openchat-logs';
        ob_start();
        require VIEW_PATH . '/pages/admin/stats/openchat-logs.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }
}
