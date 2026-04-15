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

        $stats         = $this->fetchStats();
        $recentOrders  = $this->fetchRecentOrders();
        $recentMembers = $this->fetchRecentMembers();

        require VIEW_PATH . '/pages/admin/dashboard.php';
    }

    private function fetchStats(): array
    {
        $stats = [
            'today_members'    => 0,
            'today_orders'     => 0,
            'today_revenue'    => 0,
            'total_enrolls'    => 0,
            'pending_contacts' => 0,
            'pending_applies'  => 0,
        ];

        try {
            $stats['today_members'] = (int) (DB::selectOne(
                "SELECT COUNT(*) AS cnt FROM lc_member WHERE DATE(created_at) = CURDATE()"
            )['cnt'] ?? 0);
        } catch (\Throwable) {}

        try {
            $stats['today_orders'] = (int) (DB::selectOne(
                "SELECT COUNT(*) AS cnt FROM lc_order WHERE DATE(paid_at) = CURDATE() AND status = 'paid'"
            )['cnt'] ?? 0);
        } catch (\Throwable) {}

        try {
            $stats['today_revenue'] = (int) (DB::selectOne(
                "SELECT COALESCE(SUM(amount), 0) AS total FROM lc_order WHERE DATE(paid_at) = CURDATE() AND status = 'paid'"
            )['total'] ?? 0);
        } catch (\Throwable) {}

        try {
            $stats['total_enrolls'] = (int) (DB::selectOne(
                "SELECT COUNT(*) AS cnt FROM lc_enroll"
            )['cnt'] ?? 0);
        } catch (\Throwable) {}

        try {
            $stats['pending_contacts'] = (int) (DB::selectOne(
                "SELECT COUNT(*) AS cnt FROM lc_qna WHERE status = 'wait'"
            )['cnt'] ?? 0);
        } catch (\Throwable) {}

        try {
            $stats['pending_applies'] = (int) (DB::selectOne(
                "SELECT COUNT(*) AS cnt FROM lc_instructor_apply WHERE status = 'pending'"
            )['cnt'] ?? 0);
        } catch (\Throwable) {}

        return $stats;
    }

    /**
     * 최근 결제 5건
     *
     * @return list<array{mb_id: string, class_title: string, amount: int, payment_status: string}>
     */
    private function fetchRecentOrders(): array
    {
        try {
            return DB::select(
                "SELECT o.order_idx, m.mb_id, c.title AS class_title,
                        o.amount, o.status AS payment_status
                   FROM lc_order o
                   JOIN lc_member m ON m.member_idx = o.member_idx
                   JOIN lc_class  c ON c.class_idx  = o.class_idx
                  WHERE o.status != 'free'
                  ORDER BY o.created_at DESC
                  LIMIT 5"
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * 최근 가입 회원 5명
     *
     * @return list<array{mb_id: string, mb_name: string, signup_type: string, created_at: string}>
     */
    private function fetchRecentMembers(): array
    {
        try {
            return DB::select(
                "SELECT mb_id, mb_name, signup_type, created_at
                   FROM lc_member
                  ORDER BY created_at DESC
                  LIMIT 5"
            );
        } catch (\Throwable) {
            return [];
        }
    }
}
