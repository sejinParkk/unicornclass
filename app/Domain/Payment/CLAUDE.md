# Payment 도메인

Toss Payments 연동을 통한 프리미엄 강의 결제·환불 처리를 담당하는 도메인.

---

## 관련 DB 테이블

| 테이블 | 역할 |
|--------|------|
| `lc_order` | 주문/결제 (order_no UNIQUE, payment_status, pg_tid, amount) |

---

## 관련 Repository / Controller 파일

| 파일 | 경로 |
|------|------|
| (OrderRepository) | 미구현 — `app/Repositories/OrderRepository.php` 예정 |
| (PaymentService) | 미구현 — `app/Services/PaymentService.php` 예정 |

> 현재 결제 관련 Controller/Repository는 아직 구현되지 않음. 명세는 `docs/30_features/payment.md` 참조.

---

## 핵심 비즈니스 규칙

- **PG사**: Toss Payments, SDK v1 (`https://js.tosspayments.com/v1`)
- **환경변수**: `TOSS_CLIENT_KEY`(프론트용), `TOSS_SECRET_KEY`(서버용, 절대 노출 금지)
- **주문번호 채번**: `'UC' + date('YmdHis') + random(4)` — `lc_order.order_no` UNIQUE 제약
- **결제 상태 흐름**: `pending` → `paid` → `refund_req` → `refunded` (또는 거절 시 → `paid` 복원)
  - `failed`: 결제 실패 (동일 orderId 재시도 가능)
- **금액 위변조 방지**: 서버에서 DB 주문금액과 Toss 콜백 amount 파라미터 반드시 비교
- **중복 결제 방지**: orderId UNIQUE + `payment_status === 'paid'` 이면 재처리 거부
- **환불 계산**:
  - 전액 환불: 결제 후 7일 이내 AND 진도율 33% 미만
  - 비례 환불: `floor(결제금액 / 전체수강일 * 잔여수강일)`
- **환불 처리 흐름**: 사용자 신청 → `lc_order.payment_status = 'refund_req'` → 관리자 승인 → Toss 환불 API 호출 → `refunded` + `lc_enroll.is_active = 0`
- **환불 후 진도**: `lc_progress` 데이터는 삭제하지 않고 보존
- **할부**: 강의별 허용 개월 설정, 무이자 여부는 Toss/카드사가 자동 처리
- **CSRF**: 결제 요청 폼에 CSRF 토큰 필수
- **환불 권한**: 환불 신청 시 반드시 `mb_idx` 본인 확인

---

## 참조 문서

- `docs/20_db.md` — lc_order 스키마
- `docs/30_features/payment.md` — Toss Payments 연동 전체 명세 (플로우, 환불 계산식 포함)
- `docs/10_routes.md` — `/checkout/*`, `/admin/orders/*` 라우트
