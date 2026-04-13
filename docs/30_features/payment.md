# Toss Payments 결제 기능 명세

---

## 1. 개요

| 항목 | 내용 |
|------|------|
| PG사 | Toss Payments |
| SDK | Toss Payments JS SDK v1 (`https://js.tosspayments.com/v1`) |
| 결제 수단 | 신용카드, 체크카드, 계좌이체, 간편결제 (카카오페이, 네이버페이 등) |
| 할부 | 카드 결제 시 할부 개월 선택 가능 (관리자 설정) |
| 통화 | KRW |
| 환경변수 | `TOSS_CLIENT_KEY`, `TOSS_SECRET_KEY` |

---

## 2. 결제 플로우 (5단계)

```
[1 주문 확인] → [2 결제 수단 선택] → [3 정보 입력] → [4 Toss 승인] → [5 완료]
```

### Step 1 — 주문 확인
- 강의명, 수강 방식, 가격(정가/할인가), 수강 기간 표시
- 주문번호 사전 채번: `'UC' + date('YmdHis') + random(4)`

### Step 2 — 결제 수단 선택

| 수단 | 처리 |
|------|------|
| 신용카드 | 카드번호·유효기간·CVC 입력 또는 Toss UI |
| 체크카드 | 신용카드와 동일 흐름 |
| 계좌이체 | 은행 선택 → Toss 가상계좌 또는 실시간 이체 |
| 간편결제 | Toss Payments 팝업 UI |

### Step 3 — 정보 입력
- 결제자 이름, 이메일 (자동 입력, 수정 가능)
- 신용/체크카드: 할부 개월 선택 (관리자 설정 범위 내)
- 무이자 여부: 카드사마다 상이, 안내 문구 고정 표시

### Step 4 — Toss Payments 승인 요청

```js
// 프론트엔드: Toss JS SDK 호출
const tossPayments = TossPayments(clientKey);
tossPayments.requestPayment('카드', {
    amount: 198000,
    orderId: 'UC20260409123456abcd',
    orderName: '스마트스토어 월 1000만원 달성 비법 완전정복',
    customerName: '홍길동',
    successUrl: 'https://unicornclass.com/checkout/success',
    failUrl: 'https://unicornclass.com/checkout/fail',
    // 할부 설정 (관리자 설정값 전달)
    card: { installmentPlan: [{ months: 2 }, { months: 3 }, { months: 6 }] }
});
```

### Step 5 — 서버 측 최종 승인

```
GET /checkout/success?paymentKey=...&orderId=...&amount=...
→ 서버: amount 위변조 검증 (DB 주문금액과 비교)
→ Toss Payments 승인 API 호출 (POST /v1/payments/confirm)
→ 승인 성공 → lc_order 업데이트 + lc_enroll INSERT
→ 결제 완료 페이지 렌더링
```

---

## 3. 서버 승인 API 연동

### 승인 요청

```
POST https://api.tosspayments.com/v1/payments/confirm
Authorization: Basic {Base64(secretKey:)}
Content-Type: application/json

{
  "paymentKey": "...",
  "orderId": "UC20260409123456abcd",
  "amount": 198000
}
```

### 승인 성공 처리

```php
// PaymentService::confirm()
public function confirm(string $paymentKey, string $orderId, int $amount): void
{
    // 1. DB 주문금액과 요청금액 일치 확인 (위변조 방지)
    $order = $this->orderRepo->findByOrderNo($orderId);
    if ($order->amount !== $amount) throw new \RuntimeException('금액 불일치');

    // 2. 중복 결제 방지
    if ($order->payment_status === 'paid') throw new \RuntimeException('이미 처리된 주문');

    // 3. Toss Payments API 호출
    $response = $this->tossClient->confirm($paymentKey, $orderId, $amount);

    // 4. DB 업데이트 (트랜잭션)
    $this->pdo->beginTransaction();
    $this->orderRepo->markPaid($order->order_idx, $paymentKey, $response['approvedAt']);
    $this->enrollRepo->create($order->mb_idx, $order->class_idx, $order->order_idx);
    $this->pdo->commit();
}
```

### 승인 실패 처리

```
GET /checkout/fail?code=...&message=...&orderId=...
→ lc_order.payment_status = 'failed' 업데이트
→ 실패 사유 표시 + 재시도 버튼
→ 중복 결제 방지: 동일 orderId 재시도 가능 (status=failed일 때만)
```

---

## 4. lc_order 상태 흐름

```
pending → paid      (결제 성공)
pending → failed    (결제 실패)
paid    → refund_req (환불 신청)
refund_req → refunded (환불 완료)
refund_req → paid    (환불 거절 → 원복)
```

### DB status 값 정의

| status | 의미 |
|--------|------|
| `pending` | 결제 진행 중 (미완료) |
| `paid` | 결제 완료 |
| `failed` | 결제 실패 |
| `refund_req` | 환불 신청 접수 |
| `refunded` | 환불 완료 |

---

## 5. 환불 처리

### 환불 가능 조건 계산

```php
public function calcRefund(Order $order, Enroll $enroll, Progress $progress): array
{
    $daysSincePaid  = (time() - strtotime($order->paid_at)) / 86400;
    $isWithin7Days  = $daysSincePaid <= 7;
    $isUnder33Pct   = $progress->rate < 33;

    if ($isWithin7Days && $isUnder33Pct) {
        return ['type' => 'full', 'amount' => $order->amount];
    }

    // 비례 환불: (결제금액 ÷ 전체수강일) × 잔여수강일
    $totalDays    = (strtotime($enroll->expire_at) - strtotime($order->paid_at)) / 86400;
    $remainDays   = max(0, (strtotime($enroll->expire_at) - time()) / 86400);
    $refundAmount = (int)floor($order->amount / $totalDays * $remainDays);

    return ['type' => 'partial', 'amount' => $refundAmount];
}
```

### 환불 API 호출

```
POST https://api.tosspayments.com/v1/payments/{paymentKey}/cancel
Authorization: Basic {Base64(secretKey:)}

{
  "cancelReason": "사용자 환불 요청",
  "cancelAmount": 163900
}
```

### 환불 처리 흐름

```
1. 사용자: 마이페이지 > 결제내역 > 환불 신청
2. 환불 유형 자동 판단 (전액/비례) + 예상 금액 표시
3. 환불 사유 선택 필수
4. POST /api/mypage/orders/{idx}/refund
   → lc_refund INSERT
   → lc_order.payment_status = 'refund_req'
5. 관리자 검토 후 승인
   → Toss Payments 환불 API 호출
   → lc_order.payment_status = 'refunded'
   → lc_enroll 비활성화 (is_active = 0)
6. 처리 완료: 영업일 기준 3~5일 내 원결제 수단 환불
```

### 환불 사유 선택지

- 실수로 결제했어요
- 강의 내용이 기대와 달라요
- 수강 시간이 없어요
- 중복 결제했어요
- 기타 (직접 입력)

---

## 6. 할부 설정 (관리자)

- 강의 등록/수정 시 허용 할부 개월 설정
- 무이자 여부: Toss Payments가 카드사별로 자동 표시
- 프론트에 안내 문구 고정: "무이자 여부는 카드사마다 상이합니다"
- `lc_class.installment_months` 컬럼으로 관리 (예: `"2,3,6,12"`)

---

## 7. 보안 체크리스트

| 항목 | 내용 |
|------|------|
| Secret Key 노출 방지 | 서버 사이드에서만 사용, `.env` 관리 |
| 금액 위변조 방지 | 서버에서 DB 금액과 파라미터 amount 비교 |
| 중복 결제 방지 | `orderId` UNIQUE + `payment_status` 확인 |
| CSRF | 결제 요청 폼에 CSRF 토큰 필수 |
| 환불 권한 확인 | 환불 신청 시 `mb_idx` 본인 확인 필수 |
| 웹훅 검증 | Toss 서버 IP 화이트리스트 또는 서명 검증 |

---

## 8. 무료강의 신청 플로우 (3단계)

```
[1 신청 확인] → [2 동의] → [3 신청 완료]
```

### Step 1 — 신청 확인
- 강의명, 강의 방식, 수강 안내 표시

### Step 2 — 동의
- 개인정보 수집·이용 동의 (필수)
- 마케팅 수신 동의 (필수 — 무료강의 신청의 조건)
- 미동의 시 신청 불가 안내

### Step 3 — 신청 완료

```php
// EnrollService::enrollFree()
public function enrollFree(int $mbIdx, int $classIdx): void
{
    // 중복 신청 방지
    if ($this->enrollRepo->exists($mbIdx, $classIdx)) return;

    $this->pdo->beginTransaction();
    $this->enrollRepo->createFree($mbIdx, $classIdx); // expire_at = NULL
    $this->pdo->commit();

    // Google Sheets 기록 (실패해도 롤백 안 함)
    try {
        $this->sheetsService->appendRow($classIdx, $mbIdx);
    } catch (\Exception $e) {
        error_log('Google Sheets API 실패: ' . $e->getMessage());
    }
}
```
