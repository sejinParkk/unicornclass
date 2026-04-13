<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\OrderRepository;

class OrderController
{
    private OrderRepository $repo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->repo = new OrderRepository();
    }

    // =========================================================================
    // GET /admin/orders
    // =========================================================================
    public function index(): void
    {
        $filters = [
            'q'         => trim($_GET['q'] ?? ''),
            'status'    => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
        ];
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;

        $result     = $this->repo->getAdminList($filters, $page, $limit);
        $orders     = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $pageTitle  = '결제 관리';
        $activeMenu = 'orders';
        ob_start();
        require VIEW_PATH . '/pages/admin/orders/index.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // GET /admin/orders/{order_idx}
    // =========================================================================
    public function show(string $orderIdx): void
    {
        $idx   = (int) $orderIdx;
        $order = $this->repo->findByIdx($idx);
        if (!$order) {
            http_response_code(404); exit;
        }

        $csrfToken  = Csrf::token();
        $pageTitle  = '결제 상세 #' . $idx;
        $activeMenu = 'orders';
        ob_start();
        require VIEW_PATH . '/pages/admin/orders/show.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/admin.php';
    }

    // =========================================================================
    // POST /admin/orders/{order_idx}/refund/approve
    // =========================================================================
    public function refundApprove(string $orderIdx): void
    {
        Csrf::verify();

        $idx   = (int) $orderIdx;
        $order = $this->repo->findByIdx($idx);
        if (!$order || $order['status'] !== 'refund_req') {
            header("Location: /admin/orders/{$idx}?error=" . urlencode('환불 처리할 수 없는 주문입니다.'));
            exit;
        }

        // Toss Payments 환불 API (키가 설정된 경우에만)
        $secretKey = $_ENV['TOSS_SECRET_KEY'] ?? '';
        if ($secretKey && $order['toss_payment_key']) {
            $this->callTossRefund($order['toss_payment_key'], $order['amount'], '관리자 환불 승인');
        }

        $this->repo->approveRefund($idx);
        header("Location: /admin/orders/{$idx}?refund_done=1");
        exit;
    }

    // =========================================================================
    // POST /admin/orders/{order_idx}/refund/reject
    // =========================================================================
    public function refundReject(string $orderIdx): void
    {
        Csrf::verify();

        $idx   = (int) $orderIdx;
        $order = $this->repo->findByIdx($idx);
        if (!$order || $order['status'] !== 'refund_req') {
            header("Location: /admin/orders/{$idx}?error=" . urlencode('환불 거절 처리할 수 없는 주문입니다.'));
            exit;
        }

        $this->repo->rejectRefund($idx);
        header("Location: /admin/orders/{$idx}?refund_rejected=1");
        exit;
    }

    // -------------------------------------------------------------------------
    // Toss Payments 환불 API 호출 (내부)
    // -------------------------------------------------------------------------
    private function callTossRefund(string $paymentKey, int $amount, string $reason): void
    {
        $secretKey = $_ENV['TOSS_SECRET_KEY'] ?? '';
        $auth      = base64_encode($secretKey . ':');

        $ch = curl_init("https://api.tosspayments.com/v1/payments/{$paymentKey}/cancel");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . $auth,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'cancelReason' => $reason,
                'cancelAmount' => $amount,
            ]),
            CURLOPT_TIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            $msg = json_decode((string) $resp, true)['message'] ?? 'Toss API 오류';
            error_log("[OrderController] Toss 환불 실패: {$msg}");
        }
    }
}
