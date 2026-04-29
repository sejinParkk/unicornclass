<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\ClassRepository;
use App\Support\GoogleSheetsService;

// AJAX JSON 전용 CSRF 검증 헬퍼 (실패 시 JSON 반환 후 exit)
function verifyCsrfJson(): void
{
    $submitted = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $stored    = $_SESSION['_csrf_token'] ?? '';
    if (!$stored || !hash_equals($stored, $submitted)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => '보안 토큰이 유효하지 않습니다.']);
        exit;
    }
}

class ClassController
{
    private ClassRepository $repo;

    public function __construct()
    {
        $this->repo = new ClassRepository();
    }

    // =========================================================================
    // GET /classes
    // =========================================================================
    public function index(): void
    {
        $type        = $_GET['type'] ?? '';
        $categoryIdx = (int) ($_GET['cat'] ?? 0);
        $page        = max(1, (int) ($_GET['page'] ?? 1));
        $limit       = 9;

        if (!in_array($type, ['free', 'premium', ''], true)) {
            $type = '';
        }

        $categories = $this->repo->getPublicCategories();
        $result     = $this->repo->getPublicList($page, $limit, $type, $categoryIdx);
        $list       = $result['list'];
        $total      = $result['total'];
        $pages      = (int) ceil($total / $limit);

        $pageTitle = '클래스 - 유니콘클래스';
        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/classes/index.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /classes/{class_idx}
    // =========================================================================
    public function show(string $class_idx): void
    {
        $class = $this->repo->findPublicById((int) $class_idx);

        if (!$class) {
            http_response_code(404);
            exit('강의를 찾을 수 없습니다.');
        }

        $member    = Auth::isMember() ? Auth::member() : null;
        $memberIdx = $member ? (int) $member['member_idx'] : 0;

        // 연락처가 세션에 없으면 DB에서 보완
        if ($member && empty($member['mb_phone'])) {
            $row = \App\Core\DB::selectOne(
                'SELECT mb_phone FROM lc_member WHERE member_idx = ? LIMIT 1',
                [$memberIdx]
            );
            $member['mb_phone'] = $row['mb_phone'] ?? '';
        }

        // 수강 여부
        $enroll    = $memberIdx ? $this->repo->findEnroll($memberIdx, (int) $class_idx) : null;
        $isEnrolled = $enroll !== null;

        // 찜 여부
        $isWished = $memberIdx ? ($this->repo->findWish($memberIdx, (int) $class_idx) !== null) : false;

        // 버튼 상태 결정
        // ─────────────────────────────────────────────────────────────────
        // 'login_required' — 비로그인
        // 'enrolled'        — 이미 수강 중 (무료 신청 완료 or 유료 구매 완료)
        // 'closed'          — sale_end_at 이 이미 지남 (마감)
        // 'waiting'         — enroll_start_at 이 아직 되지 않음 (신청 대기)
        // 'apply'           — 신청/결제 가능
        // ─────────────────────────────────────────────────────────────────
        // 프리미엄 수강권이 만료된 경우 재구매 가능하도록 enrolled 처리 제외
        $isExpiredEnroll = $isEnrolled
            && ($enroll['type'] ?? '') === 'premium'
            && !empty($enroll['expire_at'])
            && strtotime($enroll['expire_at']) < time();

        if (!$member) {
            $btnStatus = 'login_required';
        } elseif ($isEnrolled && !$isExpiredEnroll) {
            $btnStatus = 'enrolled';
        } elseif (!empty($class['sale_end_at']) && strtotime($class['sale_end_at']) < time()) {
            $btnStatus = 'closed';
        } elseif (!empty($class['enroll_start_at']) && strtotime($class['enroll_start_at']) > time()) {
            $btnStatus = 'waiting';
        } else {
            $btnStatus = 'apply';
        }

        // 실제 판매가: price=0이면 정가(price_origin)로 판매
        $effectivePrice = ($class['price'] > 0) ? (int)$class['price'] : (int)$class['price_origin'];

        // 할인율 계산 (프리미엄 / price > 0일 때만 유효)
        $discountRate = 0;
        if ($class['price'] > 0 && $class['price_origin'] > 0 && $class['price'] < $class['price_origin']) {
            $discountRate = (int) round(($class['price_origin'] - $class['price']) / $class['price_origin'] * 100);
        }

        // CSRF 토큰
        $csrfToken = Csrf::token();

        $pageTitle = htmlspecialchars($class['title']) . ' - 유니콘클래스';
        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/classes/show.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // POST /classes/{class_idx}/enroll   (AJAX JSON — 무료 수강 신청)
    // =========================================================================
    public function enroll(string $class_idx): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // 로그인 필수
        if (!Auth::isMember()) {
            echo json_encode(['success' => false, 'error' => '로그인이 필요합니다.']);
            exit;
        }

        // CSRF
        verifyCsrfJson();

        $member    = Auth::member();
        $memberIdx = (int) $member['member_idx'];
        $classIdx  = (int) $class_idx;

        $class = $this->repo->findPublicById($classIdx);
        if (!$class || $class['type'] !== 'free') {
            echo json_encode(['success' => false, 'error' => '강의 정보를 찾을 수 없습니다.']);
            exit;
        }

        // 이미 신청했는지 확인
        if ($this->repo->findEnroll($memberIdx, $classIdx)) {
            echo json_encode(['success' => false, 'error' => '이미 신청하셨습니다.']);
            exit;
        }

        // 마감 확인
        if (!empty($class['sale_end_at']) && strtotime($class['sale_end_at']) < time()) {
            echo json_encode(['success' => false, 'error' => '신청이 마감되었습니다.']);
            exit;
        }

        $this->repo->createFreeEnroll($memberIdx, $classIdx, $class['kakao_url'], $class['vimeo_url']);

        // Google Sheets 기록 (실패해도 수강 신청은 유지)
        try {
            $sheets = new GoogleSheetsService();
            $sheets->appendEnrollRow([
                'member_idx'  => $memberIdx,
                'name'        => $member['name']  ?? '',
                'email'       => $member['email'] ?? '',
                'phone'       => $member['phone'] ?? '',
                'class_idx'   => $classIdx,
                'class_title' => $class['title'],
                'enrolled_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('[GoogleSheets] 수강 신청 기록 실패: ' . $e->getMessage());
        }

        echo json_encode([
            'success'   => true,
            'kakao_url' => $class['kakao_url'],
            'vimeo_url' => $class['vimeo_url'],
        ]);
        exit;
    }

    // =========================================================================
    // POST /classes/{class_idx}/checkout   (AJAX JSON — 유료 결제)
    // =========================================================================
    public function checkout(string $class_idx): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // 로그인 필수
        if (!Auth::isMember()) {
            echo json_encode(['success' => false, 'error' => '로그인이 필요합니다.']);
            exit;
        }

        // CSRF
        verifyCsrfJson();

        $member    = Auth::member();
        $memberIdx = (int) $member['member_idx'];
        $classIdx  = (int) $class_idx;

        $class = $this->repo->findPublicById($classIdx);
        if (!$class || $class['type'] !== 'premium') {
            echo json_encode(['success' => false, 'error' => '강의 정보를 찾을 수 없습니다.']);
            exit;
        }

        $existingEnroll  = $this->repo->findEnroll($memberIdx, $classIdx);
        $isExpiredEnroll = $existingEnroll
            && ($existingEnroll['type'] ?? '') === 'premium'
            && !empty($existingEnroll['expire_at'])
            && strtotime($existingEnroll['expire_at']) < time();

        if ($existingEnroll && !$isExpiredEnroll) {
            echo json_encode(['success' => false, 'error' => '이미 구매하셨습니다.']);
            exit;
        }

        // ─────────────────────────────────────────────────────────────────────
        // 결제 처리 스텁 — PG사 결정 후 이 부분을 교체하세요
        // ─────────────────────────────────────────────────────────────────────
        // 실제 판매가: price=0이면 정가(price_origin)로 판매
        $effectivePrice = ($class['price'] > 0) ? (int)$class['price'] : (int)$class['price_origin'];

        $payResult = $this->processPayment([
            'member_idx'   => $memberIdx,
            'class_idx'    => $classIdx,
            'amount'       => $effectivePrice,
            'amount_origin'=> $class['price_origin'],
            'method'       => $_POST['pay_method'] ?? 'card',
            'orderer_name' => $_POST['orderer_name'] ?? $member['mb_name'],
            'orderer_email'=> $_POST['orderer_email'] ?? $member['mb_email'],
            'orderer_phone'=> $_POST['orderer_phone'] ?? $member['mb_phone'],
        ]);
        // ─────────────────────────────────────────────────────────────────────

        if (!$payResult['success']) {
            echo json_encode(['success' => false, 'error' => $payResult['error_msg'] ?? '결제에 실패했습니다.']);
            exit;
        }

        // 주문 생성 (토스 응답의 결제수단 정보 포함)
        $orderIdx = $this->repo->createOrder($memberIdx, $classIdx, $effectivePrice, $class['price_origin'], [
            'toss_payment_key' => $payResult['toss_payment_key'] ?? null,
            'toss_order_id'    => $payResult['toss_order_id']    ?? null,
            'pay_method'       => $payResult['pay_method']       ?? null,
            'card_company'     => $payResult['card_company']     ?? null,
            'card_type'        => $payResult['card_type']        ?? null,
            'card_number'      => $payResult['card_number']      ?? null,
            'card_install'     => $payResult['card_install']     ?? 0,
            'card_approve_no'  => $payResult['card_approve_no']  ?? null,
        ]);

        // 수강 기간 계산
        $durationDays = (int) ($class['duration_days'] ?? 180);
        $expireAt     = date('Y-m-d H:i:s', strtotime("+{$durationDays} days"));

        // 기간 연장(만료된 수강권) vs 신규 등록 분기
        if ($isExpiredEnroll) {
            $this->repo->extendPremiumEnroll($memberIdx, $classIdx, $orderIdx, $expireAt);
        } else {
            $this->repo->createPremiumEnroll($memberIdx, $classIdx, $orderIdx, $class['kakao_url'], null, $expireAt);
        }

        echo json_encode([
            'success'   => true,
            'order_no'  => $payResult['order_no'],
            'amount'    => $class['price'],
            'paid_at'   => date('Y.m.d H:i:s'),
            'method'    => $_POST['pay_method'] ?? 'card',
            'kakao_url' => $class['kakao_url'],
            'learn_url' => '/classes/' . $classIdx . '/learn',
            'class_idx' => $classIdx,
        ]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 결제 처리 — 토스페이먼츠 연동 후 이 메서드를 교체하세요
    //
    // 현재: 성공 시뮬레이션 (실결제 없음)
    //
    // 토스페이먼츠 연동 시 교체 구조:
    //   1. 프론트에서 토스 SDK로 결제창 호출 → paymentKey, orderId, amount 수신
    //   2. 이 메서드에서 토스 승인 API 호출:
    //      POST https://api.tosspayments.com/v1/payments/confirm
    //      { paymentKey, orderId, amount }
    //   3. 응답에서 아래 필드 파싱 후 반환:
    //      - toss_payment_key : $tossRes['paymentKey']
    //      - toss_order_id    : $tossRes['orderId']
    //      - pay_method       : $tossRes['method']          // "카드"|"가상계좌"|"계좌이체" 등
    //      - card_company     : $tossRes['card']['company']      // "삼성"|"신한"|"KB국민" 등
    //      - card_type        : $tossRes['card']['cardType']    // "신용"|"체크"|"기프트"
    //      - card_number      : $tossRes['card']['number']      // "12**-****-****-3456"
    //      - card_install     : $tossRes['card']['installmentPlanMonths'] // 0=일시불
    //      - card_approve_no  : $tossRes['card']['approveNo']
    // ─────────────────────────────────────────────────────────────────────────
    private function processPayment(array $orderData): array
    {
        // ★ 결제 실패 테스트 시 아래 주석 해제
        // return ['success' => false, 'error_msg' => '카드 정보를 확인하거나 다른 결제수단을 이용해 주세요.'];

        // ★ 현재: 항상 성공으로 시뮬레이션
        $orderNo = 'UC-' . date('Ymd') . '-' . str_pad((string) rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return [
            'success'          => true,
            'toss_payment_key' => null,
            'toss_order_id'    => $orderNo,
            'pay_method'       => null,
            'card_company'     => null,
            'card_number'      => null,
            'card_install'     => 0,
            'card_approve_no'  => null,
        ];
    }

    // =========================================================================
    // POST /api/wish/{class_idx}   (AJAX JSON — 찜 토글)
    // =========================================================================
    public function wishToggle(string $class_idx): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isMember()) {
            echo json_encode(['success' => false, 'error' => 'login_required']);
            exit;
        }

        verifyCsrfJson();

        $memberIdx = (int) Auth::member()['member_idx'];
        $classIdx  = (int) $class_idx;

        $existing = $this->repo->findWish($memberIdx, $classIdx);
        if ($existing) {
            $this->repo->deleteWish($memberIdx, $classIdx);
            echo json_encode(['success' => true, 'wished' => false]);
        } else {
            $this->repo->createWish($memberIdx, $classIdx);
            echo json_encode(['success' => true, 'wished' => true]);
        }
        exit;
    }

    // =========================================================================
    // POST /api/openchat-log   (AJAX JSON — 오픈채팅 클릭 로그)
    // =========================================================================
    public function openchatLog(string $class_idx): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $memberIdx = Auth::isMember() ? (int) Auth::member()['member_idx'] : null;
        $this->repo->logOpenchatClick((int) $class_idx, $memberIdx);

        echo json_encode(['success' => true]);
        exit;
    }

    // =========================================================================
    // GET /classes/{class_idx}/learn
    // =========================================================================
    public function learn(string $class_idx): void
    {
        // 수강 권한 확인 (비회원 먼저 체크)
        if (!Auth::isMember()) {
            header('Location: /login?returnUrl=' . urlencode("/classes/{$class_idx}/learn"));
            exit;
        }

        $class = $this->repo->findPublicById((int) $class_idx);
        if (!$class) {
            http_response_code(404);
            exit('강의를 찾을 수 없습니다.');
        }

        $memberIdx = (int) Auth::member()['member_idx'];
        $enroll    = $this->repo->findEnroll($memberIdx, (int) $class_idx);

        if (!$enroll) {
            header('Location: /classes/' . $class_idx . '?error=no_enroll');
            exit;
        }

        // 현재 챕터 결정 (URL 파라미터 or 첫 챕터)
        $chapters       = $class['chapters'] ?? [];
        $currentChapter = null;
        $chapterIdxParam = (int) ($_GET['chapter'] ?? 0);

        if ($chapterIdxParam > 0) {
            foreach ($chapters as $ch) {
                if ((int) $ch['chapter_idx'] === $chapterIdxParam) {
                    $currentChapter = $ch;
                    break;
                }
            }
        }
        if (!$currentChapter && !empty($chapters)) {
            $currentChapter = $chapters[0];
        }

        // 챕터별 완료 여부 맵 { chapter_idx => true }
        $progressMap = $this->repo->getProgressMap($memberIdx, (int) $class_idx);

        // 결제 정보 (프리미엄 수강이면 order_idx 연결)
        $order = null;
        if ($enroll['order_idx']) {
            $order = \App\Core\DB::selectOne(
                'SELECT order_idx, amount, paid_at, toss_order_id
                 FROM lc_order WHERE order_idx = ?',
                [(int) $enroll['order_idx']]
            );
        }

        // 수강 만료 D-day (프리미엄 + expire_at 있을 때)
        $expireDays = null;
        if ($enroll['expire_at']) {
            $diff = (new \DateTimeImmutable($enroll['expire_at']))->diff(new \DateTimeImmutable());
            $expireDays = $diff->invert ? $diff->days : -$diff->days; // 양수=만료까지 남은 일, 음수=이미 만료
        }

        $pageTitle = $class['title'] . ' — 강의 수강';
        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/classes/learn.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // POST /api/classes/{class_idx}/progress  (AJAX JSON — 챕터 완료 토글)
    // =========================================================================
    public function markProgress(string $class_idx): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isMember()) {
            echo json_encode(['success' => false, 'error' => 'login_required']);
            exit;
        }

        verifyCsrfJson();

        $memberIdx  = (int) Auth::member()['member_idx'];
        $classIdx   = (int) $class_idx;
        $chapterIdx = (int) ($_POST['chapter_idx'] ?? 0);

        if ($chapterIdx <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'invalid_chapter']);
            exit;
        }

        // 수강 권한 재확인
        $enroll = $this->repo->findEnroll($memberIdx, $classIdx);
        if (!$enroll) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'no_enroll']);
            exit;
        }

        $isDone = $this->repo->toggleProgress($memberIdx, $classIdx, $chapterIdx);

        echo json_encode(['success' => true, 'is_complete' => $isDone]);
        exit;
    }

    // =========================================================================
    // POST /api/classes/{class_idx}/complete  (AJAX — 영상 종료 시 챕터 완료 처리)
    // =========================================================================
    public function completeChapter(string $class_idx): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isMember()) {
            echo json_encode(['success' => false, 'error' => 'login_required']);
            exit;
        }

        verifyCsrfJson();

        $memberIdx  = (int) Auth::member()['member_idx'];
        $classIdx   = (int) $class_idx;
        $chapterIdx = (int) ($_POST['chapter_idx'] ?? 0);

        if ($chapterIdx <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'invalid_chapter']);
            exit;
        }

        $enroll = $this->repo->findEnroll($memberIdx, $classIdx);
        if (!$enroll) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'no_enroll']);
            exit;
        }

        $this->repo->markComplete($memberIdx, $classIdx, $chapterIdx);

        echo json_encode(['success' => true]);
        exit;
    }
}
