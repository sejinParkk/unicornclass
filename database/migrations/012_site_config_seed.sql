-- Migration 012: lc_site_config 기본 정보 키 시드
-- 실행일: 2026-04-14

INSERT IGNORE INTO lc_site_config (config_key, config_value) VALUES
  ('site_name',    NULL),
  ('company_name', NULL),
  ('ceo_name',     NULL),
  ('business_no',  NULL),
  ('phone',        NULL),
  ('email',        NULL),
  ('address',      NULL),
  ('footer_copy',  NULL);
