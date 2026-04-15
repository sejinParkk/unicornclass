<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\MemberRepository;
use App\Repositories\MypageRepository;

class MypageController
{
    private MemberRepository  $memberRepo;
    private MypageRepository  $mypageRepo;

    public function __construct()
    {
        Auth::requireLogin();
        $this->memberRepo  = new MemberRepository();
        $this->mypageRepo  = new MypageRepository();
    }

    // =========================================================================
    // GET /mypage/profile
    // =========================================================================
    public function profileForm(): void
    {
        $session    = Auth::member();
        $member     = $this->memberRepo->findByIdx((int) $session['member_idx']);
        if (!$member) { http_response_code(404); exit; }

        $csrfToken   = Csrf::token();
        $errors      = [];
        $pwErrors    = [];
        $saved       = isset($_GET['saved']);
        $pwChanged   = isset($_GET['pw_changed']);
        $pageTitle   = '정보수정 — 유니콘클래스';
        $mpActiveNav = 'profile';

        ob_start();
        require VIEW_PATH . '/pages/mypage/profile.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // POST /mypage/profile
    // =========================================================================
    public function profileUpdate(): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];
        $member    = $this->memberRepo->findByIdx($memberIdx);
        if (!$member) { http_response_code(404); exit; }

        $action = $_POST['_action'] ?? 'profile';

        if ($action === 'password') {
            $this->handlePasswordChange($memberIdx, $member);
            return;
        }

        // ── 기본 정보 수정 ──
        $name      = trim($_POST['mb_name'] ?? '');
        $email     = trim($_POST['mb_email'] ?? '');
        $mailling  = isset($_POST['mb_mailling']) ? 1 : 0;
        $sms       = isset($_POST['mb_sms'])      ? 1 : 0;

        $errors = $this->validateProfile($name, $email, $memberIdx);

        if ($errors) {
            $csrfToken   = Csrf::token();
            $saved       = false;
            $pwChanged   = false;
            $pwErrors    = [];
            $pageTitle   = '정보수정 — 유니콘클래스';
            $mpActiveNav = 'profile';
            ob_start();
            require VIEW_PATH . '/pages/mypage/profile.php';
            $mpContent = ob_get_clean();
            require VIEW_PATH . '/layout/mypage.php';
            return;
        }

        $this->memberRepo->updateProfile($memberIdx, [
            'mb_name'    => $name,
            'mb_email'   => $email,
            'mb_phone'   => $member['mb_phone'] ?? null,   // 전화번호는 이 폼에서 변경 불가, 기존값 유지
            'mb_mailling'=> $mailling,
            'mb_sms'     => $sms,
        ]);

        // 세션 갱신
        $_SESSION['_member']['mb_name']  = $name;
        $_SESSION['_member']['mb_email'] = $email;

        header('Location: /mypage/profile?saved=1');
        exit;
    }

    // =========================================================================
    // 비밀번호 변경 처리
    // =========================================================================
    private function handlePasswordChange(int $memberIdx, array $member): void
    {
        // 소셜 전용 계정은 비밀번호 없음
        if (empty($member['mb_password'])) {
            header('Location: /mypage/profile?pw_error=' . urlencode('소셜 계정은 비밀번호를 변경할 수 없습니다.'));
            exit;
        }

        $currentPw = $_POST['current_password'] ?? '';
        $newPw     = $_POST['new_password']     ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';

        $pwErrors = $this->validatePassword($member, $currentPw, $newPw, $confirmPw);

        if ($pwErrors) {
            // Ajax 요청이면 오류 JSON 반환
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'errors' => $pwErrors]);
                exit;
            }
            $session     = Auth::member();
            $member      = $this->memberRepo->findByIdx($memberIdx);
            $csrfToken   = Csrf::token();
            $errors      = [];
            $saved       = false;
            $pwChanged   = false;
            $pageTitle   = '정보수정 — 유니콘클래스';
            $mpActiveNav = 'profile';
            ob_start();
            require VIEW_PATH . '/pages/mypage/profile.php';
            $mpContent = ob_get_clean();
            require VIEW_PATH . '/layout/mypage.php';
            return;
        }

        $hashed = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->memberRepo->updatePassword($memberIdx, $hashed);

        // Ajax 요청이면 JSON 반환, 일반 폼이면 리다이렉트
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: /mypage/profile?pw_changed=1');
        exit;
    }

    // =========================================================================
    // 유효성 검사
    // =========================================================================
    private function validateProfile(string $name, string $email, int $memberIdx): array
    {
        $errors = [];

        if ($name === '') {
            $errors['mb_name'] = '이름을 입력해주세요.';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 20) {
            $errors['mb_name'] = '이름은 2~20자로 입력해주세요.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['mb_email'] = '이메일 형식이 올바르지 않습니다.';
        } elseif ($email !== '' && $this->memberRepo->existsByEmailExcept($email, $memberIdx)) {
            $errors['mb_email'] = '이미 사용 중인 이메일입니다.';
        }

        return $errors;
    }

    private function validatePassword(array $member, string $current, string $new, string $confirm): array
    {
        $errors = [];

        if ($current === '') {
            $errors['current_password'] = '현재 비밀번호를 입력해주세요.';
        } elseif (!password_verify($current, $member['mb_password'])) {
            $errors['current_password'] = '현재 비밀번호가 올바르지 않습니다.';
        }

        if ($new === '') {
            $errors['new_password'] = '새 비밀번호를 입력해주세요.';
        } elseif (strlen($new) < 8) {
            $errors['new_password'] = '새 비밀번호는 8자 이상이어야 합니다.';
        }

        if ($confirm === '') {
            $errors['confirm_password'] = '비밀번호 확인을 입력해주세요.';
        } elseif ($new !== '' && $new !== $confirm) {
            $errors['confirm_password'] = '새 비밀번호가 일치하지 않습니다.';
        }

        return $errors;
    }

    // =========================================================================
    // GET /mypage/my-class
    // =========================================================================
    public function myClass(): void
    {
        $session    = Auth::member();
        $memberIdx  = (int) $session['member_idx'];

        $type       = in_array($_GET['type'] ?? '', ['free', 'premium']) ? $_GET['type'] : 'all';
        $classes    = $this->mypageRepo->getMyClasses($memberIdx, $type);
        $counts     = $this->mypageRepo->getMyClassCounts($memberIdx);

        $pageTitle  = '나의 강의 — 유니콘클래스';
        $mpActiveNav = 'my-class';

        ob_start();
        require VIEW_PATH . '/pages/mypage/my-class.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // GET /mypage/wishlist
    // =========================================================================
    public function wishlist(): void
    {
        $session    = Auth::member();
        $memberIdx  = (int) $session['member_idx'];

        $wishlist   = $this->mypageRepo->getWishlist($memberIdx);
        $csrfToken  = Csrf::token();

        $pageTitle   = '찜목록 — 유니콘클래스';
        $mpActiveNav = 'wishlist';

        ob_start();
        require VIEW_PATH . '/pages/mypage/wishlist.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // GET /mypage/orders
    // =========================================================================
    public function orders(): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];
        $page      = max(1, (int) ($_GET['page'] ?? 1));
        $limit     = 10;

        ['list' => $orders, 'total' => $total] =
            $this->mypageRepo->getOrders($memberIdx, $page, $limit);

        $totalPages  = (int) ceil($total / $limit);
        $pageTitle   = '결제내역 — 유니콘클래스';
        $mpActiveNav = 'orders';

        ob_start();
        require VIEW_PATH . '/pages/mypage/orders.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // GET /mypage/orders/{order_idx}
    // =========================================================================
    public function orderShow(int $orderIdx): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $order = $this->mypageRepo->getOrderDetail($orderIdx, $memberIdx);
        if (!$order) { http_response_code(404); exit; }

        // 환불 가능 여부: paid + 결제일 7일 이내 + 진도율 33% 미만
        $canRefund = false;
        if ($order['status'] === 'paid') {
            $paidAt     = new \DateTimeImmutable($order['paid_at']);
            $withinDays = $paidAt->modify('+7 days') >= new \DateTimeImmutable();
            $rate       = (int) $order['total_episodes'] > 0
                ? (int) $order['done_count'] / (int) $order['total_episodes'] * 100
                : 0;
            $canRefund  = $withinDays || $rate < 33;
        }

        $csrfToken   = Csrf::token();
        $pageTitle   = '결제내역 상세 — 유니콘클래스';
        $mpActiveNav = 'orders';

        ob_start();
        require VIEW_PATH . '/pages/mypage/order-show.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // POST /mypage/orders/{order_idx}/refund
    // =========================================================================
    public function orderRefund(int $orderIdx): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $reason = trim($_POST['reason'] ?? '');
        $detail = trim($_POST['detail'] ?? '');
        $full   = $reason !== '' ? $reason . ($detail !== '' ? ' — ' . $detail : '') : $detail;

        if ($full === '') {
            header('Location: /mypage/orders/' . $orderIdx . '?err=reason');
            exit;
        }

        $ok = $this->mypageRepo->requestRefund($orderIdx, $memberIdx, $full);

        header('Location: /mypage/orders/' . $orderIdx . ($ok ? '?refund=1' : '?err=fail'));
        exit;
    }
    // =========================================================================
    // GET /mypage/qna
    // =========================================================================
    public function qnaList(): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $status  = in_array($_GET['status'] ?? '', ['wait', 'done']) ? $_GET['status'] : '';
        $qnaList = $this->mypageRepo->getQnaList($memberIdx, $status);

        $pageTitle   = '1:1 문의 — 유니콘클래스';
        $mpActiveNav = 'qna';

        ob_start();
        require VIEW_PATH . '/pages/mypage/qna-list.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // GET /mypage/qna/write  (신규 작성 or ?edit={idx} 수정)
    // =========================================================================
    public function qnaForm(): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $editIdx = (int) ($_GET['edit'] ?? 0);
        $qna     = null;

        if ($editIdx > 0) {
            $qna = $this->mypageRepo->getQnaDetail($editIdx, $memberIdx);
            if (!$qna || $qna['status'] !== 'wait') {
                header('Location: /mypage/qna');
                exit;
            }
        }

        $csrfToken   = Csrf::token();
        $errors      = [];
        $pageTitle   = ($qna ? '문의 수정' : '문의 작성') . ' — 유니콘클래스';
        $mpActiveNav = 'qna';

        ob_start();
        require VIEW_PATH . '/pages/mypage/qna-write.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // POST /mypage/qna/write
    // =========================================================================
    public function qnaStore(): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $editIdx  = (int) ($_POST['edit_idx'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $title    = trim($_POST['title'] ?? '');
        $content  = trim($_POST['content'] ?? '');

        $errors = [];
        if ($category === '') $errors['category'] = '문의 분류를 선택해주세요.';
        if ($title === '')    $errors['title']    = '제목을 입력해주세요.';
        if (mb_strlen($title) > 200) $errors['title'] = '제목은 200자 이내로 입력해주세요.';
        if ($content === '')  $errors['content']  = '내용을 입력해주세요.';

        if ($errors) {
            $qna         = $editIdx > 0 ? $this->mypageRepo->getQnaDetail($editIdx, $memberIdx) : null;
            $csrfToken   = Csrf::token();
            $pageTitle   = '문의 작성 — 유니콘클래스';
            $mpActiveNav = 'qna';
            ob_start();
            require VIEW_PATH . '/pages/mypage/qna-write.php';
            $mpContent = ob_get_clean();
            require VIEW_PATH . '/layout/mypage.php';
            return;
        }

        if ($editIdx > 0) {
            \App\Core\DB::execute(
                "UPDATE lc_qna SET category = ?, title = ?, content = ?, updated_at = NOW()
                 WHERE qna_idx = ? AND member_idx = ? AND status = 'wait'",
                [$category, $title, $content, $editIdx, $memberIdx]
            );
            header('Location: /mypage/qna/' . $editIdx . '?saved=1');
        } else {
            $qnaIdx = $this->mypageRepo->createQna($memberIdx, [
                'category' => $category,
                'title'    => $title,
                'content'  => $content,
            ]);
            header('Location: /mypage/qna/' . $qnaIdx . '?created=1');
        }
        exit;
    }

    // =========================================================================
    // GET /mypage/qna/{qna_idx}
    // =========================================================================
    public function qnaShow(int $qnaIdx): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $qna = $this->mypageRepo->getQnaDetail($qnaIdx, $memberIdx);
        if (!$qna) { http_response_code(404); exit; }

        $csrfToken   = Csrf::token();
        $pageTitle   = '문의 상세 — 유니콘클래스';
        $mpActiveNav = 'qna';

        ob_start();
        require VIEW_PATH . '/pages/mypage/qna-show.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // POST /mypage/qna/{qna_idx}/delete
    // =========================================================================
    public function qnaDelete(int $qnaIdx): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $this->mypageRepo->deleteQna($qnaIdx, $memberIdx);

        header('Location: /mypage/qna?deleted=1');
        exit;
    }

    // =========================================================================
    // GET /mypage/reviews
    // =========================================================================
    public function reviews(): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $reviewableClasses = $this->mypageRepo->getReviewableClasses($memberIdx);
        $myReviews         = $this->mypageRepo->getReviews($memberIdx);

        $pageTitle   = '내 후기 — 유니콘클래스';
        $mpActiveNav = 'reviews';

        ob_start();
        require VIEW_PATH . '/pages/mypage/reviews.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // GET /mypage/reviews/write  (?class_idx=N 신규 | ?edit=N 수정)
    // =========================================================================
    public function reviewForm(): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $editIdx  = (int) ($_GET['edit']      ?? 0);
        $classIdx = (int) ($_GET['class_idx'] ?? 0);

        $review   = null;
        $class    = null;

        if ($editIdx > 0) {
            $review = $this->mypageRepo->getReviewDetail($editIdx, $memberIdx);
            if (!$review) { header('Location: /mypage/reviews'); exit; }
            $classIdx = (int) $review['class_idx'];
        }

        if ($classIdx > 0) {
            $class = \App\Core\DB::selectOne(
                "SELECT class_idx, title FROM lc_class WHERE class_idx = ? AND is_active = 1",
                [$classIdx]
            );
        }

        if (!$class) { header('Location: /mypage/reviews'); exit; }

        // 신규 작성 시 이미 후기가 있으면 차단
        if (!$editIdx && $this->mypageRepo->hasReview($memberIdx, $classIdx)) {
            header('Location: /mypage/reviews');
            exit;
        }

        $csrfToken   = Csrf::token();
        $errors      = [];
        $pageTitle   = ($editIdx ? '후기 수정' : '후기 작성') . ' — 유니콘클래스';
        $mpActiveNav = 'reviews';

        ob_start();
        require VIEW_PATH . '/pages/mypage/review-form.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // POST /mypage/reviews/write
    // =========================================================================
    public function reviewStore(): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $editIdx  = (int) ($_POST['edit_idx']  ?? 0);
        $classIdx = (int) ($_POST['class_idx'] ?? 0);
        $rating   = (int) ($_POST['rating']    ?? 0);
        $content  = trim($_POST['content']     ?? '');

        $errors = [];
        if ($rating < 1 || $rating > 5) $errors['rating']  = '별점을 선택해주세요.';
        if ($content === '')             $errors['content'] = '후기 내용을 입력해주세요.';
        if (mb_strlen($content) < 20)   $errors['content'] = '후기 내용은 20자 이상 입력해주세요.';

        if ($errors) {
            $review  = $editIdx > 0 ? $this->mypageRepo->getReviewDetail($editIdx, $memberIdx) : null;
            $class   = \App\Core\DB::selectOne(
                "SELECT class_idx, title FROM lc_class WHERE class_idx = ?", [$classIdx]
            );
            $csrfToken   = Csrf::token();
            $pageTitle   = '후기 작성 — 유니콘클래스';
            $mpActiveNav = 'reviews';
            ob_start();
            require VIEW_PATH . '/pages/mypage/review-form.php';
            $mpContent = ob_get_clean();
            require VIEW_PATH . '/layout/mypage.php';
            return;
        }

        if ($editIdx > 0) {
            $this->mypageRepo->updateReview($editIdx, $memberIdx, [
                'rating'  => $rating,
                'content' => $content,
            ]);
        } else {
            // 중복 방지 재확인
            if ($this->mypageRepo->hasReview($memberIdx, $classIdx)) {
                header('Location: /mypage/reviews');
                exit;
            }
            $this->mypageRepo->createReview($memberIdx, [
                'class_idx' => $classIdx,
                'rating'    => $rating,
                'content'   => $content,
            ]);
        }

        header('Location: /mypage/reviews?saved=1');
        exit;
    }
    // =========================================================================
    // GET /mypage/withdraw
    // =========================================================================
    public function withdrawForm(): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        // 탈퇴 불가 조건 체크
        $blockReason = $this->getWithdrawBlockReason($memberIdx);

        $csrfToken   = Csrf::token();
        $pageTitle   = '회원탈퇴 — 유니콘클래스';
        $mpActiveNav = 'profile';

        ob_start();
        require VIEW_PATH . '/pages/mypage/withdraw.php';
        $mpContent = ob_get_clean();

        require VIEW_PATH . '/layout/mypage.php';
    }

    // =========================================================================
    // POST /mypage/withdraw
    // =========================================================================
    public function withdraw(): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        // 탈퇴 불가 조건 재확인
        $blockReason = $this->getWithdrawBlockReason($memberIdx);
        if ($blockReason) {
            header('Location: /mypage/withdraw?err=' . urlencode($blockReason));
            exit;
        }

        // 확인 문구 검증
        $confirm = trim($_POST['confirm_text'] ?? '');
        if ($confirm !== '탈퇴합니다') {
            header('Location: /mypage/withdraw?err=' . urlencode('확인 문구를 정확히 입력해주세요.'));
            exit;
        }

        // 탈퇴 처리
        $this->mypageRepo->withdraw($memberIdx);

        // 세션 파기 후 메인 이동
        session_destroy();
        header('Location: /?withdrawn=1');
        exit;
    }

    // =========================================================================
    // 탈퇴 불가 조건 체크 (공통 헬퍼)
    // =========================================================================
    private function getWithdrawBlockReason(int $memberIdx): string
    {
        // 수강 중인 프리미엄 강의 존재 여부
        $activeEnroll = \App\Core\DB::selectOne(
            "SELECT 1 FROM lc_enroll
             WHERE member_idx = ? AND type = 'premium'
               AND (expire_at IS NULL OR expire_at > NOW())
             LIMIT 1",
            [$memberIdx]
        );
        if ($activeEnroll) {
            return '수강 중인 강의가 있으면 탈퇴할 수 없습니다. 수강 기간 만료 후 탈퇴해주세요.';
        }

        // 환불 처리 중 주문 존재 여부
        $pendingRefund = \App\Core\DB::selectOne(
            "SELECT 1 FROM lc_order WHERE member_idx = ? AND status = 'refund_req' LIMIT 1",
            [$memberIdx]
        );
        if ($pendingRefund) {
            return '환불 처리 중인 주문이 있습니다. 처리 완료 후 탈퇴해주세요.';
        }

        return '';
    }
}
