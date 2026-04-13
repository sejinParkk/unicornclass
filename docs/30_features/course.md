# 강의 기능 명세

## 1. 강의 목록 (`/classes`)

### 필터 구조

| 필터 | 파라미터 | 값 |
|------|----------|----|
| 탭 | `type` | `free` / `premium` |
| 카테고리 | `category` | `ai` / `sns` / `design` / `marketing` / `youtube` / `commerce` / (생략=전체) |
| 페이지 | `page` | 정수 (기본 1) |

### 레이아웃
- 3열 그리드, 9개/페이지
- 페이지네이션: 이전/다음 + 페이지 번호

### 강의 카드 구성

| 요소 | 설명 |
|------|------|
| 썸네일 | 16:9 비율, 호버 시 카드 리프트 + 그림자 |
| 배지 | HOT / NEW / PREMIUM / FREE (중복 가능) |
| 제목 | 1줄 말줄임 |
| 강사명 + 카테고리 | 하단 메타 |

### DB 쿼리
```sql
SELECT c.*, i.name AS instructor_name
FROM lc_class c
JOIN lc_instructor i ON c.instructor_idx = i.instructor_idx
WHERE c.is_active = 1
  AND (c.class_type = ? OR ? IS NULL)           -- 탭 필터
  AND (c.category = ? OR ? IS NULL)             -- 카테고리 필터
ORDER BY c.sort_order ASC, c.created_at DESC
LIMIT 9 OFFSET ?
```

---

## 2. 강의 상세 (`/classes/{class_idx}`)

### 레이아웃
- 좌: 강의 설명 영역 (breadcrumb, 썸네일, 탭 컨텐츠, 강사 소개)
- 우: 스티키 사이드바 (가격, 신청 버튼)

### 콘텐츠 탭

| 탭 | 내용 |
|----|------|
| 강의 소개 | `lc_class.description` HTML 렌더링 |
| Q&A | `lc_qna` 목록 (수강자만 작성 가능) |
| 후기 | `lc_review` 목록 + 별점 평균 |

### 스티키 사이드바 구성

| 요소 | 설명 |
|------|------|
| 배지 | HOT / NEW / PREMIUM / FREE |
| 제목 | 강의명 |
| 좋아요/공유 | 토글 + SNS 공유 |
| 강사 정보 | 이름 + 수강신청 수 |
| 가격 | 정가 취소선 + 할인가 |
| 수강신청 버튼 | 무료: 즉시 신청 / 프리미엄: 결제 페이지 |
| 장바구니 버튼 | 프리미엄만 표시 |

### 결제 플로우 (프리미엄)
1. **상품 확인** → 강의명, 금액 확인
2. **결제 수단 선택** → 신용카드 / 체크카드 / 계좌이체
3. **결제 정보 입력** → 카드번호, 유효기간, CVC 또는 계좌 정보
4. **완료** → `lc_order` INSERT + `lc_enroll` INSERT + 수강 기간 설정

### 수강신청 (무료)
- `POST /api/classes/{idx}/enroll` → `lc_enroll` INSERT
- 로그인 필요, 중복 신청 방지

---

## 3. VOD 플레이어 (`/classes/{class_idx}/learn`)

### 접근 권한
- `lc_enroll` 존재 확인
- 프리미엄: `expire_at > NOW()` 추가 확인
- 미충족 시 강의 상세 페이지로 리다이렉트

### 레이아웃
- 좌: Vimeo 플레이어 + 챕터 목록 + 강의자료
- 우: 강사 카드 + 오픈채팅 버튼 + 결제 정보

### Vimeo 플레이어
- `<iframe>` embed (Vimeo Video ID 기반)
- 챕터 클릭 → 해당 시간으로 seek (Vimeo JS API)
- 에피소드 목록: `lc_class_episode` (sort_order ASC)
- 재생 완료 시 진도 업데이트: `lc_progress` UPDATE

### 챕터 목록 구성

| 요소 | 설명 |
|------|------|
| 번호 | 에피소드 순서 |
| 제목 | `ep_title` |
| 재생 시간 | `ep_duration` |
| 현재 재생 중 | 활성 스타일 |

### 강의 자료 (`lc_class_material`)

| 타입 | UI | 버튼 |
|------|----|------|
| `file` | 파일명 + 크기 + 확장자 아이콘 | 다운로드 버튼 |
| `link` | 링크명 + 플랫폼 아이콘 (Notion/GDrive/YouTube) | 새탭 열기 버튼 |

### 오픈채팅 버튼
- `lc_enroll.kakao_url` → 새탭 열기
- 클릭 시 로그 기록: `POST /api/openchat/log` → `lc_openchat_log` INSERT

### 진도 업데이트
```sql
-- 에피소드 완료 시
UPDATE lc_progress
SET current_ep = ?,
    rate = ROUND(current_ep / total_ep * 100),
    updated_at = NOW()
WHERE enroll_idx = ?
```

---

## 4. 찜 기능

- 강의 상세 사이드바 + 강의 목록 카드에서 토글
- 로그인 필요 (미로그인 시 로그인 페이지 이동)
- `POST /api/classes/{idx}/wish` → `lc_wish` INSERT or DELETE
- 응답: `{ "wished": true/false, "count": N }`

---

## 5. 좋아요 기능

- 강의 상세 사이드바
- `POST /api/classes/{idx}/like` → `lc_like` INSERT or DELETE
- `lc_class.like_count` 동기 업데이트

---

## 6. 검색 (`/search`)

### 검색 로직
```sql
SELECT 'class' AS type, class_idx AS idx, title AS name, thumbnail, category
FROM lc_class
WHERE is_active = 1 AND (title LIKE ? OR category LIKE ?)

UNION

SELECT 'instructor' AS type, instructor_idx AS idx, name, photo AS thumbnail, category
FROM lc_instructor
WHERE is_active = 1 AND name LIKE ?
```
- 파라미터: `%keyword%`

### 결과 표시
- 강의 섹션 (3열 그리드) + 강사 섹션 (4열 그리드)
- 키워드 `<mark>` 하이라이트
- 결과 없음: 빈 상태 + 추천 검색어 태그

### 검색 로그
```sql
INSERT INTO lc_search_log (keyword, result_count, mb_idx, searched_at)
VALUES (?, ?, ?, NOW())
```
