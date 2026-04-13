# 수강 / 결제 명세

---

## 1. 수강 신청 유형별 처리

### 1-1. 무료강의 수강 신청

```
POST /api/enroll/free
→ 로그인 확인
→ 강의 활성화 확인 (is_active = 1)
→ 중복 신청 확인 (lc_enroll 조회)
→ lc_enroll INSERT (expire_at = NULL)
→ Google Sheets API 호출 (실패해도 롤백 없음)
→ 성공: 신청 완료 화면
```

### 1-2. 프리미엄강의 결제 신청

```
POST /checkout/{class_idx} (주문 생성)
→ lc_order INSERT (status = 'pending')
→ Toss Payments JS SDK 호출 (프론트)
→ GET /checkout/success (Toss 콜백)
→ 서버 금액 검증 + Toss 승인 API
→ lc_order 업데이트 (status = 'paid')
→ lc_enroll INSERT (expire_at = paid_at + 수강기간)
→ 결제 완료 페이지
```

---

## 2. lc_enroll 레코드 상세

```sql
CREATE TABLE lc_enroll (
    enroll_idx   INT UNSIGNED  PRIMARY KEY AUTO_INCREMENT,
    mb_idx       INT UNSIGNED  NOT NULL,
    class_idx    INT UNSIGNED  NOT NULL,
    order_idx    INT UNSIGNED  NULL,           -- 무료강의는 NULL
    class_type   ENUM('free','premium') NOT NULL,
    is_active    TINYINT(1)    DEFAULT 1,      -- 환불/탈퇴 시 0으로 비활성화
    enrolled_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    expire_at    DATETIME      NULL,           -- 무료강의 NULL = 무제한
    UNIQUE KEY uq_member_class (mb_idx, class_idx)
);
```

### 수강 상태 판단 로직

```php
public function getEnrollStatus(Enroll $enroll, \stdClass $class): string
{
    if (!$enroll->is_active) return 'inactive';

    if ($enroll->class_type === 'free') return 'active'; // 무료는 항상 active

    if ($enroll->expire_at === null) return 'active';

    $now = time();
    $expireTs = strtotime($enroll->expire_at);

    if ($expireTs > $now) return 'active';           // 수강 중

    // 만료됨 - 연장 가능 여부 확인
    $saleEndTs = $class->sale_end_at ? strtotime($class->sale_end_at) : PHP_INT_MAX;

    return $saleEndTs > $now ? 'expired_extendable' : 'expired_closed';
}
```

| 반환값 | 의미 | 마이페이지 UI |
|--------|------|--------------|
| `active` | 수강 중 | 강의 보기 버튼 활성 |
| `expired_extendable` | 만료, 연장 가능 | 기간 연장 버튼 |
| `expired_closed` | 만료, 판매 종료 | 연장 불가 (회색) |
| `inactive` | 비활성 (환불됨) | 목록 미노출 |

---

## 3. 수강 기간 설정 규칙

| 강의 유형 | 기본 수강 기간 | 설정 방법 |
|----------|--------------|----------|
| 무료 | 무제한 (`expire_at = NULL`) | 고정 |
| 프리미엄 | 180일 | 관리자 강의 등록 시 `access_days` 입력 |

```php
// 수강 기간 계산
$expireAt = date('Y-m-d H:i:s', strtotime("+{$class->access_days} days"));
```

### 기간 연장 처리

```sql
-- 기간 연장: 새 결제 완료 후
UPDATE lc_enroll
SET expire_at = DATE_ADD(NOW(), INTERVAL ? DAY),
    order_idx = ?
WHERE enroll_idx = ?
```

> 재구매 시 새 lc_order 생성 + 기존 lc_enroll의 expire_at만 갱신

---

## 4. 진도 추적 (`lc_progress`)

### 테이블 구조 (확장)

```sql
CREATE TABLE lc_progress (
    progress_idx         INT UNSIGNED  PRIMARY KEY AUTO_INCREMENT,
    enroll_idx           INT UNSIGNED  NOT NULL UNIQUE,
    current_ep           INT UNSIGNED  DEFAULT 0,   -- 완료한 챕터 수
    total_ep             INT UNSIGNED  DEFAULT 0,   -- 전체 챕터 수
    rate                 TINYINT UNSIGNED DEFAULT 0, -- 수강률 0~100
    completed_chapters   TEXT          NULL,         -- 완료 챕터 idx 목록 (JSON 또는 CSV)
    is_certified         TINYINT(1)    DEFAULT 0,    -- 수료증 발급 여부
    certified_at         DATETIME      NULL,
    updated_at           DATETIME      ON UPDATE CURRENT_TIMESTAMP
);
```

### 진도 업데이트 API

```
POST /api/progress/update
Body: { enroll_idx, chapter_idx }

→ 수강 권한 확인 (enroll 본인 + expire_at 유효)
→ completed_chapters에 chapter_idx 추가 (중복 제외)
→ current_ep = COUNT(completed_chapters)
→ total_ep = COUNT(lc_class_chapter WHERE class_idx=? AND is_active=1)
→ rate = ROUND(current_ep / total_ep * 100)
→ rate >= 80 && is_certified = 0 → is_certified = 1, certified_at = NOW()
```

---

## 5. 오픈채팅 링크 처리

- 수강 확정된 회원에게만 노출 (active 상태)
- `lc_enroll.kakao_url` 컬럼 저장
  - 무료강의: 신청 완료 시 `lc_class.kakao_url` 복사
  - 프리미엄강의: 결제 완료 시 복사
- 오픈채팅 버튼 클릭 → `lc_openchat_log` INSERT (중복 기록 허용, 클릭당 1건)

```sql
INSERT INTO lc_openchat_log (mb_idx, class_idx, clicked_at)
VALUES (?, ?, NOW())
```

---

## 6. 환불 후 수강 처리

```php
// 환불 승인 시 실행
public function processRefundApproval(int $orderIdx): void
{
    $order = $this->orderRepo->find($orderIdx);

    $this->pdo->beginTransaction();

    // 1. 주문 상태 변경
    $this->orderRepo->updateStatus($orderIdx, 'refunded');

    // 2. 수강 비활성화
    $this->enrollRepo->deactivate($order->mb_idx, $order->class_idx);

    // 3. 진도 초기화 여부 (정책: 유지)
    // lc_progress는 삭제하지 않고 그대로 보존

    $this->pdo->commit();
}
```

---

## 7. 수료증 발급

| 조건 | 내용 |
|------|------|
| 발급 기준 | `lc_progress.rate >= 80` |
| 자동 발급 | 진도 업데이트 시 rate가 80 이상이 되는 순간 `is_certified = 1` |
| 발급 형식 | PDF 다운로드 |
| 접근 경로 | 마이페이지 > 나의 강의 > 수료증 발급 |
| URL | `GET /mypage/certificate/{enroll_idx}` |

```php
// 수료증 접근 권한 확인
if ($progress->is_certified !== 1) abort(403, '수료 조건을 충족하지 않았습니다.');
if ($enroll->mb_idx !== $session->mbIdx) abort(403);

// PDF 생성 후 다운로드
```

---

## 화면 명세

### 무료강의 신청 (3단계 모달)

**진입:** 강의 상세 > "무료강의 신청하기" 버튼 클릭 (로그인 필요)
**형태:** 전체 화면 오버레이 모달

| 단계 | 표시 내용 |
|------|-----------|
| 1. 신청 확인 | 강의명, 강의 방식, 수강 안내 / "다음" 버튼 |
| 2. 동의 | 개인정보 수집·이용 동의 (필수) + 마케팅 수신 동의 (필수) / 미동의 시 "다음" 비활성 |
| 3. 신청 완료 | "신청이 완료되었습니다" + "강의 바로 보기" 버튼 |

**중복 신청 시:** 모달 없이 바로 강의 플레이어로 이동

---

### 프리미엄강의 결제 (5단계)

> 상세 화면 명세는 `payment.md` 참조. 수강 등록 연동 흐름만 기재.

| 단계 | 수강 관련 처리 |
|------|--------------|
| 1. 주문 확인 | `lc_order INSERT (status='pending')` |
| 4. 결제 승인 | `lc_order UPDATE (status='paid')` + `lc_enroll INSERT` |
| 5. 완료 페이지 | 수강 시작일 / 만료일 표시 + "강의 바로 보기" 버튼 |

**결제 실패 시:** 실패 사유 표시 + "다시 시도" 버튼 (동일 orderId 재시도 가능)

---

### 수료증 발급 (`/mypage/certificate/{enroll_idx}`)

**진입 조건:** `lc_progress.rate >= 80` AND 본인 수강 건
**형태:** PDF 다운로드 (브라우저 새탭 또는 직접 다운로드)

| 요소 | 내용 |
|------|------|
| 접근 불가 | rate < 80 → 403 "수료 조건을 충족하지 않았습니다" |
| 타인 접근 | enroll.mb_idx ≠ 세션 → 403 |
| 마이페이지 진입점 | 나의 강의 카드 > "수료증 발급" 버튼 (rate >= 80일 때만 노출) |

---

## 8. 탈퇴 시 수강 처리

```
회원탈퇴 처리 시:
1. lc_member.mb_status = 'withdrawn'
2. 환불 처리 중인 lc_refund 존재 시 탈퇴 불가 (안내: "환불 처리 완료 후 탈퇴 가능")
3. lc_enroll.is_active = 0 (전체 비활성화)
4. 개인정보: 30일 후 일괄 삭제 배치 처리
5. 수강 이력, 결제 기록: 법령에 따라 5년 보관 (mb_idx 연결 상태)
```
