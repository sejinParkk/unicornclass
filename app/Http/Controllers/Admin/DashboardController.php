<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\DB;

class DashboardController
{
    public function index(): void
    {
        Auth::requireAdmin();

        // 통계 — 테이블이 모두 완성되기 전까지 0으로 안전하게 처리
        $stats = $this->fetchStats();

        $recentOrders  = [];
        $recentMembers = [];

        require VIEW_PATH . '/pages/admin/dashboard.php';
    }

    private function fetchStats(): array
    {
        $default = [
            'today_members'    => 0,
            'today_orders'     => 0,
            'today_revenue'    => 0,
            'total_enrolls'    => 0,
            'pending_contacts' => 0,
            'pending_applies'  => 0,
        ];

        try {
            $default['today_members'] = (int) (DB::selectOne(
                "SELECT COUNT(*) AS cnt FROM lc_member WHERE DATE(created_at) = CURDATE()"
            )['cnt'] ?? 0);
        } catch (\Throwable) {}

        return $default;
    }
}
