<?php

declare(strict_types=1);

use App\Core\Router;

/** @var Router $router */

// -------------------------------------------------------------------------
// 업로드 파일 서빙 (storage/uploads/ → DocumentRoot 외부)
// -------------------------------------------------------------------------
$router->get('/uploads/class/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        http_response_code(404); exit;
    }
    $path = ROOT_PATH . '/storage/uploads/class/' . $filename;
    if (!file_exists($path)) { http_response_code(404); exit; }
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    readfile($path);
});

// 강사 프로필 사진 서빙
$router->get('/uploads/instructor/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        http_response_code(404); exit;
    }
    $path = ROOT_PATH . '/storage/uploads/instructor/' . $filename;
    if (!file_exists($path)) { http_response_code(404); exit; }
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    readfile($path);
});

// 사이트 이미지 서빙 (로고, 파비콘)
$router->get('/uploads/site/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        http_response_code(404); exit;
    }
    $path = ROOT_PATH . '/storage/uploads/site/' . $filename;
    if (!file_exists($path)) { http_response_code(404); exit; }
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    readfile($path);
});

// 이벤트 배너 이미지 서빙
$router->get('/uploads/banner/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        http_response_code(404); exit;
    }
    $path = ROOT_PATH . '/storage/uploads/banner/' . $filename;
    if (!file_exists($path)) { http_response_code(404); exit; }
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    readfile($path);
});

// 팝업 이미지 서빙
$router->get('/uploads/popup/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        http_response_code(404); exit;
    }
    $path = ROOT_PATH . '/storage/uploads/popup/' . $filename;
    if (!file_exists($path)) { http_response_code(404); exit; }
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    readfile($path);
});

// 후기 이미지 서빙
$router->get('/uploads/review/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        http_response_code(404); exit;
    }
    $path = ROOT_PATH . '/storage/uploads/review/' . $filename;
    if (!file_exists($path)) { http_response_code(404); exit; }
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    readfile($path);
});

// 강의 자료 파일 서빙 (로그인 + 수강 여부 검증)
$router->get('/uploads/materials/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        http_response_code(404); exit;
    }

    // 로그인 필수
    if (!\App\Core\Auth::isMember()) {
        http_response_code(403);
        exit('로그인 후 이용 가능합니다.');
    }

    // 파일이 속한 강의 조회
    $file = \App\Core\DB::selectOne(
        'SELECT f.class_idx FROM lc_class_file f WHERE f.file_path = ? AND f.is_active = 1 LIMIT 1',
        [$filename]
    );
    if (!$file) { http_response_code(404); exit; }

    // 수강(신청) 여부 확인
    $memberIdx = (int) \App\Core\Auth::member()['member_idx'];
    $enroll = \App\Core\DB::selectOne(
        'SELECT 1 FROM lc_enroll WHERE member_idx = ? AND class_idx = ? LIMIT 1',
        [$memberIdx, (int) $file['class_idx']]
    );
    if (!$enroll) {
        http_response_code(403);
        exit('수강 신청 후 이용 가능합니다.');
    }

    $path = ROOT_PATH . '/storage/uploads/materials/' . $filename;
    if (!file_exists($path)) { http_response_code(404); exit; }
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . rawurlencode(basename($path)) . '"');
    readfile($path);
});

// -------------------------------------------------------------------------
// 공개 페이지
// -------------------------------------------------------------------------
$router->get('/', [\App\Http\Controllers\HomeController::class, 'index']);
$router->get('/about', [\App\Http\Controllers\AboutController::class, 'index']);

// -------------------------------------------------------------------------
// 인증
// -------------------------------------------------------------------------
$router->get('/login',    [\App\Http\Controllers\AuthController::class, 'loginForm']);
$router->post('/login',   [\App\Http\Controllers\AuthController::class, 'login']);
$router->post('/logout',  [\App\Http\Controllers\AuthController::class, 'logout']);

$router->get('/register',  [\App\Http\Controllers\AuthController::class, 'registerForm']);
$router->post('/register', [\App\Http\Controllers\AuthController::class, 'register']);

$router->get('/find-id',   [\App\Http\Controllers\AuthController::class, 'findId']);
$router->post('/find-id',  [\App\Http\Controllers\AuthController::class, 'findId']);

$router->get('/find-password',         [\App\Http\Controllers\AuthController::class, 'resetPassword']);
$router->post('/find-password/reset',  [\App\Http\Controllers\AuthController::class, 'doResetPassword']);

// 소셜 로그인
$router->get('/auth/kakao',          [\App\Http\Controllers\AuthController::class, 'kakaoRedirect']);
$router->get('/auth/kakao/callback', [\App\Http\Controllers\AuthController::class, 'kakaoCallback']);
$router->get('/auth/naver',          [\App\Http\Controllers\AuthController::class, 'naverRedirect']);
$router->get('/auth/naver/callback', [\App\Http\Controllers\AuthController::class, 'naverCallback']);

// -------------------------------------------------------------------------
// 회원 API (SMS 인증, 아이디 중복 확인)
// -------------------------------------------------------------------------
$router->post('/api/member/send-sms',    [\App\Http\Controllers\Api\MemberApiController::class, 'sendSms']);
$router->post('/api/member/verify-sms',  [\App\Http\Controllers\Api\MemberApiController::class, 'verifySms']);
$router->post('/api/member/check-id',    [\App\Http\Controllers\Api\MemberApiController::class, 'checkId']);

// -------------------------------------------------------------------------
// 강의
// -------------------------------------------------------------------------
$router->get('/classes',                          [\App\Http\Controllers\ClassController::class, 'index']);
$router->get('/classes/{class_idx}',              [\App\Http\Controllers\ClassController::class, 'show']);
$router->get('/classes/{class_idx}/learn',             [\App\Http\Controllers\ClassController::class, 'learn']);
$router->post('/classes/{class_idx}/enroll',           [\App\Http\Controllers\ClassController::class, 'enroll']);
$router->post('/api/classes/{class_idx}/progress',     [\App\Http\Controllers\ClassController::class, 'markProgress']);
$router->post('/api/classes/{class_idx}/complete',     [\App\Http\Controllers\ClassController::class, 'completeChapter']);
$router->post('/classes/{class_idx}/checkout',    [\App\Http\Controllers\ClassController::class, 'checkout']);
$router->post('/api/wish/{class_idx}',            [\App\Http\Controllers\ClassController::class, 'wishToggle']);
$router->post('/api/openchat-log/{class_idx}',    [\App\Http\Controllers\ClassController::class, 'openchatLog']);

// -------------------------------------------------------------------------
// 검색
// -------------------------------------------------------------------------
$router->get('/search', [\App\Http\Controllers\SearchController::class, 'index']);

// -------------------------------------------------------------------------
// 결제
// -------------------------------------------------------------------------
$router->get('/checkout/{class_idx}',  [\App\Http\Controllers\CheckoutController::class, 'show']);
$router->post('/checkout/{class_idx}', [\App\Http\Controllers\CheckoutController::class, 'process']);
$router->get('/checkout/success',      [\App\Http\Controllers\CheckoutController::class, 'success']);
$router->get('/checkout/fail',         [\App\Http\Controllers\CheckoutController::class, 'fail']);

// -------------------------------------------------------------------------
// 강사
// -------------------------------------------------------------------------
$router->get('/instructors',                       [\App\Http\Controllers\InstructorController::class, 'index']);
$router->get('/instructors/apply',                 [\App\Http\Controllers\InstructorController::class, 'applyForm']);
$router->post('/instructors/apply',                [\App\Http\Controllers\InstructorController::class, 'applyStore']);
$router->get('/instructors/{instructor_idx}',      [\App\Http\Controllers\InstructorController::class, 'show']);

// -------------------------------------------------------------------------
// 고객센터
// -------------------------------------------------------------------------
$router->get('/supports/faqs',                     [\App\Http\Controllers\SupportController::class, 'faqs']);
$router->get('/supports/notices',                  [\App\Http\Controllers\SupportController::class, 'notices']);
$router->get('/supports/notices/{notice_idx}',     [\App\Http\Controllers\SupportController::class, 'noticeShow']);
$router->get('/supports/terms',                    [\App\Http\Controllers\SupportController::class, 'terms']);
$router->get('/supports/privacy',                  [\App\Http\Controllers\SupportController::class, 'privacy']);
$router->get('/supports/policy/{type}',            [\App\Http\Controllers\SupportController::class, 'policy']);
$router->get('/supports/contact',                  [\App\Http\Controllers\SupportController::class, 'contactList']);
$router->get('/supports/contact/write',            [\App\Http\Controllers\SupportController::class, 'contactForm']);
$router->post('/supports/contact/write',           [\App\Http\Controllers\SupportController::class, 'contactStore']);
$router->get('/supports/contact/{qna_idx}',        [\App\Http\Controllers\SupportController::class, 'contactShow']);

// -------------------------------------------------------------------------
// 마이페이지 (로그인 필요 — 각 컨트롤러에서 Auth::requireLogin() 호출)
// -------------------------------------------------------------------------
$router->get('/mypage/my-class',               [\App\Http\Controllers\MypageController::class, 'myClass']);
$router->get('/mypage/wishlist',               [\App\Http\Controllers\MypageController::class, 'wishlist']);
$router->get('/mypage/orders',                 [\App\Http\Controllers\MypageController::class, 'orders']);
$router->get('/mypage/orders/{order_idx}',          [\App\Http\Controllers\MypageController::class, 'orderShow']);
$router->post('/mypage/orders/{order_idx}/refund',  [\App\Http\Controllers\MypageController::class, 'orderRefund']);
$router->get('/mypage/qna',                        [\App\Http\Controllers\MypageController::class, 'qnaList']);
$router->get('/mypage/qna/write',                  [\App\Http\Controllers\MypageController::class, 'qnaForm']);
$router->post('/mypage/qna/write',                 [\App\Http\Controllers\MypageController::class, 'qnaStore']);
$router->get('/mypage/qna/{qna_idx}',              [\App\Http\Controllers\MypageController::class, 'qnaShow']);
$router->post('/mypage/qna/{qna_idx}/delete',      [\App\Http\Controllers\MypageController::class, 'qnaDelete']);
$router->get('/mypage/reviews',                [\App\Http\Controllers\MypageController::class, 'reviews']);
$router->get('/mypage/reviews/write',          [\App\Http\Controllers\MypageController::class, 'reviewForm']);
$router->post('/mypage/reviews/write',         [\App\Http\Controllers\MypageController::class, 'reviewStore']);
$router->get('/mypage/profile',                [\App\Http\Controllers\MypageController::class, 'profileForm']);
$router->post('/mypage/profile',               [\App\Http\Controllers\MypageController::class, 'profileUpdate']);
$router->get('/mypage/withdraw/check',         [\App\Http\Controllers\MypageController::class, 'withdrawCheck']);
$router->get('/mypage/withdraw',               [\App\Http\Controllers\MypageController::class, 'withdrawForm']);
$router->post('/mypage/withdraw',              [\App\Http\Controllers\MypageController::class, 'withdraw']);

// -------------------------------------------------------------------------
// 관리자 (mb_role = 'admin' 필요 — 각 컨트롤러에서 Auth::requireAdmin() 호출)
// -------------------------------------------------------------------------
$router->get('/admin',                                           [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
$router->get('/admin/login',                                     [\App\Http\Controllers\Admin\AuthController::class, 'loginForm']);
$router->post('/admin/login',                                    [\App\Http\Controllers\Admin\AuthController::class, 'login']);
$router->get('/admin/logout',                                    [\App\Http\Controllers\Admin\AuthController::class, 'logout']);

$router->get('/admin/categories',                                [\App\Http\Controllers\Admin\CategoryController::class, 'index']);
$router->get('/admin/categories/create',                         [\App\Http\Controllers\Admin\CategoryController::class, 'create']);
$router->post('/admin/categories',                               [\App\Http\Controllers\Admin\CategoryController::class, 'storeForm']);
$router->post('/admin/categories/store',                         [\App\Http\Controllers\Admin\CategoryController::class, 'store']);
$router->get('/admin/categories/{idx}/edit',                     [\App\Http\Controllers\Admin\CategoryController::class, 'edit']);
$router->post('/admin/categories/{idx}',                         [\App\Http\Controllers\Admin\CategoryController::class, 'updateForm']);
$router->post('/admin/categories/{idx}/update',                  [\App\Http\Controllers\Admin\CategoryController::class, 'update']);
$router->post('/admin/categories/{idx}/delete',                  [\App\Http\Controllers\Admin\CategoryController::class, 'destroy']);

$router->get('/admin/classes',                                   [\App\Http\Controllers\Admin\ClassController::class, 'index']);
$router->get('/admin/classes/create',                            [\App\Http\Controllers\Admin\ClassController::class, 'create']);
$router->post('/admin/classes',                                  [\App\Http\Controllers\Admin\ClassController::class, 'store']);
$router->get('/admin/classes/{class_idx}/edit',                  [\App\Http\Controllers\Admin\ClassController::class, 'edit']);
$router->post('/admin/classes/{class_idx}',                      [\App\Http\Controllers\Admin\ClassController::class, 'update']);
$router->post('/admin/classes/{class_idx}/delete',               [\App\Http\Controllers\Admin\ClassController::class, 'destroy']);

// 챕터 관리 API (JSON)
$router->post('/admin/api/chapters',                             [\App\Http\Controllers\Admin\ChapterController::class, 'store']);
$router->post('/admin/api/chapters/reorder',                     [\App\Http\Controllers\Admin\ChapterController::class, 'reorder']);
$router->post('/admin/api/chapters/{idx}/update',                [\App\Http\Controllers\Admin\ChapterController::class, 'update']);
$router->post('/admin/api/chapters/{idx}/delete',                [\App\Http\Controllers\Admin\ChapterController::class, 'destroy']);

$router->get('/admin/instructors',                               [\App\Http\Controllers\Admin\InstructorController::class, 'index']);
$router->get('/admin/instructors/create',                        [\App\Http\Controllers\Admin\InstructorController::class, 'create']);
$router->post('/admin/instructors',                              [\App\Http\Controllers\Admin\InstructorController::class, 'store']);
$router->get('/admin/instructors/{instructor_idx}/edit',         [\App\Http\Controllers\Admin\InstructorController::class, 'edit']);
$router->post('/admin/instructors/{instructor_idx}',             [\App\Http\Controllers\Admin\InstructorController::class, 'update']);
$router->post('/admin/instructors/{instructor_idx}/delete',      [\App\Http\Controllers\Admin\InstructorController::class, 'destroy']);

$router->get('/admin/instructor-apply',                          [\App\Http\Controllers\Admin\InstructorApplyController::class, 'index']);
$router->get('/admin/instructor-apply/{apply_idx}',              [\App\Http\Controllers\Admin\InstructorApplyController::class, 'show']);
$router->post('/admin/instructor-apply/{apply_idx}/approve',     [\App\Http\Controllers\Admin\InstructorApplyController::class, 'approve']);
$router->post('/admin/instructor-apply/{apply_idx}/reject',      [\App\Http\Controllers\Admin\InstructorApplyController::class, 'reject']);

$router->get('/admin/members',                                   [\App\Http\Controllers\Admin\MemberController::class, 'index']);
$router->get('/admin/members/{mb_idx}',                          [\App\Http\Controllers\Admin\MemberController::class, 'show']);
$router->post('/admin/members/{mb_idx}/status',                  [\App\Http\Controllers\Admin\MemberController::class, 'updateStatus']);
$router->post('/admin/members/{mb_idx}/profile',                 [\App\Http\Controllers\Admin\MemberController::class, 'updateProfile']);

$router->get('/admin/orders',                                    [\App\Http\Controllers\Admin\OrderController::class, 'index']);
$router->get('/admin/orders/{order_idx}',                        [\App\Http\Controllers\Admin\OrderController::class, 'show']);
$router->post('/admin/orders/{order_idx}/refund/approve',        [\App\Http\Controllers\Admin\OrderController::class, 'refundApprove']);
$router->post('/admin/orders/{order_idx}/refund/reject',         [\App\Http\Controllers\Admin\OrderController::class, 'refundReject']);

$router->get('/admin/contacts',                                  [\App\Http\Controllers\Admin\ContactController::class, 'index']);
$router->get('/admin/contacts/{contact_idx}',                    [\App\Http\Controllers\Admin\ContactController::class, 'show']);
$router->post('/admin/contacts/{contact_idx}/answer',            [\App\Http\Controllers\Admin\ContactController::class, 'answer']);

$router->get('/admin/reviews',                                   [\App\Http\Controllers\Admin\ReviewController::class, 'index']);
$router->get('/admin/reviews/{review_idx}',                      [\App\Http\Controllers\Admin\ReviewController::class, 'show']);
$router->post('/admin/reviews/{review_idx}/active',              [\App\Http\Controllers\Admin\ReviewController::class, 'toggleActive']);
$router->post('/admin/reviews/{review_idx}/delete',              [\App\Http\Controllers\Admin\ReviewController::class, 'destroy']);

$router->get('/admin/notices',                                   [\App\Http\Controllers\Admin\NoticeController::class, 'index']);
$router->get('/admin/notices/create',                            [\App\Http\Controllers\Admin\NoticeController::class, 'create']);
$router->post('/admin/notices',                                  [\App\Http\Controllers\Admin\NoticeController::class, 'store']);
$router->get('/admin/notices/{notice_idx}/edit',                 [\App\Http\Controllers\Admin\NoticeController::class, 'edit']);
$router->post('/admin/notices/{notice_idx}',                     [\App\Http\Controllers\Admin\NoticeController::class, 'update']);
$router->post('/admin/notices/{notice_idx}/delete',              [\App\Http\Controllers\Admin\NoticeController::class, 'destroy']);

$router->get('/admin/faqs',                                      [\App\Http\Controllers\Admin\FaqController::class, 'index']);
$router->get('/admin/faqs/create',                               [\App\Http\Controllers\Admin\FaqController::class, 'create']);
$router->post('/admin/faqs',                                     [\App\Http\Controllers\Admin\FaqController::class, 'store']);
$router->get('/admin/faqs/{faq_idx}/edit',                       [\App\Http\Controllers\Admin\FaqController::class, 'edit']);
$router->post('/admin/faqs/{faq_idx}',                           [\App\Http\Controllers\Admin\FaqController::class, 'update']);
$router->post('/admin/faqs/{faq_idx}/delete',                    [\App\Http\Controllers\Admin\FaqController::class, 'destroy']);

$router->get('/admin/search-logs',                               [\App\Http\Controllers\Admin\StatsController::class, 'searchLogs']);
$router->get('/admin/openchat-logs',                             [\App\Http\Controllers\Admin\StatsController::class, 'openchatLogs']);

// 이벤트 배너
$router->get('/admin/banners',                                   [\App\Http\Controllers\Admin\BannerController::class, 'index']);
$router->get('/admin/banners/create',                            [\App\Http\Controllers\Admin\BannerController::class, 'create']);
$router->post('/admin/banners',                                  [\App\Http\Controllers\Admin\BannerController::class, 'store']);
$router->get('/admin/banners/{banner_idx}/edit',                 [\App\Http\Controllers\Admin\BannerController::class, 'edit']);
$router->post('/admin/banners/{banner_idx}',                     [\App\Http\Controllers\Admin\BannerController::class, 'update']);
$router->post('/admin/banners/{banner_idx}/delete',              [\App\Http\Controllers\Admin\BannerController::class, 'destroy']);

// 팝업
$router->get('/admin/popups',                                    [\App\Http\Controllers\Admin\PopupController::class, 'index']);
$router->get('/admin/popups/create',                             [\App\Http\Controllers\Admin\PopupController::class, 'create']);
$router->post('/admin/popups',                                   [\App\Http\Controllers\Admin\PopupController::class, 'store']);
$router->get('/admin/popups/{popup_idx}/edit',                   [\App\Http\Controllers\Admin\PopupController::class, 'edit']);
$router->post('/admin/popups/{popup_idx}',                       [\App\Http\Controllers\Admin\PopupController::class, 'update']);
$router->post('/admin/popups/{popup_idx}/delete',                [\App\Http\Controllers\Admin\PopupController::class, 'destroy']);

// 설정
$router->get('/admin/settings',                                  [\App\Http\Controllers\Admin\SettingController::class, 'index']);
$router->post('/admin/settings',                                 [\App\Http\Controllers\Admin\SettingController::class, 'update']);

// 약관 관리
$router->get( '/admin/terms',                       [\App\Http\Controllers\Admin\TermsController::class, 'index']);
$router->get( '/admin/terms/{type}/versions',       [\App\Http\Controllers\Admin\TermsController::class, 'versions']);
$router->get( '/admin/terms/{type}/create',         [\App\Http\Controllers\Admin\TermsController::class, 'createForm']);
$router->post('/admin/terms/{type}',                [\App\Http\Controllers\Admin\TermsController::class, 'store']);
$router->get( '/admin/terms/v/{idx}/edit',          [\App\Http\Controllers\Admin\TermsController::class, 'editForm']);
$router->post('/admin/terms/v/{idx}',               [\App\Http\Controllers\Admin\TermsController::class, 'update']);
$router->post('/admin/terms/v/{idx}/current',       [\App\Http\Controllers\Admin\TermsController::class, 'setCurrent']);
$router->post('/admin/terms/v/{idx}/delete',        [\App\Http\Controllers\Admin\TermsController::class, 'destroy']);

// 관리자 프로필
$router->get('/admin/profile',                                   [\App\Http\Controllers\Admin\ProfileController::class, 'index']);
$router->post('/admin/profile',                                  [\App\Http\Controllers\Admin\ProfileController::class, 'update']);
$router->post('/admin/profile/password',                         [\App\Http\Controllers\Admin\ProfileController::class, 'changePassword']);
