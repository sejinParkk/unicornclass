<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Csrf;
use App\Repositories\MemberRepository;
use App\Repositories\SmsVerificationRepository;

class MemberApiController
{
    private MemberRepository $memberRepo;
    private SmsVerificationRepository $smsRepo;

    public function __construct()
    {
        $this->memberRepo = new MemberRepository();
        $this->smsRepo    = new SmsVerificationRepository();
    }

    // -------------------------------------------------------------------------
    // POST /api/member/send-sms
    // body: csrf_token, phone, purpose, mb_id(optional, find_password 전용)
    // -------------------------------------------------------------------------
    public function sendSms(): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $phone   = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
        $purpose = $_POST['purpose'] ?? '';
        $mbId    = trim($_POST['mb_id'] ?? '') ?: null;

        // 유효성 검사
        if (!preg_match('/^01[016789]\d{7,8}$/', $phone)) {
            echo json_encode(['ok' => false, 'message' => '올바른 휴대폰 번호를 입력해주세요.']);
            return;
        }
        if (!in_array($purpose, ['find_id', 'find_password', 'register'], true)) {
            echo json_encode(['ok' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }

        // 일 5회 제한
        if ($this->smsRepo->countTodayRequests($this->formatPhone($phone), $purpose) >= 5) {
            echo json_encode(['ok' => false, 'message' => '하루 최대 5회까지 인증요청이 가능합니다.']);
            return;
        }

        $phoneFormatted = $this->formatPhone($phone);

        // 비밀번호 찾기: 아이디+번호 선검증
        if ($purpose === 'find_password') {
            if (!$mbId) {
                echo json_encode(['ok' => false, 'message' => '아이디를 입력해주세요.']);
                return;
            }
            $member = $this->memberRepo->findByIdAndPhone($mbId, $phoneFormatted);
            if (!$member || !$member['is_active']) {
                echo json_encode(['ok' => false, 'message' => '아이디와 휴대폰 번호가 일치하는 계정이 없습니다.']);
                return;
            }
        }

        // OTP 생성 (실제 SMS 발송 없음 — DB에 저장)
        $code = $this->smsRepo->create($phoneFormatted, $purpose, $mbId);

        // 개발 편의: 응답에 코드 포함 (운영 시 제거)
        echo json_encode([
            'ok'      => true,
            'message' => '인증번호가 생성되었습니다.',
            '_dev_code' => $code,   // TODO: 알리고 연동 후 제거
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/member/verify-sms
    // body: csrf_token, phone, code, purpose, mb_id(optional)
    // -------------------------------------------------------------------------
    public function verifySms(): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $phone   = $this->formatPhone(preg_replace('/[^0-9]/', '', $_POST['phone'] ?? ''));
        $code    = trim($_POST['code'] ?? '');
        $purpose = $_POST['purpose'] ?? '';
        $mbId    = trim($_POST['mb_id'] ?? '') ?: null;

        if ($code === '' || strlen($code) !== 6) {
            echo json_encode(['ok' => false, 'message' => '인증번호 6자리를 입력해주세요.']);
            return;
        }

        $ok = $this->smsRepo->verify($phone, $code, $purpose);

        if (!$ok) {
            echo json_encode(['ok' => false, 'message' => '인증번호가 올바르지 않거나 만료되었습니다.']);
            return;
        }

        // 인증 성공 → purpose별 후처리
        if ($purpose === 'register') {
            // 이미 가입된 번호 차단
            if ($this->memberRepo->existsByPhone($phone)) {
                $member = $this->memberRepo->findByPhone($phone);
                $socialType = $member['signup_type'] ?? 'email';
                if ($socialType !== 'email') {
                    echo json_encode([
                        'ok'          => false,
                        'blocked'     => true,
                        'social_type' => $socialType,
                        'message'     => '이미 ' . $this->socialName($socialType) . '로 가입된 번호입니다.',
                    ]);
                } else {
                    echo json_encode([
                        'ok'      => false,
                        'blocked' => true,
                        'message' => '이미 가입된 번호입니다.',
                    ]);
                }
                return;
            }
            $_SESSION['sms_verified_register'] = $phone;
        }

        if ($purpose === 'find_id') {
            $_SESSION['sms_verified_find_id'] = $phone;
            // 회원 조회 결과 함께 반환
            $member = $this->memberRepo->findByPhone($phone);
            if (!$member) {
                echo json_encode(['ok' => true, 'found' => false]);
                return;
            }
            if ($member['signup_type'] !== 'email') {
                echo json_encode([
                    'ok'          => true,
                    'found'       => true,
                    'social'      => true,
                    'social_type' => $member['signup_type'],
                    'social_name' => $this->socialName($member['signup_type']),
                ]);
                return;
            }
            $id        = $member['mb_id'];
            $keep      = 4;
            $maskedId  = substr($id, 0, $keep) . str_repeat('*', max(1, strlen($id) - $keep));
            echo json_encode(['ok' => true, 'found' => true, 'social' => false, 'masked_id' => $maskedId]);
            return;
        }

        if ($purpose === 'find_password') {
            $_SESSION['sms_verified_find_password'] = [
                'phone' => $phone,
                'mb_id' => $mbId,
            ];
        }

        echo json_encode(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // POST /api/member/check-id
    // -------------------------------------------------------------------------
    public function checkId(): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $mbId = trim($_POST['mb_id'] ?? '');

        if ($mbId === '' || !preg_match('/^[a-z0-9]{4,20}$/', $mbId)) {
            echo json_encode(['available' => false, 'message' => '아이디는 소문자 영문/숫자 4~20자여야 합니다.']);
            return;
        }

        $exists = $this->memberRepo->existsById($mbId);
        echo json_encode([
            'available' => !$exists,
            'message'   => $exists ? '이미 사용 중인 아이디입니다.' : '사용 가능한 아이디입니다.',
        ]);
    }

    // -------------------------------------------------------------------------
    private function formatPhone(string $digits): string
    {
        // 숫자만 들어온 경우 하이픈 포맷으로
        if (!str_contains($digits, '-')) {
            if (strlen($digits) === 11) {
                return substr($digits, 0, 3) . '-' . substr($digits, 3, 4) . '-' . substr($digits, 7);
            }
            if (strlen($digits) === 10) {
                return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6);
            }
        }
        return $digits;
    }

    private function socialName(string $type): string
    {
        return match ($type) {
            'kakao' => '카카오',
            'naver' => '네이버',
            default => '소셜',
        };
    }
}
