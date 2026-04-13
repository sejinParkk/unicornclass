# Class 도메인

강의(클래스) 등록·조회·수강·진도 관리를 담당하는 핵심 도메인.

---

## 관련 DB 테이블

| 테이블 | 역할 |
|--------|------|
| `lc_class` | 강의 메인 정보 (type, title, price, thumbnail 등) |
| `lc_class_category` | 강의 카테고리 |
| `lc_class_chapter` | 챕터(에피소드) — Vimeo URL, duration(초 단위 INT) |
| `lc_class_file` | 강의 자료 (file/link 유형) |
| `lc_review` | 강의 후기 (rating 1~5) |
| `lc_wishlist` | 찜 목록 (실제 테이블명 주의: 기획서는 `lc_wish`) |
| `lc_progress` | 수강 진도 — 챕터별 1행 구조 (기획서의 rate/current_ep 컬럼 없음) |

---

## 관련 Repository / Controller 파일

| 파일 | 경로 |
|------|------|
| ClassRepository | `app/Repositories/ClassRepository.php` |
| ChapterRepository | `app/Repositories/ChapterRepository.php` |
| ClassMaterialRepository | `app/Repositories/ClassMaterialRepository.php` |
| Admin\ClassController | `app/Http/Controllers/Admin/ClassController.php` |
| Admin\ChapterController | `app/Http/Controllers/Admin/ChapterController.php` |

---

## 핵심 비즈니스 규칙

- **강의 유형** `type`: `free`(무료, expire_at=NULL) / `premium`(유료, Toss Payments, expire_at=paid_at+duration_days)
  - 기획서의 `class_type`, `discount_price` 컬럼명은 실제 DB와 다름 → 실제: `type`, `price_origin`
- **챕터 duration**: DB는 **초(seconds) 정수**로 저장. UI 표시 시 `ChapterRepository::secondsToDisplay()`, 입력은 `MM:SS` → `displayToSeconds()` 변환
- **챕터 저장**: 강의 등록/수정 폼의 저장 버튼 클릭 시 `chapters_json` hidden 필드로 일괄 처리
- **total_episodes**: 챕터 추가·삭제 후 `ClassRepository::syncTotalEpisodes()`로 자동 갱신
- **삭제 정책**: 수강자 있으면 소프트 삭제(`is_active=0`), 없으면 물리 삭제 + 썸네일 파일도 물리 삭제
- **강의 유형 수정 불가**: 수강자 있을 경우 `type` 변경 불가 (`hasEnrollments()` 체크)
- **썸네일**: `storage/uploads/class/{uuid}.{ext}` 저장, `FileUploader::uploadClassThumbnail()` / `deleteClassThumbnail()`
- **강의 자료**: `storage/uploads/materials/{uuid}.{ext}`, 허용 확장자 pdf/doc/docx/xls/xlsx/ppt/pptx/zip/jpg/png, 최대 50MB
- **진도율**: `ROUND(완료챕터수 / total_episodes * 100)`, 80% 이상 시 수료 조건 충족
- **찜 테이블**: 실제명 `lc_wishlist`, FK 컬럼 `member_idx` (기획서의 `mb_idx` 아님)
- **vimeo_url**: 수강 권한 확인 후 서버에서만 반환 (클라이언트 직접 노출 금지)
- **Google Sheets API**: 무료강의 신청 완료 후 호출, 실패해도 enroll 롤백하지 않음

---

## 참조 문서

- `docs/20_db.md` — lc_class, lc_class_chapter, lc_class_file, lc_progress, lc_wishlist 스키마
- `docs/30_features/class.md` — 강의 도메인 전체 비즈니스 로직 명세
- `docs/10_routes.md` — `/classes/*`, `/admin/classes/*` 라우트
