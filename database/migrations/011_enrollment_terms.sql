-- Migration 011: 강의 신청 팝업용 약관 유형 추가
-- 실행일: 2026-04-14

ALTER TABLE lc_terms
  MODIFY COLUMN type ENUM(
    'terms',
    'privacy',
    'refund',
    'youth',
    'instructor',
    'copyright',
    'cookie',
    'marketing',
    'disclaimer',
    'purchase',
    'ecommerce',
    'privacy_third'
  ) NOT NULL;

INSERT IGNORE INTO lc_terms (type, title, content) VALUES
  ('marketing',      '마케팅 수신 동의',               ''),
  ('disclaimer',     '면책조항',                       ''),
  ('purchase',       '구매 조건 동의',                 ''),
  ('ecommerce',      '전자금융거래 이용약관',           ''),
  ('privacy_third',  '개인정보 제3자 제공 동의 (PG사)', '');
