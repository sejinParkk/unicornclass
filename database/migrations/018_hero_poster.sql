-- ============================================================
-- 018_hero_poster.sql
-- 히어로 배너 포스터 이미지 키 추가
-- ============================================================

INSERT IGNORE INTO lc_site_config (config_key, config_value) VALUES
    ('hero_poster', NULL);
