-- Migration 013: 히어로 배너 동영상 키 추가
-- 실행일: 2026-04-14

INSERT IGNORE INTO lc_site_config (config_key, config_value) VALUES
  ('hero_video', NULL);
