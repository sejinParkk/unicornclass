# Enroll 도메인

강의 수강 신청(무료/유료), 수강 기간 관리, 진도 추적, 오픈채팅 연동을 담당하는 도메인.

---

## 관련 DB 테이블

| 테이블 | 역할 |
|--------|------|
| `lc_enroll` | 수강 신청 (mb_idx, class_idx, order_idx, type, expire_at, is_active) |
| `lc_progress` | 수강 진도 — 챕터별 1행 구조 (`is_complete`, `completed_at`) |

---

## 관련 Repository / Controller 파일

| 파일 | 경로 |
|------|------|
| (EnrollRepository) | 미구현 — `app/Repositories/EnrollRepository.php` 예정 |
| (EnrollService) | 미구현 — `app/Services/EnrollService.php` 예정 |

> 현재 Enroll 전용 Repository/Service는 미구현. 명세는 `docs/30_features/enroll.md` 참조.

---

## 핵심 비즈니스 규칙

- **무료강의 수강**: `expire_at = NULL` (무제한), `order_idx = NULL`, Google Sheets API 호출 (실패해도 롤백 없음)
- **프리미엄 수강**: `expire_at = paid_at + duration_days`, `order_idx` 연결
- **중복 신청 방지**: `UNIQUE KEY uq_member_class (mb_idx, class_idx)` — `enrollRepo->exists()` 사전 확인
- **수강 상태 판단** (`getEnrollStatus()`):
  - `inactive`: `is_active = 0` (환불·탈퇴)
  - `active`: 무료 또는 `expire_at > NOW()`
  - `expired_extendable`: 만료 + 강의 판매 중
  - `expired_closed`: 만료 + 판매 종료
- **기간 연장**: 재구매 시 새 `lc_order` 생성 + 기존 `lc_enroll.expire_at` 갱신 (새 enroll 생성 아님)
- **진도 구조 주의**: `lc_progress`는 챕터별 개별 행 (`chapter_idx` + `is_complete`). 기획서의 `current_ep`, `total_ep`, `rate`, `completed_chapters` 컬럼은 **실제 DB에 없음**
- **진도 업데이트**: `POST /api/progress/update` — 수강 권한(`is_active=1`, `expire_at` 유효) 확인 후 처리
- **진도율 계산**: `ROUND(완료챕터수 / total_episodes * 100)`, 80% 이상 시 수료 조건
- **오픈채팅 URL**: 수강 신청 시 `lc_class.kakao_url` → `lc_enroll.kakao_url` 복사, active 상태일 때만 노출
- **탈퇴 시**: `lc_enroll.is_active = 0` 전체 비활성화, 수강 이력은 법령상 5년 보관
- **환불 후**: `lc_enroll.is_active = 0`, `lc_progress`는 삭제하지 않고 보존

---

## 참조 문서

- `docs/20_db.md` — lc_enroll, lc_progress 스키마
- `docs/30_features/enroll.md` — 수강/결제 명세 전체 (상태 판단 로직, 기간 연장, 수료증 포함)
- `docs/30_features/class.md` — 강의 유형 분기, 수강 조건 체크 규칙
- `docs/10_routes.md` — `/mypage/my-class`, `/api/progress/*` 라우트
