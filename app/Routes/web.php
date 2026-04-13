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

// 강의 자료 파일 서빙
$router->get('/uploads/materials/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        http_response_code(404); exit;
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
$router->get('/', fn() => require VIEW_PATH . '/pages/home.php');
$router->get('/about', fn() => require VIEW_PATH . '/pages/about.php');

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
// 강의
// -------------------------------------------------------------------------
$router->get('/classes',                          [\App\Http\Controllers\ClassController::class, 'index']);
$router->get('/classes/{class_idx}',              [\App\Http\Controllers\ClassController::class, 'show']);
$router->get('/classes/{class_idx}/learn',        [\App\Http\Controllers\ClassController::class, 'learn']);
$router->post('/classes/{class_idx}/enroll',      [\App\Http\Controllers\ClassController::class, 'enroll']);
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
$router->get('/mypage/orders/{order_idx}',     [\App\Http\Controllers\MypageController::class, 'orderShow']);
$router->get('/mypage/qna',                    [\App\Http\Controllers\MypageController::class, 'qnaList']);
$router->get('/mypage/qna/{qna_idx}',          [\App\Http\Controllers\MypageController::class, 'qnaShow']);
$router->get('/mypage/reviews',                [\App\Http\Controllers\MypageController::class, 'reviews']);
$router->get('/mypage/reviews/write',          [\App\Http\Controllers\MypageController::class, 'reviewForm']);
$router->post('/mypage/reviews/write',         [\App\Http\Controllers\MypageController::class, 'reviewStore']);
$router->get('/mypage/profile',                [\App\Http\Controllers\MypageController::class, 'profileForm']);
$router->post('/mypage/profile',               [\App\Http\Controllers\MypageController::class, 'profileUpdate']);
$router->get('/mypage/withdraw',               [\App\Http\Controllers\MypageController::class, 'withdrawForm']);
$router->post('/mypage/withdraw',              [\App\Http\Controllers\MypageController::class, 'withdraw']);

// -------------------------------------------------------------------------
// 관리자 (mb_role = 'admin' 필요 — 각 컨트롤러에서 Auth::requireAdmin() 호출)
// -------------------------------------------------------------------------
$router->get('/admin',                                           [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
$router->get('/admin/login',                                     [\App\Http\Controllers\Admin\AuthController::class, 'loginForm']);
$router->post('/admin/login',                                    [\App\Http\Controllers\Admin\AuthController::class, 'login']);
$router->get('/admin/logout',                                    [\App\Http\Controllers\Admin\AuthController::class, 'logout']);

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

// 설정
$router->get('/admin/settings',                                  [\App\Http\Controllers\Admin\SettingController::class, 'index']);
$router->post('/admin/settings',                                 [\App\Http\Controllers\Admin\SettingController::class, 'update']);

// 약관 관리
$router->get('/admin/terms',                                     [\App\Http\Controllers\Admin\TermsController::class, 'index']);
$router->post('/admin/terms/{type}',                             [\App\Http\Controllers\Admin\TermsController::class, 'update']);

// 관리자 프로필
$router->get('/admin/profile',                                   [\App\Http\Controllers\Admin\ProfileController::class, 'index']);
$router->post('/admin/profile',                                  [\App\Http\Controllers\Admin\ProfileController::class, 'update']);
$router->post('/admin/profile/password',                         [\App\Http\Controllers\Admin\ProfileController::class, 'changePassword']);
