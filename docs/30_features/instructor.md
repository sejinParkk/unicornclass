# 강사 기능 명세

## 1. 강사 목록 (`/instructors`)

### 레이아웃
- 4열 그리드, 12명/페이지
- 페이지네이션: 이전/다음 + 번호
- 상단: 서브 배너 + 총 강사 수 표시 (빨간색 강조)
- 하단 CTA: "강사 지원하기" 배너 → `/instructors/apply`

### 강사 카드

| 요소 | 설명 |
|------|------|
| 사진 | 240px 고정 높이, object-fit: cover |
| 이름 | bold |
| 소개 문구 | `intro_1`, `intro_2` (1~2줄 말줄임) |
| SNS 아이콘 | 인스타그램, 유튜브, 블로그, 이메일 (하단 고정) |
| 호버 | 카드 리프트 + 빨간 그라디언트 오버레이 + SNS 아이콘 강조 |

### DB 쿼리
```sql
SELECT *
FROM lc_instructor
WHERE is_active = 1
ORDER BY sort_order ASC, instructor_idx ASC
LIMIT 12 OFFSET ?
```

---

## 2. 강사 상세 (`/instructors/{instructor_idx}`)

### 상단 프로필 섹션

| 요소 | 설명 |
|------|------|
| 사진 | 280×340px, V자형 데코레이션 오버레이 |
| 이름 + 분야 | 우측 상단 |
| SNS 아이콘 | 클릭 → 외부 링크 새탭 |
| 소개 불릿 | `lc_instructor_intro` (sort_order ASC) |
| 경력 불릿 | `lc_instructor_career` (sort_order ASC) |

### 담당 강의 섹션

- "[강사명]의 강의" 타이틀
- 강의 카드 3열 그리드 (강의 목록과 동일한 카드)
- DB: `SELECT * FROM lc_class WHERE instructor_idx = ? AND is_active = 1`

### SNS 링크 처리

| 필드 | 없을 때 |
|------|---------|
| `sns_instagram` | 아이콘 숨김 |
| `sns_youtube` | 아이콘 숨김 |
| `sns_blog` | 아이콘 숨김 |
| `sns_email` | 아이콘 숨김 |

---

## 3. 강사 지원 (`/instructors/apply`)

### 폼 섹션 구성

#### 3-1. 기본 정보 (필수)

| 필드 | 유효성 |
|------|--------|
| 이름 | 2~50자 |
| 휴대폰 | 숫자 11자리 |
| 이메일 | 이메일 형식 |
| 강의 분야 | 선택: 커머스 / AI / SNS / 유튜브 / 부동산 / 창업 / 기타 |
| 강의 경력 | 선택: 없음 / 1년 미만 / 1~3년 / 3~5년 / 5년 이상 |

#### 3-2. 강사 소개 (필수)

| 필드 | 유효성 |
|------|--------|
| 경력·자기소개 | 100자 이상 권장 (textarea) |
| 강의 계획 | 자유 기술 (textarea) |
| 선호 강의 형태 | 무료 웨비나 / 유료 VOD / 혼합 |

#### 3-3. SNS / 채널 (선택)

| 필드 | 비고 |
|------|------|
| 인스타그램 URL | URL 형식 |
| 유튜브 URL | URL 형식 |
| 블로그 URL | URL 형식 |
| 기타 URL | URL 형식 |

#### 3-4. 포트폴리오 (선택)

| 요소 | 제한 |
|------|------|
| 파일 업로드 | PDF / PPT / 이미지, 최대 20MB, 최대 3개 |
| 외부 링크 | Notion, Google Drive 등 URL |

#### 3-5. 동의 (필수)
- 개인정보 수집·이용 동의 (필수)

### 제출 처리

```
POST /instructors/apply
→ 유효성 검사
→ lc_instructor_apply INSERT
→ 파일 업로드 → lc_instructor_apply_file INSERT (최대 3개)
→ 성공 모달 출력
```

### 성공 모달
- "지원이 완료되었습니다" 안내
- 검토 기간: 영업일 3~5일
- 버튼: "강사 페이지 보기" / "메인으로 돌아가기"
- 중복 제출 방지: 버튼 submit 후 disabled 처리

### 보안
- 파일 업로드: 허용 확장자 화이트리스트 (`pdf`, `ppt`, `pptx`, `jpg`, `jpeg`, `png`)
- 파일명 UUID로 변경 저장
- CSRF 토큰 필수

---

## 4. 강사 관리 (관리자)

> 관리자 페이지는 별도 구현 예정. 아래는 핵심 기능 목록.

| 기능 | 설명 |
|------|------|
| 강사 지원 목록 | 상태별 필터 (pending / approved / rejected) |
| 지원서 상세 | 파일 다운로드, 링크 확인 |
| 지원 승인/거절 | `lc_instructor_apply.status` 업데이트 |
| 강사 등록 | 승인 후 `lc_instructor` INSERT |
| 오픈채팅 클릭 통계 | `lc_openchat_log` 강의별 집계 |
