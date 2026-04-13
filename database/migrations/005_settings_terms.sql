-- Migration 005: 사이트 설정 확장 + 약관 테이블 생성
-- 실행일: 2026-04-09

-- 1. lc_admin에 email 컬럼 추가
ALTER TABLE lc_admin
  ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL AFTER name;

-- 2. lc_site_config에 로고/파비콘/SNS 키 추가
INSERT IGNORE INTO lc_site_config (config_key, config_value, config_label) VALUES
  ('logo',          NULL, '로고 파일명'),
  ('favicon',       NULL, '파비콘 파일명'),
  ('sns_instagram', NULL, 'SNS 인스타그램 URL'),
  ('sns_youtube',   NULL, 'SNS 유튜브 URL'),
  ('sns_facebook',  NULL, 'SNS 페이스북 URL'),
  ('sns_blog',      NULL, 'SNS 블로그 URL');

-- 3. lc_terms 테이블 생성
CREATE TABLE IF NOT EXISTS lc_terms (
  terms_idx  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  type       ENUM('terms','privacy') NOT NULL,
  title      VARCHAR(200)     NOT NULL,
  content    LONGTEXT         NOT NULL DEFAULT '',
  updated_at DATETIME         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (terms_idx),
  UNIQUE KEY uq_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. 초기 약관 행
INSERT IGNORE INTO lc_terms (type, title, content) VALUES
  ('terms',   '이용약관',         ''),
  ('privacy', '개인정보처리방침', '');
