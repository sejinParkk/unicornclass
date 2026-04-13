<?php

declare(strict_types=1);

use App\Core\Router;

/** @var Router $router */

// -------------------------------------------------------------------------
// 인증 / 회원
// -------------------------------------------------------------------------
$router->post('/api/auth/login',           [\App\Http\Controllers\Api\AuthApiController::class, 'login']);
$router->post('/api/auth/logout',          [\App\Http\Controllers\Api\AuthApiController::class, 'logout']);

$router->post('/api/member/check-id',      [\App\Http\Controllers\Api\MemberApiController::class, 'checkId']);
$router->post('/api/member/send-sms',      [\App\Http\Controllers\Api\MemberApiController::class, 'sendSms']);
$router->post('/api/member/verify-sms',    [\App\Http\Controllers\Api\MemberApiController::class, 'verifySms']);

// -------------------------------------------------------------------------
// 강의
// -------------------------------------------------------------------------
$router->get('/api/classes',                          [\App\Http\Controllers\Api\ClassApiController::class, 'index']);
$router->get('/api/classes/{class_idx}',              [\App\Http\Controllers\Api\ClassApiController::class, 'show']);
$router->post('/api/classes/{class_idx}/like',        [\App\Http\Controllers\Api\ClassApiController::class, 'like']);
$router->post('/api/classes/{class_idx}/wish',        [\App\Http\Controllers\Api\ClassApiController::class, 'wish']);
$router->post('/api/enroll/free',                     [\App\Http\Controllers\Api\EnrollApiController::class, 'enrollFree']);

// -------------------------------------------------------------------------
// 검색
// -------------------------------------------------------------------------
$router->get('/api/search',               [\App\Http\Controllers\Api\SearchApiController::class, 'search']);
$router->get('/api/search/suggest',       [\App\Http\Controllers\Api\SearchApiController::class, 'suggest']);

// -------------------------------------------------------------------------
// 결제
// -------------------------------------------------------------------------
$router->post('/api/checkout/prepare',                        [\App\Http\Controllers\Api\CheckoutApiController::class, 'prepare']);
$router->post('/api/checkout/webhook',                        [\App\Http\Controllers\Api\CheckoutApiController::class, 'webhook']);
$router->post('/api/mypage/orders/{order_idx}/refund',        [\App\Http\Controllers\Api\CheckoutApiController::class, 'refund']);

// -------------------------------------------------------------------------
// 진도
// -------------------------------------------------------------------------
$router->post('/api/progress/update',     [\App\Http\Controllers\Api\ProgressApiController::class, 'update']);

// -------------------------------------------------------------------------
// 마이페이지
// -------------------------------------------------------------------------
$router->get('/api/mypage/classes',       [\App\Http\Controllers\Api\MypageApiController::class, 'classes']);
$router->get('/api/mypage/orders',        [\App\Http\Controllers\Api\MypageApiController::class, 'orders']);
$router->get('/api/mypage/orders/{order_idx}', [\App\Http\Controllers\Api\MypageApiController::class, 'orderShow']);
$router->get('/api/mypage/wishlist',      [\App\Http\Controllers\Api\MypageApiController::class, 'wishlist']);
$router->post('/api/mypage/reviews',      [\App\Http\Controllers\Api\MypageApiController::class, 'reviewStore']);
$router->put('/api/mypage/reviews/{review_idx}', [\App\Http\Controllers\Api\MypageApiController::class, 'reviewUpdate']);
$router->post('/api/mypage/profile',      [\App\Http\Controllers\Api\MypageApiController::class, 'profileUpdate']);

// -------------------------------------------------------------------------
// 오픈채팅
// -------------------------------------------------------------------------
$router->post('/api/openchat/log',        [\App\Http\Controllers\Api\OpenChatApiController::class, 'log']);

// -------------------------------------------------------------------------
// 강사 지원
// -------------------------------------------------------------------------
$router->post('/api/instructor/apply',    [\App\Http\Controllers\Api\InstructorApiController::class, 'apply']);

// -------------------------------------------------------------------------
// 고객센터
// -------------------------------------------------------------------------
$router->get('/api/faqs',                 [\App\Http\Controllers\Api\SupportApiController::class, 'faqs']);
$router->post('/api/supports/contact',    [\App\Http\Controllers\Api\SupportApiController::class, 'contact']);

// -------------------------------------------------------------------------
// 관리자 API
// -------------------------------------------------------------------------
$router->get('/admin/api/classes',                                   [\App\Http\Controllers\Admin\Api\ClassAdminApiController::class, 'index']);
$router->post('/admin/api/classes',                                  [\App\Http\Controllers\Admin\Api\ClassAdminApiController::class, 'store']);
$router->put('/admin/api/classes/{idx}',                             [\App\Http\Controllers\Admin\Api\ClassAdminApiController::class, 'update']);
$router->delete('/admin/api/classes/{idx}',                          [\App\Http\Controllers\Admin\Api\ClassAdminApiController::class, 'destroy']);

$router->post('/admin/api/chapters',                                 [\App\Http\Controllers\Admin\Api\ChapterAdminApiController::class, 'store']);
$router->put('/admin/api/chapters/{idx}',                            [\App\Http\Controllers\Admin\Api\ChapterAdminApiController::class, 'update']);
$router->delete('/admin/api/chapters/{idx}',                         [\App\Http\Controllers\Admin\Api\ChapterAdminApiController::class, 'destroy']);

$router->get('/admin/api/members',                                   [\App\Http\Controllers\Admin\Api\MemberAdminApiController::class, 'index']);
$router->put('/admin/api/members/{idx}/status',                      [\App\Http\Controllers\Admin\Api\MemberAdminApiController::class, 'updateStatus']);

$router->get('/admin/api/orders',                                    [\App\Http\Controllers\Admin\Api\OrderAdminApiController::class, 'index']);
$router->post('/admin/api/orders/{idx}/refund/approve',              [\App\Http\Controllers\Admin\Api\OrderAdminApiController::class, 'refundApprove']);
$router->post('/admin/api/orders/{idx}/refund/reject',               [\App\Http\Controllers\Admin\Api\OrderAdminApiController::class, 'refundReject']);

$router->get('/admin/api/instructor-apply',                          [\App\Http\Controllers\Admin\Api\InstructorApplyAdminApiController::class, 'index']);
$router->post('/admin/api/instructor-apply/{idx}/approve',           [\App\Http\Controllers\Admin\Api\InstructorApplyAdminApiController::class, 'approve']);
$router->post('/admin/api/instructor-apply/{idx}/reject',            [\App\Http\Controllers\Admin\Api\InstructorApplyAdminApiController::class, 'reject']);

$router->post('/admin/api/faqs',                                     [\App\Http\Controllers\Admin\Api\FaqAdminApiController::class, 'store']);
$router->put('/admin/api/faqs/{idx}',                                [\App\Http\Controllers\Admin\Api\FaqAdminApiController::class, 'update']);
$router->delete('/admin/api/faqs/{idx}',                             [\App\Http\Controllers\Admin\Api\FaqAdminApiController::class, 'destroy']);

$router->post('/admin/api/notices',                                  [\App\Http\Controllers\Admin\Api\NoticeAdminApiController::class, 'store']);
$router->post('/admin/api/contacts/{idx}/answer',                    [\App\Http\Controllers\Admin\Api\ContactAdminApiController::class, 'answer']);
$router->get('/admin/api/stats/dashboard',                           [\App\Http\Controllers\Admin\Api\StatsAdminApiController::class, 'dashboard']);
