<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class OrderRepository
{
    // =========================================================================
    // 목록 조회 (관리자)
    // =========================================================================
    public function getAdminList(array $filters, int $page, int $limit): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = '(m.mb_id LIKE ? OR m.mb_name LIKE ? OR m.mb_email LIKE ? OR c.title LIKE ?)';
            $like     = '%' . $filters['q'] . '%';
            $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
        }

        if (!empty($filters['status'])) {
            $where[]  = 'o.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[]  = 'DATE(o.paid_at) >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[]  = 'DATE(o.paid_at) <= ?';
            $params[] = $filters['date_to'];
        }

        $whereStr = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) AS cnt
                     FROM lc_order o
                     JOIN lc_member m ON m.member_idx = o.member_idx
                     JOIN lc_class  c ON c.class_idx  = o.class_idx
                     WHERE {$whereStr}";
        $total = (int) (DB::selectOne($countSql, $params)['cnt'] ?? 0);

        $offset = ($page - 1) * $limit;
        $listSql = "SELECT o.*, m.mb_id, m.mb_name, m.mb_email, c.title AS class_title
                    FROM lc_order o
                    JOIN lc_member m ON m.member_idx = o.member_idx
                    JOIN lc_class  c ON c.class_idx  = o.class_idx
                    WHERE {$whereStr}
                    ORDER BY o.created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
        $list = DB::select($listSql, $params);

        return ['list' => $list, 'total' => $total];
    }

    // =========================================================================
    // 상세 조회
    // =========================================================================
    public function findByIdx(int $orderIdx): ?array
    {
        return DB::selectOne(
            "SELECT o.*, m.mb_id, m.mb_name, m.mb_email, m.mb_phone,
                    c.title AS class_title, c.type AS class_type, c.total_episodes,
                    (SELECT COUNT(*) FROM lc_progress p
                     WHERE p.member_idx = o.member_idx AND p.class_idx = o.class_idx
                       AND p.is_complete = 1) AS done_count
             FROM lc_order o
             JOIN lc_member m ON m.member_idx = o.member_idx
             JOIN lc_class  c ON c.class_idx  = o.class_idx
             WHERE o.order_idx = ?",
            [$orderIdx]
        );
    }

    /**
     * 환불 금액 계산 (전액 또는 비례)
     * 반환: ['refund_amount' => int, 'type' => '전액 환불'|'잔여기간 비례 환불'|'환불 불가']
     */
    public function calcRefundAmount(array $order): array
    {
        if ((int)$order['amount'] <= 0 || empty($order['paid_at'])) {
            return ['refund_amount' => 0, 'type' => '환불 불가'];
        }

        $now     = new \DateTimeImmutable();
        $paidAt  = new \DateTimeImmutable($order['paid_at']);
        $rate    = (int)$order['total_episodes'] > 0
            ? (int) round((int)$order['done_count'] / (int)$order['total_episodes'] * 100)
            : 0;

        if ($rate < 33 && $paidAt->modify('+7 days') >= $now) {
            return ['refund_amount' => (int)$order['amount'], 'type' => '전액 환불'];
        }

        // 비례 환불: 수강 기간 조회
        $enroll = DB::selectOne(
            "SELECT enrolled_at, expire_at FROM lc_enroll WHERE order_idx = ? LIMIT 1",
            [(int)$order['order_idx']]
        );

        if (!$enroll || empty($enroll['expire_at'])) {
            return ['refund_amount' => 0, 'type' => '환불 불가'];
        }

        $startDt     = new \DateTimeImmutable($enroll['enrolled_at']);
        $expireDt    = new \DateTimeImmutable($enroll['expire_at']);
        $totalDays   = (int) $startDt->diff($expireDt)->days;
        $elapsedDays = min((int) $startDt->diff($now)->days, $totalDays);
        $remainDays  = max(0, $totalDays - $elapsedDays);
        $perDay      = $totalDays > 0 ? (int)$order['amount'] / $totalDays : 0;
        $refundAmt   = (int) round($perDay * $remainDays);

        return ['refund_amount' => $refundAmt, 'type' => '잔여기간 비례 환불'];
    }

    // =========================================================================
    // 환불 승인: refund_req → refunded
    // =========================================================================
    public function approveRefund(int $orderIdx): void
    {
        DB::execute(
            "UPDATE lc_order SET status = 'refunded', refunded_at = NOW() WHERE order_idx = ? AND status = 'refund_req'",
            [$orderIdx]
        );
        // 수강 비활성화
        DB::execute(
            "UPDATE lc_enroll SET expire_at = NOW() WHERE order_idx = ?",
            [$orderIdx]
        );
    }

    // =========================================================================
    // 환불 거절: refund_req → paid (원복)
    // =========================================================================
    public function rejectRefund(int $orderIdx): void
    {
        DB::execute(
            "UPDATE lc_order SET status = 'paid' WHERE order_idx = ? AND status = 'refund_req'",
            [$orderIdx]
        );
    }

    // =========================================================================
    // 오늘 결제 통계
    // =========================================================================
    public function todayStats(): array
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS revenue
             FROM lc_order WHERE status = 'paid' AND DATE(paid_at) = CURDATE()"
        );
        return ['count' => (int) ($row['cnt'] ?? 0), 'revenue' => (int) ($row['revenue'] ?? 0)];
    }
}
