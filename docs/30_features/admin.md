# 관리자 기능 명세

---

## 1. 관리자 접근 구조

| 항목 | 내용 |
|------|------|
| URL prefix | `/admin/*` |
| 접근 조건 | `lc_member.mb_role = 'admin'` |
| 미인가 접근 | 403 응답 |
| 세션 | 일반 회원 세션과 동일, role 체크로 구분 |

### 관리자 미들웨어

```php
// Core/Auth.php
public static function requireAdmin(): void
{
    self::requireLogin();
    if (($_SESSION['mb_role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('접근 권한이 없습니다.');
    }
}
```

---

## 2. 관리자 메뉴 구성

| 메뉴 | URL | 설명 |
|------|-----|------|
| 대시보드 | `/admin` | 주요 통계 요약 |
| 강의 관리 | `/admin/classes` | 강의 목록/등록/수정/삭제 |
| 강사 관리 | `/admin/instructors` | 강사 목록/등록/수정 |
| 강사 지원 | `/admin/instructor-apply` | 지원서 검토/승인/거절 |
| 회원 관리 | `/admin/members` | 회원 목록/상태 변경 |
| 결제 관리 | `/admin/orders` | 주문 목록/환불 처리 |
| FAQ 관리 | `/admin/faqs` | FAQ 등록/수정/삭제 |
| 공지사항 | `/admin/notices` | 공지사항 관리 |
| 1:1 문의 | `/admin/contacts` | 문의 답변 |
| 검색 로그 | `/admin/search-logs` | 검색 키워드 분석 |
| 오픈채팅 통계 | `/admin/openchat-logs` | 강의별 입장 클릭 수 |
| 사이트 설정 | `/admin/settings` | 사이트 기본 정보·로고·SNS |
| 약관 관리 | `/admin/terms` | 이용약관·개인정보처리방침 |
| 관리자 프로필 | `/admin/profile` | 이름·이메일·비밀번호 변경 |

---

## 3. 대시보드

### 표시 지표

| 지표 | 쿼리 |
|------|------|
| 오늘 신규 회원 | `COUNT(*) WHERE DATE(created_at) = CURDATE()` |
| 오늘 결제 건수 | `COUNT(*) WHERE payment_status='paid' AND DATE(paid_at) = CURDATE()` |
| 오늘 결제 금액 | `SUM(amount) WHERE payment_status='paid' AND DATE(paid_at) = CURDATE()` |
| 전체 수강자 수 | `COUNT(DISTINCT mb_idx) FROM lc_enroll` |
| 미답변 1:1 문의 | `COUNT(*) FROM lc_contact WHERE status='waiting'` |
| 강사 지원 대기 | `COUNT(*) FROM lc_instructor_apply WHERE status='pending'` |

---

## 4. 강의 관리 (`/admin/classes`)

### 목록 조회

- 비활성 강의 포함 전체 노출
- 필터: `is_active`, `class_type`, `category`
- 정렬: `created_at DESC`
- 컬럼: 제목, 강사, 유형, 카테고리, 수강자 수, 후기 수, 판매 상태, 수정일

### 강의 등록/수정 필드

| 필드 | 설명 |
|------|------|
| 제목 | 필수 |
| 강사 | FK 선택 (lc_instructor) |
| 유형 | free / premium |
| 카테고리 | 드롭다운 |
| 썸네일 | 파일 업로드 (jpg/png/webp, 최대 5MB) |
| 가격 / 할인가 | premium만 |
| 할부 가능 개월 | 예: `2,3,6,12` |
| 수강 기간(일) | 기본 180 |
| 오픈채팅 URL | 수강자에게 노출될 카카오 링크 |
| 판매 종료일 | 공란=무기한 |
| 강의 소개 | HTML 에디터 |
| is_active | 0=비활성, 1=활성 |

### 챕터 관리

- 강의 수정 페이지 하단에 챕터 CRUD UI
- 드래그&드롭 `sort_order` 조정
- Vimeo ID 입력 + 재생 시간 입력

### 강의 삭제 정책

- 수강자 존재 시 물리 삭제 금지 → `is_active = 0` 처리
- 수강자 없을 때만 물리 삭제 가능

---

## 5. 강사 관리 (`/admin/instructors`)

### 목록

- 전체 강사 (활성/비활성)
- 정렬: `sort_order ASC`

### 강사 등록/수정 필드

| 필드 |
|------|
| 이름 (필수) |
| 사진 업로드 |
| 카테고리 (전문 분야) |
| 소개 문구 1, 2 |
| SNS: 인스타그램, 유튜브, 블로그, 이메일 |
| 소개 불릿 항목 (lc_instructor_intro, 순서 조정) |
| 경력 불릿 항목 (lc_instructor_career, 순서 조정) |
| sort_order, is_active |

---

## 6. 강사 지원 관리 (`/admin/instructor-apply`)

### 목록

| 컬럼 | 내용 |
|------|------|
| 지원자명, 이메일, 연락처 | 기본정보 |
| 강의 분야, 경력 | 요약 |
| 포트폴리오 파일 수 | 다운로드 링크 |
| 상태 | pending / approved / rejected |
| 지원일 | |

### 상태 처리

| 액션 | 처리 |
|------|------|
| 승인 | `status = 'approved'` + `lc_instructor` 자동 생성 (선택) |
| 거절 | `status = 'rejected'` |
| 이메일 발송 | 승인/거절 결과 이메일 발송 (추후 구현) |

---

## 7. 결제/환불 관리 (`/admin/orders`)

`OrderController` + `OrderRepository`. 실제 DB `lc_order` 기준.

### 목록 필터

| 필터 | 내용 |
|------|------|
| q | 회원 ID·이름·이메일·강의명 LIKE |
| status | paid / refund_req / refunded / free |
| date_from ~ date_to | paid_at 날짜 범위 |

### 상태 흐름 (실제 DB ENUM)

```
paid → refund_req (회원 환불 신청)
refund_req → refunded  (관리자 승인: Toss API 호출 + lc_enroll.expire_at = NOW())
refund_req → paid      (관리자 거절: 원복)
```

### 환불 승인 처리 (`POST /admin/orders/{idx}/refund/approve`)

1. `TOSS_SECRET_KEY` 설정 시 Toss Payments 환불 API 호출
2. `lc_order.status = 'refunded'`, `refunded_at = NOW()`
3. `lc_enroll.expire_at = NOW()` (수강 만료 처리)

### 환불 거절 처리 (`POST /admin/orders/{idx}/refund/reject`)

- `lc_order.status = 'paid'` (원복)

---

## 8. FAQ 관리 (`/admin/faqs`)

`FaqController` + `FaqRepository`. 페이지 단일 뷰에 목록+인라인 등록/수정 폼.

| 기능 | 처리 |
|------|------|
| 목록 | 카테고리별 탭 필터, sort_order ASC |
| 등록 | `POST /admin/faqs` — 카테고리, 질문, 답변, sort_order, is_active |
| 수정 | `POST /admin/faqs/{faq_idx}` — 동일 필드 |
| 삭제 | `POST /admin/faqs/{faq_idx}/delete` — 물리 삭제 |
| 카테고리 | all / payment / lecture / account / tech / etc (VARCHAR, 실제 DB) |

---

## 9. 공지사항 관리 (`/admin/notices`)

`NoticeController` + `NoticeRepository`.

| 기능 | 처리 |
|------|------|
| 목록 | `GET /admin/notices` — 제목 검색, is_active 필터, is_pinned DESC + created_at DESC |
| 등록 폼 | `GET /admin/notices/create` |
| 등록 처리 | `POST /admin/notices` |
| 수정 폼 | `GET /admin/notices/{idx}/edit` |
| 수정 처리 | `POST /admin/notices/{idx}` |
| 삭제(비활성) | `POST /admin/notices/{idx}` with `_delete=1` → `is_active = 0` |

> 실제 DB 조회수 컬럼: `views` (기획서의 `view_count` 아님).

---

## 10. 1:1 문의 답변 (`/admin/contacts`)

`ContactController` + `ContactRepository`. 실제 테이블: `lc_qna`.

| 상태 | 처리 |
|------|------|
| `wait` | 미답변, 목록에서 우선 정렬 |
| `done` | 답변 완료 |

### 답변 처리 (`POST /admin/contacts/{idx}/answer`)

```sql
UPDATE lc_qna
SET answer = ?, status = 'done', answered_by = ?, answered_at = NOW()
WHERE qna_idx = ?
```

> 기획서의 `lc_contact` → 실제 `lc_qna`, `status` 값: 'wait'/'done' (기획서의 'waiting'/'answered' 아님).
> 목록에서 `contact_idx` 파라미터 → 실제 `qna_idx`.

---

## 11. 통계 / 로그 관리

`StatsController` + `StatsRepository`.

### 검색 로그 (`GET /admin/search-logs`)

- 날짜 범위 필터 (기본: 최근 30일)
- 인기 검색어 TOP 50 (검색 횟수, 결과 없음 건수 포함)
- 날짜별 검색 건수 막대 차트
- 총 검색 건수 KPI 카드

### 오픈채팅 클릭 통계 (`GET /admin/openchat-logs`)

- 날짜 범위 필터 (기본: 최근 30일)
- 강의별 오픈채팅 클릭 수 순위 (유형 표시)
- 날짜별 클릭 수 막대 차트
- 총 클릭 수 KPI 카드

클릭 기록은 프론트 `/api/openchat/log` POST → `lc_openchat_log` INSERT.

---

## 12. 후기 관리

- 강의별 후기 목록 조회
- 비적절 후기 `is_active = 0` 처리 (숨김)
- 관리자 직접 후기 작성 가능 (메인 페이지 슬라이더 노출용)

```
lc_review.mb_idx = 관리자 mb_idx
is_admin_written = 1 (별도 컬럼 추가 고려)
```

---

## 13. 사이트 설정 (`/admin/settings`)

`lc_site_config` 테이블 키-값 구조로 관리. `SettingController` + `SettingRepository`.

### 관리 항목

| 섹션 | 항목 |
|------|------|
| 기본 정보 | site_name, company_name, ceo_name, business_no, phone, email, address, footer_copy |
| 로고/파비콘 | logo (jpg/png/webp/svg, max 2MB), favicon (ico/png/svg, max 2MB) |
| SNS 링크 | sns_instagram, sns_youtube, sns_facebook, sns_blog |

- 로고·파비콘은 `FileUploader::uploadSiteImage()` → `storage/uploads/site/{uuid}.{ext}` 저장
- 서빙: `GET /uploads/site/{filename}` 라우트

---

## 14. 약관 관리 (`/admin/terms`)

`lc_terms` 테이블 `type` ENUM('terms','privacy') 으로 구분. `TermsController` + `SettingRepository`.

| 탭 | POST URL | type 값 |
|----|----------|---------|
| 이용약관 | `/admin/terms/terms` | `terms` |
| 개인정보처리방침 | `/admin/terms/privacy` | `privacy` |

- 각 약관은 title + content(HTML 직접 입력) 필드
- 최종 수정일 표시 (`updated_at`)
- 저장 후 `?saved=terms` 또는 `?saved=privacy` 리다이렉트

공개 페이지: `GET /supports/terms`, `GET /supports/privacy` (별도 뷰 파일에서 DB 조회)

---

## 15. 관리자 프로필 (`/admin/profile`)

`ProfileController` + `AdminRepository`. 별도 `lc_admin` 테이블 기반.

### 기본 정보 수정 (`POST /admin/profile`)

- 수정 가능: `name`, `email`
- `login_id`는 읽기 전용 (변경 불가)
- 저장 시 `$_SESSION['_admin']['name']` 즉시 갱신

### 비밀번호 변경 (`POST /admin/profile/password`)

- 입력: `current_password`, `new_password`, `confirm_password`
- 검증: 현재 비밀번호 `password_verify()`, 새 비밀번호 8자 이상, 확인 일치
- 저장: `password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12])`
