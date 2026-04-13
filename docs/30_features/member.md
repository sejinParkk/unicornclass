# 회원 관리 명세

---

## 1. 회원 상태 (`mb_status`)

| 상태 | 값 | 설명 | 로그인 가능 |
|------|----|------|------------|
| 정상 | `active` | 일반 이용 가능 | O |
| 휴면 | `dormant` | 1년 이상 미접속 | X (본인인증 후 활성화) |
| 탈퇴 | `withdrawn` | 탈퇴 처리 | X |

### 상태 전환 규칙

```
active → dormant    : 마지막 로그인 후 365일 경과 (배치 처리)
dormant → active    : 본인인증(SMS) 완료 후 수동 복구
active → withdrawn  : 회원탈퇴 신청 + 비밀번호 확인
withdrawn → active  : 탈퇴 후 30일 경과 시 동일 이메일 재가입 가능
```

---

## 2. 회원 권한 (`mb_role`)

| 역할 | 값 | 접근 범위 |
|------|----|----------|
| 일반 회원 | `user` | 마이페이지, 수강, 결제, 문의 |
| 관리자 | `admin` | 전체 + `/admin/*` |

---

## 3. 회원 식별자 체계

| 필드 | 규칙 |
|------|------|
| `mb_idx` | 내부 PK, URL 노출 금지 |
| `mb_id` | 로그인 아이디, 영문+숫자 4~20자, 변경 불가 |
| `mb_email` | 이메일, 소셜 로그인 시 소셜 이메일 저장 |
| `social_id` | 소셜 로그인 고유 ID (카카오/네이버 user_id) |

---

## 4. 소셜 회원 처리 규칙

| 상황 | 처리 |
|------|------|
| 첫 소셜 로그인 (신규) | 소셜 이메일로 자동 회원가입 후 로그인 |
| 소셜 이메일 없음 | 이메일 입력 폼 추가 표시 |
| 동일 이메일 일반 계정 존재 | 계정 연동 여부 확인 팝업 → 동의 시 `social_provider/social_id` 추가 |
| 탈퇴 계정으로 소셜 재시도 | "탈퇴한 계정입니다" 팝업 |
| 다른 소셜(카카오↔네이버) 이중 가입 | 각각 독립 계정으로 처리 (연동 기능은 추후) |

### 소셜 회원 자동 생성 시 `mb_id` 채번

```php
// 소셜 ID 기반 자동 아이디 생성
$mbId = $socialProvider . '_' . substr($socialId, 0, 10);
// 충돌 시: $mbId . '_' . random(4)
```

---

## 5. 비밀번호 정책

| 항목 | 규칙 |
|------|------|
| 최소 길이 | 8자 이상 |
| 구성 | 영문 + 숫자 + 특수문자 포함 |
| 해시 | `password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12])` |
| 검증 | `password_verify()` |
| 소셜 전용 계정 | `mb_password = NULL` 허용 |
| 재설정 토큰 | `random_bytes(32)` hex, 30분 만료, 1회 사용 |

---

## 6. 아이디 찾기

```
1. 휴대폰 번호 입력
2. POST /api/member/send-sms → SMS 인증번호 발송 (6자리, 3분 유효)
3. 인증번호 입력 → POST /api/member/verify-sms
4. 검증 성공 → mb_id 마스킹 표시
   예: "hong***" (앞 4자 노출, 나머지 *)
5. 계정 없음 → "등록된 회원 정보를 찾을 수 없습니다"
```

### SMS 인증 제한

| 항목 | 규칙 |
|------|------|
| 발송 제한 | 동일 번호 1일 5회 |
| 타이머 | 3분 (180초) |
| 재발송 | 타이머 종료 후 가능 |

---

## 7. 비밀번호 재설정

```
1. 이메일 입력
2. 재설정 링크 발송 (토큰 포함)
3. 링크 클릭 → 토큰 검증 (30분, 미사용)
4. 새 비밀번호 입력 (2회 일치 확인)
5. password_hash 저장
6. 토큰 무효화 → 로그인 페이지 이동
```

### 토큰 저장 테이블

```sql
CREATE TABLE lc_password_reset (
    token       VARCHAR(64)  PRIMARY KEY,
    mb_idx      INT UNSIGNED NOT NULL,
    expires_at  DATETIME     NOT NULL,
    used        TINYINT(1)   DEFAULT 0,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
);
```

---

## 8. 정보수정 (`/mypage/profile`)

| 필드 | 수정 가능 | 조건 |
|------|----------|------|
| 이름 | O | 2~20자 |
| 이메일 | O | 형식 유효성 + 중복 체크 |
| 휴대폰 | O | 숫자 11자리 |
| 프로필 사진 | O | jpg/png/webp, 최대 2MB |
| 비밀번호 | O | 현재 비밀번호 확인 후 변경 |
| 아이디 | X | 변경 불가 |
| 소셜 연동 정보 | X | 수정 불가 |

---

## 9. 회원탈퇴 처리

### 탈퇴 가능 조건

```
1. 로그인 상태
2. 비밀번호 확인 성공 (소셜 전용 계정: 이메일 인증)
3. 환불 처리 중(refund_req) 주문 없음
```

### 탈퇴 처리 순서

```php
public function withdraw(int $mbIdx, string $password): void
{
    $member = $this->memberRepo->find($mbIdx);

    // 1. 비밀번호 확인
    if (!password_verify($password, $member->mb_password)) {
        throw new \RuntimeException('비밀번호가 일치하지 않습니다.');
    }

    // 2. 환불 진행 중 주문 확인
    if ($this->orderRepo->hasRefundPending($mbIdx)) {
        throw new \RuntimeException('환불 처리 중인 강의가 있습니다. 처리 완료 후 탈퇴 가능합니다.');
    }

    $this->pdo->beginTransaction();

    // 3. 회원 상태 변경 (물리 삭제 X)
    $this->memberRepo->updateStatus($mbIdx, 'withdrawn');

    // 4. 수강 전체 비활성화
    $this->enrollRepo->deactivateAll($mbIdx);

    // 5. 세션 파기
    session_destroy();

    $this->pdo->commit();
    // 개인정보 실제 삭제는 30일 후 배치 처리
}
```

---

## 10. 휴면 계정 배치 처리

```sql
-- 365일 미접속 계정 휴면 전환 (매일 새벽 3시 배치)
UPDATE lc_member
SET mb_status = 'dormant'
WHERE mb_status = 'active'
  AND last_login_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
```

---

## 화면 명세

### 아이디 찾기 (`/find-id`)

**진입 조건:** 비로그인
**레이아웃:** 중앙 카드 (로그인 페이지와 동일 배경)

| 단계 | 표시 요소 |
|------|-----------|
| 1. 번호 입력 | 휴대폰 번호 input + "인증번호 발송" 버튼 |
| 2. 인증번호 입력 | 6자리 input + 카운트다운 타이머 (3:00) + "확인" 버튼 |
| 3. 결과 | `hong***` 형식 마스킹 표시 + "로그인하기" 버튼 |

**에러 상태:**
- 등록된 번호 없음 → "등록된 회원 정보를 찾을 수 없습니다"
- 인증번호 불일치 → "인증번호가 올바르지 않습니다"
- 타이머 만료 → "인증 시간이 초과되었습니다. 재발송해 주세요" + 재발송 버튼 활성

---

### 비밀번호 재설정 (`/find-password`)

**진입 조건:** 비로그인
**레이아웃:** 중앙 카드

| 단계 | 표시 요소 |
|------|-----------|
| 1. 이메일 입력 | 이메일 input + "재설정 링크 발송" 버튼 |
| 2. 발송 완료 | "이메일을 확인해 주세요" 안내 + 유효시간(30분) 표시 |
| 3. 새 비밀번호 입력 (링크 클릭 후) | 새 비밀번호 + 확인 input (eye 토글) + "변경하기" 버튼 |
| 4. 완료 | "비밀번호가 변경되었습니다" + "로그인하기" 버튼 |

**에러 상태:**
- 미가입 이메일 → "등록된 이메일이 없습니다"
- 토큰 만료/사용됨 → "유효하지 않은 링크입니다. 재발송해 주세요"

---

### 회원탈퇴 (`/mypage/withdraw`)

**진입 조건:** 로그인, 일반 회원(`mb_role = 'user'`)
**레이아웃:** 마이페이지 내 단일 페이지

| 요소 | 설명 |
|------|------|
| 탈퇴 안내 문구 | 탈퇴 후 데이터 처리 방침 (30일 후 삭제) |
| 비밀번호 입력 | 소셜 전용 계정은 이메일 인증으로 대체 |
| "탈퇴하기" 버튼 | 확인 모달 → 최종 처리 |

**진행 불가 상태:**
- 환불 처리 중인 주문 존재 → "환불 처리 완료 후 탈퇴 가능합니다" 안내, 버튼 비활성

---

### 관리자: 회원 목록 (`/admin/members`)

**진입 조건:** `mb_role = 'admin'`

| 요소 | 설명 |
|------|------|
| 검색 | mb_id / mb_name / mb_email LIKE |
| 상태 필터 | 전체 / 정상(active) / 휴면(dormant) / 탈퇴(withdrawn) |
| 가입 유형 필터 | 전체 / 일반 / 카카오 / 네이버 |
| 목록 컬럼 | 아이디, 이름, 이메일, 가입유형, 수강 수, 총 결제액, 가입일, 상태 |
| 상태 변경 | 행 내 드롭다운 → `POST /admin/members/{idx}/status` |
| 페이지네이션 | 20개/페이지 |

---

### 관리자: 회원 상세 (`/admin/members/{member_idx}`)

| 섹션 | 표시 내용 |
|------|-----------|
| 기본 정보 | mb_id, mb_name, mb_email, mb_phone, 가입일, 탈퇴일 |
| 소셜 연동 | signup_type + 카카오/네이버 연동 여부 |
| 수강 이력 | 강의명, 유형, 수강 시작일, 만료일, 진도율 |
| 결제 이력 | 결제일, 강의명, 금액, 상태 |

---

## 11. 관리자: 회원 목록 조회 조건 (실제 구현)

> **실제 DB 컬럼 기준** (`member_idx`, `is_active`, `signup_type`, `leave_at`)

```sql
-- MemberRepository::getAdminList() 실제 쿼리
SELECT m.*,
       (SELECT COUNT(*) FROM lc_enroll e WHERE e.member_idx = m.member_idx) AS enroll_count,
       (SELECT COALESCE(SUM(o.amount), 0) FROM lc_order o
        WHERE o.member_idx = m.member_idx AND o.status = 'paid') AS total_paid
FROM lc_member m
WHERE
  -- 검색: mb_id / mb_name / mb_email LIKE
  -- 상태 필터:
  --   active    → is_active = 1
  --   dormant   → is_active = 0 AND leave_at IS NULL
  --   withdrawn → is_active = 0 AND leave_at IS NOT NULL
  -- signup_type 필터: signup_type = ?
ORDER BY m.created_at DESC
LIMIT ? OFFSET ?
```

### 관리자 상태 변경 (`POST /admin/members/{member_idx}/status`)

| 요청 status | 처리 |
|-------------|------|
| `active` | `is_active=1, leave_at=NULL` |
| `dormant` | `is_active=0, leave_at=NULL` (정지, 데이터 보존) |
| `withdrawn` | `is_active=0, leave_at=NOW()` (탈퇴 처리) |

### 관리자 회원 상세 (`GET /admin/members/{member_idx}`)

- 기본 정보: member_idx, mb_id, mb_name, mb_email, mb_phone, 가입일, 탈퇴일
- 소셜 연동 현황: signup_type + social_id (카카오/네이버 연동 여부)
- 수강 이력: lc_enroll JOIN lc_class
- 결제 이력: lc_order JOIN lc_class
