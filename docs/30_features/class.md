# 클래스(강의) 도메인 / 비즈니스 로직 명세

> UI/화면 명세는 기획서(`docs/planning/`) 참조. 이 문서는 도메인 규칙, 비즈니스 로직, 실제 구현 기준을 다룬다.

---

## 1. 강의 유형 분기 (free / premium)

### 유형 정의

| 구분 | `type` 값 | 수강 방식 | 결제 | 수강 기간 |
|------|-----------|-----------|------|----------|
| 무료강의 | `free` | 신청 즉시 | 없음 | 무제한 (expire_at=NULL) |
| 프리미엄강의 | `premium` | 결제 완료 후 | Toss Payments | 구매 시 설정 (기본 180일) |

> DB 컬럼명: `lc_class.type` (기획서의 `class_type` 아님)

### 분기 처리 규칙

```
if type == 'free':
    - 신청 버튼 텍스트: "무료강의 신청하기" (파란색)
    - 신청 플로우: 3단계 (신청 확인 → 동의 → 완료)
    - lc_enroll.expire_at = NULL
    - 결제 로직 없음
    - Google Sheets API 호출 (신청자 명단 기록)

if type == 'premium':
    - 신청 버튼 텍스트: "결제하기" (빨간색)
    - 신청 플로우: Toss Payments 5단계 결제
    - lc_enroll.expire_at = paid_at + duration_days
    - lc_order 레코드 생성
    - Google Sheets API 호출 없음
```

### 신청 가능 조건 체크 (공통)

```php
// 1. 로그인 확인 (무료·유료 모두)
if (!Auth::isMember()) redirect('/login?redirect=...');

// 2. 강의 활성화 확인
if (!$class['is_active']) abort(404);

// 3. 판매 종료 확인 (premium만)
if ($class['type'] === 'premium' && $class['sale_end_at'] && strtotime($class['sale_end_at']) < time()) {
    return error('판매가 종료된 강의입니다.');
}

// 4. 중복 신청/구매 확인
if ($enrollRepo->exists($memberIdx, $classIdx)) {
    return error('이미 신청하셨습니다. 마이페이지 > 내 강의에서 수강하세요.');
}
```

---

## 2. 챕터 시스템 (lc_class_chapter)

### 실제 테이블 구조

```sql
CREATE TABLE lc_class_chapter (
    chapter_idx  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    class_idx    INT UNSIGNED  NOT NULL,
    title        VARCHAR(200)  NOT NULL,
    vimeo_url    VARCHAR(500)  NOT NULL DEFAULT '',  -- Vimeo 영상 URL 전체
    duration     INT UNSIGNED  DEFAULT 0,            -- 재생 시간 (초 단위 정수)
    sort_order   SMALLINT      DEFAULT 0,
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,   -- 0=소프트 삭제
    created_at   DATETIME      DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (chapter_idx),
    KEY idx_class_idx (class_idx)
);
```

> **duration 처리**: DB는 초(seconds) 정수로 저장. 표시 시 `ChapterRepository::secondsToDisplay(int)` → `"M:SS"`.
> 관리자 입력은 `"MM:SS"` 형식 → `ChapterRepository::displayToSeconds(string)` → DB 저장.

### 관리자 챕터 관리 방식

- 강의 **등록** 폼부터 챕터 섹션이 표시됨 (수정 시에만 보이던 방식 폐기)
- 챕터 추가/수정/삭제/순서 변경은 모달에서 처리되며 **저장 버튼 클릭 시 일괄 저장**
  - 폼 submit 시 `chapters_json` hidden 필드에 직렬화하여 전송
  - `ClassController::saveChapters()` 에서 파싱 후 처리
- 수정 시 삭제된 챕터는 소프트 삭제 (`is_active=0`), 제출된 챕터는 update/create
- `lc_class.total_episodes`는 챕터 저장 후 `ClassRepository::syncTotalEpisodes()`로 자동 갱신

### 챕터 조회 규칙

```sql
-- 관리자 / 수강자용 (활성 챕터만)
SELECT *
FROM lc_class_chapter
WHERE class_idx = ? AND is_active = 1
ORDER BY sort_order ASC, chapter_idx ASC;
```

### 챕터 진도 연동

- 챕터 클릭 → 해당 Vimeo 영상으로 전환 (순차 잠금 없음, 자유 수강)
- 챕터 완료 → `lc_progress` 테이블에 챕터별 행 기록
- `vimeo_url`은 수강 권한 확인 후 서버에서 반환 (클라이언트 직접 노출 금지)

---

## 3. 강의 자료 시스템 (lc_class_file)

### 자료 유형

| 유형 | `file_type` | 저장 방식 |
|------|-------------|-----------|
| 파일 업로드 | `file` | `storage/uploads/materials/{uuid}.{ext}`, `file_path` 컬럼 저장 |
| 외부 링크 | `link` | `external_url` 컬럼 저장 |

### 파일 업로드 규격

| 항목 | 규칙 |
|------|------|
| 허용 형식 | pdf, doc, docx, xls, xlsx, ppt, pptx, zip, jpg, png |
| 최대 크기 | 50MB |
| 저장 파일명 | `bin2hex(random_bytes(16)).ext` (UUID) |
| 저장 경로 | `storage/uploads/materials/` (DocumentRoot 외부) |
| 서빙 라우트 | `GET /uploads/materials/{filename}` → `Content-Disposition: attachment` |

### 관리자 자료 관리 방식

- 강의 등록/수정 폼 내 "강의 자료" 섹션에서 관리
- 파일 추가(`new_files[]`), 링크 추가(`new_link_urls[]`) — **저장 버튼 클릭 시 일괄 저장**
- 기존 자료 삭제: `delete_file_ids` hidden 필드 → 소프트 삭제 + 파일 물리 삭제
- `ClassController::saveMaterials()`, `ClassController::deleteMaterials()`에서 처리

---

## 4. 판매 종료 처리 로직

### 판매 종료 조건

```php
// lc_class.sale_end_at 기준
$isSaleEnded = $class['sale_end_at'] && strtotime($class['sale_end_at']) < time();
```

### 상태별 UI 분기

| 조건 | `is_active` | `sale_end_at` | 처리 |
|------|-------------|---------------|------|
| 정상 판매 중 | 1 | NULL 또는 미래 | 결제 버튼 활성 |
| 판매 종료 (신규 불가) | 1 | 과거 | 버튼 비활성, "판매 종료" 표시 |
| 노출 중단 | 0 | - | 목록·상세 미노출 (404) |

### 기간 연장 로직 (수강 만료 시)

```
if enroll.expire_at < NOW():
    if class.sale_end_at > NOW() or class.sale_end_at IS NULL:
        → "기간 연장" 버튼 (클래스 상세 → 재구매)
        → 재구매 시 expire_at = NOW() + duration_days
    else:
        → "연장 불가" 안내 (비활성)
```

---

## 5. 수강 진도율 계산

### 실제 구조 (챕터별 행)

`lc_progress` 테이블은 챕터 완료 여부를 챕터당 1행으로 관리한다.

```sql
-- 특정 수강의 완료 챕터 수 조회
SELECT COUNT(*) AS completed
FROM lc_progress
WHERE member_idx = ? AND class_idx = ? AND is_complete = 1;

-- 진도율 계산
rate = ROUND(completed / total_episodes * 100)
```

### 업데이트 시점

- Vimeo 플레이어 챕터 시청 완료 이벤트 → `POST /api/progress/update`
- `lc_progress` 해당 챕터 행 `is_complete=1`, `completed_at=NOW()` 업데이트

### 수료증 발급 조건

```php
if ($completedRate >= 80) {
    // 수료증 발급 가능 상태 (별도 처리 예정)
}
```

### 진도 관련 비즈니스 규칙

| 규칙 | 내용 |
|------|------|
| 자유 수강 | 챕터 순서 제한 없음 |
| 중복 완료 방지 | 이미 완료한 챕터 재클릭 시 카운트 중복 안 함 |
| 만료 강의 진도 | `expire_at` 초과 시 진도 업데이트 API 차단 |
| 수강률 표시 | 마이페이지 카드에서 0~100% 프로그레스바로 표시 |

---

## 6. 썸네일 업로드 규칙

### 규격

| 항목 | 규칙 |
|------|------|
| 허용 형식 | jpg, png, webp |
| 최대 크기 | 5MB |
| 권장 해상도 | 1280×720px (16:9 비율) |
| 저장 파일명 | `bin2hex(random_bytes(16)).ext` (UUID) |
| 저장 경로 | `storage/uploads/class/` (DocumentRoot 외부) |
| 서빙 라우트 | `GET /uploads/class/{filename}` |

### 업로드 처리 (`FileUploader::uploadClassThumbnail`)

```php
// 1. finfo로 MIME 타입 실검증
$finfo = new \finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
// 허용: image/jpeg, image/png, image/webp

// 2. UUID 파일명 생성
$filename = bin2hex(random_bytes(16)) . '.' . $ext;
$savePath = ROOT_PATH . '/storage/uploads/class/' . $filename;

// 3. 이동 및 DB 저장 (lc_class.thumbnail = $filename)
move_uploaded_file($file['tmp_name'], $savePath);
```

### 썸네일 교체 (수정 시)

- 새 파일 업로드 → 신규 UUID 파일 저장 → 기존 파일 `FileUploader::deleteClassThumbnail()` 물리 삭제

### 미등록 시 처리

- `lc_class.thumbnail IS NULL` → View에서 기본 플레이스홀더 이미지 대체

---

## 7. 관리자 목록 조회 조건

### 사용자 목록 조회 (공개)

```sql
SELECT c.*, i.name AS instructor_name, cat.name AS category_name
FROM lc_class c
JOIN lc_instructor i   ON c.instructor_idx = i.instructor_idx
LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
WHERE c.is_active = 1
  AND (c.sale_end_at IS NULL OR c.sale_end_at > NOW())
  AND (c.type = ? OR ? IS NULL)
  AND (c.category_idx = ? OR ? IS NULL)
ORDER BY c.sort_order ASC, c.created_at DESC
LIMIT ? OFFSET ?
```

### 관리자 목록 조회 (`ClassRepository::getAdminList`)

```sql
SELECT c.*,
       i.name AS instructor_name,
       cat.name AS category_name,
       (SELECT COUNT(*) FROM lc_enroll e WHERE e.class_idx = c.class_idx) AS enroll_count,
       (SELECT COUNT(*) FROM lc_review r WHERE r.class_idx = c.class_idx) AS review_count
FROM lc_class c
JOIN lc_instructor i ON i.instructor_idx = c.instructor_idx
LEFT JOIN lc_class_category cat ON cat.category_idx = c.category_idx
-- is_active, sale_end_at 조건 없음 (비활성/판매종료 강의도 표시)
ORDER BY c.created_at DESC
LIMIT ? OFFSET ?
```

### 차이점 요약

| 조건 | 사용자 | 관리자 |
|------|--------|--------|
| `is_active = 1` | 필수 | 없음 (전체 표시) |
| `sale_end_at` 필터 | 판매 중만 | 없음 |
| 집계 컬럼 | 없음 | 수강자 수, 후기 수 |
| 정렬 | `sort_order ASC` | `created_at DESC` |

---

## 8. 관리자 강의 등록/수정 규칙

| 필드 | 등록 시 | 수정 시 |
|------|---------|---------|
| `type` | 필수 선택 | 수강자 있으면 변경 불가 (`hasEnrollments()` 체크) |
| `is_active` | 기본 0 (저장 후 수동 활성화) | 언제든 변경 가능 |
| `sale_end_at` | 선택 (공란=무기한) | 변경 가능 |
| `thumbnail` | 선택 (미입력 시 기본 이미지) | 교체 가능 (기존 파일 자동 삭제) |
| `price` / `price_origin` | premium 타입에서만 의미 있음 | 변경 가능 |
| 챕터 | 등록 폼에서 미리 구성 가능 | 동일 폼에서 수정 |
| 강의 자료 | 등록 시 파일/링크 추가 가능 | 개별 삭제 + 신규 추가 |

### 삭제 동작 (`ClassRepository::delete`)

```php
// 수강자 있으면 소프트 삭제 (is_active=0), 없으면 물리 삭제
if ($this->hasEnrollments($classIdx)) {
    // UPDATE lc_class SET is_active=0
    return 'deactivated';
}
// DELETE FROM lc_class
// 썸네일 파일도 물리 삭제
return 'deleted';
```

---

## 9. Google Sheets 연동 (무료강의 신청)

- 무료강의 신청 완료 후 Google Sheets API v4 호출
- 저장 데이터: 강의명, 신청자명, 이메일, 연락처, 신청일시
- **Google Sheets API 실패해도 신청 취소하지 않음** (lc_enroll은 이미 저장됨)
- 실패 시 에러 로그만 기록
- 환경변수로 Spreadsheet ID, Service Account 키 관리
