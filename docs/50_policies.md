# 보안 정책 / 권한 정책 / OAuth 정책

---

## 1. 보안 정책

### 1-1. 인증 & 세션

| 항목 | 정책 |
|------|------|
| 비밀번호 해시 | `password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12])` |
| 비밀번호 검증 | `password_verify()` |
| 세션 ID 재생성 | 로그인 성공 시 `session_regenerate_id(true)` |
| 세션 만료 | 기본 2시간, 활동 시 갱신 |
| Remember Me | 30일 쿠키 + DB 토큰 (`lc_remember_token` 별도 관리) |
| 세션 탈취 방지 | User-Agent + IP 바인딩 (변경 시 세션 파기) |

### 1-2. CSRF

- **모든 POST 요청에 CSRF 토큰 필수**
- 토큰 생성: `bin2hex(random_bytes(32))` → 세션 저장
- 검증: POST 요청 시 `$_POST['csrf_token']`와 세션 값 비교
- 불일치 시 403 응답
- AJAX POST: `X-CSRF-Token` 헤더 허용

```php
// 토큰 생성 (Core/Csrf.php)
public static function generate(): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

// 토큰 검증
public static function verify(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
```

### 1-3. SQL 인젝션 방지

- **모든 SQL은 Repository에서만 처리**
- **PDO Prepared Statements 100% 사용** (동적 값 직접 삽입 금지)
- ORM 미사용, Raw SQL + PDO

```php
// 올바른 예
$stmt = $pdo->prepare("SELECT * FROM lc_member WHERE mb_id = ?");
$stmt->execute([$mbId]);

// 금지
$pdo->query("SELECT * FROM lc_member WHERE mb_id = '$mbId'"); // 절대 금지
```

### 1-4. XSS 방지

- 모든 출력 값에 `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` 적용
- View에서 `<?= esc($var) ?>` 헬퍼 함수 사용 통일
- `lc_class.description` 등 HTML 필드: 허용 태그 화이트리스트 필터링 후 저장

### 1-5. 파일 업로드 보안

| 항목 | 정책 |
|------|------|
| 허용 확장자 | `pdf`, `ppt`, `pptx`, `jpg`, `jpeg`, `png` |
| 파일 크기 제한 | 최대 20MB |
| 저장 파일명 | UUID로 변경 (`uuid4().ext`) |
| 저장 위치 | `DocumentRoot` 외부 디렉토리 (`/var/www/uploads/`) |
| MIME 검증 | `finfo_file()` 실제 MIME 타입 확인 |
| 직접 접근 차단 | 업로드 디렉토리에 `.htaccess`로 실행 금지 |

### 1-6. 기타

| 항목 | 정책 |
|------|------|
| HTTPS | 운영 환경 필수 (HSTS 설정) |
| HTTP 헤더 | `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN` |
| 에러 노출 | 운영: `display_errors = Off`, `log_errors = On` |
| 환경 변수 | `.env` 파일로 관리, git 제외 |
| 비밀번호 재설정 토큰 | `random_bytes(32)` + 30분 만료 |

---

## 2. 권한 정책

### 2-1. 권한 레벨

| 레벨 | 값 (`mb_role`) | 설명 |
|------|----------------|------|
| 일반 회원 | `user` | 기본 권한 |
| 관리자 | `admin` | 전체 관리 권한 |

### 2-2. 접근 제어

| URL 패턴 | 조건 |
|----------|------|
| `/mypage/*` | 로그인 필수 (`user`, `admin`) |
| `/classes/{idx}/learn` | 로그인 + `lc_enroll` 존재 + 만료일 유효 |
| `/admin/*` | `mb_role = 'admin'` |
| `/instructors/apply` | 로그인 선택 (비로그인 제출 가능, 이메일 확인) |
| `/supports/contact/*` (마이페이지) | 로그인 필수 |

### 2-3. 미들웨어 처리

```php
// Core/Auth.php
public static function requireLogin(): void
{
    if (empty($_SESSION['mb_idx'])) {
        header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

public static function requireAdmin(): void
{
    self::requireLogin();
    if ($_SESSION['mb_role'] !== 'admin') {
        http_response_code(403);
        exit('Forbidden');
    }
}
```

### 2-4. 본인 데이터 확인

- 후기, 문의, 주문 조회 시 `WHERE mb_idx = {세션 mb_idx}` 필수
- URL 조작으로 타인 데이터 접근 불가

---

## 3. OAuth 정책

### 3-1. 카카오 로그인

| 항목 | 값 |
|------|----|
| 인증 URL | `https://kauth.kakao.com/oauth/authorize` |
| 토큰 URL | `https://kauth.kakao.com/oauth/token` |
| 프로필 URL | `https://kapi.kakao.com/v2/user/me` |
| Scope | `profile_nickname`, `account_email` |
| 저장 필드 | `social_provider='kakao'`, `social_id={kakao_id}` |
| state 파라미터 | CSRF 방지용 `bin2hex(random_bytes(16))` 세션 검증 |

**플로우:**
```
1. /auth/kakao → 카카오 인증 URL로 302
2. 카카오 인증 완료 → /auth/kakao/callback?code=...&state=...
3. state 검증 (세션 값과 비교)
4. code로 Access Token 발급
5. Access Token으로 사용자 정보 조회
6. lc_member 조회 (social_id 기준)
   - 존재: 로그인 처리
   - 미존재: 회원 생성 후 로그인
```

### 3-2. 네이버 로그인

| 항목 | 값 |
|------|----|
| 인증 URL | `https://nid.naver.com/oauth2.0/authorize` |
| 토큰 URL | `https://nid.naver.com/oauth2.0/token` |
| 프로필 URL | `https://openapi.naver.com/v1/nid/me` |
| Scope | `name`, `email`, `mobile` |
| 저장 필드 | `social_provider='naver'`, `social_id={naver_id}` |
| state 파라미터 | 카카오와 동일하게 CSRF 방지 |

### 3-3. 소셜 계정 연동 규칙

| 상황 | 처리 |
|------|------|
| 동일 이메일이 이미 일반 계정으로 존재 | 소셜 계정과 병합 허용 (사용자 확인 후) |
| 다른 소셜로 기존 소셜 재로그인 | 각 `social_provider`/`social_id` 쌍으로 독립 관리 |
| 소셜 이메일 없음 | 이메일 입력 폼 추가 표시 후 가입 |
| 탈퇴 계정 소셜 재시도 | "탈퇴한 계정" 안내 팝업 |

### 3-4. 소셜 로그인 보안

- `state` 파라미터: `random_bytes(16)` hex → 세션 저장 → 콜백에서 검증 (불일치 시 400)
- Access Token 서버 사이드에서만 처리 (클라이언트 노출 금지)
- 소셜 API 응답 신뢰 범위: ID, 이메일만 사용 (추가 정보 직접 입력 유도)

---

## 4. 환불 정책

| 조건 | 환불 금액 |
|------|----------|
| 수강 시작 7일 이내 + 수강률 33% 미만 | 전액 환불 |
| 조건 초과 | 잔여 수강 기간 비례 환불 |
| 강의 판매 종료 후 | 환불 신청 불가 |

### 처리 흐름
1. 마이페이지 > 결제내역 > 환불 신청
2. `lc_refund` INSERT (`status='requested'`)
3. 관리자 검토 → 승인 시 PG사 취소 API 호출
4. `lc_order.payment_status = 'refunded'`, `lc_enroll` 비활성화

---

## 5. 개인정보 처리 방침 (요약)

| 항목 | 내용 |
|------|------|
| 수집 항목 | 이름, 이메일, 휴대폰, 결제정보 (PG사 위탁) |
| 보유 기간 | 회원탈퇴 후 30일 (법령에 따라 일부 5년) |
| 제3자 제공 | 결제 PG사, 카카오/네이버 (소셜 로그인) |
| 열람/수정/삭제 | 마이페이지 > 정보수정 또는 1:1 문의 요청 |
| 개인정보 보호책임자 | 관리자 이메일 명기 필요 |
