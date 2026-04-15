# 유니콘클래스
## 기술스택: PHP 8.2 / MariaDB 10.11 / Docker / PSR-4 Composer
## 규칙: Controller는 얇게, 모든 SQL은 Repository만, PDO prepared statements 필수, CSRF 필수, 테이블 prefix lc_
## 참조: docs/00_overview.md, docs/10_routes.md, docs/20_db.md, docs/30_features/, docs/50_policies.md

## 문서 업데이트 규칙
- 기능 추가/수정/삭제 시 관련 docs/ 문서 항상 함께 업데이트
- DB 테이블 변경 시 docs/20_db.md 반드시 업데이트
- 라우트 추가/변경 시 docs/10_routes.md 반드시 업데이트
- API 변경 시 docs/40_api.md 반드시 업데이트

## 사용자 화면 CSS 규칙
- 사용자 화면 CSS는 반드시 `public/assets/css/styles.css`에 작성
- 각 뷰 파일 내 `<style>` 블록 작성 금지
