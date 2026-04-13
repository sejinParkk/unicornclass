# 유니콘클래스 개발 현황 및 변경 이력

> 최종 업데이트: 2026-04-10
> 실제 구현 기준으로 작성. 기획서·체크리스트와 다를 경우 이 문서가 우선.

---

## 현재 프로젝트 상태

| 항목 | 내용 |
|------|------|
| 전체 진행률 | 관리자 기능 완성, 프론트엔드 미구현 |
| 운영 환경 | Docker (PHP 8.2 + MariaDB 10.11 + Apache) |
| 접속 주소 | `localhost:8080` (앱), `localhost:8081` (phpMyAdmin) |
| DB 마이그레이션 | 007번까지 적용 완료 |
| 오토로드 클래스 수 | 35개 |

---

## 완료된 기능 목록

### 관리자 (Admin)

| 기능 | 경로 | 비고 |
|------|------|------|
| 관리자 로그인/로그아웃 | `Admin\AuthController` | 세션 분리 (`_admin`) |
| 대시보드 | `Admin\DashboardController` | 요약 통계 |
| 강의 CRUD | `Admin\ClassController` | 썸네일 업로드, 소프트 삭제 |
| 챕터 관리 | `Admin\ChapterController` | JSON API, 드래그 순서 변경 |
| 강의 자료 관리 | `ClassMaterialRepository` | 파일/링크 2가지 타입 |
| 강사 CRUD | `Admin\InstructorController` | 프로필 사진 업로드 |
| 강사 소개/경력 다중 항목 | `lc_instructor_intro`, `lc_instructor_career` | 동적 추가/삭제 UI |
| 강사 지원 검토 | `Admin\InstructorApplyController` | 승인/거절, 거절 사유 |
| 회원 목록/상세 | `Admin\MemberController` | 검색, 필터 |
| 회원 정보 수정 | `Admin\MemberController::updateProfile` | 이름·이메일·연락처·비밀번호·수신동의 |
| 회원 상태 변경 | `Admin\MemberController::updateStatus` | 정상/정지/탈퇴 |
| 결제 목록/상세 | `Admin\OrderController` | 환불 승인/거절 |
| 1:1 문의 답변 | `Admin\ContactController` | |
| 공지사항 CRUD | `Admin\NoticeController` | 상단 고정, HTML 내용 |
| FAQ CRUD | `Admin\FaqController` | 카테고리, 정렬 |
| 사이트 설정 | `Admin\SettingController` | Key-Value, 로고·파비콘 업로드 |
| 약관 관리 | `Admin\TermsController` | 이용약관, 개인정보처리방침 |
| 관리자 프로필 수정 | `Admin\ProfileController` | 이름·이메일·비밀번호 변경 |
| 검색 로그 통계 | `Admin\StatsController` | |
| 오픈채팅 클릭 통계 | `Admin\StatsController` | |

### 인증 (Auth)

| 기능 | 비고 |
|------|------|
| 이메일 회원가입 | bcrypt(cost=12), CSRF |
| 이메일/아이디 로그인 | session_regenerate_id |
| 카카오 소셜 로그인 | OAuth 2.0, state 검증 |
| 네이버 소셜 로그인 | OAuth 2.0, state 검증 |
| 아이디 찾기 | 이름·연락처 확인 |
| 비밀번호 찾기/재설정 | |
| 로그아웃 | 회원 세션만 제거 |

### 마이페이지 (Mypage)

| 기능 | 비고 |
|------|------|
| 회원정보 수정 (`/mypage/profile`) | 이름·이메일·비밀번호·수신동의, 유효성 검사 |
| 나머지 마이페이지 라우트 | stub (501) — 미구현 |

---

## 주요 설계 결정 사항

### 아키텍처
- **Controller는 얇게**: 요청 수신·유효성 검사·응답만 담당. 비즈니스 로직은 Repository
- **Repository 패턴**: 모든 SQL은 Repository에서만. PDO Prepared Statements 필수
- **뷰 렌더링**: `ob_start()` / `ob_get_clean()` + 레이아웃 인클루드 방식 (관리자 전용)
- **프론트 뷰**: 독립 HTML 파일 (인라인 CSS), 공용 레이아웃 미적용

### 인증 & 보안
- 관리자(`_admin`)와 회원(`_member`) 세션 완전 분리
- 모든 POST에 CSRF 토큰 검증 (`Csrf::verify()`)
- 모든 출력 `htmlspecialchars()` 처리
- 파일 업로드: DocumentRoot 외부(`storage/uploads/`) 저장, PHP 라우트로만 서빙

### DB
- 모든 테이블 `lc_` prefix
- 소프트 삭제: `deleted_at DATETIME NULL` (강의·강사)
- 회원 상태: `is_active + leave_at` 조합 (ENUM 미사용)

### 강사 소개/경력 다중 항목 (migration 007)
- 기존 `lc_instructor.intro`, `career` TEXT 컬럼 → 별도 테이블로 분리
- 폼 제출 시 `intros_json` / `careers_json` 히든 필드 → DELETE + re-INSERT

### 회원 정보 수정
- 연락처: 하이픈 자동 포맷(JS), 서버에서 비숫자 제거 후 11자리 검증
- 이메일·연락처: 타인 중복만 차단 (자신 제외 쿼리)
- 비밀번호: 입력 시에만 변경, 빈칸이면 기존 유지

---

## DB 테이블 목록

| 테이블 | 설명 | 마이그레이션 |
|--------|------|-------------|
| `lc_admin` | 관리자 계정 | `002_lc_admin.sql` |
| `lc_member` | 회원 | `001_lc_member.sql` |
| `lc_class_category` | 강의 카테고리 | schema.sql |
| `lc_class` | 강의 | schema.sql |
| `lc_class_chapter` | 강의 챕터 | `003_alter_class_chapter.sql` |
| `lc_class_file` | 강의 자료 (파일/링크) | `004_lc_class_file.sql` |
| `lc_enroll` | 수강 신청 | schema.sql |
| `lc_progress` | 챕터별 수강 진도 | schema.sql |
| `lc_order` | 주문/결제 | schema.sql |
| `lc_review` | 강의 후기 | schema.sql |
| `lc_wishlist` | 찜 목록 | schema.sql |
| `lc_qna` | 1:1 문의 | schema.sql |
| `lc_instructor` | 강사 | schema.sql |
| `lc_instructor_intro` | 강사 소개 항목 (다중) | `007_instructor_intro_career.sql` |
| `lc_instructor_career` | 강사 경력 항목 (다중) | `007_instructor_intro_career.sql` |
| `lc_instructor_category` | 강사 카테고리 | schema.sql |
| `lc_instructor_apply` | 강사 지원 | schema.sql |
| `lc_faq` | FAQ | schema.sql |
| `lc_notice` | 공지사항 | schema.sql |
| `lc_search_log` | 검색 로그 | schema.sql |
| `lc_openchat_log` | 오픈채팅 클릭 로그 | `006_openchat_log.sql` |
| `lc_site_config` | 사이트 설정 (Key-Value) | `005_settings_terms.sql` |
| `lc_terms` | 약관 (이용약관/개인정보) | `005_settings_terms.sql` |

---

## 미구현 / 예정 작업

### 우선순위 높음 (프론트엔드 핵심)
- [ ] 공용 프론트 레이아웃 (헤더·푸터·GNB)
- [ ] 메인 페이지 (`/`)
- [ ] 강의 목록 (`/classes`)
- [ ] 강의 상세 (`/classes/{idx}`)
- [ ] VOD 플레이어 (`/classes/{idx}/learn`)
- [ ] 마이페이지 — 나의 강의, 찜목록, 결제내역, 후기, 1:1 문의, 회원탈퇴

### 결제
- [ ] Toss Payments 연동 (SDK, 승인 API, 웹훅)
- [ ] 무료 강의 신청 플로우
- [ ] 환불 신청 (회원) / 승인 (관리자)

### 강사 프론트
- [ ] 강사 목록 (`/instructors`)
- [ ] 강사 상세 (`/instructors/{idx}`) — 소개·경력 불릿 렌더링

### 고객센터 프론트
- [ ] FAQ, 공지사항, 이용약관, 1:1 문의

### 인증 보완
- [ ] SMS 실제 발송 연동 (현재 로직만 존재)
- [ ] 소셜 로그인 — 이메일 없는 경우 추가 입력 폼

### 기타
- [ ] 수료증 PDF 생성
- [ ] 진도 추적 API (`/api/progress/update`)
- [ ] 검색 API 고도화 (강사명 매칭)
- [ ] 반응형 모바일 대응

---

## 변경 이력

| 날짜 | 내용 |
|------|------|
| 2026-04-10 | 강사 소개/경력 다중 항목 테이블 분리 (`lc_instructor_intro`, `lc_instructor_career`) |
| 2026-04-10 | 관리자 회원 상세 — 정보 수정 기능 추가 (이름·이메일·연락처·비밀번호·수신동의) |
| 2026-04-10 | 연락처 하이픈 자동 포맷 (프론트 JS) |
| 2026-04-10 | 마이페이지 `MypageController` 생성, `/mypage/profile` 구현 |
| 2026-04-10 | `MemberRepository` — `updateProfile`, `updatePassword`, `existsByEmailExcept`, `existsByPhoneExcept` 추가 |
