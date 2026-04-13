# Instructor 도메인

강사 프로필 관리, 강사 지원(apply) 접수·심사를 담당하는 도메인.

---

## 관련 DB 테이블

| 테이블 | 역할 |
|--------|------|
| `lc_instructor` | 강사 프로필 (name, field, photo, intro, career, sns_*) |
| `lc_instructor_category` | 강사 카테고리 |
| `lc_instructor_apply` | 강사 지원서 (status: pending/approved/rejected, reject_reason) |

---

## 관련 Repository / Controller 파일

| 파일 | 경로 |
|------|------|
| InstructorRepository | `app/Repositories/InstructorRepository.php` |
| Admin\InstructorController | `app/Http/Controllers/Admin/InstructorController.php` |
| Admin\InstructorApplyController | `app/Http/Controllers/Admin/InstructorApplyController.php` |

---

## 핵심 비즈니스 규칙

- **실제 컬럼 주의**: 기획서의 `intro_1`, `intro_2`, `sns_blog`, `sns_email` 컬럼은 실제 DB에 없음
  - 실제: `intro TEXT`, `career TEXT`, `sns_youtube`, `sns_instagram`, `sns_facebook`
- **프로필 사진**: `storage/uploads/instructor/{uuid}.{ext}` 저장, `/uploads/instructor/{filename}` 라우트로 서빙
  - 업로드: `FileUploader::uploadInstructorPhoto()` / 삭제: `deleteInstructorPhoto()`
  - 허용 형식: jpg/png/webp, 최대 5MB, MIME 타입 finfo로 실검증
- **강사 목록 쿼리**: `WHERE is_active = 1 ORDER BY sort_order ASC, instructor_idx ASC`
- **지원 심사 흐름**: `pending` → 관리자 승인(`approved`) 또는 거절(`rejected` + reject_reason)
- **teach_format 값**: `free_webinar` / `paid_vod` / `mixed` (UI 표시 시 한국어 변환 필요)
- **강사 지원 파일**: `lc_instructor_apply_file` 테이블로 별도 관리 (최대 3개, PDF/PPT/이미지, 20MB)
- **CSRF**: 승인/거절 POST 요청에 CSRF 토큰 필수
- **삭제 기능**: 현재 관리자 강사 삭제 엔드포인트 미구현 (is_active 토글로 노출 제어)

---

## 참조 문서

- `docs/20_db.md` — lc_instructor, lc_instructor_category, lc_instructor_apply 스키마
- `docs/30_features/instructor.md` — 강사 기능 전체 명세
- `docs/10_routes.md` — `/instructors/*`, `/admin/instructors/*`, `/admin/instructor-apply/*` 라우트
