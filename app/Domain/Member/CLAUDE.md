# Member 도메인

회원 가입·인증·소셜 로그인·정보수정·탈퇴·상태 관리를 담당하는 도메인.

---

## 관련 DB 테이블 (실제 DB 기준)

| 테이블 | 역할 |
|--------|------|
| `lc_member` | 회원 기본 정보 |
| `lc_password_reset` | 비밀번호 재설정 토큰 (30분 만료, 1회 사용) |
| `lc_admin` | 관리자 계정 (별도 테이블, 세션 키 `$_SESSION['_admin']`) |

---

## 실제 lc_member 컬럼 (기획서와 다름 — 주의)

| 컬럼 | 실제값 | 기획서 |
|------|--------|--------|
| PK | `member_idx` | `mb_idx` |
| 가입유형 | `signup_type` ENUM('email','kakao','naver') | `social_provider` |
| 상태 | `is_active` tinyint + `leave_at` datetime | `mb_status` ENUM |
| 없음 | — | `mb_role`, `mb_avatar`, `last_login_at` |

**상태 해석 규칙:**
- 정상: `is_active = 1`
- 정지(관리자 처리): `is_active = 0, leave_at IS NULL`
- 탈퇴: `is_active = 0, leave_at IS NOT NULL`

---

## 관련 Repository / Controller 파일

| 파일 | 경로 |
|------|------|
| MemberRepository | `app/Repositories/MemberRepository.php` |
| AuthController (일반) | `app/Http/Controllers/AuthController.php` |
| Admin\AuthController | `app/Http/Controllers/Admin/AuthController.php` |
| Admin\MemberController | `app/Http/Controllers/Admin/MemberController.php` |
| Auth (Core) | `app/Core/Auth.php` |

---

## 핵심 비즈니스 규칙

- **회원 상태**: `is_active` + `leave_at` 조합으로 판단 (ENUM `mb_status` 없음)
  - 정지 → active: 관리자가 `POST /admin/members/{idx}/status` (status=active)
  - 탈퇴: `leave_at = NOW()`, 30일 후 배치로 개인정보 삭제
- **권한**: 관리자는 `lc_admin` 테이블 별도 관리 (lc_member에 `mb_role` 없음)
  - 세션: `$_SESSION['_admin']` vs `$_SESSION['_member']`
- **mb_id**: 영문+숫자 4~20자, 가입 후 변경 불가
- **비밀번호**: `password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12])`, 소셜 전용 계정은 NULL
- **소셜 로그인**: `signup_type` ENUM('email','kakao','naver') + `social_id`
  - API 키: `.env` — `KAKAO_CLIENT_ID`, `KAKAO_CLIENT_SECRET`, `NAVER_CLIENT_ID`, `NAVER_CLIENT_SECRET`
  - 실제 OAuth 연동은 API 키 수령 후 구현 예정
  - 자동 `mb_id` 생성: `{provider}_{socialId[:10]}`, 충돌 시 `_{random4}` 추가
- **SMS 인증**: 동일 번호 1일 5회 제한, 3분 유효, `POST /api/member/send-sms` / `verify-sms`
- **탈퇴 조건**: 비밀번호 확인 + 환불 처리 중(`refund_req`) 주문 없음 확인 필수
- **휴면 전환**: 매일 새벽 3시 배치 — `last_login_at` 기준 (현재 DB에 컬럼 없음, 배치 구현 시 추가 필요)

---

## 참조 문서

- `docs/20_db.md` — lc_member 실제 스키마 (기획서와 차이 명시)
- `docs/30_features/member.md` — 회원 관리 전체 명세
- `docs/10_routes.md` — `/login`, `/register`, `/auth/*`, `/mypage/*`, `/admin/members/*` 라우트
