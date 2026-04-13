# 개발 체크리스트

> 기능 구현 전 설계 검토, 구현 후 확인 용도.
> 완료 시 `- [x]` 로 변경.

---

## Phase 1 — 환경 & 기반

### Docker / 인프라
- [ ] `docker compose up -d --build` 정상 실행
- [ ] `localhost:8080` PHP 동작 확인 (`Unicorn Class OK`)
- [ ] `localhost:8081` phpMyAdmin 접속 확인 (`unicorn` / `unicorn1234`)
- [ ] `localhost:3306` MariaDB 접속 확인
- [ ] `.env` 환경변수 로드 확인

### Composer / PSR-4
- [ ] `composer.json` 생성 (`autoload.psr-4` 설정)
- [ ] `src/` 디렉토리 PSR-4 네임스페이스 적용
- [ ] `composer dump-autoload` 정상 실행

### 라우터
- [ ] `public/index.php` → Core/Router 연결
- [ ] `GET /` 라우트 정상 응답
- [ ] `public/.htaccess` mod_rewrite 동작 확인
- [ ] 404 라우트 처리

### DB 연결
- [ ] `Core/DB.php` PDO 싱글턴 구현
- [ ] 연결 실패 시 에러 로그 기록, 공개 에러 미노출

---

## Phase 2 — 인증

### 회원가입
- [ ] `/register` GET 렌더링
- [ ] 아이디 중복 체크 `POST /api/member/check-id`
- [ ] 비밀번호 해시 `password_hash(BCRYPT, cost:12)`
- [ ] 필수 약관 미동의 시 제출 불가
- [ ] CSRF 토큰 검증
- [ ] `lc_member` INSERT 성공 후 세션 발급

### 로그인
- [ ] `/login` GET 렌더링
- [ ] `POST /login` 인증 처리
- [ ] `session_regenerate_id(true)` 로그인 후 실행
- [ ] 탈퇴 계정 접근 → 팝업 안내
- [ ] 휴면 계정 접근 → 본인인증 유도

### 소셜 로그인 (카카오)
- [ ] `/auth/kakao` → 카카오 인증 URL 리다이렉트
- [ ] `state` 파라미터 세션 저장 + 콜백 검증
- [ ] Access Token 발급 (서버 사이드)
- [ ] 신규 회원 자동 생성
- [ ] 기존 회원 로그인 처리
- [ ] 이메일 없는 경우 추가 입력 폼

### 소셜 로그인 (네이버)
- [ ] 카카오와 동일 흐름 구현 (`social_provider='naver'`)

### 아이디 찾기 / 비밀번호 재설정
- [ ] SMS 발송 연동 (1일 5회 제한)
- [ ] 인증번호 타이머 3분
- [ ] 비밀번호 재설정 토큰 30분 만료
- [ ] 토큰 1회 사용 후 무효화

---

## Phase 3 — 강의

### 강의 목록
- [ ] `/classes` GET 정상 렌더링
- [ ] `class_type` 탭 필터 (free/premium)
- [ ] `category` 카테고리 필터
- [ ] 페이지네이션 (9개/페이지)
- [ ] 판매 종료 강의 목록에서 미노출 (`sale_end_at` 필터)

### 강의 상세
- [ ] `/classes/{class_idx}` GET
- [ ] 탭: 강의 소개 / Q&A / 후기
- [ ] 수강 여부에 따른 버튼 분기 (수강신청/구매/수강하기)
- [ ] 좋아요 토글 API
- [ ] 찜 토글 API

### 챕터 시스템
- [ ] `lc_class_chapter` 테이블 생성
- [ ] 강의 상세에서 챕터 목록 렌더링 (vimeo_id 제외)
- [ ] VOD 플레이어에서 챕터 목록 + vimeo_id 반환 (수강 권한 확인 후)

### VOD 플레이어
- [ ] `/classes/{class_idx}/learn` 수강 권한 확인
- [ ] `expire_at` 유효성 확인
- [ ] Vimeo iframe embed 정상 렌더링
- [ ] 챕터 클릭 → Vimeo JS API seek
- [ ] 강의자료 파일 다운로드 (수강자만)
- [ ] 오픈채팅 버튼 → 클릭 로그 기록

### 진도 추적
- [ ] `POST /api/progress/update` 구현
- [ ] 챕터 중복 완료 카운트 방지
- [ ] `rate >= 80` → `is_certified = 1` 자동 전환
- [ ] 만료 강의 진도 업데이트 차단

### 검색
- [ ] `GET /api/search?q=` UNION 쿼리
- [ ] 강사명 매칭 → 해당 강사 클래스 전체
- [ ] 클래스명 직접 매칭
- [ ] 키워드 `<mark>` 하이라이트
- [ ] 결과 없음 상태 + 추천 검색어
- [ ] `lc_search_log` INSERT

---

## Phase 4 — 결제

### 무료강의 신청
- [ ] 3단계 플로우 (신청확인→동의→완료)
- [ ] `lc_enroll` INSERT (expire_at=NULL)
- [ ] Google Sheets API 연동
- [ ] API 실패 시 로그만 기록, 신청 유지

### Toss Payments 연동
- [ ] `TOSS_CLIENT_KEY`, `TOSS_SECRET_KEY` 환경변수 설정
- [ ] Toss JS SDK 정상 로드
- [ ] 주문 생성 `lc_order` INSERT (status=pending)
- [ ] `tossPayments.requestPayment()` 호출
- [ ] `/checkout/success` 금액 위변조 검증
- [ ] Toss 승인 API `POST /v1/payments/confirm` 호출
- [ ] `lc_order` paid 처리 + `lc_enroll` INSERT (트랜잭션)
- [ ] 중복 결제 방지 (`orderId` UNIQUE + status 확인)
- [ ] `/checkout/fail` 실패 처리

### 환불
- [ ] 전액/비례 환불 자동 판단 로직
- [ ] 예상 환불금액 계산 표시
- [ ] `lc_refund` INSERT (status=requested)
- [ ] 관리자 승인 → Toss 환불 API 호출
- [ ] `lc_enroll.is_active = 0` 처리

---

## Phase 5 — 마이페이지

### 나의 강의
- [ ] 수강 상태 4단계 분기 (active/expired_extendable/expired_closed/inactive)
- [ ] 진도율 프로그레스바
- [ ] 수강 기간 표시
- [ ] 기간 연장 버튼 (클래스 상세 이동)
- [ ] 오픈채팅 버튼 클릭 로그
- [ ] 페이지네이션 10개/페이지

### 찜목록
- [ ] 찜 강의 그리드
- [ ] 찜 해제 토글

### 결제내역
- [ ] 결제 목록 + 상세
- [ ] 환불 신청 버튼 (조건 충족 시만)
- [ ] 환불 사유 선택 모달

### 후기
- [ ] 유료 결제 완료 강의만 후기 작성 가능
- [ ] 강의당 1회 제한
- [ ] 수정 가능 (본인만)
- [ ] 비정상 접근(미결제) → 403

### 1:1 문의
- [ ] 문의 작성 (카테고리 + 제목 + 내용)
- [ ] 목록/상세 (본인 문의만)

### 정보수정
- [ ] 이름/이메일/연락처 수정
- [ ] 비밀번호 변경 (현재 비밀번호 확인)
- [ ] 프로필 사진 업로드

### 수료증
- [ ] `rate >= 80` 확인 후 PDF 생성
- [ ] 본인 수료증만 접근 가능

### 회원탈퇴
- [ ] 비밀번호 확인
- [ ] 환불 진행 중 탈퇴 차단
- [ ] `mb_status = 'withdrawn'` 처리

---

## Phase 6 — 강사

### 강사 목록/상세
- [ ] 강사 목록 4열 그리드, 12명/페이지
- [ ] 강사 상세: 프로필 + 소개/경력 불릿 + 담당 강의

### 강사 지원
- [ ] 지원 폼 전체 구현
- [ ] 파일 업로드 (pdf/ppt/pptx/jpg/png, 최대 20MB, 3개)
- [ ] 파일 UUID 저장, DocumentRoot 외부 경로
- [ ] MIME 타입 실제 검증 (`finfo`)
- [ ] 중복 제출 방지 (버튼 disabled)
- [ ] 성공 모달

---

## Phase 7 — 고객센터

- [ ] FAQ 목록 + 카테고리 필터 + 아코디언
- [ ] 공지사항 목록 (고정 상단) + 상세
- [ ] 이용약관 / 개인정보처리방침 정적 페이지

---

## Phase 8 — 관리자

- [ ] `/admin` 권한 미들웨어
- [ ] 대시보드 통계
- [ ] 강의 CRUD (챕터 포함)
- [ ] 강사 CRUD
- [ ] 강사 지원 검토/승인/거절
- [ ] 회원 목록/상태 변경
- [ ] 결제 목록/환불 승인
- [ ] FAQ CRUD
- [ ] 공지사항 CRUD
- [ ] 1:1 문의 답변
- [ ] 검색 로그 통계
- [ ] 오픈채팅 클릭 통계
- [ ] 후기 관리 (숨김 처리)

---

## 보안 체크리스트

- [ ] 모든 POST 요청 CSRF 토큰 검증
- [ ] 모든 SQL PDO Prepared Statements 사용
- [ ] 모든 출력 `htmlspecialchars()` 처리
- [ ] 파일 업로드 MIME 실검증
- [ ] 파일 저장 경로 DocumentRoot 외부
- [ ] `mb_idx` 기반 본인 데이터 확인 (URL 조작 방어)
- [ ] Toss 결제 금액 서버 측 재검증
- [ ] `.env` `.gitignore` 등록 확인
- [ ] `display_errors = Off` 운영 환경 적용
- [ ] 세션 로그인 후 `session_regenerate_id(true)`

---

## DB 체크리스트

- [ ] 모든 테이블 `lc_` prefix
- [ ] 외래키 제약 설정
- [ ] 주요 컬럼 인덱스 설정 (`mb_id`, `mb_email`, `class_idx`, `searched_at`)
- [ ] `utf8mb4_unicode_ci` collation 전체 통일
- [ ] 마이그레이션 SQL 파일 `database/` 에 버전 관리
