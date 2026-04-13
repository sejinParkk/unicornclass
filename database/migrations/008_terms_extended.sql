-- Migration 008: 약관 유형 추가 (환불정책, 청소년보호, 강사약관, 저작권, 쿠키)
-- 실행일: 2026-04-10

ALTER TABLE lc_terms
  MODIFY COLUMN type ENUM(
    'terms',
    'privacy',
    'refund',
    'youth',
    'instructor',
    'copyright',
    'cookie'
  ) NOT NULL;

INSERT IGNORE INTO lc_terms (type, title, content) VALUES
  ('refund',     '환불 정책',       ''),
  ('youth',      '청소년 보호정책', ''),
  ('instructor', '강사 이용약관',   ''),
  ('copyright',  '저작권 정책',     ''),
  ('cookie',     '쿠키 정책',       '');
