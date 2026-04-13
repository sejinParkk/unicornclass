# 전체 URL 라우트 목록

> HTTP 메서드: GET = 페이지 렌더링, POST = 데이터 처리, API = JSON 응답

## 공개 페이지

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| 메인 | `/` | GET | 히어로 배너, 인기강의, 강사 소개 |
| 회사소개 | `/about` | GET | 회사 비전, 통계, 강사 카테고리 |

---

## 인증

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| 로그인 | `/login` | GET | 로그인 폼 (소셜 로그인 포함) |
| 로그인 처리 | `/login` | POST | 이메일/ID + 비밀번호 인증 |
| 소셜 로그인 (카카오) | `/auth/kakao` | GET | 카카오 OAuth 리다이렉트 |
| 소셜 콜백 (카카오) | `/auth/kakao/callback` | GET | 카카오 OAuth 콜백 처리 |
| 소셜 로그인 (네이버) | `/auth/naver` | GET | 네이버 OAuth 리다이렉트 |
| 소셜 콜백 (네이버) | `/auth/naver/callback` | GET | 네이버 OAuth 콜백 처리 |
| 로그아웃 | `/logout` | POST | 세션 파기 후 메인 이동 |
| 회원가입 | `/register` | GET | 회원가입 폼 |
| 회원가입 처리 | `/register` | POST | 계정 생성 |
| 아이디 찾기 | `/find-id` | GET | 휴대폰 인증 폼 |
| 아이디 찾기 처리 | `/find-id` | POST | SMS 인증 후 아이디 반환 |
| 비밀번호 찾기 | `/find-password` | GET | 이메일 인증 폼 |
| 비밀번호 재설정 | `/find-password` | POST | 새 비밀번호 저장 |

---

## 강의 (클래스)

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| 강의 목록 | `/classes` | GET | 전체 강의, 카테고리·탭 필터, 페이지네이션 |
| 강의 목록 (카테고리) | `/classes?cat={category_idx}` | GET | 카테고리 필터 (lc_class_category.category_idx) |
| 강의 목록 (탭) | `/classes?type={free\|premium}` | GET | 무료/프리미엄 탭 |
| 강의 상세 | `/classes/{class_idx}` | GET | 강의 정보, Q&A, 후기, 결제 사이드바 |
| VOD 플레이어 | `/classes/{class_idx}/learn` | GET | Vimeo 플레이어, 챕터, 강의자료 (수강자 전용) |
| 강의 좋아요 토글 | `/api/classes/{class_idx}/like` | POST | 좋아요 추가/취소 |
| 강의 찜 토글 | `/api/classes/{class_idx}/wish` | POST | 찜 추가/취소 |

---

## 검색

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| 검색 결과 | `/search?q={keyword}` | GET | 강의·강사 통합 검색 결과, 키워드 하이라이트 |

---

## 결제

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| 결제 페이지 | `/checkout/{class_idx}` | GET | 주문 확인, 결제수단 선택 폼 |
| 결제 처리 | `/checkout/{class_idx}` | POST | PG사 결제 요청 |
| 결제 완료 | `/checkout/complete` | GET | 결제 완료 안내 |
| 결제 콜백 (PG) | `/checkout/callback` | POST | PG사 웹훅 수신·검증 |

---

## 강사

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| 강사 목록 | `/instructors` | GET | 강사 그리드, 페이지네이션 (4열, 12명/페이지) |
| 강사 상세 | `/instructors/{instructor_idx}` | GET | 프로필, 경력, 담당 강의 목록 |
| 강사 지원 | `/instructors/apply` | GET | 강사 지원 폼 |
| 강사 지원 처리 | `/instructors/apply` | POST | 지원서 저장, 파일 업로드 |

---

## 고객센터

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| FAQ | `/supports/faqs` | GET | FAQ 아코디언, 카테고리 필터 |
| 공지사항 목록 | `/supports/notices` | GET | 공지사항 목록, 페이지네이션 |
| 공지사항 상세 | `/supports/notices/{notice_idx}` | GET | 공지 상세 내용 |
| 이용약관 | `/supports/terms` | GET | 이용약관 페이지 |
| 개인정보처리방침 | `/supports/privacy` | GET | 개인정보처리방침 |
| 1:1 문의 목록 | `/supports/contact` | GET | 내 문의 목록 (로그인 필요) |
| 1:1 문의 상세 | `/supports/contact/{qna_idx}` | GET | 문의 + 관리자 답변 |
| 1:1 문의 작성 | `/supports/contact/write` | GET | 문의 작성 폼 |
| 1:1 문의 저장 | `/supports/contact/write` | POST | 문의 저장 |

---

## 마이페이지 (로그인 필요)

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| 나의 강의 | `/mypage/my-class` | GET | 수강 목록, 진도율, 기간 만료 상태 |
| 찜목록 | `/mypage/wishlist` | GET | 찜한 강의 그리드 |
| 결제내역 | `/mypage/orders` | GET | 결제 이력, 페이지네이션 |
| 결제내역 상세 | `/mypage/orders/{order_idx}` | GET | 주문 상세 + 환불 신청 |
| 1:1 문의 목록 | `/mypage/qna` | GET | 내 문의 목록 |
| 1:1 문의 상세 | `/mypage/qna/{qna_idx}` | GET | 문의 상세 |
| 후기 목록 | `/mypage/reviews` | GET | 작성한 후기 목록 |
| 후기 작성·수정 | `/mypage/reviews/write` | GET | 후기 폼 |
| 후기 저장 | `/mypage/reviews/write` | POST | 후기 저장 |
| 정보수정 | `/mypage/profile` | GET | 회원정보 수정 폼 |
| 정보수정 처리 | `/mypage/profile` | POST | 회원정보 업데이트 |
| 회원탈퇴 | `/mypage/withdraw` | GET | 탈퇴 확인 폼 |
| 회원탈퇴 처리 | `/mypage/withdraw` | POST | 계정 비활성화 |

---

## API (JSON)

| 엔드포인트 | 메서드 | 설명 |
|------------|--------|------|
| `/api/classes` | GET | 강의 목록 (필터·페이지) |
| `/api/faqs` | GET | FAQ 목록 (`?category=all`) |
| `/api/mypage/classes` | GET | 수강 목록 (`?page=1&limit=10&type=all`) |
| `/api/instructor/apply` | POST | 강사 지원서 제출 |
| `/api/openchat/log` | POST | 오픈채팅 클릭 로그 기록 |
| `/api/member/check-id` | POST | 아이디 중복 체크 |
| `/api/member/send-sms` | POST | SMS 인증번호 발송 |
| `/api/member/verify-sms` | POST | SMS 인증번호 확인 |

---

## 관리자 (`mb_role = 'admin'`)

| 페이지명 | URL | 메서드 | 설명 |
|----------|-----|--------|------|
| 대시보드 | `/admin` | GET | 요약 통계 |
| 관리자 로그인 | `/admin/login` | GET/POST | 관리자 인증 |
| 관리자 로그아웃 | `/admin/logout` | GET | 세션 종료 |
| 강의 목록 | `/admin/classes` | GET | 검색/필터/페이지네이션 |
| 강의 등록 폼 | `/admin/classes/create` | GET | 등록 폼 (챕터·자료 포함) |
| 강의 등록 처리 | `/admin/classes` | POST | 강의 저장 |
| 강의 수정 폼 | `/admin/classes/{class_idx}/edit` | GET | 수정 폼 |
| 강의 수정 처리 | `/admin/classes/{class_idx}` | POST | 강의 업데이트 |
| 강의 삭제 | `/admin/classes/{class_idx}/delete` | POST | 소프트/물리 삭제 |
| 챕터 저장 | `/admin/api/chapters` | POST | JSON API |
| 챕터 순서 변경 | `/admin/api/chapters/reorder` | POST | JSON API |
| 챕터 수정 | `/admin/api/chapters/{idx}/update` | POST | JSON API |
| 챕터 삭제 | `/admin/api/chapters/{idx}/delete` | POST | JSON API |
| 강사 목록 | `/admin/instructors` | GET | 검색/페이지네이션 |
| 강사 등록 폼 | `/admin/instructors/create` | GET | 등록 폼 |
| 강사 등록 처리 | `/admin/instructors` | POST | 강사 저장 |
| 강사 수정 폼 | `/admin/instructors/{instructor_idx}/edit` | GET | 수정 폼 |
| 강사 수정 처리 | `/admin/instructors/{instructor_idx}` | POST | 강사 업데이트 |
| 강사 지원 목록 | `/admin/instructor-apply` | GET | 검색/상태필터 |
| 강사 지원 상세 | `/admin/instructor-apply/{apply_idx}` | GET | 지원서 상세 |
| 강사 지원 승인 | `/admin/instructor-apply/{apply_idx}/approve` | POST | 상태→approved |
| 강사 지원 거절 | `/admin/instructor-apply/{apply_idx}/reject` | POST | 상태→rejected |
| 회원 목록 | `/admin/members` | GET | 검색/페이지네이션 |
| 회원 상세 | `/admin/members/{mb_idx}` | GET | 상세 정보 |
| 회원 상태변경 | `/admin/members/{mb_idx}/status` | POST | active/dormant 전환 |
| 결제 목록 | `/admin/orders` | GET | 검색/페이지네이션 |
| 결제 상세 | `/admin/orders/{order_idx}` | GET | 주문 상세 |
| 환불 승인 | `/admin/orders/{order_idx}/refund/approve` | POST | 환불 처리 |
| 환불 거절 | `/admin/orders/{order_idx}/refund/reject` | POST | 환불 거절 |
| 1:1 문의 목록 | `/admin/contacts` | GET | 검색/페이지네이션 |
| 1:1 문의 상세 | `/admin/contacts/{contact_idx}` | GET | 문의 + 답변 폼 |
| 1:1 문의 답변 | `/admin/contacts/{contact_idx}/answer` | POST | 답변 저장 |
| 공지사항 목록 | `/admin/notices` | GET | 목록 |
| 공지사항 등록 | `/admin/notices/create` | GET/POST | 등록 폼 |
| 공지사항 수정 | `/admin/notices/{notice_idx}/edit` | GET | 수정 폼 |
| 공지사항 수정 처리 | `/admin/notices/{notice_idx}` | POST | 업데이트 |
| 공지사항 삭제 | `/admin/notices/{notice_idx}/delete` | POST | 삭제 |
| FAQ 목록 | `/admin/faqs` | GET | 목록 |
| FAQ 등록 폼 | `/admin/faqs/create` | GET | 등록 폼 |
| FAQ 등록 처리 | `/admin/faqs` | POST | 저장 |
| FAQ 수정 폼 | `/admin/faqs/{faq_idx}/edit` | GET | 수정 폼 |
| FAQ 수정 처리 | `/admin/faqs/{faq_idx}` | POST | 업데이트 |
| FAQ 삭제 | `/admin/faqs/{faq_idx}/delete` | POST | 삭제 |
| 검색 로그 | `/admin/search-logs` | GET | 통계 |
| 오픈채팅 통계 | `/admin/openchat-logs` | GET | 통계 |

### 파일 서빙 라우트

| URL | 설명 |
|-----|------|
| `GET /uploads/class/{filename}` | 강의 썸네일 이미지 서빙 |
| `GET /uploads/instructor/{filename}` | 강사 프로필 사진 서빙 |
| `GET /uploads/materials/{filename}` | 강의 자료 파일 다운로드 (`Content-Disposition: attachment`) |
| `GET /uploads/site/{filename}` | 사이트 로고·파비콘 서빙 |
