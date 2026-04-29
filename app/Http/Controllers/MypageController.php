<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\MemberRepository;
use App\Repositories\MypageRepository;
use App\Support\FileUploader;

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
        $pageTitle = '정보수정 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/profile.php';
        require VIEW_PATH . '/layout/footer.php';
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

        header('Content-Type: application/json; charset=utf-8');

        // ── 기본 정보 ──
        $name     = trim($_POST['mb_name']  ?? '');
        $email    = trim($_POST['mb_email'] ?? '');
        $mailling = isset($_POST['mb_mailling']) ? 1 : 0;
        $sms      = isset($_POST['mb_sms'])      ? 1 : 0;

        $errors = $this->validateProfile($name, $email, $memberIdx);

        // ── 연락처 변경 ──
        $newPhone = null;
        if (!empty($_POST['phone_verified'])) {
            $verifiedPhone = $_SESSION['sms_verified_change_phone'] ?? null;
            if (!$verifiedPhone) {
                $errors['mb_phone'] = '휴대전화 인증을 완료해주세요.';
            } else {
                $newPhone = $verifiedPhone;
            }
        }

        // ── 비밀번호 변경 (입력 시에만) ──
        $newPw     = $_POST['new_password']     ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';
        $currentPw = $_POST['current_password'] ?? '';
        $pwErrors  = [];

        if ($currentPw !== '' || $newPw !== '' || $confirmPw !== '') {
            if (empty($member['mb_password'])) {
                $errors['new_password'] = '소셜 계정은 비밀번호를 변경할 수 없습니다.';
            } else {
                $pwErrors = $this->validatePassword($member, $currentPw, $newPw, $confirmPw);
                foreach ($pwErrors as $k => $v) {
                    $errors[$k] = $v;
                }
            }
        }

        if ($errors) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        // ── 저장 ──
        $this->memberRepo->updateProfile($memberIdx, [
            'mb_name'    => $name,
            'mb_email'   => $email,
            'mb_phone'   => $newPhone ?? ($member['mb_phone'] ?? null),
            'mb_mailling'=> $mailling,
            'mb_sms'     => $sms,
        ]);

        if ($newPw !== '') {
            $hashed = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->memberRepo->updatePassword($memberIdx, $hashed);
        }

        // ── 세션 업데이트 ──
        $_SESSION['_member']['mb_name']  = $name;
        $_SESSION['_member']['mb_email'] = $email;
        if ($newPhone) {
            $_SESSION['_member']['mb_phone'] = $newPhone;
            unset($_SESSION['sms_verified_change_phone']);
        }

        echo json_encode(['ok' => true, 'redirect' => '/mypage/profile?saved=1']);
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

        $pwRegex = '/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*\-_]).{8,}$/';
        if ($new === '') {
            $errors['new_password'] = '새 비밀번호를 입력해주세요.';
        } elseif (!preg_match($pwRegex, $new)) {
            $errors['new_password'] = '영문 + 숫자 + 특수문자 포함 8자 이상이어야 합니다.';
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
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $limit      = 10;

        $result     = $this->mypageRepo->getMyClasses($memberIdx, $type, $page, $limit);
        $classes    = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);
        $counts     = $this->mypageRepo->getMyClassCounts($memberIdx);

        $pageTitle = '나의 강의 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/my-class.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /mypage/wishlist
    // =========================================================================
    public function wishlist(): void
    {
        $session    = Auth::member();
        $memberIdx  = (int) $session['member_idx'];

        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $limit      = 12;

        $result     = $this->mypageRepo->getWishlist($memberIdx, $page, $limit);
        $wishlist   = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);
        $csrfToken  = Csrf::token();

        $pageTitle = '찜목록 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/wishlist.php';
        require VIEW_PATH . '/layout/footer.php';
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

        $totalPages = (int) ceil($total / $limit);
        $pageTitle  = '결제내역 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/orders.php';
        require VIEW_PATH . '/layout/footer.php';
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

        $csrfToken = Csrf::token();
        $pageTitle = '결제내역 상세 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/order-show.php';
        require VIEW_PATH . '/layout/footer.php';
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

        $status     = in_array($_GET['status'] ?? '', ['wait', 'done']) ? $_GET['status'] : '';
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $limit      = 10;

        $result     = $this->mypageRepo->getQnaList($memberIdx, $status, $page, $limit);
        $qnaList    = $result['list'];
        $total      = $result['total'];
        $totalPages = (int) ceil($total / $limit);

        $pageTitle = '1:1 문의 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/qna-list.php';
        require VIEW_PATH . '/layout/footer.php';
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

        $csrfToken = Csrf::token();
        $errors    = [];
        $pageTitle = ($qna ? '문의 수정' : '문의 작성') . ' — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/qna-write.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // POST /mypage/qna/write
    // =========================================================================
    public function qnaStore(): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $editIdx    = (int) ($_POST['edit_idx'] ?? 0);
        $category   = trim($_POST['category'] ?? '');
        $title      = trim($_POST['title'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $removeFile = !empty($_POST['remove_file']);

        $errors = [];
        if ($category === '')           $errors['category'] = '문의 분류를 선택해주세요.';
        if ($title === '')              $errors['title']    = '제목을 입력해주세요.';
        if (mb_strlen($title) > 200)   $errors['title']    = '제목은 200자 이내로 입력해주세요.';
        if ($content === '')            $errors['content']  = '내용을 입력해주세요.';
        if (mb_strlen($content) > 2000) $errors['content'] = '내용은 2,000자 이내로 입력해주세요.';

        header('Content-Type: application/json; charset=utf-8');

        if ($errors) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        // 파일 업로드
        $newFilename = null;
        try {
            $newFilename = \App\Support\FileUploader::uploadQnaFile($_FILES['qna_file'] ?? []);
        } catch (\RuntimeException $e) {
            echo json_encode(['ok' => false, 'errors' => ['qna_file' => $e->getMessage()]]);
            exit;
        }

        if ($editIdx > 0) {
            $existing = $this->mypageRepo->getQnaDetail($editIdx, $memberIdx);
            if (!$existing || $existing['status'] !== 'wait') {
                if ($newFilename) \App\Support\FileUploader::deleteQnaFile($newFilename);
                echo json_encode(['ok' => false, 'message' => '수정할 수 없는 문의입니다.']);
                exit;
            }

            $filePath = $existing['file_path'];
            if ($newFilename) {
                \App\Support\FileUploader::deleteQnaFile($existing['file_path']);
                $filePath = $newFilename;
            } elseif ($removeFile) {
                \App\Support\FileUploader::deleteQnaFile($existing['file_path']);
                $filePath = null;
            }

            \App\Core\DB::execute(
                "UPDATE lc_qna SET category = ?, title = ?, content = ?, file_path = ?, updated_at = NOW()
                 WHERE qna_idx = ? AND member_idx = ? AND status = 'wait'",
                [$category, $title, $content, $filePath, $editIdx, $memberIdx]
            );
            echo json_encode(['ok' => true, 'redirect' => '/mypage/qna/' . $editIdx . '?saved=1']);
        } else {
            $qnaIdx = $this->mypageRepo->createQna($memberIdx, [
                'category'  => $category,
                'title'     => $title,
                'content'   => $content,
                'file_path' => $newFilename,
            ]);
            echo json_encode(['ok' => true, 'redirect' => '/mypage/qna/' . $qnaIdx . '?created=1']);
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

        $csrfToken = Csrf::token();
        $pageTitle = '문의 상세 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/qna-show.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // POST /mypage/qna/{qna_idx}/delete
    // =========================================================================
    public function qnaDelete(int $qnaIdx): void
    {
        Csrf::verify();

        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $deleted = $this->mypageRepo->deleteQna($qnaIdx, $memberIdx);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $deleted]);
        exit;
    }

    // =========================================================================
    // GET /mypage/reviews
    // =========================================================================
    public function reviews(): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $type  = in_array($_GET['type'] ?? '', ['0', '1']) ? ($_GET['type'] ?? '0') : '0';
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;

        $result            = $this->mypageRepo->getReviewableClasses($memberIdx, $type, $page, $limit);
        $reviewableClasses = $result['list'];
        $total             = $result['total'];
        $totalPages        = (int) ceil($total / $limit);

        // 작성된 후기의 이미지 일괄 조회
        $reviewIds = array_filter(array_column($reviewableClasses, 'review_idx'));
        $reviewImageMap = $this->mypageRepo->getReviewImagesByIds(array_values($reviewIds));

        $pageTitle = '내 후기 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/reviews.php';
        require VIEW_PATH . '/layout/footer.php';
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

        $reviewImages = $editIdx > 0 ? $this->mypageRepo->getReviewImages($editIdx) : [];
        $csrfToken = Csrf::token();
        $errors    = [];
        $pageTitle = ($editIdx ? '후기 수정' : '후기 작성') . ' — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/review-form.php';
        require VIEW_PATH . '/layout/footer.php';
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
        $title    = trim($_POST['title']       ?? '');
        $content  = trim($_POST['content']     ?? '');

        // 기존 이미지 중 삭제 요청된 목록
        $deleteImages = array_filter((array) ($_POST['delete_images'] ?? []));

        // 기존 이미지 수 계산 (수정 모드)
        $existingImages = $editIdx > 0 ? $this->mypageRepo->getReviewImages($editIdx) : [];
        $keptCount = count($existingImages) - count($deleteImages);

        // 새로 업로드할 파일 목록 구성
        $newFiles = [];
        if (!empty($_FILES['review_images']['tmp_name'])) {
            $files = $_FILES['review_images'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $newFiles[] = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
            }
        }

        $errors = [];
        if ($rating < 1 || $rating > 5)  $errors['rating']  = '별점을 선택해주세요.';
        if ($title === '')                $errors['title']   = '제목을 입력해주세요.';
        if (mb_strlen($title) > 200)      $errors['title']   = '제목은 200자 이내로 입력해주세요.';
        if ($content === '')              $errors['content'] = '후기 내용을 입력해주세요.';
        if (mb_strlen($content) < 20)    $errors['content'] = '후기 내용은 20자 이상 입력해주세요.';
        if (($keptCount + count($newFiles)) > 3) $errors['images'] = '이미지는 최대 3장까지 첨부할 수 있습니다.';

        header('Content-Type: application/json; charset=utf-8');

        if ($errors) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        if ($editIdx > 0) {
            $this->mypageRepo->updateReview($editIdx, $memberIdx, [
                'rating'  => $rating,
                'title'   => $title,
                'content' => $content,
            ]);

            // 삭제 요청된 기존 이미지 처리
            foreach ($deleteImages as $imgPath) {
                $imgPath = basename($imgPath);
                $this->mypageRepo->deleteReviewImageByPath($editIdx, $imgPath);
                FileUploader::deleteReviewImage($imgPath);
            }

            // 새 이미지 업로드
            $uploaded = [];
            foreach ($newFiles as $file) {
                $filename = FileUploader::uploadReviewImage($file);
                if ($filename) $uploaded[] = $filename;
            }
            if ($uploaded) {
                $this->mypageRepo->saveReviewImages($editIdx, $uploaded);
            }
        } else {
            // 중복 방지 재확인
            if ($this->mypageRepo->hasReview($memberIdx, $classIdx)) {
                header('Location: /mypage/reviews');
                exit;
            }
            $reviewIdx = $this->mypageRepo->createReview($memberIdx, [
                'class_idx' => $classIdx,
                'rating'    => $rating,
                'title'     => $title,
                'content'   => $content,
            ]);

            // 이미지 업로드
            $uploaded = [];
            foreach ($newFiles as $file) {
                $filename = FileUploader::uploadReviewImage($file);
                if ($filename) $uploaded[] = $filename;
            }
            if ($uploaded) {
                $this->mypageRepo->saveReviewImages($reviewIdx, $uploaded);
            }
        }

        echo json_encode(['ok' => true, 'redirect' => '/mypage/reviews?saved=1']);
        exit;
    }
    // =========================================================================
    // GET /mypage/withdraw/check  (AJAX — 탈퇴 가능 여부만 반환)
    // =========================================================================
    public function withdrawCheck(): void
    {
        $session   = Auth::member();
        $memberIdx = (int) $session['member_idx'];

        $blockReason = $this->getWithdrawBlockReason($memberIdx);

        header('Content-Type: application/json; charset=utf-8');
        if ($blockReason) {
            echo json_encode(['ok' => false, 'message' => $blockReason]);
        } else {
            echo json_encode(['ok' => true]);
        }
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

        $csrfToken = Csrf::token();
        $pageTitle = '회원탈퇴 — 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/mypage/withdraw.php';
        require VIEW_PATH . '/layout/footer.php';
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
        header('Content-Type: application/json; charset=utf-8');

        $blockReason = $this->getWithdrawBlockReason($memberIdx);
        if ($blockReason) {
            echo json_encode(['ok' => false, 'message' => $blockReason]);
            exit;
        }

        $confirm = trim($_POST['confirm_text'] ?? '');
        if ($confirm !== '탈퇴합니다') {
            echo json_encode(['ok' => false, 'errors' => ['confirm_text' => '확인 문구를 정확히 입력해주세요.']]);
            exit;
        }

        $this->mypageRepo->withdraw($memberIdx);
        session_destroy();

        echo json_encode(['ok' => true, 'redirect' => '/?withdrawn=1']);
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
            return "수강 중인 강의가 있으면 탈퇴할 수 없습니다.\n수강 기간 만료 후 탈퇴해주세요.";
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
