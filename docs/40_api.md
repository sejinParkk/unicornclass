# API 엔드포인트 명세

> 모든 API는 JSON 응답. 인증 필요 API는 세션 기반.
> 공통 에러 형식: `{ "error": true, "message": "..." }`

---

## 공통 응답 형식

```json
// 성공
{ "success": true, "data": { ... } }

// 실패
{ "success": false, "message": "에러 메시지" }
```

---

## 1. 인증 (Auth)

### `POST /api/auth/login`
로그인

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `mb_id` | string | O |
| `mb_password` | string | O |
| `csrf_token` | string | O |

**응답:**
```json
{ "success": true, "data": { "mb_id": "hong123", "mb_name": "홍길동", "mb_role": "user" } }
```

**에러:**
- `401` 아이디/비밀번호 불일치
- `403` 탈퇴 또는 휴면 계정

---

### `POST /api/auth/logout`
로그아웃 (세션 파기)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `csrf_token` | string | O |

---

### `POST /api/member/check-id`
아이디 중복 확인

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `mb_id` | string | O |

**응답:**
```json
{ "success": true, "data": { "available": true } }
```

---

### `POST /api/member/send-sms`
SMS 인증번호 발송

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `phone` | string | O |
| `csrf_token` | string | O |

**에러:** `429` 1일 5회 초과

---

### `POST /api/member/verify-sms`
SMS 인증번호 확인

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `phone` | string | O |
| `code` | string | O |
| `csrf_token` | string | O |

**응답:**
```json
{ "success": true, "data": { "mb_id": "hong***" } }
```

---

## 2. 강의 (Classes)

### `GET /api/classes`
강의 목록 조회

| 파라미터 | 타입 | 필수 | 기본값 |
|----------|------|------|--------|
| `type` | `free`\|`premium` | X | 전체 |
| `category` | string | X | 전체 |
| `page` | int | X | 1 |
| `limit` | int | X | 9 |

**응답:**
```json
{
  "success": true,
  "data": {
    "classes": [ { "class_idx": 1, "title": "...", "instructor_name": "...", ... } ],
    "total": 42,
    "page": 1,
    "limit": 9
  }
}
```

---

### `GET /api/classes/{class_idx}`
강의 상세 조회

**응답:** 강의 전체 정보 + 챕터 목록 + 강사 정보 + 수강 여부(로그인 시)

---

### `POST /api/classes/{class_idx}/like`
좋아요 토글 (로그인 필요)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `csrf_token` | string | O |

**응답:**
```json
{ "success": true, "data": { "liked": true, "like_count": 128 } }
```

---

### `POST /api/classes/{class_idx}/wish`
찜 토글 (로그인 필요)

**응답:**
```json
{ "success": true, "data": { "wished": true } }
```

---

### `POST /api/enroll/free`
무료강의 수강 신청 (로그인 필요)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `class_idx` | int | O |
| `csrf_token` | string | O |

**에러:**
- `409` 이미 신청한 강의
- `400` 무료강의가 아님

---

## 3. 검색 (Search)

### `GET /api/search`
통합 검색

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `q` | string | O |

**응답:**
```json
{
  "success": true,
  "data": {
    "keyword": "홍길동",
    "total": 4,
    "classes": [ { "class_idx": 1, "title": "<mark>홍길동</mark>의...", ... } ],
    "instructors": [ { "instructor_idx": 3, "name": "<mark>홍길동</mark>", ... } ]
  }
}
```

**에러:** `400` q 파라미터 없거나 200자 초과

---

### `GET /api/search/suggest`
검색 자동완성 (추후 구현)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `q` | string | O |

---

## 4. 결제 (Checkout)

### `POST /api/checkout/prepare`
주문 생성 (결제 시작 전)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `class_idx` | int | O |
| `csrf_token` | string | O |

**응답:**
```json
{
  "success": true,
  "data": {
    "order_no": "UC20260409123456abcd",
    "amount": 198000,
    "order_name": "스마트스토어 월 1000만원 달성 비법"
  }
}
```

---

### `GET /checkout/success`
Toss Payments 결제 성공 콜백

| 쿼리 파라미터 | 설명 |
|--------------|------|
| `paymentKey` | Toss 결제 키 |
| `orderId` | 주문번호 |
| `amount` | 결제 금액 |

> 서버에서 금액 검증 후 Toss 승인 API 호출 → 완료 페이지 렌더링

---

### `GET /checkout/fail`
Toss Payments 결제 실패 콜백

| 쿼리 파라미터 | 설명 |
|--------------|------|
| `code` | 에러 코드 |
| `message` | 에러 메시지 |
| `orderId` | 주문번호 |

---

### `POST /api/mypage/orders/{order_idx}/refund`
환불 신청 (로그인 필요, 본인 주문만)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `reason` | string | O |
| `csrf_token` | string | O |

**응답:**
```json
{ "success": true, "data": { "refund_type": "full", "refund_amount": 198000 } }
```

**에러:**
- `403` 타인 주문 접근
- `409` 이미 환불 신청됨

---

## 5. 진도 (Progress)

### `POST /api/progress/update`
챕터 시청 완료 기록 (로그인 필요, 수강자만)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `enroll_idx` | int | O |
| `chapter_idx` | int | O |
| `csrf_token` | string | O |

**응답:**
```json
{
  "success": true,
  "data": {
    "current_ep": 12,
    "total_ep": 28,
    "rate": 42,
    "is_certified": false
  }
}
```

---

## 6. 마이페이지 (Mypage)

### `GET /api/mypage/classes`
나의 수강 목록

| 파라미터 | 타입 | 기본값 |
|----------|------|--------|
| `type` | `all`\|`free`\|`premium` | `all` |
| `page` | int | 1 |
| `limit` | int | 10 |

---

### `GET /api/mypage/orders`
결제 내역 목록

| 파라미터 | 타입 | 기본값 |
|----------|------|--------|
| `page` | int | 1 |
| `limit` | int | 10 |

---

### `GET /api/mypage/orders/{order_idx}`
결제 내역 상세

---

### `GET /api/mypage/wishlist`
찜 목록

---

### `POST /api/mypage/reviews`
후기 작성 (수강자만)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `class_idx` | int | O |
| `rating` | int(1~5) | O |
| `content` | string | O |
| `csrf_token` | string | O |

**에러:** `403` 수강자가 아님 또는 미결제 강의

---

### `PUT /api/mypage/reviews/{review_idx}`
후기 수정 (본인만)

---

### `POST /api/mypage/profile`
정보수정

| 파라미터 | 타입 |
|----------|------|
| `mb_name` | string |
| `mb_email` | string |
| `mb_phone` | string |
| `mb_password_new` | string (선택) |
| `mb_password_current` | string (비번 변경 시 필수) |
| `csrf_token` | string |

---

## 7. 오픈채팅 (OpenChat)

### `POST /api/openchat/log`
오픈채팅 클릭 기록 (로그인 필요)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `class_idx` | int | O |
| `csrf_token` | string | O |

---

## 8. 강사 지원 (Instructor Apply)

### `POST /api/instructor/apply`
강사 지원서 제출

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `name` | string | O |
| `phone` | string | O |
| `email` | string | O |
| `teach_field` | string | O |
| `teach_exp` | string | O |
| `bio` | string | O |
| `curriculum` | string | O |
| `teach_format` | string | O |
| `sns_*` | string | X |
| `portfolio_link` | string | X |
| `portfolio_files[]` | file | X (최대 3개) |
| `agree_privacy` | int(1) | O |
| `csrf_token` | string | O |

**에러:** `422` 유효성 검사 실패

---

## 9. 고객센터 (Support)

### `GET /api/faqs`
FAQ 목록

| 파라미터 | 타입 | 기본값 |
|----------|------|--------|
| `category` | `all`\|`payment`\|`lecture`\|`account`\|`tech` | `all` |

**응답:**
```json
{
  "success": true,
  "data": [
    { "faq_idx": 1, "category": "payment", "question": "...", "answer": "..." }
  ]
}
```

---

### `POST /api/supports/contact`
1:1 문의 작성 (로그인 필요)

| 파라미터 | 타입 | 필수 |
|----------|------|------|
| `category` | string | O |
| `title` | string | O |
| `content` | string | O |
| `csrf_token` | string | O |

---

## 10. 관리자 API (`/admin/api/`)

> 모든 관리자 API는 `mb_role = 'admin'` 필수.

| 엔드포인트 | 메서드 | 설명 |
|------------|--------|------|
| `/admin/api/classes` | GET | 강의 목록 (비활성 포함) |
| `/admin/api/classes` | POST | 강의 등록 |
| `/admin/api/classes/{idx}` | PUT | 강의 수정 |
| `/admin/api/classes/{idx}` | DELETE | 강의 삭제/비활성화 |
| `/admin/api/chapters` | POST | 챕터 등록 |
| `/admin/api/chapters/{idx}` | PUT | 챕터 수정 |
| `/admin/api/chapters/{idx}` | DELETE | 챕터 삭제 |
| `/admin/api/members` | GET | 회원 목록 |
| `/admin/api/members/{idx}/status` | PUT | 회원 상태 변경 |
| `/admin/api/orders` | GET | 주문 목록 |
| `/admin/api/orders/{idx}/refund/approve` | POST | 환불 승인 |
| `/admin/api/orders/{idx}/refund/reject` | POST | 환불 거절 |
| `/admin/api/instructor-apply` | GET | 강사 지원 목록 |
| `/admin/api/instructor-apply/{idx}/approve` | POST | 지원 승인 |
| `/admin/api/instructor-apply/{idx}/reject` | POST | 지원 거절 |
| `/admin/api/faqs` | POST | FAQ 등록 |
| `/admin/api/faqs/{idx}` | PUT | FAQ 수정 |
| `/admin/api/faqs/{idx}` | DELETE | FAQ 삭제 |
| `/admin/api/notices` | POST | 공지사항 등록 |
| `/admin/api/contacts/{idx}/answer` | POST | 1:1 문의 답변 |
| `/admin/api/stats/dashboard` | GET | 대시보드 통계 |

---

## 11. HTTP 상태코드 규칙

| 코드 | 사용 상황 |
|------|----------|
| `200` | 성공 |
| `201` | 리소스 생성 성공 |
| `400` | 요청 파라미터 오류 |
| `401` | 미인증 (로그인 필요) |
| `403` | 권한 없음 |
| `404` | 리소스 없음 |
| `409` | 중복 (이미 존재) |
| `422` | 유효성 검사 실패 |
| `429` | 요청 횟수 초과 |
| `500` | 서버 에러 |
