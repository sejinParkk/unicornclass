# 검색 기능 명세

---

## 1. 검색 진입점

| 진입 | 동작 |
|------|------|
| GNB 검색창에서 Enter / 🔍 버튼 | `GET /search?q={keyword}` |
| 검색어 공란 검색 | 전체 클래스 목록(`/classes`)으로 이동 |
| 검색 결과 페이지에서 재검색 | 검색어 유지 상태, 재요청 |

---

## 2. 검색 로직 (SQL)

### 클래스 검색

```sql
-- 강사명으로 해당 강사의 전체 클래스 검색
SELECT c.class_idx, c.title, c.thumbnail, c.category, c.class_type,
       c.price, c.discount_price, i.name AS instructor_name,
       1 AS match_type  -- 강사명 매칭
FROM lc_class c
JOIN lc_instructor i ON c.instructor_idx = i.instructor_idx
WHERE c.is_active = 1
  AND i.name LIKE ?     -- '%keyword%'

UNION

-- 클래스명으로 직접 검색
SELECT c.class_idx, c.title, c.thumbnail, c.category, c.class_type,
       c.price, c.discount_price, i.name AS instructor_name,
       2 AS match_type  -- 클래스명 매칭
FROM lc_class c
JOIN lc_instructor i ON c.instructor_idx = i.instructor_idx
WHERE c.is_active = 1
  AND c.title LIKE ?    -- '%keyword%'
```

> UNION으로 중복 제거. `match_type` 기준 정렬: 강사명 완전일치 우선.

### 강사 검색

```sql
-- 강사명 LIKE 매칭
SELECT instructor_idx, name, photo, category, intro_1
FROM lc_instructor
WHERE is_active = 1
  AND name LIKE ?   -- '%keyword%'

UNION

-- 클래스 검색 결과의 강사도 노출 (관련 강사)
SELECT DISTINCT i.instructor_idx, i.name, i.photo, i.category, i.intro_1
FROM lc_instructor i
JOIN lc_class c ON c.instructor_idx = i.instructor_idx
WHERE i.is_active = 1
  AND c.is_active = 1
  AND c.title LIKE ?  -- '%keyword%'
```

### 통합 응답 구조

```json
{
  "keyword": "홍길동",
  "total": 4,
  "classes": [
    { "class_idx": 1, "title": "홍길동의 월 1000만원...", "instructor_name": "홍길동", ... }
  ],
  "instructors": [
    { "instructor_idx": 3, "name": "홍길동", "category": "커머스", ... }
  ]
}
```

---

## 3. 키워드 하이라이트

```php
// 서버 사이드 처리
function highlightKeyword(string $text, string $keyword): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $safeKeyword = preg_quote(htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'), '/');
    return preg_replace('/(' . $safeKeyword . ')/iu', '<mark>$1</mark>', $escaped);
}
```

- 클래스 카드 제목에 `<mark>키워드</mark>` 삽입
- 강사 카드 이름에 `<mark>키워드</mark>` 삽입
- 대소문자 구분 없음 (LIKE + preg_replace `i` 플래그)

---

## 4. 검색 결과 없음 처리

### 빈 결과 상태

```
classes: [], instructors: [], total: 0
→ 빈 상태 화면 표시
→ 추천 검색어 태그 표시 (관리자 설정 또는 인기 검색어 상위 10개)
→ 안내 문구: "'{keyword}'에 대한 검색 결과가 없습니다"
```

### 추천 검색어 태그

- `lc_search_log`에서 `result_count > 0` 기준 상위 검색어 조회
- 태그 클릭 → 해당 키워드로 재검색

```sql
SELECT keyword, COUNT(*) AS cnt
FROM lc_search_log
WHERE result_count > 0
  AND searched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY keyword
ORDER BY cnt DESC
LIMIT 10
```

---

## 5. 검색 로그 (`lc_search_log`)

```sql
CREATE TABLE lc_search_log (
    log_idx       INT UNSIGNED  PRIMARY KEY AUTO_INCREMENT,
    keyword       VARCHAR(200)  NOT NULL,
    result_count  INT UNSIGNED  DEFAULT 0,
    mb_idx        INT UNSIGNED  NULL,          -- 비회원은 NULL
    searched_at   DATETIME      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_keyword (keyword),
    INDEX idx_searched_at (searched_at)
);
```

### 로그 기록 시점

```php
// 검색 처리 후 결과 건수 확정 시점에 기록
$this->searchLogRepo->insert([
    'keyword'      => $keyword,
    'result_count' => $result['total'],
    'mb_idx'       => $session->mbIdx ?? null,  // 비회원 NULL
]);
```

### 활용

| 용도 | 쿼리 |
|------|------|
| 인기 검색어 | `result_count > 0` 기준 GROUP BY keyword COUNT DESC |
| 결과 없는 검색어 | `result_count = 0` → 관리자 콘솔 확인, 콘텐츠 기획 참고 |
| 검색 트렌드 | 기간별 keyword 빈도 분석 |

---

## 6. API 명세

### `GET /api/search`

| 파라미터 | 타입 | 필수 | 설명 |
|----------|------|------|------|
| `q` | string | 필수 | 검색 키워드 (최소 1자) |

**응답 예시:**

```json
{
  "keyword": "홍길동",
  "total": 4,
  "classes": [
    {
      "class_idx": 1,
      "title": "<mark>홍길동</mark>의 월 1000만원 달성 비법",
      "thumbnail": "abc123.jpg",
      "category": "commerce",
      "class_type": "premium",
      "price": 280000,
      "discount_price": 198000,
      "instructor_name": "홍길동"
    }
  ],
  "instructors": [
    {
      "instructor_idx": 3,
      "name": "<mark>홍길동</mark>",
      "photo": "def456.jpg",
      "category": "커머스",
      "intro_1": "연매출 30억 이커머스 회사 이사..."
    }
  ]
}
```

**에러 케이스:**

| 상황 | 처리 |
|------|------|
| `q` 파라미터 없음 | 302 → `/classes` |
| `q` 공란 | 302 → `/classes` |
| `q` 200자 초과 | 400 에러 |
| SQL 특수문자 (`%`, `_`) | `addslashes()` 또는 PDO 바인딩으로 자동 이스케이프 |

---

## 7. 확장 계획

| 기능 | 비고 |
|------|------|
| 자동완성 | `GET /api/search/suggest?q=` → 실시간 키워드 추천 |
| 인기 검색어 | 메인 페이지 또는 검색창 하단 표시 |
| 검색 필터 | 카테고리, 가격범위, 무료/프리미엄 필터 추가 |
| 전문 검색 | 강의 설명(`description`)까지 FULLTEXT 검색 확장 |
